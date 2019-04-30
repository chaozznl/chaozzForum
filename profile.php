<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php 
	// if no user_id is sent by GET, open your own profile
	$user_id = 0; // guest
	if (!empty($_SESSION['user_id'])) $user_id = intval($_SESSION['user_id']); // if logged in, use the user id
	if (!empty($_REQUEST['user_id'])) $user_id =  intval($_REQUEST['user_id']); // unless a user id is sent via post or get

	$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : "";

	// some actions are staff only functions
	if ($action == "user.delete" || $action == "user.ban" || $action == "user.unban" || $action == "user.addtogroup") 
		if (empty($_SESSION['user_id']) || $_SESSION['group_id'] > 2)
			Message($txt[11], $txt[49], true);
	
	// STAFF ONLY!
	if ($action == "user.delete")
	{	
		$delete_content = isset($_REQUEST['delete_content']) ? true : false; // is the checkbox checked?
		if ($delete_content) 
		{
			$topic = chaozzdb_query ("SELECT * FROM topic WHERE user_id = $user_id");
			$topic_count = count ($topic);
			for ($i = 0; $i < $topic_count; $i++)
			{
				$result = chaozzdb_query ("DELETE FROM topic WHERE id = {$topic[$i]['id']}"); // remove topics from this user
				$result = chaozzdb_query ("DELETE FROM post WHERE topic_id = {$topic[$i]['id']}"); // remove posts on these topics
			}
			$result = chaozzdb_query ("DELETE FROM post WHERE user_id = $user_id"); // remove posts from this user
			$result = chaozzdb_query ("DELETE FROM user WHERE id = $user_id");
		}
		else
		{			
			// we are keeping this persons posts, so lets not remove the user but rename him to deleted_user with a random password
			$random_password = rand_string(8);
			$result = chaozzdb_query ("UPDATE user SET name = deleted_user, password = $random_password, signature = , avatar = default.png WHERE id = $user_id");
		}
		Message ($txt[22], $txt[74], true);
	}
	
	// STAFF ONLY!
	if ($action == "user.ban") 
	{
		$user = chaozzdb_query ("SELECT * FROM user WHERE user_id = $user_id");

		$result = chaozzdb_query ("UPDATE user SET group_id = 4 WHERE id = $user_id"); // group_id 4 = banned
		$result = chaozzdb_query ("INSERT INTO ban VALUES ({$user[0]['ip']})"); // ban ip
		
		Message ($txt[22], $txt[75], true);
	}
	
	// STAFF ONLY!
	if ($action == "user.unban") 
	{
		$user = chaozzdb_query ("SELECT * FROM user WHERE user_id = $user_id");
		
		$result = chaozzdb_query ("UPDATE user SET group_id = 3 WHERE id = $user_id"); // group_id 3 = poster
		$result = chaozzdb_query ("DELETE FROM ban WHERE name = {$user[0]['ip']}"); // unban ip
		
		Message ($txt[22], $txt[76], true);
	}
	
	// STAFF ONLY!
	if ($action == "user.addtogroup") 
	{
		$save['group_id'] = !empty($_REQUEST['group_id']) ? intval($_REQUEST['group_id']) : $default_group_id;

		$result = chaozzdb_query ("UPDATE user SET group_id = {$save['group_id']} WHERE id = $user_id"); 
		Message ($txt[22], $txt[77], true);
	}
	
	// user updated his password
	if ($action == "user.update")
	{
		$save['pass'] 		= !empty($_POST['pass']) ? chaozzdb_password($_POST['pass']) : "";
		$save['avatar'] 	= !empty($_POST['avatar']) ? urlencode($_POST['avatar']) : "";
		$save['signature'] 	= !empty($_POST['signature']) ? urlencode($_POST['signature']) : "";
		
		// are you logged in? if so, is it your profile, or are you perhaps staff? if not, error
		if (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] != $user_id && $_SESSION['group_id'] >= $default_group_id))
			Message ($txt[22], $txt[135], true);
		
		// validate the avatar
		if (preg_match('/[^a-z_\-.0-9]/i', $save['avatar']) || strlen($save['avatar'] > 15))
			$save['avatar'] = "default.png";
		
		// signature length check
		if (strlen($save['signature']) > intval($settings[0]['max_signature_length']))
			Message ($txt[11], $txt[151], true);
		
		// no reason to validate the password, we hash it anyway.
		if ($save['pass'] != "")
			$result = chaozzdb_query ("UPDATE user SET password = {$save['pass']} WHERE id = $user_id");
		if ($save['avatar'] != "")
			$result = chaozzdb_query ("UPDATE user SET avatar = {$save['avatar']} WHERE id = $user_id");
		if ($save['signature'] != "")
			$result = chaozzdb_query ("UPDATE user SET signature = {$save['signature']} WHERE id = $user_id");
		Message ($txt[22], $txt[82], true);
	}
	
	// catch the form values , check them , then save them
	if ($action == "user.register") 
	{
		$save['name'] 		= !empty($_POST['name']) ? urlencode($_POST['name']) : "";
		$save['pass'] 		= !empty($_POST['pass']) ? chaozzdb_password($_POST['pass']) : "";
		$save['group_id'] 	= $default_group_id;
		$save['email']	 	= !empty($_POST['email']) ? urlencode($_POST['email']) : "";
		$save['ip']			= GetUserIP();
		$save['avatar']		= urlencode($_POST['avatar']);
		$save['signature']	= urlencode($_POST['signature']);
		$save['joindate']	= $now;
		$save['captcha']	= !empty($_POST['captcha']) ? $_POST['captcha'] : "";
		
		// check captcha
		if (strtoupper($_SESSION['captcha']['code']) != strtoupper($save['captcha']))
			Message ($txt[11], $txt[136], true);
			
		// validate the username
		if (!ctype_alnum($save['name']))
			Message ($txt[11], $txt[132], true);
		
		// username length check
		if (strlen($save['name']) < 0 || strlen($save['name']) > intval($settings[0]['max_username_length']))
			Message ($txt[11], $txt[150], true);
		
		// signature length check
		if (strlen($save['signature']) > intval($settings[0]['max_signature_length']))
			Message ($txt[11], $txt[151], true);
		
		// validate the avatar
		if ($save['avatar'] == "" || preg_match('/[^a-z_\-.0-9]/i', $save['avatar']) || strlen($save['avatar'] > 15))
			$save['avatar'] = urlencode("default.png");
		
		// validate password
		if ($save['pass'] == "")
			Message ($txt[11], $txt[134], true);
		
		// validate email address
		if (!filter_var($save['email'], FILTER_VALIDATE_EMAIL))
			Message ($txt[11], $txt[133], true);
		
		// save this user to the database (name	password	group_id	email	joindate	ip	avatar	signature)
		$result = chaozzdb_query ("INSERT INTO user VALUES ({$save['name']}, {$save['pass']}, {$save['group_id']}, {$save['email']}, {$save['joindate']}, {$save['ip']}, {$save['avatar']}, {$save['signature']})");
		Message ($txt[22], $txt[81], true);
	}	
	
	// guest? show form to join
	if (!isset($_SESSION['user_id']))
	{
		include("./includes/simple-php-captcha.php");
		$_SESSION['captcha'] = simple_php_captcha();
?>
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<!-- avatar, post and options //-->
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[84]; ?>
						</div>	
					</div>	
					<div class="columns">		
						<div class="column col-12 div-content">
							<form method="POST" action="index.php?page=profile">
								<input type="hidden" name="action" value="user.register">
								<span class="div-label"><?php echo $txt[85]; ?></span>
								<br>
								<input type="text" name="name" maxlength="<?php echo intval($settings[0]['max_username_length']); ?>" placeholder="your username" autofocus>
								<br>
								<span class="div-label"><?php echo $txt[87]; ?></span>
								<br>
								<input type="password" name="pass" maxlength="20" placeholder="your password">
								<br>
								<span class="div-label"><?php echo $txt[89]; ?></span>
								<br>
								<input type="text" name="email" maxlength="254" placeholder="your email address">
								<br>
								<span class="div-label"><?php echo $txt[90]; ?></span>
								<br>
								<select name="avatar">
<?php								
	if ($dir = @opendir('./avatars')) 
	{
		while (($file = readdir($dir)) !== false)
			if ($file != "." && $file != ".." && $file != "index.php")
			{
				$filename = explode (".", $file);
				echo '<option value="'.$file.'">'.$filename[0];
			}

		closedir($dir);
	}
?>
								</select>
								<br>
								<span class="div-label"><?php echo $txt[126]; ?></span>
								<br>
								<input type="text" name="signature" size="40" maxlength="<?php echo intval($settings[0]['max_signature_length']); ?>" placeholder="your signature">
								<br>
								<span class="div-label"><?php echo $txt[88]; ?></span>
								<br>
								<img src="<?php echo $_SESSION['captcha']['image_src']; ?>" align="middle">
								<br>
								<input type="text" name="captcha" size="6" maxlength="6">
								<br>
								<input type="submit" value="<?php echo $txt[91]; ?>">
						</div>
					</div>	
				</div>
			</div>	
<?php					
	}
	else
	{
		// show the profile of $user_id
		$user = chaozzdb_query ("SELECT * FROM user WHERE id = $user_id");
		if (count($user) == 0)
			Message ($txt[11], $txt[159], true);
		
		$post = chaozzdb_query ("SELECT * FROM post WHERE user_id = $user_id");
		$postcount = count($post);
?>		
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<!-- show profile //-->
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[83]; ?>
						</div>	
					</div>
					
					<div class="columns">		
						<div class="column col-12 div-content">
							<div class="columns">		
								<div class="column col-2 div-center">
									<img class="img-responsive" src="avatars/<?php echo urldecode($user[0]['avatar']); ?>">
								</div>	
								<div class="column col-10">
									<span class="div-label"><?php echo $txt[85]; ?>:</span> <?php echo urldecode($user[0]['name']); ?>
									<br>
									<span class="div-label"><?php echo $txt[118]; ?>:</span> <?php echo Number2Date($user[0]['joindate']); ?>
									<br>
									<span class="div-label"><?php echo $txt[126]; ?>:</span> <?php echo urldecode($user[0]['signature']); ?>
									<br>
									<span class="div-label"><?php echo $txt[47]; ?>:</span> <?php echo $postcount; ?>
									<br>
								</div>
							</div>
						</div>	
					</div>
				</div>
			</div>	
<?php		
		// if you are the person logged in (or staff), show change password field and change avatar field
		if ($_SESSION['user_id'] == $user_id || $_SESSION['group_id'] < $default_group_id)
		{
?>
			<br>
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<!-- edit profile //-->
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[137]; ?>
						</div>	
					</div>
					
					<div class="columns">		
						<div class="column col-12 div-content">
							<form method="POST" action="index.php?page=profile">
								<input type="hidden" name="action" value="user.update">
								<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
								<span class="div-label"><?php echo $txt[126]; ?></span>
								<br>
								<input type="text" name="signature" size="40" maxlength="<?php echo intval($settings[0]['max_signature_length']); ?>" placeholder="your signature" value="<?php echo urldecode($user[0]['signature']); ?>">
								<br>
								<span class="div-label"><?php echo $txt[86]; ?></span>
								<br>
								<input type="password" name="pass" size="20" maxlength="20" value="">
								<br>
								<span class="div-label"><?php echo $txt[90]; ?></span>
								<br>
								<select name="avatar">';
<?php								
	if ($dir = @opendir('./avatars')) 
	{
		while (($file = readdir($dir)) !== false)
			if ($file != "." && $file != ".." && $file != "index.php")
			{
				$filename = explode (".", $file);
				$selected = "";
				if ($file == urldecode($user[0]['avatar'])) $selected = " selected";
				echo '<option value="'.$file.'"'.$selected.'>'.$filename[0];
			}

		closedir($dir);
	}
?>	
								</select>
								<br>
								<br>
								<input type="submit" value="<?php echo $txt[91]; ?>">
							</form>
						</div>
					</div>
				</div>
			</div>	
<?php					
		}
		
		// if you're staff, show ban, unban, delete and add to group
		if ($_SESSION['group_id'] < $default_group_id)
		{
?>
			<br>
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<!-- edit profile //-->
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[140]; ?>
						</div>	
					</div>
					
					<div class="columns">		
						<div class="column col-12 div-content">
							<?php echo $txt[145]; ?>: <?php echo $user[0]['ip']; ?>
							<br>
							<a href="?page=profile&action=user.banip&user_id=<?php echo $user_id; ?>"><i class="fas fa-user-slash forum-button"></i></a>
							<br>
							<a href="?page=profile&action=user.unbanip&user_id=<?php echo $user_id; ?>"><i class="fas fa-user forum-button"></i></a>
							<br>
							<form method="POST" action="index.php?page=profile">
								<input type="hidden" name="action" value="user.addtogroup">
								<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
								<select name="group_id">
<?php								
						$group = chaozzdb_query ("SELECT * FROM group");
						for ($i = 0; $i < count($group) ; $i++)
							echo '<option value="'.$group[$i]['id'].'">'.$group[$i]['name'];
?>
								</select>
								<input type="submit" value="<?php echo $txt[139]; ?>">
							</form>
							<br>
							<form method="POST" action="index.php?page=profile">
								<input type="hidden" name="action" value="user.delete">
								<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
								<input type="checkbox" name="delete_content">
								<?php echo $txt[155]; ?>
								<br>
								<br>
								<input type="submit" value="<?php echo $txt[154]; ?>" onclick="return confirm('<?php echo $txt[144]; ?>')">
							</form>	
						</div>
					</div>
				</div>
			</div>	
<?php					
		}
	}
?>
