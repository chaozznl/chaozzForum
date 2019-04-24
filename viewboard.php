<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php
	// board restrictions might apply
	$board = chaozzdb_query ("SELECT * FROM board WHERE id = $board_id");
	if (count($board) == 0) Message($txt[11], $txt[11], true);

	$post_topic_link = ""; // you can not post a topic here as a guest
	
	// logged in, are you allowed on this board
	if (isset($_SESSION['user_id'])) 
	{
		// not allowed to access this board
		if ($board[0]['group_id'] < $_SESSION['group_id']) 
			Message ($txt[11], $txt[48], true);
		
		// allowed here, but can you post
		if ($board[0]['readonly'] == 0 || $_SESSION['group_id'] < $default_group_id) 
			$post_topic_link = "<a href=\"index.php?page=post&action=topic.add&board_id=".intval($board[0]['id'])."\"><img src=\"gfx/button_new-topic.png\" align=\"right\" /></a>";
	}
	
	// get some details from the category
	$category = chaozzdb_query ("SELECT * FROM category WHERE id = {$board[0]['category_id']}"); 
	
	// navigation
	echo "<table class=\"datatable\" width=\"80%\">";
	echo "<caption>";
	echo "<img src=\"gfx/nav.gif\" align=\"left\" />";
	echo $post_topic_link;
	echo "<a href=\"index.php\">".urldecode($settings[0]['forum_name'])."</a> ";
	echo "> <a href=\"index.php\">".urldecode($category[0]['name'])."</a> ";
	echo "> <a href=\"index.php?page=viewboard&board_id=".intval($board[0]['id'])."\">".urldecode($board[0]['name'])."</a></caption>";
	
	for ($loop = 0; $loop < 2; $loop++)
	{
		// first list the sticky topics
		if ($loop == 0)
		{
			$topic = chaozzdb_query ("SELECT * FROM topic WHERE board_id = $board_id ORDER BY sticky DESC"); 
			$topic_count = countValue($topic, "sticky", 1);
			$title = $txt[106];
			$next_page_link = "";
			$prev_page_link = "";
		}
		else
		{
			$start = 0;
			if (isset($_GET['start'])) $start = intval($_GET['start']);
			
			$topics_per_page = intval($settings[0]['topics_per_page']);
			
			$topic = chaozzdb_query ("SELECT * FROM topic WHERE board_id = $board_id ORDER BY update_date DESC LIMIT $start,$topics_per_page"); 
			$topic_count = count($topic);
			
			// previous page link
			if ($start > 0)
			{
				$prev_start = $start - $topics_per_page;
				$prev_page_link = "[<a href=\"page=viewboard&board_id=$board_id&start=$prev_start\">Previous page</a>]";
			}
			
			// next page
			if ($topic_count > $topics_per_page)
			{
				$next_start = $start + $topics_per_page;
				$next_page_link = "[<a href=\"page=viewboard&board_id=$board_id&start=$next_start\">Next page</a>]";
				$topic_count --; // subtract one record, because we queried one too many
			}
			else $next_page_link = "";
			
			
			$title = $txt[107];
		}
		if (count($topic) > 0)
		{
			echo "<tr><th colspan=\"4\" class=\"title\"><strong>$title</strong></th></tr>";
			for ($i = 0; $i < $topic_count; $i++)
			{
				if ($loop == 1)
					if ($topic[$i]['sticky'] == 1) continue; // skip the sticky posts
				
				// number of posts in this topic
				$post = chaozzdb_query ("SELECT * FROM post WHERE topic_id = {$topic[$i]['id']} ORDER BY create_date DESC"); // all posts in this topic, sorted by date DESC
				$num_posts = count($post) -1; // we subtract 1 because the firs post is the topic start post and not a reply
				
				// original topic starter
				$user = chaozzdb_query ("SELECT * FROM user WHERE id = {$topic[$i]['user_id']}"); // who posted this?
				
				echo "<td width=\"25\" class=\"altpost\"><img src=\"gfx/topic.gif\" /></td>";
				echo "<td width=\"*\" class=\"post\">";
				
				if (intval($topic[$i]['sticky']) == 1) echo "<img src=\"gfx/sticky.gif\" align=\"right\" />";
				if (intval($topic[$i]['locked']) == 1) echo "<img src=\"gfx/locked.gif\" align=\"right\" />";
				echo "<a href=\"index.php?page=viewtopic&topic_id=".intval($topic[$i]['id'])."\">".urldecode($topic[$i]['name'])."</a><br /><small>".$txt[38]." ".urldecode($user[0]['name'])."</small></td>";
				echo "<td width=\"100\" class=\"altpost\"><small>".$num_posts." ".$txt[99]."</small>";
				echo "<td width=\"300\" class=\"post\"><small>".$txt[143]." ".Number2Date($post[0]['create_date'])." ".$txt[38]." ".urldecode($user[0]['name'])."</small></td></tr>";
			}
		}
	}		
	if ($prev_page_link != "" && $next_page_link != "") echo "<tr><td colspan=\"4\">$prev_page_link $next_page_link</td></tr>";
	echo "</table>";
?>	