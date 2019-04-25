<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php
	echo "<table class=\"datatable\" width=\"80%\">";
	echo "<caption><img src=\"gfx/nav.gif\" /> ".urldecode($settings[0]['forum_name'])."</caption>";

	$show_hidden = false; // don't show hidden boards
	if (isset($_SESSION['user_id']) && $_SESSION['group_id'] <3 ) $show_hidden = true; // unless it's an admin/mod
	
	$category = chaozzdb_query ("SELECT * FROM category ORDER BY cat_order ASC");
	for ($i = 0; $i < count($category); $i++)
	{
		echo "<tr><th colspan=\"4\" class=\"title\">".urldecode($category[$i]['name'])."</th></tr>";
		$board = chaozzdb_query ("SELECT * FROM board WHERE category_id = {$category[$i]['id']} ORDER BY board_order ASC");
		if (count($board) == 0) { }
		else 
		{
			for ($j = 0; $j < count($board); $j++)
			{
				if (intval($board[$j]['hidden']) == 1 && !$show_hidden) continue; // hidden board but not allowed to view hidden? skip this board
				$last_post_id = 0;
				$last_post_topic_id = 0;
				$last_post_title = "-";
				$last_post_user = "-";
				$last_post_date = "-";
				
				$topic = chaozzdb_query ("SELECT * FROM topic WHERE board_id = {$board[$j]['id']}");
				if (count($topic) == 0) { $num_topics = 0; $num_posts = 0;}
				else 
				{ 
					$num_topics = count($topic); 
					for ($k = 0; $k < count($topic); $k++) 
					{
						$post = chaozzdb_query ("SELECT * FROM post WHERE topic_id = {$topic[$k]['id']} ORDER BY id DESC"); // last post in topic first
						$num_posts = count($post); 
						if (count($post) > 0) 
						{ 
							$last_post_id = intval($post[0]['id']);
							$last_post_title = urldecode($topic[$k]['name']);
							$last_post_topic_id = intval($topic[$k]['id']);
							$last_post_user = urldecode($topic[$k]['last_poster']);
							$last_post_date = Number2Date($post[0]['create_date']); // convert database date format to readable format
						}				
					}
				}	
				
				// board type
				echo "<tr><td width=\"50\" class=\"altpost\">";
				if (intval($board[$j]['readonly']) == "2") echo "<img src=\"gfx/readonly.gif\" />";
				else echo "<img src=\"gfx/normal.gif\" />";
				echo "</td>";
				
				// hidden/readonly
				echo "<td width=\"*\" class=\"post\"><a href=\"index.php?page=viewboard&board_id=".intval($board[$j]['id'])."\">".urldecode($board[$j]['name']);
				if (intval($board[$j]['hidden']) == "2") echo " <em>(".$txt[32].")</em>";
				if (intval($board[$j]['readonly']) == "2") echo " <em>(".$txt[31].")</em>";
				echo "</a>";
				echo "<br><small>".urldecode($board[$j]['description'])."</small></td>";
				
				// topic count, last post info
				echo "<td width=\"100\" class=\"altpost\"><small>".$num_topics." ".$txt[36]."<br />".$num_posts." ".$txt[37]."</small></td>";
				echo "<td width=\"300\" class=\"post\"><small>".$txt[143]." <a href=\"index.php?page=viewtopic&topic_id=".$last_post_topic_id."\">".$last_post_title."</a><br />".$txt[38]." ".$last_post_user." ".$txt[39]." ".$last_post_date."</small></td></tr>";
			}
		}
		
	}
	echo "</table>";
	echo "<br />";
	echo "<br />";
	echo  "<table class=\"datatable\" width=\"80%\">";
	echo "<caption>".$txt[40]."</caption>";
	echo "<tr><td width=\"50\" class=\"altpost\"><img src=\"gfx/info.gif\" /></td>";
	echo "<td width=\"*\" class=\"post\">";
	
	// last member
	$user = chaozzdb_query ("SELECT id, name FROM user ORDER BY id DESC");
	if (count($user) == 0) 
	{ 
		$last_member = "-"; 
		$num_members = 0;
	}
	else 
	{ 
		$last_member = urldecode($user[0]['name']); 
		$num_members = count($user);
	}	
	echo $txt[41].": <strong>".$last_member."</strong><br />";
	echo $txt[117].": <strong>".$num_members."</strong><br />";
	
	// total topics
	$topic = chaozzdb_query ("SELECT id FROM topic");
	$num_topics = count($topic);
	echo $txt[42].": <strong>".$num_topics."</strong><br />";
	
	// total posts (-topics)
	$post = chaozzdb_query ("SELECT id, name FROM post");
	$num_posts = count($post)-$num_topics; 
	echo $txt[43].": <strong>".$num_posts."</strong><br />";
	echo "</td></tr>";
	echo "</table>";
?>	