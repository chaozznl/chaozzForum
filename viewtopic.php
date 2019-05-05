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
	
	// reply button, if applicable
	$post_reply_link = "";
	if (isset($_SESSION['user_id']))
	{
		// is the topic unlocked and the board not readonly, or are you staff?
		if ((intval($topic[0]['locked']) == 0 && intval($board[0]['readonly']) == 0 && isset($_SESSION['user_id'])) || (isset($_SESSION['user_id']) && $_SESSION['group_id'] < 3)) 
			$post_reply_link = '<a href="'.$url.'/post/action/post.add/topic_id/'.intval($topic[0]['id']).'.htm"><i class="fas fa-plus-square forum-button" title="'.$txt[163].'"></i></a>';
	}	
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
							<?php echo $post_reply_link ?>
						</div>
					</div>
				</div>
			</div>	
<?php	
	$topic = chaozzdb_query ("SELECT * FROM topic WHERE id = $topic_id");
	if (count($topic) == 0)
	{
?>
			<!-- no topics found //-->
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto div-content">
					<?php echo $txt[109]; ?>
				</div>
			</div>	
<?php
	}					
	else 
	{
?>		
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto div-outline">
					<!-- // topic title //-->
					<div class="columns">		
						<div class="column col-12 div-title">
							<i class="fas fa-file-alt forum-icon"></i>
							<?php echo $txt[110]; ?>: <?php echo urldecode($topic[0]['name']); ?>
<?php				
	if (intval($topic[0]['sticky']) == 1) echo '<i class="fas fa-thumbtack forum-icon" title="'.$txt[164].'"></i>';
	if (intval($topic[0]['locked']) == 1) echo '<i class="fas fa-lock forum-icon" title="'.$txt[165].'"></i>';
?>				
						</div>
					</div>	
<?php		
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
			$prev_page_link = '<a href="'.$url.'/viewtopic/topic_id/'.$topic_id.'/start/'.$prev_start.'.htm"><i class="fas fa-arrow-left forum-icon"></a>';
		}
		
		// next page
		if ($post_count > $posts_per_page)
		{
			$next_start = $start + $posts_per_page;
			$next_page_link = '<a href="'.$url.'/viewtopic/topic_id/'.$topic_id.'/start/'.$next_start.'.htm"><i class="fas fa-arrow-right forum-icon"></a>';
			$post_count --; // subtract one record, because we queried one too many
		}
		else $next_page_link = "";
		
		for ($i = 0; $i < count($post); $i++)
		{
			$user = chaozzdb_query ("SELECT * FROM user WHERE id = {$post[$i]['user_id']}");
?>
					<!-- // topic date and options //-->
					<div class="columns">		
						<div class="column col-12 div-topic-options">
							<div class="columns">		
								<!-- topic date //-->
								<div class="column col-7">
									<?php echo $txt[111]; ?> <?php echo Number2Date($post[$i]['create_date']); ?>
								</div>
								
								<!-- options //-->
								<div class="column col-5 div-right">
<?php								
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
?>				
									<a href="<?php echo $url; ?>/post/action/topic.delete/topic_id/<?php echo $topic[0]['id']; ?>.htm" onclick="return confirm('<?php echo $txt[144]; ?>')">
									<i class="fas fa-trash-alt forum-button" title="<?php echo $txt[166]; ?>"></i></a>
<?php									
			}
			
			// EDIT TOPIC
			// are you within edit limits?
			$edit_limit_ok = false;
			$edit_limit = intval($settings[0]['edit_limit']); // current edit limit
			$now_time = date($date_format);
			if (MinutesDiff($now_time, $post[$i]['create_date']) < $edit_limit) $edit_limit_ok = true;
			
			if ($_SESSION['group_id'] < 3 || $edit_limit_ok)
			{
?>				
									<a href="<?php echo $url; ?>/post/action/topic.edit/topic_id/<?php echo intval($topic[0]['id']); ?>.htm">
									<i class="fas fa-edit forum-button" title="<?php echo $txt[69]; ?>"></i></a>
<?php									
			}
		}
		else 
		{
			// DELETE POST
			// are you staff, or is delete_own_posts enabled? then show the delete post button
			if ($_SESSION['group_id'] < 3 || intval($settings[0]['delete_own_posts']) == 1)
			{
?>				
									<a href="<?php echo $url; ?>/post/action/post.delete/post_id/<?php echo intval($post[$i]['id']); ?>.htm" onclick="return confirm('<?php echo $txt[144]; ?>')">
									<i class="fas fa-trash-alt forum-button" title="<?php echo $txt[167]; ?>"></i></a>
<?php									
			}
			
			// EDIT POST
			// are you within edit limits?
			$edit_limit_ok = false;
			$edit_limit = intval($settings[0]['edit_limit']); // current edit limit
			$now_time = date($date_format);
			if (MinutesDiff($now_time, $post[$i]['create_date']) < $edit_limit) $edit_limit_ok = true;
			if ($_SESSION['group_id'] < 3 || $edit_limit_ok)
			{
?>				
									<a href="<?php echo $url; ?>/post/action/post.edit/post_id/<?php echo intval($post[$i]['id']); ?>.htm">
									<i class="fas fa-edit forum-button" title="<?php echo $txt[70]; ?>"></i></a>
<?php									
			}
		}
	}		
