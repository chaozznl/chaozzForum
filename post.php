<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php if (!isset($_SESSION['user_id'])) Message ($txt[11], $txt[94], true); ?>
<?php
	$caption = "";
	$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : "";
	
	// some actions are staff only functions
	if ($action == "topic.move" || $action == "topic.lock" || $action == "topic.unlock" || $action == "topic.sticky") 
		if ($_SESSION['group_id'] == $default_group_id)
			Message($txt[11], $txt[49], true);
	
	// check board
	if ($board_id > 0)
	{
		$board = chaozzdb_query ("SELECT * FROM board WHERE id = $board_id");
		if (count($board) == 0)
			Message ($txt[11], $txt[156], true); // board not found
		
		// staff board but normal user?? accesss denied
		if ($board[0]['group_id'] < $_SESSION['group_id'])
			Message ($txt[11], $txt[48], true);
	}
		
	// check topic
	if ($topic_id > 0)
	{	
		$topic = chaozzdb_query ("SELECT * FROM topic WHERE id = $topic_id");
		if (count($topic) == 0)
			Message ($txt[11], $txt[51], true); // invalid topic
		
		// locked topic? accesss denied
		if ($action == "topic.edit" || $action == "topic.save" || $action == "topic.delete" || $action == "post.edit" || $action == "post.save" || $action == "post.delete")
			if ($topic[0]['locked'] && $_SESSION['group_id'] >= $default_group_id)
				Message ($txt[11], $txt[48], true);
	}
		
	// check post
	if ($post_id > 0)
	{
		$post = chaozzdb_query ("SELECT * FROM post WHERE id = $post_id");
		if (count($post) == 0)
			Message ($txt[11], $txt[52], true); // invalid toppostic
	}		
	
	if ($action == "topic.move") 
	{
		// if board_id = 0 error
		$result = chaozzdb_query ("UPDATE topic SET board_id = $board_id WHERE id = $topic_id");
		Message ($txt[22], $txt[50], true);
	}
	
	if ($action == "topic.delete") 
	{
		// not your topic, or you can not delete your own topic is set, and you're not staff? access denied
		if (($topic[0]['user_id'] != $_SESSION['user_id'] || intval($settings[0]['delete_own_topics']) == 0) && $_SESSION['group_id'] == $default_group_id)
			Message ($txt[11], $txt[48], true);

		$board_id = intval($topic[0]['board_id']);
		chaozzdb_query ("DELETE FROM post WHERE topic_id = $topic_id");
		chaozzdb_query ("DELETE FROM topic WHERE id = $topic_id");
		
		include($lang_file); // update lang file with board_id
		Message ($txt[22], $txt[59], true);
	}
	
	
	if ($action == "topic.edit") 
	{
		// trying to edit not your own post when not and admin/mod
		if ($_SESSION['user_id'] != $topic[0]['user_id'] && $_SESSION['group_id'] == $default_group_id) 
			Message($txt[11], $txt[49], true);
		
		// let's check if you are within the editing limits, -1 means edit unlimited
		if ($_SESSION['group_id'] >= $default_group_id)
		{
			$edit_limit = $settings[0]['edit_limit'];
			if (MinutesDiff($now, $topic[0]['create_date']) > $edit_limit)
				Message ($txt[11], $txt[123].$settings[0]['edit_limit'].$txt[129], true);
		}
		
		$post = chaozzdb_query ("SELECT * FROM post WHERE topic_id = $topic_id");
		if (count($post) == 0)
			Message ($txt[11], $txt[52], true);

		$post_id = intval($post[0]['id']);
		$post[0]['name'] = br2nl(urldecode($post[0]['name'])); // convert <br> to newline for the text area in the form
		$caption = $txt[69];
		$post_action = "topic.save";
	}
	
	if ($action == "post.edit") 
	{	
		// trying to edit not your own post when not and admin/mod
		if ($_SESSION['user_id'] != $post[0]['user_id'] && $_SESSION['group_id'] == $default_group_id) 
			Message($txt[11], $txt[49], true);
		
		// let's check if you are within the editing limits, -1 means edit unlimited
		if ($_SESSION['group_id'] >= $default_group_id)
		{
			$edit_limit = $settings[0]['edit_limit'];
				if (MinutesDiff($now, $post[0]['create_date']) > $edit_limit)
					Message ($txt[11], $txt[123].$settings[0]['edit_limit'].$txt[129], true);
		}
		
		$post_id = intval($post[0]['id']);
		$topic_id = intval($post[0]['topic_id']);
		$post[0]['name'] = br2nl(urldecode($post[0]['name'])); // convert <br> to newline for the text area in the form
		$caption = $txt[70];
		$post_action = "post.save";

		$topic = chaozzdb_query ("SELECT * FROM topic WHERE id = $topic_id");
		if (count($topic) == 0)
			Message ($txt[11], $txt[51], true);

	}
		
	if ($action == "topic.add") 
	{
		$caption = $txt[119];
		$post_action = "topic.save";
	}
	
	if ($action == "post.add") 
	{
		$caption = $txt[120];
		$post_action = "post.save";
	}
	
	if ($action == "topic.save") 
	{
		if (empty($_POST['title']) || empty($_POST['postmessage'])) 
			Message($txt[11], $txt[53], true);
		
		// bypass max_input_vars limitation
		$postdata = file_get_contents('php://input'); // contains post data: key1=value1&key2=value2 etc
		$postdata = explode("&", $postdata); // explode all key=value pairs into an array
		
		for ($i = 0; $i < count($postdata); $i++)
		{
			$postvalue = explode("=", $postdata[$i]); // explode postdata into key/value
			$key = trim($postvalue[0]);
			$value = trim($postvalue[1]);
			$postarray[$key] = $value; // create a new key/value array
		}
		
		$save['topic_id'] = $topic_id;
		$save['topic_title'] = $postarray['title'];
		$save['post_message'] = $postarray['postmessage'];
		//$save['topic_title'] = urlencode($_POST['title']);
		//$save['post_message'] = urlencode($_POST['postmessage']);
		
		// check title length
		if (strlen($save['topic_title']) < 1 || strlen($save['topic_title']) > intval($settings[0]['max_title_length']))
			Message ($txt[11], $txt[152], true);
		
		// check post length
		if (strlen($save['post_message']) < 1 || strlen($save['post_message']) > intval($settings[0]['max_post_length']))
			Message ($txt[11], $txt[153], true);
		
		if ($topic_id == 0) 
		{
			$topic_id = chaozzdb_query ("INSERT INTO topic VALUES ({$save['topic_title']}, $user_id, $board_id, 0, 0, $now, $now, {$_SESSION['name']})"); // create topic
			$result = chaozzdb_query ("INSERT INTO post VALUES ({$save['post_message']}, $topic_id, $user_id, $now, 0)"); // and add the post
			include($lang_file); // update lang file with new topic id
			Message ($txt[22], $txt[54], true);
		}
		else 
		{
			$result = chaozzdb_query ("UPDATE topic SET name = {$save['topic_title']} WHERE id = $topic_id");
			$result = chaozzdb_query ("UPDATE post SET name = {$save['post_message']}, update_date = $now WHERE id = $post_id");
			Message ($txt[22], $txt[55], true);
		}
	}
	
	if ($action == "post.save") 
	{
		if (empty($_POST['postmessage'])) 
			Message($txt[11], $txt[56], true);

		// bypass max_input_vars limitation
		$postdata = file_get_contents('php://input'); // contains post data: key1=value1&key2=value2 etc
		$postdata = explode("&", $postdata); // explode all key=value pairs into an array
		
		for ($i = 0; $i < count($postdata); $i++)
		{
			$postvalue = explode("=", $postdata[$i]); // explode postdata into key/value
			$key = trim($postvalue[0]);
			$value = trim($postvalue[1]);
			$postarray[$key] = $value; // create a new key/value array
		}
		$save['post_message'] = $postarray['postmessage'];
		//$save['post_message'] = urlencode($_POST['postmessage']);
		
		// check post length
		if (strlen($save['post_message']) < 1 || strlen($save['post_message']) > intval($settings[0]['max_post_length']))
			Message ($txt[11], $txt[153], true);
		
		if ($post_id == 0) 
		{
			$result = chaozzdb_query ("INSERT INTO post VALUES ({$save['post_message']}, $topic_id, $user_id, $now, 0)"); // and add the post
			$result = chaozzdb_query ("UPDATE topic SET update_date = $now, last_poster_id = {$_SESSION['user_id']} WHERE id = $topic_id"); // update the topic
			Message ($txt[22], $txt[57], true);
		}
		else 
		{
			$result = chaozzdb_query ("UPDATE post SET name = {$save['post_message']}, update_date = $now WHERE id = $post_id");
			Message ($txt[22], $txt[58], true);
		}
	}
	
	if ($action == "post.delete") 
	{
		// not your post, or you can not delete your own post is set, and you're not staff? access denied
		if (($post[0]['user_id'] != $_SESSION['user_id'] || intval($settings[0]['delete_own_posts']) == 0) && $_SESSION['group_id'] == $default_group_id)
			Message($txt[11], $txt[49], true);
		
		$topic_id = $post[0]['topic_id']; // get the topic id, so we can go back to the topic after deleting it
		include($lang_file); // update lang file with topic_id
		
		$result = chaozzdb_query ("DELETE FROM post WHERE id = $post_id");
		Message ($txt[22], $txt[60], true);
	}
	
	if ($action == "topic.lock" || $action == "topic.unlock") 
	{
		if ($action == "topic.lock") $locked = 1;
		else $locked = 0;
		
		$result = chaozzdb_query ("UPDATE topic SET locked = $locked WHERE id = $topic_id");

		if ($action == "topic.lock") 
			Message ($txt[22], $txt[62], true);
		else 
			Message ($txt[22], $txt[64], true);
	}
	
	if ($action == "topic.sticky" || $action == "topic.unsticky") {
		if ($action == "topic.sticky") $sticky = 1;
		else $sticky = 0;
		
		$result = chaozzdb_query ("UPDATE topic SET sticky = $sticky WHERE id = $topic_id");

		if ($action == "topic.sticky") 
			Message ($txt[22], $txt[66], true);
		else 
			Message ($txt[22], $txt[68], true);
	}
	
