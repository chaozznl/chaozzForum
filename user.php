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
    if ($action == "login") {
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
	    echo '
		<form method="POST" action="index.php?page=user">
		<input type="hidden" name="action" value="login">
		<table border="0" class="datatable" width="40%">
			<caption>'.$txt[104].'</caption>
			<tr><th>'.$txt[85].'</th><td class="post"><input type="text" name="name" size="20" maxlength="20" autofocus></td></tr>
			<tr><th>'.$txt[87].'</th><td class="post"><input type="password" name="pass" size="20" maxlength="20"></td></tr>
			<tr><th>'.$txt[88].'</ht><td class="post"><img src="'.$_SESSION['captcha']['image_src'].'" align="middle" /><br>
					<input type="text" name="captcha" size="6" maxlength="6"></td></tr>					
			<tr><td colspan="2" class="altpost"><div align="center"><input type="submit" value="'.$txt[105].'"></div></td></tr>
		</form> 
		</table>';
	}
?>