<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php
	$topic = chaozzdb_query ("SELECT * FROM topic WHERE id = $topic_id"); 
	if (count($topic) == 0) Message($txt[11], $txt[51], true);
	
	// board restrictions might apply
	$board = chaozzdb_query ("SELECT * FROM board WHERE id = {$topic[0]['board_id']}");
	if (count($board) == 0) Message($txt[11], $txt[51], true);
	
	if ($board[0]['group_id'] != "3") 
		if (isset($_SESSION['user_id']) && $_SESSION['group_id'] > 2) 
			Message ($txt[11], $txt[98], true);
	
	// get some details from the category
	$category = chaozzdb_query ("SELECT * FROM category WHERE id = {$board[0]['category_id']}"); 
	
	// navigation
	echo "<table class=\"datatable\" width=\"80%\">";
	echo "<caption>";
	if (isset($_SESSION['user_id']))
	{
		echo "<img src=\"gfx/nav.gif\" align=\"left\"  />";
		// is the topic unlocked and the board not readonly, or are you staff?
		if ((intval($topic[0]['locked']) == 0 && intval($board[0]['readonly']) == 0 && isset($_SESSION['user_id'])) || (isset($_SESSION['user_id']) && $_SESSION['group_id'] < 3)) 
		{
			echo "<a href=\"index.php?page=post&action=post.add&topic_id=".intval($topic[0]['id'])."\"><img src=\"gfx/button_reply.png\" align=\"right\"  /></a> ";
		}
	}
	echo "<a href=\"index.php\">".urldecode($settings[0]['forum_name'])."</a> ";
	echo "<img src=\"./gfx/arrow.png\" style=\"vertical-align:middle\" >&nbsp;<a href=\"index.php\">".urldecode($category[0]['name'])."</a> ";
	echo "<img src=\"./gfx/arrow.png\" style=\"vertical-align:middle\" >&nbsp;<a href=\"index.php?page=viewboard&board_id=".intval($board[0]['id'])."\">".urldecode($board[0]['name'])."</a></caption>";
	
	$topic = chaozzdb_query ("SELECT * FROM topic WHERE id = $topic_id");
	if (count($topic) == 0) echo "<tr><td colspan=\"2\">".$txt[109]."</td></tr>";
	else 
	{
		// topic title
		echo "<tr><th colspan=\"2\" class=\"title\">";
		
		if (intval($topic[0]['sticky']) == 1) echo "<img src=\"gfx/sticky.gif\" align=\"right\" />";
		if (intval($topic[0]['locked']) == 1) echo "<img src=\"gfx/locked.gif\" align=\"right\" />";
		
		echo "<img src=\"gfx/topic.gif\" align=\"left\" / hspace=\"5\"> <strong>".$txt[110].": ".urldecode($topic[0]['name'])."</strong>";
		
		echo "</th></tr>";
		
		// posts in this topic
		
		
		$start = 0;
		if (isset($_GET['start'])) $start = intval($_GET['start']);
		
		$posts_per_page = intval($settings[0]['posts_per_page']);
		
		$post = chaozzdb_query ("SELECT * FROM post WHERE topic_id = $topic_id ORDER BY create_date ASC LIMIT $start, $posts_per_page");
		$post_count = count($topic);
		
		// previous page link
		if ($start > 0)
		{
			$prev_start = $start - $posts_per_page;
			$prev_page_link = "[<a href=\"page=viewtopic&topic_id=$topic_id&start=$prev_start\">Previous page</a>]";
		}
		
		// next page
		if ($post_count > $posts_per_page)
		{
			$next_start = $start + $posts_per_page;
			$next_page_link = "[<a href=\"page=viewtopic&topic_id=$topic_id&start=$next_start\">Next page</a>]";
			$post_count --; // subtract one record, because we queried one too many
		}
		else $next_page_link = "";
		
		for ($i = 0; $i < count($post); $i++)
		{
			$user = chaozzdb_query ("SELECT * FROM user WHERE id = {$post[$i]['user_id']}");
			
			// date
			echo "<tr><td colspan=\"2\" class=\"date\"><div align=\"right\">";
			echo $txt[111]." ".Number2Date($post[$i]['create_date']);
			echo "</div></td></tr>";
		
			// avatar
			echo "<tr><td class=\"info\" rowspan=\"2\"><strong><a href=\"index.php?page=profile&user_id=".intval($user[0]['id'])."\">".urldecode($user[0]['name'])."</a></strong><br />";
			echo "<img src=\"avatars/".$user[0]['avatar']."\" width=\"100\" height=\"100\" hspace=\"10\" />"; 
			echo "</td>";
			
			echo "<td width=\"100%\" height=\"*\" class=\"post\">";
			
			// are you logged in and either staff or the owner of this topic or post?
			if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $user[0]['id'] || $_SESSION['group_id'] < 3)) 
			{
				// first post, so topic post
				if ($i == 0 && $start == 0) 
				{
					// DELETE TOPIC
					// are you staff, or is delete_own_topics enabled? then show the delete topic button
					if ($_SESSION['group_id'] < 3 || intval($settings[0]['delete_own_topics']) == 1)
					{
						echo "<a href=\"index.php?page=post&action=topic.delete&topic_id=".$topic[0]['id']."\" onclick=\"return confirm('".$txt[144]."')\">";
						echo "<img src=\"gfx/button_remove-topic.png\" align=\"right\" /></a>";
					}
					
					// EDIT TOPIC
					// are you within edit limits?
					$edit_limit_ok = false;
					$edit_limit = intval($settings[0]['edit_limit']); // current edit limit
					$now_time = date($date_format);
					if (MinutesDiff($now_time, $post[$i]['create_date']) < $edit_limit) $edit_limit_ok = true;
					
					if ($_SESSION['group_id'] < 3 || $edit_limit_ok)
					{
						echo "<a href=\"index.php?page=post&action=topic.edit&topic_id=".intval($topic[0]['id'])."\">";
						echo "<img src=\"gfx/button_edit.png\" align=\"right\" /></a>";
					}
				}
				else 
				{
					// DELETE POST
					// are you staff, or is delete_own_posts enabled? then show the delete post button
					if ($_SESSION['group_id'] < 3 || intval($settings[0]['delete_own_posts']) == 1)
					{
						echo "<a href=\"index.php?page=post&action=post.delete&post_id=".intval($post[$i]['id'])."&topic_id=".intval($post[$i]['topic_id'])."\" onclick=\"return confirm('" .$txt[144]."')\">";
						echo "<img src=\"gfx/button_remove-post.png\" align=\"right\" /></a>";
					}
					
					// EDIT POST
					// are you within edit limits?
					$edit_limit_ok = false;
					$edit_limit = intval($settings[0]['edit_limit']); // current edit limit
					$now_time = date($date_format);
					if (MinutesDiff($now_time, $post[$i]['create_date']) < $edit_limit) $edit_limit_ok = true;
					if ($_SESSION['group_id'] < 3 || $edit_limit_ok)
					{
						echo "<a href=\"index.php?page=post&action=post.edit&post_id=".intval($post[$i]['id'])."\">";
						echo "<img src=\"gfx/button_edit.png\" align=\"right\" /></a>";
					}
				}
			}
			
			// the post
			echo ReplaceBBC(nl2br2(urldecode($post[$i]['name'])));
			// signature
			echo "</tr><tr><td width=\"100%\" height=\"15px\"><small>".ReplaceBBC(urldecode($user[0]['signature']))."&nbsp;</small>";
			echo "</td></tr>";
		}
	}	
	if (isset($_SESSION['user_id']) && $_SESSION['group_id'] < 3)
	{
		echo "<tr><td colspan=\"2\">";
		$board = chaozzdb_query ("SELECT * FROM board");
		if (count($board) > 0)
		{
			echo '	<form method="POST" action="index.php?page=post">
						<input type="hidden" name="action" value="topic.move">
						<select name="category_name" style="direction: rtl;">';
					
					for ($i = 0; $i < count($board); $i++)
						echo '<option value="'.intval($board[$i]['id']).'">'.urldecode($board[$i]['name']).'</option>';
			echo '		<input type="submit" value="move" style="direction: rtl;">
					</form>';
		}
		if (intval($topic[0]['locked']) == 0) 
			echo "<a href=\"index.php?page=post&action=topic.lock&topic_id=".$topic[0]['id']."\"><img src=\"gfx/button_lock.png\" align=\"right\" /></a>";
		else
			echo "<a href=\"index.php?page=post&action=topic.unlock&topic_id=".$topic[0]['id']."\"><img src=\"gfx/button_unlock.png\" align=\"right\" /></a>";
	
		if (intval($topic[0]['sticky']) == 0) 
			echo "<a href=\"index.php?page=post&action=topic.sticky&topic_id=".$topic[0]['id']."\"><img src=\"gfx/button_sticky.png\" align=\"right\" /></a>";
		else
			echo "<a href=\"index.php?page=post&action=topic.unsticky&topic_id=".$topic[0]['id']."\"><img src=\"gfx/button_unsticky.png\" align=\"right\" /></a>";
		echo "</td></tr>";
	}
	echo "</table>";
?>	