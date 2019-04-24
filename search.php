<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php if (!isset($_SESSION['user_id'])) Message ($txt[11], $txt[94], true); ?>
<?php
	if (!empty($_GET['word'])) 
	{ 
		$word = urlencode($_GET['word']);
		$search = chaozzdb_query ("SELECT * FROM post WHERE name ~= $word ORDER BY create_date DESC");
		echo '
			<table class="datatable" width="60%">
				<caption>'.$txt[93].'</caption>';

		if (count($search) == 0)
			Message($txt[11], $txt[128], true);
		else
		{
			for ($i = 0; $i < count($search); $i++)
			{
				$topic = chaozzdb_query ("SELECT * FROM topic WHERE id = {$search[$i]['topic_id']}");
				echo '
					<tr><td width="50" class="altpost"><img src="gfx/normal.gif" /></td><td width="100%" class="post">
					<strong><a href="index.php?page=viewtopic&topic_id='.intval($topic[0]['id']).'">'.urldecode($topic[0]['name']).'</a></strong><br />'.ReplaceBBC(urldecode($search[$i]['name'])).'</td></tr>';
			}
		}
		echo '
			</table>';
	}

	echo '
	<table class="datatable" width="40%">
		<caption>'.$txt[95].'</caption>
		<tr><th>'.$txt[96].'</th><td class="post">
			<form method="GET" action="index.php">
				<input type="hidden" name="page" value="search">
				<input type="text" name="word" value="" size="40" maxlength="40">
				<input type="submit" value="'.$txt[97].'">
			</form>
		</td></tr>
	</table>';
?>