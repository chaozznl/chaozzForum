<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php if (!isset($_SESSION['user_id'])) Message ($txt[11], $txt[94], true); ?>
<?php
	$user = chaozzdb_query ("SELECT * FROM user ORDER BY name ASC"); 
	if (count($user) == 0) die(); // this should never happen. there should always be an admin
		
	echo "<table class=\"datatable\" width=\"60%\">";
	echo "<caption>".$txt[44]."</caption>";
	echo "<tr><th>".$txt[45]."</th><th>".$txt[46]."</th><th>".$txt[118]."</th><th>".$txt[47]."</th></tr>";
	
	for ($i = 0; $i < count ($user); $i++)
	{
		// number of posts
		$post = chaozzdb_query ("SELECT * FROM post WHERE user_id = {$user[$i]['id']}");
		$num_posts = count($post);
		
		// group
		$group = chaozzdb_query ("SELECT * FROM group WHERE id = {$user[$i]['group_id']}");
		
		// joined date
		$joindate = Number2Date($user[$i]['joindate']);

		echo "<tr><td class=\"post\"><img src=\"gfx/member.gif\" />";
		echo "<a href=\"index.php?page=profile&user_id=".intval($user[$i]['id'])."\">".urldecode($user[$i]['name'])."</a>"; 
		echo "</td><td class=\"altpost\">".urldecode($group[0]['name'])."</td>";
		echo "<td class=\"post\">".$joindate."</td>";
		echo "<td class=\"altpost\">".$num_posts."</td></tr>";
	}
	echo "</table>";
?>