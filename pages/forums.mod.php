<?php

############################################################
# Forums
#
class Page extends PageTemplate {
	
	public $title = "Forums";
	
	############################################################
	# List forums
	#
	function _home() {
		global $db, $smarty;
		
		$id = intval($_GET['id']);
		
		if ($id == 0) {
			$q = $db->query("SELECT forums.*, topics.title topic_title, topics.id 
				topic_id, users.id author_id, users.name author FROM forums
			LEFT JOIN topics ON topics.id = latest_topic
			LEFT JOIN users ON users.id = topics.last_post_author
			WHERE lft > 1 ORDER BY lft ASC", __file__, __line__);
			
			if ($q->num_rows == 0) return "No forums exist on this webpage";
			
			$forums = array();
			$stack = array();
			while ($forum = $q->fetch_assoc()) {
				$change = 1;
				while (count($stack) > 0 && $forum['rgt'] > $stack[count($stack) -1]) {
					array_pop($stack);
					$change--;
				}
				
				$forum['depth_change'] = $change;
				$forum['depth'] = count($stack);
				$forums[] = $forum;
				
				$stack[] = $forum['rgt'];
			}
			$smarty->assign('forums', $forums);
			
			
		} else { // Get the topics of the forum
			// make sure forum exists
			$q = $db->query("SELECT name, rgt, closed FROM forums WHERE lft = {$id}", __file__, __line__);
			if ($q->num_rows == 0) return "Forum does not exist";
			$forum = $q->fetch_assoc();
			
			// redirect to forum list if the forum is unpostable
			if ($forum['closed']) {
				$_GET['id'] = 0;
				return $this->_home();
			}
			
			// Get the path
			$q = $db->query("SELECT name, lft FROM forums 
			WHERE lft <= {$id} AND rgt >= {$forum['rgt']} 
			ORDER BY lft ASC", __file__, __line__);
			while ($node = $q->fetch_assoc())
				$path[] = $node;
			
			$smarty->assign_by_ref('forum', $forum);
			$smarty->assign_by_ref('path', $path);
			
			// Select the topics
			$q = $db->query("SELECT topics.*, users.name AS author FROM topics
			INNER JOIN users ON topics.author_id = users.id
			WHERE topics.forum_id = {$id}
			ORDER BY last_post_date DESC", __file__, __line__);
			while ($topic = $q->fetch_assoc())
				$topics[] = $topic;
			
			$smarty->assign_by_ref('topics', $topics);
			return fetch_template('topics.tpl');
		}
		
		
		return fetch_template('forums.tpl');
	}
	
	function _edit() {
		global $db, $user, $smarty;
		if (!$user->logged_in) {
			return "You must be logged in to post here.";
		}
		$id = intval($_GET['id']);
		
		$q = $db->query("SELECT posts.*, forums.lft, forums.rgt, topics.title topic_title
		FROM posts
		INNER JOIN topics ON topics.id = posts.topic_id
		INNER JOIN forums ON forums.lft = topics.forum_id
		WHERE posts.id = {$id}");
		if ($q->num_rows == 0) return "Invalid ID";
		
		$post = $q->fetch_assoc();
		
		/** Check Permsissions */
		//~
		if (!($user->is_admin || $user->id == $post['author_id']))
			return "Permission denied";
		/** */
		
		if ($_POST['form_submit']) {
			
			$body = mysql_real_escape_string($_POST['body']);
			
			$q = $db->query("UPDATE posts SET 
				body = '{$body}',
				edit_date = ".time().",
				edit_by = {$user->id}
			WHERE id = {$id}");
			
			// Go to the post
			header("Location: ?act=forums&f=view&id={$post['topic_id']}#{$post['id']}");
		}
		
		// Get the path
		$q = $db->query("SELECT name, lft FROM forums 
		WHERE lft <= {$post['rgt']} AND rgt >= {$post['rgt']} 
		ORDER BY lft ASC", __file__, __line__);
		while ($node = $q->fetch_assoc())
			$path[] = $node;
		
		$smarty->assign_by_ref('post', $post);
		$smarty->assign_by_ref('path', $path);
		
		return fetch_template('editpost.tpl');
	}
	
	function _deletepost() {
		global $db, $user, $smarty;
		if (!$user->logged_in) {
			return "You must be logged in to delete this.";
		}
		$id = intval($_GET['id']);
		
		$q = $db->query("SELECT posts.*, topics.forum_id, forums.latest_topic forum_latest_topic,
			topics.last_post_date topic_last_post_date, forums.lft, forums.rgt
		FROM posts
		INNER JOIN topics ON posts.topic_id = topics.id
		INNER JOIN forums ON topics.forum_id = forums.lft
		WHERE posts.id = {$id}", __file__, __line__);
		if ($q->num_rows == 0) return "Invalid id";
		
		$post = $q->fetch_assoc();
		$q->close();
		
		if ($post['topic_post']) return "Can not delete root post of topic";
		
		/** Check Permsissions */
		//~
		if (!($user->is_admin || $user->id == $post['author_id']))
			return "Permission denied";
		/** */
		
		/**
		 * 1. Delete Post
		 * 2. Update topic
		 * 		- Lower reply count
		 *		- Update latest post if aplicable
		 * 3. Update Forums
		 *      - Change reply count on whole path
		 *      - Update latest post where applicable
		 * 4. Update User
		 */
		
		$q = $db->query("DELETE FROM posts WHERE id = {$id}",
			__file__, __line__);
		
		// Is the post being deleted the latest one?
		if ($post['post_date'] == $post['topic_last_post_date']) {
			// Get new latest post from topic
			$q = $db->query("SELECT post_date, author_id FROM posts WHERE
			topic_id = {$post['topic_id']}
			ORDER BY post_date DESC LIMIT 1", __file__, __line__);
			list($last_post_date, $last_post_author) = $q->fetch_row();
			$q->close();
		}
		
		$q = $db->query("UPDATE topics SET 
			replies = replies - 1".
			($last_post_author & $last_post_date ? 
			", last_post_date = {$last_post_date},
			last_post_author = {$last_post_author}"
			: "")
		." WHERE id = ".$post['topic_id'], __file__, __line__);
		
		$q = $db->query("UPDATE forums SET 
			replies = replies - 1
		WHERE lft <= {$post['lft']} AND rgt >= {$post['rgt']}"
		, __file__, __line__);
		
		// If the latest post on lowest level form is this one,
		// and we are changing the latest post inside the topic
		// the forums in path need to be checked for a new latest
		if ($post['forum_latest_topic'] == $post['topic_id'] &&
			$post['post_date'] == $post['topic_last_post_date']) 
		{
			// Find the new latest
			$q = $db->query("SELECT id FROM topics 
			WHERE forum_id = {$post['forum_id']}
			ORDER BY last_post_date DESC LIMIT 1", __file__, __line__);
			if ($q->num_rows == 0) {
				$topic_ip = 0;
			} else {
				list($topic_id) = $q->fetch_row();
			}
			$q->close();
			
			$q = $db->query("UPDATE forums SET 
				latest_topic = {$topic_id}
				WHERE latest_topic = {$post['topic_id']}", __file__, __line__);
			
		}
		
	}
	
	function _deletetopic() {
		global $db, $user, $smarty;
		if (!$user->logged_in) {
			return "You must be logged in to delete this.";
		}
		$id = intval($_GET['id']);
		
		$q = $db->query("SELECT topics.*, lft, rgt FROM topics
		INNER JOIN forums ON topics.forum_id = forums.lft
		WHERE topics.id = {$id}");
		if ($q->num_rows == 0) return "Invalid id";
		
		$topic = $q->fetch_assoc();
		$q->close();
		
		/** Check Permsissions */
		//~
		if (!($user->is_admin || $user->id == $topic['author_id']))
			return "Permission denied";
		/** */
		
		/**
		 * 1. Delete the topic
		 * 2. Delete the posts
		 * 3. Update Forums (messy)
		 *     - Decrease counters for all in path
		 *     - Fetch latest topic before current topic
		 *     - Update new latest on nodes in path where latest_topic
		 *			id is same as deleted topic
		 */
		
		$q = $db->query("DELETE FROM topics WHERE 
			id = {$id}", __file__, __line__);
		
		$q = $db->query("DELETE FROM posts WHERE 
			topic_id = {$id}", __file__, __line__);
		
		$q = $db->query("UPDATE forums SET 
			topics = topics - 1,
			replies = replies - ".$topic['replies']."
		WHERE lft <= {$topic['lft']} AND rgt >= {$topic['rgt']}"
		, __file__, __line__);
		
		$q = $db->query("SELECT id FROM topics 
		WHERE forum_id = ".$topic['forum_id']." AND last_post_date <= {$topic['last_post_date']}
		ORDER BY last_post_date DESC LIMIT 1", __file__, __line__);
		
		if ($q->num_rows == 0) { // none
			$new_topic = 0;
		} else {
			list($new_topic) = $q->fetch_row();
		}
		
		$q = $db->query("UPDATE forums SET 
			latest_topic = {$new_topic}
		WHERE lft <= {$topic['lft']} AND rgt >= {$topic['rgt']}
		AND latest_topic = ".$topic['id'], __file__, __line__);
		
		return "Post deleted. (I hope!)";
	}
	
	function _newreply() {
		global $db, $user, $smarty;
		
		if (time() - $_SESSION['heat'] < 10) {
			return "You just posted, wait a bit.";
		}
		
		if (!$user->logged_in) {
			return "You must be logged in to post here.";
		}
		$id = intval($_GET['id']);
		
		// Get the topic
		$q = $db->query("SELECT topics.*, forums.lft, forums.rgt FROM topics 
		INNER JOIN forums ON topics.forum_id = forums.lft
		WHERE id = {$id}", __file__, __line__);
		if ($q->num_rows == 0) {
			return "Invalud id.";
		}
		
		$topic = $q->fetch_assoc();
		
		if ($_POST['form_submit']) {
			
			$body = mysql_real_escape_string($_POST['body']);
			
			if (empty($body)) {
				$smarty->assign('errors',array("Body must be filled out"));
				return $this->_view();
			}
			
			/**
			 * 1. Insert Post
			 * 2. Update Topic
			 * 3. Update Forum
			 * 4. Update User
			 */
			
			$time = time();
			
			$q = $db->query("INSERT INTO posts SET
				author_id = {$user->id},
				post_date = {$time},
				body = '{$body}',
				post_ip = '".getip()."',
				topic_id = {$id}", __file__, __line__);
			
			$insert_id = mysql_insert_id();
			
			$q = $db->query("UPDATE topics SET 
				replies = replies + 1,
				last_post_date = {$time},
				last_post_author = {$user->id}
			WHERE id = {$id}", __file__, __line__);
			
			$q = $db->query("UPDATE forums SET
				replies = replies + 1,
				latest_topic = {$id}
			WHERE lft <= {$topic['lft']} AND rgt >= ".$topic['rgt'], __file__, __line__);
			
			$user->posts++;
			$user->update();
			
			$_SESSION['heat'] = time();
			
			header("Location: ?act=forums&f=view&id={$id}#{$insert_id}"); // forward to topic/post
		}
		
		
	}
	
	############################################################
	# View a topic, and post replies in that topic
	#
	function _view() {
		global $db, $user, $smarty;
		if (!is_numeric($_GET['id'])) return "Invalid id";
		$id = intval($_GET['id']);
		
		/**
		 * 1. Get Topic
		 *   - update view count
		 * 2. Get Posts
		 * 3. Get Path
		 */
		
		$q = $db->query("SELECT topics.*, forums.lft, forums.rgt FROM topics 
		INNER JOIN forums on forums.lft = topics.forum_id
		WHERE topics.id = {$id}", __file__, __line__);
		if ($q->num_rows == 0) return "Invalid id";
		
		$topic = $q->fetch_assoc();
		
		$q = $db->query("UPDATE topics SET views = views + 1 WHERE id = {$id}");
		
		$q = $db->query("SELECT posts.*, users.name author FROM posts 
		INNER JOIN users ON posts.author_id = users.id
		WHERE topic_id = {$id} ORDER BY post_date ASC", __file__, __line__);
		
		while ($post = $q->fetch_assoc()) {
			$post['body'] = bb_code($post['body']);
			$posts[] = $post;
		}
		
		
		// Get the path
		$q = $db->query("SELECT name, lft FROM forums 
		WHERE lft <= {$topic['rgt']} AND rgt >= {$topic['rgt']} 
		ORDER BY lft ASC", __file__, __line__);
		while ($node = $q->fetch_assoc())
			$path[] = $node;
		
		$smarty->assign_by_ref('topic', $topic);
		$smarty->assign_by_ref('posts', $posts);
		$smarty->assign_by_ref('path', $path);
		
		return fetch_template('topic.tpl');
		
	}
	
	function _newtopic() {
		global $db, $user, $smarty;
		
		if (time() - $_SESSION['heat'] < 10) {
			return "You just posted, wait a bit.";
		}
		
		if (!$user->logged_in) {
			return "You must be logged in to post here.";
		}
		$id = intval($_GET['id']);
		// fetch the forum
		$q = $db->query("SELECT name, rgt FROM forums WHERE lft = {$id}", __file__, __line__);
		if ($q->num_rows == 0) return "Forum doesn't exist";
		$forum = $q->fetch_assoc();
		$smarty->assign_by_ref('forum', $forum);
		
		if ($_POST['form_submit']) { // posted the topic
			
			$body = mysql_real_escape_string($_POST['body']);
			$title = mysql_real_escape_string(htmlentities($_POST['title']));
			
			if (empty($title))
				$errors[] = "Title must be filled out";
			
			if (empty($body))
				$errors[] = "Body must be filled out";
			
			if (!empty($errors)) {
				$smarty->assign('errors', $errors);
				unset($_POST['form_submit']);
				return $this->_newtopic();
			}
			
			/**
			 * 1. Insert topic
			 * 2. Insert the post
			 * 3. Update the forums
			 * 4. Update user
			 */
			
			$time = time();
			
			$q = $db->query("INSERT INTO topics SET 
				forum_id = {$id},
				title = '{$title}', 
				author_id = {$user->id},
				post_date = {$time},
				last_post_date = {$time},
				last_post_author = {$user->id}", __file__, __line__);
			
			$topic_id = mysql_insert_id();
			
			$q = $db->query("INSERT INTO posts SET
				title = '{$title}',
				author_id = {$user->id},
				post_date = {$time},
				body = '{$body}',
				post_ip = '".getip()."',
				topic_post = 1,
				topic_id = ".$topic_id, __file__, __line__);
			
			$q = $db->query("UPDATE forums SET
				topics = topics + 1,
				latest_topic = {$topic_id}
			WHERE lft <= {$id} AND rgt >= ".$forum['rgt'], __file__, __line__);
			
			$user->posts++;
			$user->update();
			
			$_SESSION['heat'] = time();
			
			header("Location: ?act=forums&f=view&id={$topic_id}"); // forward to topic/post
			
		}
		
		
		return fetch_template('newtopic.tpl');
	}
	
	function _addforum() {
		global $db, $user;
		
		if (!$user->is_admin) return "Access Denied";
		exit(0);
		
		$parent = "Games";
		$name = "Netplay";
		
		// Get the parent
		$q = $db->query("SELECT rgt FROM forums WHERE name = '{$parent}'");
		if ($q->num_rows == 0) return "Parent not found";
		$parent = $q->fetch_assoc();
		
		// Update left and right of rightmost nodes
		$db->query("UPDATE forums SET rgt = rgt + 2 WHERE rgt >= ".$parent['rgt']);
		$db->query("UPDATE forums SET lft = lft + 2 WHERE lft > ".$parent['rgt']);
		
		// Insert node
		$db->query("INSERT INTO forums SET 
			name = '{$name}',
			lft = ".$parent['rgt'].",
			rgt = ".($parent['rgt'] + 1)."");
		
		return "Looks good";
		
	}
	
	
	function _clear() {
		global $db, $user;
		
		if (!$user->is_admin) return "Access Denied";
		
		$db->query("TRUNCATE posts");
		$db->query("TRUNCATE topics");
		$db->query("UPDATE forums SET
			replies = 0,
			topics = 0,
			latest_topic = 0");
		
	}
	

}


?>
