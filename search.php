<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php if (!isset($_SESSION['user_id'])) Message ($txt[11], $txt[94], true); ?>
<?php
	if (!empty($_GET['word'])) 
	{ 
		$word = urlencode($_GET['word']);
		$search = chaozzdb_query ("SELECT * FROM post WHERE name ~= $word ORDER BY create_date DESC");
		if (count($search) == 0)
			Message($txt[11], $txt[128], false);
		else
		{
?>
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[93]; ?>
						</div>
					</div>	
					<div class="columns">		
						<div class="column col-12 div-content">
							<div class="columns">		
<?php
			for ($i = 0; $i < count($search); $i++)
			{
				$topic = chaozzdb_query ("SELECT * FROM topic WHERE id = {$search[$i]['topic_id']}");
?>
								<div class="column col-1 div-center">
									<i class="fas fa-file-alt forum-icon"></i>
								</div>
								<div class="column col-11">
									<a href="index.php?page=viewtopic&topic_id=<?php echo intval($topic[0]['id']); ?>"><?php echo urldecode($topic[0]['name']); ?></a>
									<br>
									<?php echo ReplaceBBC(urldecode($search[$i]['name'])); ?>
								</div>
<?php						
			}
?>		
							</div>
						</div>
					</div>
				</div>
			</div>			
			<br>
<?php	
		}
	}
?>
			<!-- search field //-->
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[95]; ?>
						</div>
					</div>	
					<div class="columns">		
						<div class="column col-12 div-content">
							<span class="div-label"><?php echo $txt[96]; ?></span>
							<form method="GET" action="index.php">
								<input type="hidden" name="page" value="search">
								<input type="text" name="word" value="" maxlength="40" autofocus>
								<input type="submit" value="<?php echo $txt[97]; ?>">
							</form>
						</div>
					</div>
				</div>
			</div>	