?>	
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $caption; ?>
						</div>
					</div>	
					<div class="columns">		
						<div class="column col-12 div-content">
							<form method="POST" action="<?php echo $url; ?>/post.htm" name="postform">
<?php
		if ($action == "topic.add" || $action == "post.add")
		{
			$topic[0]['name'] = "";
			$post[0]['name'] = "";
		}
		
		if ($action == "topic.edit" || $action == "topic.add") 
		{
?>
							<span class="div-label"><?php echo $txt[71]; ?></span>
							<br>
							<input type="text" maxlength="<?php echo intval($settings[0]['max_title_length']); ?>" name="title" value="<?php echo urldecode($topic[0]['name']); ?>" autofocus>
							<br>
<?php							
		}
?>		
							<input type="hidden" name="action" value="<?php echo $post_action; ?>">
							<input type="hidden" name="board_id" value="<?php echo $board_id; ?>">
							<input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
							<input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

							<span class="div-label"><?php echo $txt[72]; ?></div>
							<br>							
							<textarea name="postmessage" rows="15" maxlength="<?php echo intval($settings[0]['max_post_length']); ?>"><?php echo urldecode($post[0]['name']); ?></textarea>
							<br>
<?php
		foreach($smiles as $smile=>$image)
		{
			echo '<a href="javascript:void(0);" onclick="replaceText(\' '.$smile.'\', document.forms.postform.postmessage); return false;"><i class="'.$image.' forum-smiley"></i></a>';
		}
