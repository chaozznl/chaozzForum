<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php if (!isset($_SESSION['user_id']) || $_SESSION['group_id'] >= $default_group_id) include("./footer.php"); ?>
<?php
	$action = !empty($_POST['action']) ? $_POST['action'] : "";
	$category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : 0;

	// for these actions you need a category id
	if ($action == "category.update" || $action == "category.delete" || $action == "board.add") 
		if ($category_id == 0) 
			Message ($txt[11], $txt[157], true); // fatal error, invalid id
	
	// for these actions you need a board id
	if ($action == "board.update" || $action == "board.delete") 
		if ($board_id == 0) 
			Message ($txt[11], $txt[156], true); // fatal error, invalid id
	
	if ($action == "category.add" || $action == "category.update") 
	{
		$category['name'] = !empty ($_POST['cat_name']) ? urlencode($_POST['cat_name']) : "";
		$category['cat_order'] = !empty($_POST['cat_order']) ? intval($_POST['cat_order']) : 0;
		
		if ($category['name'] == "") 
			Message ($txt[11], $txt[131], true); // fatal error, invalid name
		
		if ($action == "category.add")
		{
			chaozzdb_query ("INSERT INTO category VALUES {$category['name']}, {$category['cat_order']}");
			Message ($txt[22], $txt[1], false);
		}
		else
		{
			chaozzdb_query ("UPDATE category SET name = {$category['name']}, cat_order = {$category['cat_order']} WHERE id = $category_id");
			Message ($txt[22], $txt[3], false);
		}
	}
	
	if ($action == "board.add" || $action == "board.update") 
	{
		$board['name'] = !empty($_POST['board_name']) ? urlencode($_POST['board_name']) : "";
		$board['description'] = !empty($_POST['board_description']) ? urlencode($_POST['board_description']) : "";
		$board['board_order'] = !empty($_POST['board_order']) ? intval($_POST['board_order']) : 0;
		$board['group_id'] = !empty(intval($_POST['group_id'])) ? intval($_POST['group_id']) : $default_group_id;
		$board['readonly'] = !empty($_POST['readonly']) ? intval($_POST['readonly']) : 0;
		$board['hidden'] = !empty($_POST['hidden']) ? intval($_POST['hidden']) : 0;
		
		if ($board['name'] == "") 
			Message ($txt[11], $txt[131], true); // fatal error, invalid name
	
	
		if ($action == "board.add") 
		{
			chaozzdb_query ("INSERT INTO board VALUES {$board['name']}, {$board['description']}, $category_id, {$board['board_order']}, {$board['group_id']}, {$board['readonly']}, {$board['hidden']}");
			Message ($txt[22], $txt[4], false);
		}
		else
		{
			chaozzdb_query ("UPDATE board SET name = {$board['name']}, description = {$board['description']}, category_id = $category_id, board_order = {$board['board_order']}, group_id = {$board['group_id']}, readonly = {$board['readonly']}, hidden = {$board['hidden']} WHERE id = $board_id");
			Message ($txt[22], $txt[5], false);
		}
	}
	
	if ($action == "board.delete") 
	{
		// find all topics in this board, then delete all posts in that topic
		$topics = chaozzdb_query ("SELECT id FROM topic WHERE board_id = $board_id");
		if (count($topics) > 0)
			for ($i = 0; $i < count($topics); $i++)
				chaozzdb_query ("DELETE FROM post WHERE topic_id = {$topics[$i]['id']}"); // delete all posts in the topics of this board
		
		chaozzdb_query ("DELETE FROM topic WHERE board_id = $board_id"); // delete all topics in this board
		chaozzdb_query ("DELETE FROM board WHERE id = $board_id"); // delete the board
		Message ($txt[22], $txt[7], false);
	}
	
	if ($action == "category.delete") 
	{
		$board = chaozzdb_query ("SELECT id FROM board WHERE category_id = $category_id");
		if (count($board) > 0)
		{
			for ($i = 0; $i < count($board); $i++)
			{
				$topics = chaozzdb_query ("SELECT id FROM topic WHERE board_id = {$board[$i]['id']}");
				if (count($topics) > 0)
					for ($j = 0; $j < count($topics); $j++)
						chaozzdb_query ("DELETE FROM post WHERE topic_id = {$topics[$j]['id']}"); // delete all posts in the topics of this board
					
				chaozzdb_query ("DELETE FROM topic WHERE board_id = {$board[$i]['id']}"); // delete all topics in this board
				chaozzdb_query ("DELETE FROM board WHERE id = {$board[$i]['id']}"); // delete the board
			}
		}
		chaozzdb_query ("DELETE FROM category WHERE id = $category_id"); // delete the category
		Message ($txt[22], $txt[8], false);
	}
	
	if ($action == "save.settings") 
	{
		$setting['id'] = "1";	// id = 1 record for the settings
		$setting['forum_name'] = urlencode($_POST['forum_title']);
		$setting['url'] = urlencode($_POST['forum_url']);
		$setting['maintenance_mode'] = intval($_POST['maintenance_mode']);
		$setting['topics_per_page'] = intval($_POST['topics_per_page']);
		$setting['posts_per_page'] = intval($_POST['posts_per_page']);
		$setting['language'] = urlencode($_POST['language']);
		$setting['news'] = urlencode($_POST['news']);
		$setting['edit_limit'] = intval($_POST['edit_limit']);
		$setting['guest_view'] = intval($_POST['guest_view']);
		$setting['max_username_length'] = intval($_POST['max_username_length']);
		$setting['max_signature_length'] = intval($_POST['max_signature_length']);
		$setting['max_title_length'] = intval($_POST['max_title_length']);
		$setting['max_post_length'] = intval($_POST['max_post_length']);
		
		if ($setting['edit_limit'] < 0 || $setting['edit_limit'] > 59) 
		{
			Message ($txt[11], $txt[124], true);
		}
		chaozzdb_query ("UPDATE settings SET forum_name = {$setting['forum_name']}, url = {$setting['url']}, maintenance_mode = {$setting['maintenance_mode']}, topics_per_page = {$setting['topics_per_page']}, posts_per_page = {$setting['posts_per_page']}, language = {$setting['language']}, news = {$setting['news']}, edit_limit = {$setting['edit_limit']}, guest_view = {$setting['guest_view']}, max_username_length = {$setting['max_username_length']}, max_signature_length = {$setting['max_signature_length']}, max_title_length = {$setting['max_title_length']}, max_post_length = {$setting['max_post_length']}  WHERE id = {$setting['id']}"); 
		Message ($txt[22], $txt[10], false);
	}
	
	// read the (updated) settings
	$settings = chaozzdb_query ("SELECT * FROM settings WHERE id = 1");
	if (count($settings) == 0) 
	{
		Message ($txt[11], $txt[12], true);
	}
	else 
	{
?>		
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[13]; ?>
						</div>	
					</div>	
					<div class="columns">		
						<div class="column col-12 div-content">
							<form method="POST" action="<?php echo $url; ?>/admin.htm">
								<input type="hidden" name="action" value="save.settings">
								<span class="div-label"><?php echo $txt[14]; ?></span>
								<br>
								<input type="text" name="forum_title" maxlength="40" value="<?php echo urldecode($settings[0]['forum_name']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[15]; ?></span>
								<br>
								<input type="text" name="forum_url"  maxlength="60" value="<?php echo urldecode($settings[0]['url']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[17]; ?></span>
								<br>
								<?php FillPullDown("option", "maintenance_mode", intval($settings[0]['maintenance_mode'])); ?>
								<br>
								<span class="div-label"><?php echo $txt[18]; ?></span>
								<br>
								<input type="text" name="topics_per_page" maxlength="3" value="<?php echo intval($settings[0]['topics_per_page']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[19]; ?></span>
								<br>
								<input type="text" name="posts_per_page" maxlength="3" value="<?php echo intval($settings[0]['posts_per_page']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[121]; ?></span>
								<br>
								<input type="text" name="edit_limit" maxlength="3" value="<?php echo intval($settings[0]['edit_limit']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[146]; ?></span>
								<br>
								<input type="text" name="max_username_length" maxlength="3" value="<?php echo intval($settings[0]['max_username_length']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[147]; ?></span>
								<br>
								<input type="text" name="max_signature_length" maxlength="3" value="<?php echo intval($settings[0]['max_signature_length']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[148]; ?></span>
								<br>
								<input type="text" name="max_title_length" maxlength="3" value="<?php echo intval($settings[0]['max_title_length']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[149]; ?></span>
								<br>
								<input type="text" name="max_post_length" maxlength="3" value="<?php echo intval($settings[0]['max_post_length']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[112]; ?></span>
								<br>
								<select name="language">
<?php								
	if ($dir = @opendir('./lang')) {
		while (($file = readdir($dir)) !== false) {
			if ($file != "." && $file != ".." && $file != "index.php") {
				echo "<option value=\"".$file."\"";
				if ($file == urldecode($settings[0]['language'])) { echo " selected"; }
				echo ">".$file;
			}
		}	  
		closedir($dir);
	}
?>
								</select>
								<br>
								<span class="div-label"><?php echo $txt[116]; ?></span>
								<br>
								<input type="text" name="news" maxlength="150" value="<?php echo urldecode($settings[0]['news']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[98]; ?></span>
								<br>
								<?php FillPullDown("option", "guest_view", intval($settings[0]['guest_view'])); ?>
								<br>
								<span class="div-label"><?php echo $txt[141]; ?></span>
								<br>
								<?php FillPullDown("option", "delete_own_topics", intval($settings[0]['delete_own_topics'])); ?>
								<br>
								<span class="div-label"><?php echo $txt[142]; ?></span>
								<br>
								<?php FillPullDown("option", "delete_own_posts", intval($settings[0]['delete_own_posts'])); ?>
								<br>
								<input type="submit" value="<?php echo $txt[21]; ?>">
							</form>
						</div>
					</div>
				</div>
			</div>	
			<br>
<?php		
	}
	
	$category = chaozzdb_query ("SELECT * FROM category ORDER BY cat_order ASC");
	if (count($category) == 0) 
	{		
		Message($txt[22], $txt[23], false);
	}
	else 
	{
?>
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[25]; ?>
						</div>
					</div>
<?php					
		for ($i = 0; $i < count($category); $i++)
		{
?>
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo urldecode($category[$i]['name']); ?>
						</div>
					</div>
					<div class="columns">		
						<div class="column col-12 div-content">
							<form method="POST" action="<?php echo $url; ?>/admin.htm">
								<input type="hidden" name="action" value="category.update">
								<input type="hidden" name="category_id" value="<?php echo intval($category[$i]['id']); ?>">
								<span class="div-label"><?php echo $txt[24]; ?></span>
								<br>
								<input type="text" name="cat_name" maxlength="50" value="<?php echo urldecode($category[$i]['name']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[26]; ?></span>
								<br>
								<input type="text" name="cat_order" maxlength="2" value="<?php echo intval($category[$i]['cat_order']); ?>">
								<br>
								<input type="submit" value="<?php echo $txt[27]; ?>">
							</form>	
							<form method="POST" action="<?php echo $url; ?>/admin.htm">
								<input type="hidden" name="action" value="category.delete">
								<input type="hidden" name="category_id" value="<?php echo intval($category[$i]['id']); ?>">
								<input type="submit" value="<?php echo $txt[28]; ?>" onclick="return confirm('<?php echo $txt[144]; ?>')">
							</form>	
						</div>
					</div>
<?php					
			// now the  boards
			$board = chaozzdb_query ("SELECT * FROM board WHERE category_id = {$category[$i]['id']} ORDER BY board_order ASC");
			if (count($board) == 0) 
			{		
				Message ($txt[22], $txt[156], false);
			}
			else 
			{
				for ($j = 0; $j < count($board); $j++)
				{
?>					
					<div class="columns">		
						<div class="column col-1 col-sm-2 div-center div-content-left">
<?php								
		if (intval($board[$j]['readonly']) == 1) 
			echo '<i class="fas fa-lock forum-button"></i>';
		else 
			echo  '<i class="fas fa-file-alt forum-button"></i>';
?>				
						</div>
						<!-- board name and description //-->
						<div class="column col-3 col-sm-2 div-content">
							<span class="div-label"><?php echo urldecode($board[$j]['name']); ?></span>
						</div>
						<div class="column col-8 div-content">
							<form method="POST" action="<?php echo $url; ?>/admin.htm">
								<input type="hidden" name="action" value="board.update">
								<input type="hidden" name="board_id" value="<?php echo intval($board[$j]['id']); ?>">
								<span class="div-label"><?php echo $txt[24]; ?></span>
								<br>
								<input type="text" name="board_name" maxlength="50" value="<?php echo urldecode($board[$j]['name']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[158]; ?></span>
								<br>
								<input type="text" name="board_description" maxlength="250" value="<?php echo urldecode($board[$j]['description']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[26]; ?></span>
								<br>
								<input type="text" name="board_order" maxlength="2" value="<?php echo intval($board[$j]['board_order']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[29]; ?></span>
								<br>
								<?php FillPulldown("category", "category_id", intval($board[$j]['category_id'])); ?>
								<br>
								<span class="div-label"><?php echo $txt[30]; ?></span>
								<br>
								<?php FillPulldown("group", "group_id", intval($board[$j]['group_id'])); ?>
								<br>
								<span class="div-label"><?php echo $txt[31]; ?></span>
								<br>
								<?php FillPulldown("option", "readonly", intval($board[$j]['readonly'])); ?>
								<br>
								<span class="div-label"><?php echo $txt[32]; ?></span>
								<br>
								<?php FillPulldown("option", "hidden", intval($board[$j]['hidden'])); ?>
								<br>
								<input type="submit" value="<?php echo $txt[27]; ?>">
							</form>	
							<form method="POST" action="<?php echo $url; ?>/admin.htm">
								<input type="hidden" name="action" value="board.delete">
								<input type="hidden" name="board_id" value="<?php echo intval($board[$j]['id']); ?>">
								<input type="submit" value="<?php echo $txt[28]; ?>" onclick="return confirm('<?php echo $txt[144]; ?>')">
							</form>	
						</div>	
					</div>	
<?php					
				}
			}
		}
?>
				</div>
			</div>
<?php			
	}
