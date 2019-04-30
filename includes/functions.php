<?php
	$date_format = "YmdHis"; // DON'T CHANGE THIS!!
	$now = Date($date_format);
	$default_group_id = 3; //1=admin/2=moderator/3=member/4=banned

	$smiles = array(
		':)'=>'fas fa-smile',
		':P'=>'fas fa-grin-tongue-wink',
		';)'=>'"fas fa-smile-wink',
		':D'=>'fas fa-laugh-beam',
		':}'=>'fas fa-grin-beam-sweat',
		'<3'=>'fas fa-kiss-beam',
		':<3'=>'fas fa-kiss-wink-heart',
		':{'=>'fas fa-sad-tear',
		':=('=>'fas fa-sad-cry'
	);
	
	Function rand_string($length) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		return substr(str_shuffle($chars),0,$length);
	}
	
	Function MinutesDiff($fromtime, $totime) 
	{
		$fromyear 	= substr($fromtime, 0, 4);
		$frommonth 	= substr($fromtime, 4, 2);
		$fromday 	= substr($fromtime, 6, 2);
		$fromhour 	= substr($fromtime, 8, 2);
		$fromminute = substr($fromtime, 10, 2);
		$fromsecond = substr($fromtime, 12, 2);
		
		$toyear 	= substr($totime, 0, 4);
		$tomonth 	= substr($totime, 4, 2);
		$today 		= substr($totime, 6, 2);
		$tohour 	= substr($totime, 8, 2);
		$tominute 	= substr($totime, 10, 2);
		$tosecond 	= substr($totime, 12, 2);
		
		$from = mktime($fromhour,  $fromminute, $fromsecond, $frommonth, $fromday, $fromyear);
		$to = mktime($tohour, $tominute, $tosecond, $tomonth, $today, $toyear);
		$result = $to - $from;
		if ($result < 0) $result *= -1;
		return intval($result / 60);
	}
	
	Function countValue($data, $field, $value) 
	{
		$i = 0;
		foreach($data as $point)
			if($point[$field] == $value)
				$i++;

		return $i;
	}

	Function nl2br2($string) 
	{
		$string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
		return $string;
	}
	
	Function br2nl($text) 
	{
		return str_replace("<br />","\r\n",$text); 
	}	
	
	// convert database format of a date to a readable format
	Function Number2Date($number, $showdays = true) 
	{
		if($number == 0) return "";
		if (strlen($number) == 0 || $number == '99999999999999') return "Unknown";
		global $date_format;
		$now = date($date_format);
		
		$year 	= substr($number, 0, 4);
		$month 	= substr($number, 4, 2);
		$day 	= substr($number, 6, 2);
		if (strlen($number) > 8) {
			$hour 	= substr($number, 8, 2);
			$minute = substr($number, 10, 2);
			$second = substr($number, 12, 2);
		}
		else {
			$hour = "00";
			$minute = "00";
			$second = "00";
		}
		
		$date_day = $year."-".$month."-".$day;
		$date_time = $hour.":".$minute.":".$second;
		
		// today / yesterday
		if ($showdays) {
			$today = $now;
			$yesterday = date($date_format, strtotime("-1 day"));
			if (substr($today, 0, 8) == $year.$month.$day) $date_day = "Today";
			if (substr($yesterday, 0, 8) == $year.$month.$day) $date_day = "Yesterday";
		}	
		
		return  $date_day." ".$date_time;
	}

	Function MakeAlphaNumeric($string) 
	{
		$string = ereg_replace("[^A-Za-z0-9]", " ", $string );
		return $string;
	}
	
	//FillPulldown(table, name of pulldown, default value in pulldown);
	Function FillPulldown($db_table, $select_name, $value) 
	{
		echo "<select name=\"".$select_name."\">";
		// where id > -1 is because options is the only table that has a record with an id of 0 (disabled)
		$result = chaozzdb_query ("SELECT * FROM $db_table WHERE id > -1 ORDER BY id ASC");
		if (count($result) == 0) return false;

		for ($i = 0; $i < count($result); $i++)
		{	
			if ($result[$i]['id'] == $value) $selected = " selected";
			else $selected = "";
			
			echo "<option value=\"".$result[$i]['id']."\"".$selected.">".$result[$i]['name'];
		}
		echo "</select>";
	}
	
	Function Message($title, $message, $fatal) 
	{
		global $called_from_index; // for footer
?>
			<div class="columns">		
				<div class="column col-5 col-sm-12 col-mx-auto div-outline">
					<div class="columns">		
						<div class="column col-12 div-title">
							<?php echo $title; ?>
						</div>
					</div>
					<div class="columns">		
						<div class="column col-12 div-content">
							<?php echo $message; ?>
						</div>
					</div>
				</div>
			</div>
			<br>
<?php		
		if ($fatal == "true") 
		{
			include("./footer.php");
			die();
		}
	}
	
	Function replaceBBC($text)
	{
		global $smiles;
		
		$text = preg_replace("#\[quote\](.*?)\[/quote\]#si","<table class=\"datatable\" width=\"90%\"><caption>quote</caption><tr><td><small>\\1</small></td></tr></table>", $text);
		$text = preg_replace("#\[b\](.*?)\[/b\]#si","<b>\\1</b>", $text);
		$text = preg_replace("#\[u\](.*?)\[/u\]#si","<u>\\1</u>", $text);
		$text = preg_replace("#\[i\](.*?)\[/i\]#si","<i>\\1</i>", $text);
		$text = preg_replace("#\[url\](.*?)\[/url\]#si","<a href=\\1 target=_blank>\\1</a>", $text);
		$text = preg_replace("#\[url=(.*?)\](.*?)\[/url\]#si","<a href=\\1 target=_blank>\\2</a>", $text);
		$text = preg_replace("#\[img\](.*?)\[/img\]#si","<img src=\\1>", $text);
		$text = preg_replace("#\[color=(.*?)\](.*?)\[/color\]#si","<font color=\\1>\\2</font>", $text);
		$text = preg_replace("#\[edit\](.*?)\[/edit\]#si","<font color=orange><b>Edit</b></font>: \\1", $text);
		foreach($smiles as $smile=>$image)
			$text = str_replace($smile,'<i class="'.$image.' fa-smiley"></i>', $text);
		return $text;
	}
	
	Function GetUserIP() 
	{
		$ip = '';
		if (isset($_SERVER)) { 
			if (isset($_SERVER["REMOTE_ADDR"])) $ip = $_SERVER["REMOTE_ADDR"];
			elseif (isset($_SERVER["HTTP_CLIENT_IP"])) $ip = $_SERVER["HTTP_CLIENT_IP"];
			elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; // can be spoofed, try last
			else $ip = 'unknown';
		}  
		else { 
			if (getenv( 'REMOTE_ADDR' )) $ip = getenv( 'REMOTE_ADDR' );
			elseif (getenv( 'HTTP_CLIENT_IP' )) $ip = getenv( 'HTTP_CLIENT_IP' );
			elseif (getenv( 'HTTP_X_FORWARDED_FOR' )) $ip = getenv( 'HTTP_X_FORWARDED_FOR' );
			else $ip = 'unknown';
		}
		return $ip;
	}
?>