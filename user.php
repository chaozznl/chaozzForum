<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php
	$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : "";
	
	// logout, destroy session
    if ($action == "logout") 
	{
		session_destroy();
		Message($txt[22], $txt[101],"true");
	}
	
	// login
    if ($action == "login") 
	{
		$name = !empty($_POST['name']) ? urlencode($_POST['name']) : "";
		$pass = !empty($_POST['pass']) ? chaozzdb_password($_POST['pass']) : "";
		$captcha = !empty($_POST['captcha']) ? $_POST['captcha'] : "";
		
		// check captcha
		if (strtoupper($_SESSION['captcha']['code']) != strtoupper($captcha))
			Message ($txt[11], $txt[136], true);
			
		$user = chaozzdb_query ("SELECT * FROM user WHERE name = $name");
		
		// invalid username or password
		if (count ($user) == 0 || $user[0]['password'] != $pass) 
		{ 
			//remove cookie
			session_destroy();
			Message ($txt[11], $txt[102], true);
		}
		
	    // set cookie
		$_SESSION['user_id'] = intval($user[0]['id']);
		$_SESSION['name'] = urldecode($user[0]['name']);
		$_SESSION['group_id'] = intval($user[0]['group_id']);
		Message ($txt[22], $txt[103], true);
	}
    
	// show login form if you are not logged in
	if (!isset($_SESSION['user_id'])) 
	{
		 // captcha
		include("./includes/simple-php-captcha.php");
		$_SESSION['captcha'] = simple_php_captcha();
?>

			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[104]; ?>
						</div>
					</div>
					<div class="columns">		
						<div class="column col-12 div-content">					
							<form method="POST" action="<?php echo $url; ?>/user.htm">
								<input type="hidden" name="action" value="login">
								<span class="div-label"><?php echo $txt[85]; ?><span>
								<br>
								<input type="text" name="name" maxlength="20" autofocus>
								<br>
								<span class="div-label"><?php echo $txt[87]; ?></span>
								<br>
								<input type="password" name="pass" maxlength="20">
								<br>
								<span class="div-label"><?php echo $txt[88]; ?></span>
								<br>
								<img src="<?php echo $_SESSION['captcha']['image_src']; ?>">
								<br>
								<input type="text" name="captcha" maxlength="6">
								<br>								
								<input type="submit" value="<?php echo $txt[105]; ?>">
							</form>
						</div>
					</div>
				</div>
			</div>	
<?php
	}
?>