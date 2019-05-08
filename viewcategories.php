<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto">
					<i class="fas fa-folder-open forum-icon"></i>
					<a href="<?php echo $url; ?>"><?php echo urldecode($settings[0]['forum_name']); ?></a>
				</div>
			</div>
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto div-outline">
<?php
	$show_hidden = false; // don't show hidden boards
	if (isset($_SESSION['user_id']) && $_SESSION['group_id'] <3 ) $show_hidden = true; // unless it's an admin/mod
	
	$category = chaozzdb_query ("SELECT * FROM category ORDER BY cat_order ASC");
	for ($i = 0; $i < count($category); $i++)
	{
?>		
					<div class="columns">		
						<div class="column col-12 div-title">
							<i class="fas fa-folder-open forum-icon"></i> <?php echo urldecode($category[$i]['name']); ?>
						</div>
					</div>
<?php			
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
							$last_post_title = htmlentities(urldecode($topic[$k]['name']));
							$last_post_topic_id = intval($topic[$k]['id']);
							$last_post_user_id = intval($topic[$k]['last_poster_id']);
							$last_user = chaozzdb_query ("SELECT * FROM user WHERE id = $last_post_user_id"); // query user table for the name of this user
							$last_post_user = urldecode($last_user[0]['name']);
							$last_post_date = Number2Date($post[0]['create_date']); // convert database date format to readable format
						}				
					}
				}	
				
				// board type
?>				
					<div class="columns">		
						<!-- hidden / read only //-->
						<div class="column col-1 col-sm-2 div-center div-content-left">
<?php								
				if (intval($board[$j]['readonly']) == 1) 
					echo '<i class="fas fa-lock forum-button-content"></i>';
				else 
					echo  '<i class="fas fa-file-alt forum-button-content"></i>';
?>				
						</div>
					
						<!-- board name and description //-->
						<div class="column col-6 col-sm-5 div-content">
							<a href="<?php echo urldecode($settings[0]['url']); ?>/viewboard/board_id/<?php echo intval($board[$j]['id']); ?>.htm"><?php echo urldecode($board[$j]['name']); ?>
<?php									
				if (intval($board[$j]['hidden']) == 1) echo ' <em>('.$txt[32].')</em>';
				if (intval($board[$j]['readonly']) == 1) echo ' <em>('.$txt[31].')</em>';
?>
							</a>
							<br><?php echo urldecode($board[$j]['description']); ?>
						</div>
				
						<!-- topic count //-->
						<div class="column col-2 div-content">
							<?php echo $num_topics; ?> <?php echo $txt[36]; ?><br>
							<?php echo $num_posts; ?> <?php echo $txt[37]; ?>
						</div>
				
						<!-- last post update //-->
						<div class="column col-3 div-content">
							<a href="<?php echo urldecode($settings[0]['url']); ?>/viewtopic/topic_id/<?php echo $last_post_topic_id; ?>.htm"><?php echo $last_post_title; ?></a>
							<br><?php echo $txt[38]; ?> <a href="<?php echo $url; ?>/profile/user_id/<?php echo intval($last_post_user_id); ?>.htm"><?php echo $last_post_user; ?></a> <?php echo $txt[39]; ?> <?php echo $last_post_date; ?>
						</div>
					</div>
<?php					
			}
		}
		
	}
?>	
				</div>
			</div>	
			<br>
			<!-- forum stats //-->
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[40]; ?>
						</div>
					</div>	
					
					<div class="columns">		
						<div class="column col-12 col-mx-auto">
							<div class="columns">		
								<div class="column col-1 col-sm-2 div-center div-content-left">
									<i class="fas fa-chart-pie forum-button-content"></i>
								</div>
								<div class="column col-11 col-sm-10 col-sm-10 div-content">
<?php	
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
	echo $txt[41].': <span class="div-label">'.$last_member.'</span><br />';
	echo $txt[117].': <span class="div-label">'.$num_members.'</span><br />';
	
	// total topics
	$topic = chaozzdb_query ("SELECT id FROM topic");
	$num_topics = count($topic);
	echo $txt[42].': <span class="div-label">'.$num_topics.'</span><br />';
	
	// total posts (-topics)
	$post = chaozzdb_query ("SELECT id, name FROM post");
	$num_posts = count($post)-$num_topics; 
	echo $txt[43].': <span class="div-label">'.$num_posts.'</span><br />';
?>	
								</div>
							</div>
						</div>
					</div>	
				</div>
			</div>	