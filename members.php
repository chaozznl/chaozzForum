<?php if (!$called_from_index) Message ($txt[11], $txt[48], true); ?>
<?php if (!isset($_SESSION['user_id'])) Message ($txt[11], $txt[94], true); ?>
<?php
	$user = chaozzdb_query ("SELECT * FROM user ORDER BY name ASC"); 
	if (count($user) == 0) die(); // this should never happen. there should always be an admin
?>
			<div class="columns">		
				<div class="column col-9 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $txt[44]; ?>
						</div>
					</div>	
					<div class="columns">		
						<div class="column col-12 div-title">
							<div class="columns">		
								<div class="column col-3">
									<?php echo $txt[45]; ?>
								</div>
								<div class="column col-3">
									<?php echo $txt[46]; ?>
								</div>
								<div class="column col-3">
									<?php echo $txt[118]; ?>
								</div>
								<div class="column col-3">
									<?php echo $txt[47]; ?>
								</div>
							</div>	
						</div>	
					</div>		
<?php	
	for ($i = 0; $i < count ($user); $i++)
	{
		// number of posts
		$post = chaozzdb_query ("SELECT * FROM post WHERE user_id = {$user[$i]['id']}");
		$num_posts = count($post);
		
		// group
		$group = chaozzdb_query ("SELECT * FROM group WHERE id = {$user[$i]['group_id']}");
		
		// joined date
		$joindate = Number2Date($user[$i]['joindate']);
?>
					<div class="columns">		
						<div class="column col-12 div-content">
							<div class="columns">		
								<div class="column col-3">
									<a href="index.php?page=profile&user_id=<?php echo intval($user[$i]['id']); ?>"><?php echo urldecode($user[$i]['name']); ?></a>
								</div>
								<div class="column col-3">
									<?php echo urldecode($group[0]['name']); ?>
								</div>
								<div class="column col-3">
									<?php echo $joindate; ?>
								</div>
								<div class="column col-3">
									<?php echo $num_posts; ?>
								</div>
<?php								
	}
?>
							</div>
						</div>
					</div>
				</div>
			</div>	