?>	
			<br>
			
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[33]; ?>
						</div>
					</div>	
					<div class="columns">		
						<div class="column col-12 div-content">
							<form method="POST" action="<?php echo $url; ?>/admin.htm">
								<input type="hidden" name="action" value="category.add">
								<span class="div-label"><?php echo $txt[24]; ?></span>
								<br>
								<input type="text" name="cat_name" maxlength="50" value="">
								<br>
								<span class="div-label"><?php echo $txt[26]; ?></span>
								<br>
								<input type="text" name="cat_order" maxlength="2" value="">
								<br>
								<input type="submit" value="<?php echo $txt[34]; ?>">
							</form>	
						</div>
					</div>
				</div>
			</div>	
			
			<br>
			
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[35]; ?>
						</div>
					</div>	
					<div class="columns">		
						<div class="column col-12 div-content">
							<form method="POST" action="<?php echo $url; ?>/admin.htm">
								<input type="hidden" name="action" value="board.add">
								<span class="div-label"><?php echo $txt[24]; ?></span>
								<br>
								<input type="text" name="board_name" maxlength="50" value="">
								<br>
								<span class="div-label"><?php echo $txt[158]; ?></span>
								<br>
								<input type="text" name="board_category" maxlength="250" value="">
								<br>
								<span class="div-label"><?php echo $txt[26]; ?></span>
								<br>
								<input type="text" name="board_order" maxlength="2" value="">
								<br>
								<span class="div-label"><?php echo $txt[29]; ?></span>
								<br>
								<?php FillPulldown("category", "category_id", 1); ?> <!-- default category is 1 //-->
								<br>
								<span class="div-label"><?php echo $txt[30]; ?></span>
								<br>
								<?php FillPulldown("group", "group_id", 3); ?> <!-- default group is posters (members) //-->
								<br />
								<span class="div-label"><?php echo $txt[31]; ?></span>
								<br>
								<?php FillPulldown("option", "readonly", 0); ?> <!-- default is not readonly //-->
								<br>
								<span class="div-label"><?php echo $txt[32]; ?></span>
								<br>
								<?php FillPulldown("option", "hidden", 0); ?> <!-- default is not hidden //-->
								<br>
								<input type="submit" value="<?php echo $txt[34]; ?>">
							</form>	
						</div>
					</div>
				</div>
			</div>	