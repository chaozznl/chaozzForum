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
			$post_topic_link = '<a href="'.$url.'/post/action/topic.add/board_id/'.intval($board[0]['id']).'.htm"><i class="fas fa-plus-square forum-button" title="'.$txt[162].'"></i></a>';
	}
	
	// get some details from the category
	$category = chaozzdb_query ("SELECT * FROM category WHERE id = {$board[0]['category_id']}"); 
?>	
			<!-- // navigation //-->
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto">
					<div class="columns">		
						<div class="column col-7">
							<i class="fas fa-folder-open forum-icon"></i>
							<a href="<?php echo $url; ?>"><?php echo urldecode($settings[0]['forum_name']); ?></a>
							<i class="fas fa-chevron-right forum-icon"></i>
							<a href="<?php echo $url; ?>"><?php echo urldecode($category[0]['name']); ?></a>
							<i class="fas fa-chevron-right"></i>
							<a href="<?php echo $url; ?>/viewboard/board_id/<?php echo intval($board[0]['id']); ?>.htm"><?php echo urldecode($board[0]['name']); ?></a>
						</div>
						<div class="column col-5 div-right">
							<?php echo $post_topic_link ?>
						</div>
					</div>
				</div>
			</div>	
			
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto div-outline">
<?php	
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
				$prev_page_link = '<a href="'.$url.'/viewboard/board_id/'.$board_id.'/start/'.$prev_start.'.htm"><i class="fas fa-arrow-left forum-icon"></i></a>';
			}
			
			// next page
			if ($topic_count > $topics_per_page)
			{
				$next_start = $start + $topics_per_page;
				$next_page_link = '<a href="'.$url.'/viewboard/board_id/'.$board_id.'/start/'.$next_start.'.htm"><i class="fas fa-arrow-right forum-icon"></i></a>';
				$topic_count --; // subtract one record, because we queried one too many
			}
			else $next_page_link = "";
			
			
			$title = $txt[107];
		}
		if (count($topic) > 0)
		{
?>			
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $title; ?>
						</div>
					</div>	
<?php			
			for ($i = 0; $i < $topic_count; $i++)
			{
				if ($loop == 1)
					if ($topic[$i]['sticky'] == 1) continue; // skip the sticky posts
				
				// number of posts in this topic
				$reply = chaozzdb_query ("SELECT * FROM post WHERE topic_id = {$topic[$i]['id']} ORDER BY create_date DESC"); // all posts in this topic, sorted by date DESC
				$num_reply = count($reply) -1; // we subtract 1 because the firs post is the topic start post and not a reply
				
				// original topic starter
				$user = chaozzdb_query ("SELECT * FROM user WHERE id = {$topic[$i]['user_id']}"); // who posted this?
?>
					<div class="columns">		
						<div class="column col-12">
							<div class="columns">		
								<div class="column col-1 div-center div-content-left">
									<i class="fas fa-file-alt forum-icon"></i>
								</div>
								<div class="column col-6 div-content">
<?php				
				if (intval($topic[$i]['sticky']) == 1) echo '<i class="fas fa-thumbtack forum-icon"></i>';
				if (intval($topic[$i]['locked']) == 1) echo '<i class="fas fa-lock forum-icon"></i>';
?>				
									<a href="<?php echo $url; ?>/viewtopic/topic_id/<?php echo intval($topic[$i]['id']); ?>.htm"><?php echo urldecode($topic[$i]['name']); ?></a>
									<?php echo $txt[38]; ?> <?php echo urldecode($user[0]['name']); ?>
								</div>	
								<div class="column col-2 div-content">
									<td width="100" class="altpost"><?php echo $num_reply; ?> <?php echo $txt[99]; ?>
								</div>	
								<div class="column col-3 div-content">
									<td width="300" class="post"><?php $txt[143]; ?> <?php echo Number2Date($reply[0]['create_date']); ?> 
									<?php echo $txt[38]; ?> <?php echo urldecode($user[0]['name']); ?>
								</div>	
							</div>
						</div>
					</div>	
<?php								
			}
		}
	}		
	if ($prev_page_link != "" && $next_page_link != "") 
	{
?>
					<div class="columns">		
						<div class="column col-12">
							<?php echo $prev_page_link; ?> <?php echo $next_page_link; ?>
						</div>
					</div>	
<?php
	}
?>			
				</div>
			</div>	