?>	
								</div>
							</div>
						</div>
					</div>	
					
					<!-- avatar, post and options //-->
					<div class="columns">		
						<div class="column col-12">
							<div class="columns">		
								<!-- avatar //-->
								<div class="column col-2 div-center div-content-left">
									<a href="<?php echo $url; ?>/profile/user_id/<?php echo intval($user[0]['id']); ?>.htm"><?php echo urldecode($user[0]['name']); ?></a>
									<br>
									<img class="user-avatar img-responsive" src="<?php echo $url; ?>/avatars/<?php echo $user[0]['avatar']; ?>"> 
								</div>
								
								<!-- post //-->
								<div class="column col-10 div-content">
									<?php echo ReplaceBBC(nl2br2(htmlentities(urldecode($post[$i]['name'])))); ?>
								</div>	

							</div>
						</div>	
					</div>	
<?php			
					//ReplaceBBC(urldecode($user[0]['signature']))."&nbsp;</small>";
		}
	
		if (isset($_SESSION['user_id']) && $_SESSION['group_id'] < 3)
		{
?>
					<!-- // admin options //-->
					<div class="columns">		
						<div class="column col-12 div-admin-options">
							<div class="columns">		
								<div class="column col-7">
									&nbsp;
								</div>
								<div class="column col-5 div-right">
<?php		
			$board = chaozzdb_query ("SELECT * FROM board");
			if (count($board) > 0)
			{
?>			
									<form method="POST" action="<?php echo $url; ?>/post.htm">
										<input type="hidden" name="action" value="topic.move">
										<input type="hidden" name="topic_id" value="<?php echo $topic[0]['id']; ?>">
										<select name="board_id">';
<?php					
			for ($i = 0; $i < count($board); $i++)
				echo '<option value="'.intval($board[$i]['id']).'">'.urldecode($board[$i]['name']).'</option>';
?>		
										<input type="submit" value="move">
									</form>
<?php									
			}
			
			if (intval($topic[0]['locked']) == 0) 
				echo '<a href="'.$url.'/post/action/topic.lock/topic_id/'.$topic[0]['id'].'.htm"><i class="fas fa-lock forum-button" title="'.$txt[168].'"></i></a>';
			else
				echo '<a href="'.$url.'/post/action/topic.unlock/topic_id/'.$topic[0]['id'].'.htm"><i class="fas fa-unlock-alt forum-button" title="'.$txt[169].'"></i></a>';
		
			if (intval($topic[0]['sticky']) == 0) 
				echo '<a href="'.$url.'/post/action/topic.sticky/topic_id/'.$topic[0]['id'].'.htm"><i class="fas fa-thumbtack forum-button" title="'.$txt[170].'"></i></a>';
			else
				echo '<a href="'.$url.'/post/action/topic.unsticky/topic_id/'.$topic[0]['id'].'.htm"><i class="fas fa-thumbtack forum-button" title="'.$txt[171].'"></i></a>';
?>		
								</div>
							</div>
						</div>
					</div>	
<?php					
		}
?>
				</div>
			</div>
<?php		
	}
?>	