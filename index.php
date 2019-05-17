<?php
	session_start();
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	$called_from_index = true; // this value is set and check on included pages so these pages can not be called directly
	
	$page = "viewcategories"; // default page
	if (!empty($_REQUEST['page'])) $page = $_REQUEST['page'];
	if (!ctype_alnum($page)) die; // security
	
	require_once("./includes/chaozzDB.php"); // flat file database engine
	include("./includes/functions.php");
	
	$settings = chaozzdb_query ("SELECT * FROM settings WHERE id = 1"); // read before LANGUAGE file. some settings variables are used in it.
	$url = urldecode($settings[0]['url']); // for quick reference throughout the script
	
	// read the most important IDs
	$post_id = !empty($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;
	$topic_id = !empty($_REQUEST['topic_id']) ? intval($_REQUEST['topic_id']) : 0;
	$board_id = !empty($_REQUEST['board_id']) ? intval($_REQUEST['board_id']) : 0;
	$user_id = isset($_SESSION['user_id']) ? intval ($_SESSION['user_id']) : 0; 

	$lang_file = "./lang/".urldecode($settings[0]['language']);
	include($lang_file);
?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
		<meta name="description" content="chaozzForum - Flatfile Forum Software" />
		<meta name="keywords" content="Forum, Flatfile, No database, Simple, Boards, Forums, chaozzForum, chaozzDB" />
		<meta name="author" content="E. Wenners / www.chaozz.nl" />
		<title><?php echo urldecode($settings[0]['forum_title']) ?> - <?php echo urldecode($settings[0]['forum_subtitle']) ?></title>
		<link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">
		<link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre-exp.min.css">
		<link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre-icons.min.css">
		<link rel="stylesheet" href="<?php echo $url; ?>/css/stylesheet.css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
		<script language="JavaScript" type="text/javascript" src="<?php echo $url; ?>/includes/javascript.js"></script>
		<script language = "javascript">
		<!-- 
		  function addSmiley(smile) {
		  var message = postform.postmessage.value;
		  postform.postmessage.value = message + " " + smile;
		  postform.postmessage.focus();
		  }
		//-->  
		</script>
		
	</head>
	<body>
		<div class="container">
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-header">
							<div class="div-forum-title"><?php echo urldecode($settings[0]['forum_title']); ?></div>
							<div class="div-forum-subtitle"><?php echo urldecode($settings[0]['forum_subtitle']); ?></div>
						</div>
					</div>
			
					<div class="columns">		
						<div class="column col-12 div-header div-right">
						<!-- forum options //-->
							<a href="<?php echo $url; ?>/"><i class="fas fa-home forum-button-header" title="<?php echo $txt[160]; ?>"></i></a>
<?php
	if (!isset($_SESSION['user_id'])) 
	{
?>					
							<a href="<?php echo $url; ?>/profile.htm"><i class="fas fa-user-edit forum-button-header" title="<?php echo $txt[84]; ?>"></i></a>
							<a href="<?php echo $url; ?>/user.htm"><i class="fas fa-sign-in-alt forum-button-header" title="<?php echo $txt[105]; ?>"></i></a>
<?php					
	}
	else 
	{
?>					
							<a href="<?php echo $url; ?>/profile.htm"><i class="fas fa-user-edit forum-button-header" title="<?php echo $txt[83]; ?>"></i></a>
							<a href="<?php echo $url; ?>/search.htm"><i class="fas fa-search forum-button-header" title="<?php echo $txt[97]; ?>"></i></a>
							<a href="<?php echo $url; ?>/members.htm"><i class="fas fa-users forum-button-header" title="<?php echo $txt[44]; ?>"></i></a>
<?php					
		if ($_SESSION['group_id'] == 1)
		{
?>						
							<a href="<?php echo $url; ?>/admin.htm"><i class="fas fa-toolbox forum-button-header" title="<?php echo $txt[13]; ?>"></i></a>
<?php							
		}
?>
							<a href="<?php echo $url; ?>/user/action/logout.htm"><i class="fas fa-sign-out-alt forum-button-header" title="<?php echo $txt[161]; ?>"></i></a>
<?php							
	}				
?>					
						</div>	
					</div>
<?php
	if ($settings[0]['news'] != "") 
	{
?>				
					<div class="columns">		
						<div class="column col-12 div-news">
								<div class="columns">
									<!-- news //-->
									<div class="column col-9">
										<?php echo $txt[116]; ?>: <?php echo ReplaceBBC(urldecode($settings[0]['news'])); ?>
									</div>	
									<!-- welcome message //-->
									<div class="column col-3 div-right">
<?php
	if (!isset($_SESSION['user_id'])) 
		echo $txt[114]; // welcome the guest
	else 
		echo $txt[113].' <span class="div-welcome-alias">'.$_SESSION['name'].'</span>!'; // welcome the user
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
			<br>
<?php
	$banned = false;
	
	// logged in or not, if you're on the ip ban list, you can not view this forum
	$ban = chaozzdb_query ("SELECT id FROM ban WHERE name = ".GetUserIP());
	if (count($ban) > 0) $banned = true;
	
	if (isset($_SESSION['user_id']))
	{
		$ban = chaozzdb_query ("SELECT * FROM user WHERE id = {$_SESSION['user_id']}");
		// you're group id in the database is banned-group
		if (intval($ban[0]['group_id']) == 4) $banned = true;
		// your group got changed while you were logged in, so we need to update your session
		if ($_SESSION['group_id'] != $ban[0]['group_id']) $_SESSION['group_id'] = $ban[0]['group_id'];
	}
	else
	{
		// you're not logged in? then check if guests are allowed to view this page
		if ($page == "viewboard" || $page == "viewtopic" || $page == "search" || $page == "viewcategories")
			if ($settings[0]['guest_view'] == 0 && !isset($_SESSION['user_id']))
				Message ($txt[11], $txt[48], true);
	}

	// banned
	if ($banned)
		Message ($txt[11], $txt[115], true);
	
	// under maintenance
	if ($settings[0]['maintenance_mode'] == 1)
		if (!isset($_SESSION['user_id']) || $_SESSION['group_id'] >= $default_group_id)
			Message ($txt[11], $txt[127], true);
	
	// does the page we request exist?
	if (file_exists("./".$page.".php")) 
	{ 
		// reset them just to be sure
		unset($record); unset($value);
		unset($board_record); unset($board_value);
		unset($topic_record); unset($topic_value);
		unset($post_record); unset($post_value);
		unset($current_record);
		include("./".$page.".php"); 
	}
	else Message ($txt[11], "404 error (".$page.")", false);
	include("./footer.php");
?>