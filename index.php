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
		<meta http-equiv="content-type" content="text/html; charset=<?php echo $charset ?>" />
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
		<meta name="description" content="BoardsDHB - Boards Don't Hit Back Forum Software" />
		<meta name="keywords" content="Forum, Flatfile, No database, Simple, Boards, Forums, BoardsDHB, FubarForum, Fubar" />
		<meta name="author" content="E. Wenners / chaozz@work / www.chaozz.nl" />
		<title><?php echo urldecode($settings[0]['forum_name']) ?></title>
		<link rel="stylesheet" type="text/css" href="css/stylesheet.css" />
		<script language="JavaScript" type="text/javascript" src="includes/javascript.js"></script>
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
		<table class="datatable" width="80%">
			<tr><td class="title"><h2><?php echo urldecode($settings[0]['forum_name']); ?></h2>
<?php
				if (!isset($_SESSION['user_id'])) 
				{
					echo "<a href=\"index.php?page=profile\"><img src=\"gfx/button_register.png\" align=\"right\"  /></a>"; 
					echo "<a href=\"index.php?page=user\"><img src=\"gfx/button_login.png\" align=\"right\"  /></a>";
					echo $txt[114];
				}
				else 
				{
					echo "<a href=\"index.php?page=user&action=logout\"><img src=\"gfx/button_logout.png\" align=\"right\"  /></a>";
					echo "<a href=\"index.php?page=members\"><img src=\"gfx/button_members.png\" align=\"right\"  /></a>";
					echo "<a href=\"index.php?page=profile\"><img src=\"gfx/button_profile.png\" align=\"right\"  /></a>";
					echo "<a href=\"index.php?page=search\"><img src=\"gfx/button_search.png\" align=\"right\"  /></a>";
					
					if ($_SESSION['group_id'] == 1)
						echo "<a href=\"index.php?page=admin\"><img src=\"gfx/button_admin.png\" align=\"right\"  /></a>";
					echo $txt[113]." <strong>".$_SESSION['name']."</strong>!"; 
				}				
?>					
				<a href="index.php"><img src="gfx/button_home.png" align="right"  /></a>
			</tr></td>
<?php		if ($settings[0]['news'] != "") echo "<tr><td class=\"altrow\"><strong>".$txt[116].":</strong> ".urldecode($settings[0]['news'])."</td></tr>"; ?>
		</table>
		<br	/>
		<br />
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