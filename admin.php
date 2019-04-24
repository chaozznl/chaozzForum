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
		$board['board_order'] = !empty($_POST['board_order']) ? intval($_POST['board_order']) : 0;
		$board['group_id'] = !empty(intval($_POST['group_id'])) ? intval($_POST['group_id']) : $default_group_id;
		$board['readonly'] = !empty($_POST['readonly']) ? intval($_POST['readonly']) : 0;
		$board['hidden'] = !empty($_POST['hidden']) ? intval($_POST['hidden']) : 0;
		
		if ($board['name'] == "") 
			Message ($txt[11], $txt[131], true); // fatal error, invalid name
	
	
		if ($action == "board.add") 
		{
			chaozzdb_query ("INSERT INTO board VALUES {$board['name']}, $category_id, {$board['board_order']}, {$board['group_id']}, {$board['readonly']}, {$board['hidden']}");
			Message ($txt[22], $txt[4], false);
		}
		else
		{
			chaozzdb_query ("UPDATE board SET name = {$board['name']}, category_id = $category_id, board_order = {$board['board_order']}, group_id = {$board['group_id']}, readonly = {$board['readonly']}, hidden = {$board['hidden']} WHERE id = $board_id");
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
		echo '	
		<table class="datatable" width="70%">
			<form method="POST" action="index.php?page=admin">
				<input type="hidden" name="action" value="save.settings">
				<caption>'.$txt[13].'</caption>
				<tr><th>'.$txt[14].'</th><td class="post"><input type="text" name="forum_title" size="40" maxlength="40" value="'.urldecode($settings[0]['forum_name']).'"></td></tr>
				<tr><th>'.$txt[15].'</th><td class="post"><input type="text" name="forum_url" size="60" maxlength="60" value="'.urldecode($settings[0]['url']).'"></td></tr>
				<tr><th>'.$txt[17].'</th><td class="post">'; FillPullDown("option", "maintenance_mode", intval($settings[0]['maintenance_mode'])); echo'</td></tr>
				<tr><th>'.$txt[18].'</th><td class="post"><input type="text" name="topics_per_page" size="3" maxlength="3" value="'.intval($settings[0]['topics_per_page']).'"></td></tr>
				<tr><th>'.$txt[19].'</th><td class="post"><input type="text" name="posts_per_page" size="3" maxlength="3" value="'.intval($settings[0]['posts_per_page']).'"></td></tr>
				<tr><th>'.$txt[121].'</th><td class="post"><input type="text" name="edit_limit" size="3" maxlength="3" value="'.intval($settings[0]['edit_limit']).'"></td></tr>
				<tr><th>'.$txt[146].'</th><td class="post"><input type="text" name="max_username_length" size="3" maxlength="3" value="'.intval($settings[0]['max_username_length']).'"></td></tr>
				<tr><th>'.$txt[147].'</th><td class="post"><input type="text" name="max_signature_length" size="3" maxlength="3" value="'.intval($settings[0]['max_signature_length']).'"></td></tr>
				<tr><th>'.$txt[148].'</th><td class="post"><input type="text" name="max_title_length" size="3" maxlength="3" value="'.intval($settings[0]['max_title_length']).'"></td></tr>
				<tr><th>'.$txt[149].'</th><td class="post"><input type="text" name="max_post_length" size="3" maxlength="3" value="'.intval($settings[0]['max_post_length']).'"></td></tr>
				<tr><th>'.$txt[112].'</th><td class="post"><select name="language">';
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
				echo '</select></td></tr>
				<tr><th>'.$txt[116].'</th><td class="post"><input type="text" name="news" size="50" maxlength="150" value="'.urldecode($settings[0]['news']).'"></td></tr>
				<tr><th>'.$txt[98].'</th><td class="post">'; FillPullDown("option", "guest_view", intval($settings[0]['guest_view'])); echo'</td></tr>
				<tr><th>'.$txt[141].'</th><td class="post">'; FillPullDown("option", "delete_own_topics", intval($settings[0]['delete_own_topics'])); echo'</td></tr>
				<tr><th>'.$txt[142].'</th><td class="post">'; FillPullDown("option", "delete_own_posts", intval($settings[0]['delete_own_posts'])); echo'</td></tr>
				<tr><td colspan="2" class="altpost"><div align="center"><input type="submit" value="'.$txt[21].'"></div></td></tr>
			</form>
		</table>
		<br />
		<br />';
	}
	
	$category = chaozzdb_query ("SELECT * FROM category ORDER BY cat_order ASC");
	if (count($category) == 0) 
	{		
		Message($txt[22], $txt[23], false);
	}
	else 
	{
		echo '
			<table class="datatable" width="70%">
				<caption>'.$txt[25].'</caption>';
		for ($i = 0; $i < count($category); $i++)
		{
			echo '
				<tr><th colspan="2">'.urldecode($category[$i]['name']).'</th><td class="post">
					<form method="POST" action="index.php?page=admin">
						<input type="hidden" name="action" value="category.update">
						<input type="hidden" name="category_id" value="'.intval($category[$i]['id']).'">
						'.$txt[24].' <input type="text" name="cat_name" size="50" maxlength="50" value="'.urldecode($category[$i]['name']).'"><br />
						'.$txt[26].' <input type="text" name="cat_order" size="2" maxlength="2" value="'.intval($category[$i]['cat_order']).'"><br>
						<input type="submit" value="'.$txt[27].'">
					</form>	
					<form method="POST" action="index.php?page=admin">
						<input type="hidden" name="action" value="category.delete">
						<input type="hidden" name="category_id" value="'.intval($category[$i]['id']).'">
						<input type="submit" value="'.$txt[28].'">
					</form>	
				</td></tr>
				';
			
			// now the  boards
			$board = chaozzdb_query ("SELECT * FROM board WHERE category_id = {$category[$i]['id']} ORDER BY board_order ASC");
			if (count($board) == 0) 
			{		
				echo '<tr><td width="50"><img src="gfx/normal.gif" /></td><td>No boards found</td><td>&nbsp;</td></tr>'; 
			}
			else 
			{
				for ($j = 0; $j < count($board); $j++)
				{
					echo '
					<tr><td width="50" class="altpost"><img src="gfx/normal.gif" /></td><td class="title">'.urldecode($board[$j]['name']).'</td><td class="post">
						<form method="POST" action="index.php?page=admin">
							<input type="hidden" name="action" value="board.update">
							<input type="hidden" name="board_id" value="'.intval($board[$j]['id']).'">
							'.$txt[24].' <input type="text" name="board_name" size="50" maxlength="50" value="'.urldecode($board[$j]['name']).'"><br />
							'.$txt[26].' <input type="text" name="board_order" size="2" maxlength="2" value="'.intval($board[$j]['board_order']).'"><br />
							'.$txt[29];
							FillPulldown("category", "category_id", intval($board[$j]['category_id']));
							echo '
							<br />
							'.$txt[30];
							FillPulldown("group", "group_id", intval($board[$j]['group_id']));
							echo '
							<br />
							'.$txt[31];
							FillPulldown("option", "readonly", intval($board[$j]['readonly']));
							echo '
							<br />
							'.$txt[32];
							FillPulldown("option", "hidden", intval($board[$j]['hidden']));
							echo '
							<br>
							<input type="submit" value="'.$txt[27].'">
						</form>	
						<form method="POST" action="index.php?page=admin">
							<input type="hidden" name="action" value="board.delete">
							<input type="hidden" name="board_id" value="'.intval($board[$j]['id']).'">
							<input type="submit" value="'.$txt[28].'">
						</form>	
					</td></tr>';
				}
			}
		}
			
		echo $chaozzdb_last_error;
		echo '
			</table>
			<br />
			<br />';
	}
	
	echo '
		<table class="datatable" width="40%">
			<caption>'.$txt[33].'</caption>
			<tr><td class="post">
				<form method="POST" action="index.php?page=admin">
					<input type="hidden" name="action" value="category.add">
					'.$txt[24].' <input type="text" name="cat_name" size="50" maxlength="50" value=""><br />
					'.$txt[26].' <input type="text" name="cat_order" size="2" maxlength="2" value="">
					<input type="submit" value="'.$txt[34].'">
				</form>	
			</td></tr>	
		</table>
		<br />
		<br />';	
		
	echo '
		<table class="datatable" width="40%">
			<caption>'.$txt[35].'</caption>
			<tr><td class="post">
							<form method="POST" action="index.php?page=admin">
								<input type="hidden" name="action" value="board.add">
								'.$txt[24].' <input type="text" name="board_name" size="50" maxlength="50" value=""><br />
								'.$txt[26].' <input type="text" name="board_order" size="2" maxlength="2" value=""><br />
								'.$txt[29];
								FillPulldown("category", "category_id", 1); // default category is 1
								echo '
								<br />
								'.$txt[30];
								FillPulldown("group", "group_id", 3); // default group is posters (members)
								echo '
								<br />
								'.$txt[31];
								FillPulldown("option", "readonly", 0); // default is not readonly
								echo '
								<br />
								'.$txt[32];
								FillPulldown("option", "hidden", 0); // default is not hidden
								echo '
								<input type="submit" value="'.$txt[34].'">
							</form>	
			</td></tr>	
		</table>
		<br />
		<br />';	
?>