?>			
							<a href="javascript:void(0);" onclick="surroundText('[b]', '[/b]', document.forms.postform.postmessage); return false;"><i class="fas fa-bold forum-markup" title="bold"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[u]', '[/u]', document.forms.postform.postmessage); return false;"><i class="fas fa-underline forum-markup" title="underline"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[i]', '[/i]', document.forms.postform.postmessage); return false;"><i class="fas fa-italic forum-markup" title="italic"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[s]', '[/s]', document.forms.postform.postmessage); return false;"><i class="fas fa-strikethrough forum-markup" title="strikethrough"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[url]', '[/url]', document.forms.postform.postmessage); return false;"><i class="fas fa-globe-americas forum-markup" title="url"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[img]', '[/img]', document.forms.postform.postmessage); return false;"><i class="fas fa-image forum-markup" title="image"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[thumb]', '[/thumb]', document.forms.postform.postmessage); return false;"><i class="fas fa-compress forum-markup" title="thumbnail"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[youtube]', '[/youtube]', document.forms.postform.postmessage); return false;"><i class="fab fa-youtube forum-markup" title="youtube"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[quote]', '[/quote]', document.forms.postform.postmessage); return false;"><i class="fas fa-quote-right forum-markup" title="quote"></i></a>
							<a href="javascript:void(0);" onclick="surroundText('[code]', '[/code]', document.forms.postform.postmessage); return false;"><i class="fas fa-file-code forum-markup" title="code"></i></a>
							<br>							
							<input type="submit" value="<?php echo $txt[73]; ?>">
						</div>
					</div>
				</div>
			</div>