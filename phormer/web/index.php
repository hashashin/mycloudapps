<?php
	require_once("funcs.php");
	$time_start = getmicrotime();

	if (!file_exists(ADMIN_PASS_FILE))
		header('Location: admin.php');

	if (!file_exists('data/visits.xml'))
		 @copy("files/adminfiles/visits.xml.def", "data/visits.xml");

	$categs = array();
	$photos = array();
	$stories = array();
	$comments = array();
	$basis = array();
	$visits = array();
	parse_container('categs', 	'Category', 'data/categories.xml');
	parse_container('stories', 	'Story', 	'data/stories.xml');
	parse_container('photos', 	'Photo', 	'data/photos.xml');
	parse_container('comments', 'Comment', 	'data/comments.xml');
	parse_container('basis', 	'Basis', 	'data/basis.xml');
	parse_container('visits', 	'Visit', 	'data/visits.xml');

	$criminals = array('extra', 'pgname', 'pgdesc', 'auname', 'auemail', 'icon', 'bannedip', 'copyright');
	foreach ($criminals as $key)
		if (isset($basis[$key]))
			$basis[$key] = strtr($basis[$key], $transtable);

	$smoothcriminals = array('pgname', 'auname', 'auemail', 'icon');
	foreach ($smoothcriminals as $key)
		if (isset($basis[$key]))
			$basis[$key] = strtr($basis[$key], $transmanual);

	$stories = array_reverse($stories, true);

	$nphotos = count($photos)-1; // for lastpid!
	$nstories = count($stories)-1; // for lastsid!

	if (isset($_GET['mode']))
		$basis['mode'] = $_GET['mode'];
	if (isset($_GET['theme']))
		$basis['theme'] = $_GET['theme'];
	$alls = (isset($_GET['alls']))?$_GET['alls']:0;
	$wStories = $alls*(-count($stories));

	if (!isset($basis['defjbn']))
		$basis['defjbn'] = 50;   // photos in box mode
	if (!isset($basis['defrpc']))
		$basis['defrpc'] = 20;   // recent photos in index
	if (!isset($basis['deftrc']))
		$basis['deftrc'] = 10;   // top rated in index
	if (!isset($basis['defrsc']))
		$basis['defrsc'] = 5;    // stories in index
	if (!isset($basis['defrss']))
		$basis['defrss'] = 1000; // stories in side bar

	$rps = (isset($_GET['rps']))?$_GET['rps']:0;
	$rpn = (isset($_GET['rpn']))?$_GET['rpn']:$basis['defrpc']; // recent photos count (number)
	$trs = (isset($_GET['trs']))?$_GET['trs']:0;
	$trn = (isset($_GET['trn']))?$_GET['trn']:$basis['deftrc']; // top rated count (number)
	$rss = (isset($_GET['rss']))?$_GET['rss']:0;
	$rsn = (isset($_GET['rsn']))?$_GET['rsn']:$basis['defrsc']; // recent stories count (number)

	define("DEFAULT_N", max(20, $basis['defrpc']+$basis['deftrc']));
	$n   = (isset($_GET['n'] ))?$_GET['n']:DEFAULT_N;
	$ns  = (isset($_GET['ns'] ))?$_GET['ns']:0;

	/* getting access mode */
	$p = 	(isset($_GET['p']))?$_GET['p']:-1;
	$c = 	(isset($_GET['c']))?$_GET['c']:-1;
	$s = 	(isset($_GET['s']))?$_GET['s']:-1;
	$u = 	(isset($_GET['u']))?$_GET['u']:-1;
	$j = 	(isset($_GET['j']))?$_GET['j']:-1; 		// export in javascript
	$jj = 	(isset($_GET['jj']))?$_GET['jj']:-1; 	// export in php

	$feat =	(isset($_GET['feat']))?$_GET['feat']:"";

	$QS = $_SERVER['QUERY_STRING'];
	if (($p == -1)	&&	($c == -1)	&&	($s == -1)	&&	($u == -1)	&&	($j == -1) &&	($jj == -1)
		&& (strlen($QS) > 0) && (strpos($QS, "=") === FALSE)) {
		$u = $QS;
	}

	/* checking the falsehood of $u and else */
	$ok_msg = '';
	$ok_rate = false;
	$alert_msg = '';
	$alert_query = '';

	if ($u != -1) {
		reset($categs);
		while (list($ck, $cv) = each($categs))
			if (is_array($cv))
				if (strcasecmp($cv['name'], $u) == 0) {
					$c = $ck;
					$u = -1;
					break;
				}
		reset($categs);
	}
	if ($u != -1) {
		reset($stories);
		while (list($ck, $cv) = each($stories))
			if (is_array($cv))
				if (strcasecmp($cv['name'], $u) == 0) {
					$s = $ck;
					$u = -1;
					break;
				}
		reset($stories);
	}
	if ($u != -1) {
		sscanf($u, "%d", $t);
		if (isset($photos[$t]) && strcmp($t, "lastpid") != 0) {
			$p = $t;
			$u = -1;
		}
		else
			$alert_query = "The requested parameter ($u) is not a valid one!";
	}


	$cmd = (isset($_GET['cmd']))?$_GET['cmd']:(isset($_POST['cmd'])?$_POST['cmd']:'');

	if (($p != -1) && !isset($photos[$p]))
		$alert_query = "The requested photo [pid = $p] does not exist!";
	if (($c != -1) && !isset($categs[$c]))
		$alert_query = "The requested category [cid = $c] does not exist!";
	if (($s != -1) && !isset($stories[$s]))
		$alert_query = "The requested story [sid = $s] does not exist!";

	$bad_query = (strlen($alert_query) > 0);
	if ($bad_query)
		$p = $c = $s = -1;

	$isAdmin = (isset($_COOKIE['phormer_passwd']) && auth_admin($_COOKIE['phormer_passwd'], "admin/"));
	$byAdmin = isset($_POST['byadmin']) && strcmp($_POST['byadmin'], 'yes') == 0;
	$isAnAddCmnt = (strcmp($cmd, 'addcmntp') == 0) || (strcmp($cmd, 'addcmntp') == 0);
	if ($isAnAddCmnt && !isset($_POST['wvw']))
		$_POST['wvw'] = "_";

	if (strlen($cmd) && !$bad_query) {
		if ($isAnAddCmnt) {
			if ($byAdmin && !$isAdmin)
				$alert_msg = "Only Administrator can leave comment as <b>Admin</b>";
			else if (!$byAdmin && !isset($_POST['name']))
				$alert_msg = "Name field can not be left blank.";
			else if (!isset($_POST['txt']) || (strlen($_POST['txt']) == 0))
				$alert_msg = "Text Body can not be left empty.";
			else if (bannedIP($_SERVER['REMOTE_ADDR']))
				$alert_msg = "Your IP is locked on this PhotoGallery. Contact Author!";
			else if (!$isAdmin && isset($basis['wvw']) && isset($_POST['wvw']) &&
					 hasWV() && (strcasecmp($_POST['wvw'], $basis['wvw']) != 0)) {
					$alert_msg = "Incorrect word verification entry!";
					if (isset($basis['wvw']))
						$alert_msg .= " You should have entered \"".$basis['wvw']."\". Try again!"; #\"".$_POST['wvw']."\"";
				}
			else if (!$byAdmin && !$isAdmin && isset($_POST['name']) && (strcmp($_POST['name'], "ADMIN") == 0))
				$alert_msg = "Only real Administrator, can leave comments as <b>ADMIN</b>!";
			if (strlen($alert_msg) == 0) { // yet has chance to be added!
				if ($byAdmin)
					$_POST['name'] = "ADMIN";
				if (isset($_POST['reply']) && isset($comments[$_POST['reply']]))
					$reply = $_POST['reply'];
				else
					$reply = 0;
			}
		}
		switch ($cmd) {
			case 'addcmntp':
				if (strlen($alert_msg) == 0) {
					$photo = getAllPhotoInfo($p, "./");
					if (strcmp($photo['getcmnts'], 'yes') != 0)
						$alert_msg = "This Photo doesn't get Comment!";
					else {
						if (isset($_POST['date']) && ($_POST['date']))
							$date = "00-01-01 00:00";
						else
							$date = date("Y-m-d H:i", GetTimeWithDiffer());
						$comments[++$comments['lastiid']] = array('name' => $_POST['name'],
									'email' => $_POST['email'], 'url' => $_POST['url'],
									'date' => $date, 'txt' => htmlspecialchars($_POST['txt'], ENT_COMPAT, 'UTF-8'),
									'ip' => $_SERVER['REMOTE_ADDR'], 'owner' => "p".$_GET['p'],
									'reply' => $reply);
						$ok_msg = "Comment added successfully";
						save_container('comments', 'Comment', './data/comments.xml');
					}
				}
				break;
			case 'addcmnts':
				if (strlen($alert_msg) == 0) {
					if (strcmp($stories[$s]['getcmnts'], 'yes') != 0)
						$alert_msg = "This Story doesn't get Comment!";
					else {
						if (isset($_POST['date']) && ($_POST['date']))
							$date = "00-01-01 00:00";
						else {
							$date = date("Y-m-d H:i", GetTimeWithDiffer());
						}
						$comments[++$comments['lastiid']] = array('name' => $_POST['name'],
									'email' => $_POST['email'], 'url' => $_POST['url'],
									'date' => $date, 'txt' => htmlspecialchars($_POST['txt'], ENT_COMPAT, 'UTF-8'),
									'ip' => $_SERVER['REMOTE_ADDR'], 'owner' => "s".$_GET['s'],
									'reply' => $reply);
						$ok_msg = "Comment added successfully.";
						save_container('comments', 'Comment', './data/comments.xml');
					}
				}
				break;
			case 'logout':
				$carr = array();
				$let = ($c != -1)?"c":"s";
				$carr = ($c != -1)?$categs[$c]:$stories[$s];
				setcookie('pass_'.$let.$$let, "", time()-3600);
				$_COOKIE['pass_'.$let.$$let] = "";
				break;
			case 'login':
				if (!isset($_POST['pass']))
					$alert_msg = "Password field should not be blank.";
				else {
					$carr = array();
					$let = ($c != -1)?"c":"s";
					$carr = ($c != -1)?$categs[$c]:$stories[$s];
					if (strcmp(md5($_POST['pass']), md5($carr['pass'])) != 0)
						$alert_msg = "Invalid Password!";
					else {
						setcookie('pass_'.$let.$$let, md5($_POST['pass']), time()+3600*24*7);
						$_COOKIE['pass_'.$let.$$let] = md5($_POST['pass']);
						if (isset($_GET['done']))
							header("Location: .?p={$_GET['done']}");
					}
				}
				break;
			case 'rate':
				if ($p == -1)
					$alert_msg = "PhotoID can not be left blank.";
				else {
					$urrate = (isset($_COOKIE['rate_'.$p])?$_COOKIE['rate_'.$p]:0);
					$rating = array();
					$rating = explode(" ", $photos[$p]);
					$hits = $rating[0];
					$rating = explode("/", $rating[1]);
					$rating[0] += $_GET['rate']-$urrate;
					$rating[1] += ($urrate == 0);
					$photos[$p] = sprintf("%d %d/%d", $hits, $rating[0], $rating[1]);
					$ok_rate = true;
					$ok_msg = "Your rating \"{$_GET['rate']}\" for this photo saved successfully!".
						(($urrate==0)?"":" (and your previous rating ($urrate) dismissed.)");
					save_container('photos', 'Photo', './data/photos.xml');
					setcookie('rate_'.$p, $_GET['rate'], time()+3600*24*365);
					$_COOKIE['rate_'.$p] = $_GET['rate'];
					$rate = 0;
					@$rate = round($rating[0]/$rating[1], 2);
					$rate .= " by ".$rating[1];
					echo "<ajax>Done $rate</ajax>";
					die();
				}
				break;
			case 'wvcheck' :
				$u_md5 = isset($_GET['md5'])?$_GET['md5']:"_";
				$b_md5 = substr(md5($basis['wvw']), 0, 20);
				echo "<ajax>";
				echo (strcmp($u_md5, $b_md5) == 0)?"TrueWV":"FakeWV";
				echo $b_md5;
				echo "</ajax>";
				die();
				break;
			case 'delcmnt':
				if (!$isAdmin)
					$alert_msg = "Only Administrator can delete comments!";
				else if (!isset($_GET['cmntid']))
					$alert_msg = "CommentId not posted.";
				else {
					$cmntid = (int)$_GET['cmntid'];
					if (!isset($comments[$cmntid]))
						$alert_msg = "Comment #$cmntid not found.";
					else {
						$ok_msg = "Comment #$cmntid by \"{$comments[$cmntid]['name']}\" deleted successfully!";
						unset($comments[$cmntid]);
						save_container('comments', 'Comment', './data/comments.xml');
					}
				}
				break;
			default:
				$alert_msg = "Unknown command \"".$cmd."\"!";
		}
		if ($isAnAddCmnt) { //  && (strlen($alert_msg) == 0) removed, because one may use public PC
			setcookie('phorm_cmnt_name', $_POST['name'], time()+3600*24*30*12);
			setcookie('phorm_cmnt_email', $_POST['email'], time()+3600*24*30*12);
			setcookie('phorm_cmnt_url', $_POST['url'], time()+3600*24*30*12);
		}
	}

	if ($j != -1) {
		$path = pathinfo($_SERVER['PHP_SELF']);
		$add = "http://".$_SERVER['SERVER_NAME'].$path["dirname"];
		if (strcmp($path["dirname"], "/") != 00)
			$add .= "/";
		if (photo_exists($j)) {
			$size = (isset($_GET['size']))?$_GET['size']:1;
			$img = thumb_just_img($j, $size, "./");
			echo "document.write('<a href=\"$add?p=$j\"><img src=\"$add/$img\" /></a>');";
		}
		else
			echo "document.write('<a href=\"$add\">The photo #$j does not exist</a>');";
		die();
	}

	if ($jj != -1) {
		$path = pathinfo($_SERVER['PHP_SELF']);
		$add = "http://".$_SERVER['SERVER_NAME'].$path["dirname"];
		if (strcmp($path["dirname"], "/") != 00)
			$add .= "/";
		if (photo_exists($jj)) {
			$size = (isset($_GET['size']))?$_GET['size']:1;
			$img = thumb_just_img($jj, $size, "./");

			$s = "<a href=\\\"$add?p=$jj\\\"><img src=\\\"$add/$img\\\" /></a>";
		}
		else
			$s = "The photo #$jj does not exist!";
		$s .= "\n";

		echo "<?php\n"; #"
		echo "\t \$s = \"$s\"; \n";
		echo "\t echo \$s; \n";
		echo "?>\n";
		die();
	}

	haveAVisit();

	if (strcmp($feat, "slideshow") == 0) {
		$r = array();
		$title = "SlideShow";

		write_headers("SlideShow :: ".$basis['pgname']);
		write_top();

		if ($c != -1) {
			$clet = "c";
			$cname = "Category";
			$cid = $c;
			if (!checkThePass($clet, $cid))
				auth_failed_div($clet, $cid, $cname);
			else {
				$r = $categs[$c]['photo'];
				$backend = ".?c=$c";
				$theend = ".?feat=slideshow&c=$c";
				$stitle = "<a href=\"$theend\">\"{$categs[$c]['name']}\"</a>";
			}
		}
		else if ($s != -1) {
			$clet = "s";
			$cname = "Story";
			$cid = $s;
			if (!checkThePass($clet, $cid))
				auth_failed_div($clet, $cid, $cname);
			else {
				$r = $stories[$s]['photo'];
				$backend = ".?s=$s";
				$theend = ".?feat=slideshow&s=$s";
				$stitle = "<a href=\"$theend\">\"{$stories[$s]['name']}\"</a>";
			}
		}
		else {
			$r = $photos;
			unset($r['lastpid']);
			$r = array_keys($r);
			$backend = ".";
			$theend = ".?feat=slideshow";
			$stitle = "<a href=\"$theend\">\"Recent Photos\"</a>";
		}

		while (list($key, $val) = each($r)) {
			if (!canthumb($val))
				unset($r[$key]);
		}

		rsort($r);
		for ($i=0; $i<$ns && count($r); $i++)
			array_shift($r);

		$threshold = $n;
		if (($threshold < 50) && ($n == DEFAULT_N))
			$threshold = 50;

		if (count($r) > $threshold) {
			$theend .= "&ns=".($ns + $threshold);
			$theend .= "&n=".($threshold);
			while (count($r) > $threshold)
				array_pop($r);
		}
		else
			$theend = "";

		if (count($r) != 0 ) {
			$r = array_flip($r);
			reset($r);
			while (list($key, $val) = each($r)) {
				$r[$key] = getAllPhotoInfo($key);
				$r[$key]['src'] = PHOTO_PATH.getImageFileName($key, 0);
				if (!file_exists($r[$key]['src']))
					$r[$key]['src'] = PHOTO_PATH.getImageFileName($key, 9);

				$dates = sscanf($r[$key]['datetake'], "%d/%d/%d");
				$r[$key]['date'] = date("l, M jS \o\f Y", mktime(0, 0, 0, $dates[1], $dates[2], $dates[0]));
			}
			reset($r);

			$r0 = current($r);

		#echo count($r)."~~~~~~~~~~~~<br />\n";
		#print_r(array_keys($r));

		$backend .= "&n=$n";
		if ($ns > 0)
			$backend .= "&ns=$ns";

?>
	<div class="partmain" id="slideShow">
		<div class="titlepart" style="letter-spacing: 2px; padding-bottom: 1em; margin-bottom: 0px;">
			<span class="leaveReply" style="padding-right: 5px;">[ <a href="<?php echo $backend; ?>">Back</a> ]</span>
			<span class="leaveReply" style="padding-right: 5px;">[ <a id="ss_smaller_link" class="q" onclick="javascript:ss_toggleSmaller();">Smaller Size</a> ]</span>

			<span class="reddot" style="padding: 0px 10px;">&#149;</span>
			SlideShow of <?php echo $stitle; ?>  :: Slide
			<span id="ss_n">1</span> of <?php echo count($r); ?>
			<div id="headSlideshow">
				<span class="dt">&#147;</span><a
					id="ss_link1" href="?p=<?php echo key($r); ?>" style="letter-spacing: 5px;"><span
					id="ss_title"><?php echo $r0['name']; ?></span></a><span
					class="dt">&#148;</span>
					<br />
			</div>
		</div>

			<div style="margin: 0px auto 5px; width: 90%">
				<table style="width: 100%"><tr>
					<td style="text-align: left" width="25%">
						[ <a class="q" onclick="javascript:ss_prev();">Previous</a> ]
					</td><td style="text-align: center" width="50%">
						[ <a class="q" id="ss_playpause_link" onclick="javascript:ss_playpause();">Pause it</a> ]
						&nbsp; &nbsp;
						<select class="rate" id="ss_refresh" style="position: relative; top: 6px;">
							<option value="1000">&nbsp;1 Sec</option>
							<option value="2000">&nbsp;2 Sec</option>
							<option value="5000">&nbsp;5 Sec</option>
							<option value="10000" selected="selected">10 Sec</option>
							<option value="20000">20 Sec</option>
							<option value="30000">30 Sec</option>
							<option value="60000">60 Sec</option>
						</select>
					</td><td style="text-align: right" width="25%">
						[ <a class="q" onclick="javascript:ss_next();">Next</a> ]
					</td>
				</tr></table>
			</div>


		<div style="text-align: center; margin: 15px 0px 10px;">
			<a id="ss_link2" href="?p=<?php echo key($r); ?>" style="display:inline;">
				<img id="ss_photo" src="<?php echo $r0['src']; ?>" onload="javscript:ss_loaddone();" />
			</a>
			<div id="ss_theend" style="display: none; margin: 10em 0em;" class="">
				<b>This is the end of SlideShow <?php if (strlen($theend)) echo " to last ".($ns+$threshold)." photos"; ?>.</b><br /><br />
<?php if (strlen($theend)) echo "[ <a class=\"q\" href=\"$theend\">Check Laters &#133;</a> ]"; ?>
			</div>
			<div class="divClear"></div>
		</div>

		<div style="margin: 1em auto 5px; width: 90%">
			<table style="width: 100%"><tr>
				<td style="text-align: left" width="25%">
					[ <a class="q"onclick="javascript:ss_prev();">Previous</a> ]
				</td><td style="text-align: center" width="50%">
					[ <a class="q" id="ss_playpause_link2" onclick="javascript:ss_playpause();">Pause it</a> ]
					&nbsp; &nbsp;
				</td><td style="text-align: right" width="25%">
					[ <a class="q" onclick="javascript:ss_next();">Next</a> ]
				</td>
			</tr></table>
		</div>

		<br />
		<div id="ss_date"><?php echo $r0['date']; ?></div><br />
		<div class="midInfo" id="ss_info">
			<span id="ss_desc"><?php echo nl2br($r0['desc']); ?></span>
<?php

			echo '<script language="javascript" type="text/javascript">';
				echo "ss_pid 	= new Array();\n";
				echo "ss_ttl 	= new Array();\n";
				echo "ss_date 	= new Array();\n";
				echo "ss_src 	= new Array();\n";
				echo "ss_desc 	= new Array();\n";
				reset($r);
				for ($n=0; list($key, $val) = each($r); $n++)
					echo "\t ss_pid [$n] = '{$key}'; "
						."\t ss_date[$n] = '{$val['date']}'; "
						."\t ss_src [$n] = '{$val['src']}'; "
						."\t ss_ttl [$n] = '".str_replace(array("\n", "'"), array('', '&#039;'), nl2br(strtr($val['name'], $transtable)))."'; "
						."\t ss_desc[$n] = '".str_replace(array("\n", "'"), array('', '&#039;'), nl2br($val['desc']))."'; \n";
				echo "\t".'ss_update();'."\n";
				echo "\t".'setTimeout("ss_slideshow()", 10000);'."\n";
			echo '</script>';

?>
		</div>
		<div class="end" style="padding-top: 0px"></div>
<?php
		}

	} // slideshow
	else if ($p != -1) {
		$photo = getAllPhotoInfo($p);
		if (!checkThePass("s", $photo['story'])) {
			write_headers("Unauthorized :: ".$basis['pgname']);
			write_top();
			unauthorized("s", $photo['story'], $p);
		}
		else if (!checkThePass("c", $photo['categ'])) {
			write_headers("Unauthorized :: ".$basis['pgname']);
			write_top();
			unauthorized("c", $photo['categ'], $p);
		}
		else {
			$curLastVisit = array();
			if (strlen($cmd) == 0) {
				$rating = array();
				$rating = explode(" ", $photos[$p]);
				$hits = $rating[0];
				$hits++;
				@eval("@\$rate =".$rating[1].";");
				if (($rate < 1) || ($rate > 5))
					$rating[1] = "0/0";
				if (isset($rating[2]))
					$curLastVisit = $rating;

				$photos[$p] = sprintf("%d %s %s", $hits, $rating[1], date("Y/m/d-G:i:s", GetTimeWithDiffer()));
				save_container('photos', 'Photo', './data/photos.xml');
			}
			$hr = "<center><div class=\"hr\"></div></center>";
			$dd = "<span class=\"darkdot\">&#149;</span>";
			$spc = "\t\t<div class=\"spc\"> </div>\n";
			$imgAddress = PHOTO_PATH.getImageFileName($p, 0);
			if (!file_exists($imgAddress))
				$imgAddress = PHOTO_PATH.getImageFileName($p, 9);
			if (!file_exists($imgAddress))
				$imgAddress = PHOTO_PATH.getImageFileName($p, 4);
			$name = $photo['name'];
			if (strlen($name) == 0)
				write_headers($basis['pgname']);
			else
				write_headers("\"$name\" of ".$basis['pgname']);
			write_top();

			echo "\t<div class=\"pvTitle\">"
					."<span class=\"pvTitleInfo\">"
						.($isAdmin?"[<a target=\"_top\" "
						."href=\"admin.php?page=photos&cmd=del&pid=$p\" onclick=\"return confirmDelete('Photo #$p')\" title=\"Delete this photo in the Administration Area\"> Delete </a>]&nbsp; &nbsp;":"")
						.($isAdmin?"[<a target=\"_top\" "
						."href=\"admin.php?page=photos&cmd=doEdt&pid=$p\" title=\"Edit this photo in the Administration Area\"> Edit </a>]&nbsp; &nbsp;":"")
						."[<a target=\"_top\" href=\"javascript:toggleInfo();\" title=\"Show/Hide additional info and de/centralize the photo\">"
							."  <span id=\"hin\">Hide&nbsp;</span> info "
						."</a>]&nbsp; &nbsp;"
						."[<a target=\"_top\" href=\".?c={$photo['categ']}\" title=\"Navigate to container Category ({$categs[$photo['categ']]['name']})\"> Up </a>]"
					."</span>\n";
			if (strlen($name))
				echo "<span class=\"dt\">&#147;</span> "
					."<span class=\"titleName\"><a class=\"oneImageTitle\" title=\"Refresh this page\" href=\".?p=$p\">$name</a></span> "
					."<span class=\"dt\">&#148;</span>\n";
			else
				echo "&nbsp;";
			echo "</div>\n";
			$photoDesc = (strlen($photo['desc']) > 0)?nl2br($photo['desc']):"No Descripton.";
			$photoInfo = nl2br($photo['photoinfo']);
			$tuple = (strlen($photo['photoinfo']));
			global $hasgd;
			$widthHeight = "";
			if ($hasgd && file_exists($imgAddress)) {
				list($width, $height) = getimagesize($imgAddress);
				$widthHeight = "width=\"${width}px\" height=\"${height}px\" ";
			}
			$larger = PHOTO_PATH.getImageFileName($p, 9);
			$haslarger = (file_exists($larger) && (strcasecmp($larger, $imgAddress) != 0));
			$largerInfo = "";
			if ($haslarger && $hasgd) {
				list($w, $h) = getimagesize($larger);
				$sizekb = round(filesize($larger)/1024);
				$largerInfo = ":: {$w}x{$h} :: {$sizekb}KB";
			}
			echo "\t<div class=\"wholePhoto\">\n";
			echo "\t\t<div class=\"photoTheImg\" >\n"
					.($haslarger?"<a title=\"Click to view original size $largerInfo\" href=\"$larger\">":"")
					."<div id=\"theImage\" style=\"float: left;\" >"
						."<img src=\"$imgAddress\" $widthHeight alt=\"{$photo['name']}\"  />\n"
						.($haslarger?"</a>":"")
						."<div class=\"divClear\" style=\"width: 300px;\"></div>"
					."</div>"
				."</div>\n";
			echo "\t\t<div id=\"photoBoxes\">\n";
			echo "\t\t<div class=\"photoBox\">\n"
					."<span class=\"titlePhotoBox\">Photo Notes "
					."</span><br />"
					/* .$dd */ .$photoDesc
					.($tuple?$hr /* .$dd */:"")
					.$photoInfo
					."</div>\n";
			echo "\t\t<div class=\"photoBox\">\n";
				echo "\t\t\t<span class=\"titlePhotoBox\">Further Details</span><br />\n";
				$dates = sscanf($photo['dateadd'], "%d/%d/%d %d:%d");
				$mtime = date("M jS y, h:i", mktime($dates[3], $dates[4], 0, $dates[1], $dates[2], $dates[0]));


				echo "\t\t\t$dd Date added: $mtime<br />\n";
				if (strncmp($photo['dateadd'], $photo['datetake'], 10) != 0) {
					$dates = sscanf($photo['datetake'], "%d/%d/%d");
					$mtime = date("D, M jS \o\f y", mktime(0, 0, 0, $dates[1], $dates[2], $dates[0]));
					echo "\t\t\t$dd Date taken: $mtime<br />\n";
				}
				echo $spc;
				echo "\t\t\t$dd Photo ID: <a target=\"_top\" href=\".?p=$p\">$p</a><br />\n";
				echo "\t\t\t$dd Category: <a href=\".?c={$photo['categ']}\">{$categs[$photo['categ']]['name']}</a><br />\n";
				echo "\t\t\t$dd Story: <a href=\".?s={$photo['story']}\">{$stories[$photo['story']]['name']}</a><br />\n";
				echo $spc;
				if (file_exists(PHOTO_PATH.getImageFileName($p, '9')) ||
					file_exists(PHOTO_PATH.getImageFileName($p, '0')))
					echo "\t\t\t$dd Other Sizes: "
							.($haslarger?"<a href=\"".thumb_just_img($p, 9, "./")."\" title=\"Photo $p $largerInfo\">Original</a>, \n":"")
							."<a href=\"".thumb_just_img($p, 1, "./")."\" title=\"width: 240px\">BlogSize</a>\n"
							."<br />\n";
			echo "\t\t</div>\n ";
			echo "\t\t<div class=\"photoBox\" style=\"line-height: 150%\">\n";
				echo "\t\t\t<span class=\"titlePhotoBox\">Photo Statistics</span><br />\n";
				$rating = array();
				$rating = explode(" ", $photos[$p]);
				echo "\t\t\t$dd Hits: $rating[0]<br />\n";

				if (isset($curLastVisit[2]))
					echo "\t\t\t$dd Last Visit: ".SecsToText(-1*below_GetRecency($curLastVisit))."<br />\n";

				$rate = 0;
				@eval("@\$rate = ".$rating[1].";");
				$rate = round($rate, 2);
				$rating = explode("/", $rating[1]);
				echo "\t\t\t$dd Rated <span id=\"sumRate\">$rate by {$rating[1]}</span> persons<br />\n";
				$status = (isset($_COOKIE['rate_'.$p])?'Modify your rating':'Rate now');
?>
				<div>
					<noscript>
					<span class="darkdot">&#149;</span> <b>Rating needs javascript</b><br />
					</noscript>
					<span class="darkdot">&#149;</span>
					<span id="rateStatus">
						<?php echo $status; ?>:
					</span>
					<span id="indicator" class="dot" style="padding: 0px; color: #454; font-size: 1em;"></span>
				</div>
				<span style="margin-left: 7px;"></span>
				<select name="rate" class="rate" id="rateSelect" onchange="javascript:SaveRating(<?php echo $p; ?>, this.value);">
<?php
				$urrate = (isset($_COOKIE['rate_'.$p])?$_COOKIE['rate_'.$p]:0);
				$rname = array("Select Your Rate","Damn it!", "I dislike it!", "Umm...", "Nice at all!", "Excellent!");
				for ($i=0; $i<=5; $i++)
					echo "\t\t\t\t\t<option value=\"$i\"".(($i == $urrate)?" selected=\"selected\"":"").">".(($i>0)?"$i: ":"")."{$rname[$i]}</option>\n";
?>
				</select>
			</div>
			</div>
			<script language="javascript" type="text/javascript">
				reToggleInfo();
			</script>
		<div class="divClear"></div>
		</div>
		<br />
<?php
			writeNextz($p);

			echo "<a name=\"cmnts\"></a>";
			if (strlen($alert_msg))
				echo "<div class=\"alert_msg\">$alert_msg</div>\n";
			else if (strlen($ok_msg) && !$ok_rate)
				echo "<div class=\"ok_msg\">$ok_msg</div>\n";
			if (strcmp($photo['getcmnts'], 'yes') == 0)
				writeCommenting('p', $p);
		}
	}
	else {
	$t = "";
	if ($c != -1)
		$t = "\"".$categs[$c]['name']."\" Category of ";
	else if ($s != -1)
		$t = "\"".$stories[$s]['name']."\" Story of ";
	write_headers($t.$basis['pgname']);
	write_top();
?>
	<div id="sidecol">
		<div id="sidecolinner">
			<?php
				if ($c != -1) {
					if ((strlen($categs[$c]['pass']) > 0) && (checkThePass('c', $c)))
						write_actions('categs', 'c', $c);
				} else if ($s != -1) {
					if ((strlen($stories[$s]['pass']) > 0) && (checkThePass('s', $s)))
						write_actions('stories', 's', $s);
				}
				if (showOnSideBar("checkcateg"))
					write_conts('categs', 'Categories');
				if (showOnSideBar("checkstory"))
					write_conts('stories', 'Stories');
			?>
<?php
	if (showOnSideBar("checkstate"))
		write_stats();

	if (showOnSideBar("checklink")) {
		reset($basis['links']);
		$t = current($basis['links']);
		if ((count($basis['links']) > 1) || (strlen($t['name']))) {
	?>

			<div class="part">
				<div class="submenu">
<?php
			$targ = "_blank"; // default
			if (isset($basis['linktarget'])) {
				if (strcmp($basis['linktarget'], "_blank") == 0)
					$targ = "_blank";
				else if (strcmp($basis['linktarget'], "_self") == 0)
					$targ = "_self";
			}

			reset($basis['links']);
			while (list($key, $val) = each($basis['links'])) {
				if (strlen($val['href']) == 0)
					echo "\t\t\t\t</div>\n"
						."\t\t\t\t<div class=\"titlepart\"><span class=\"reddot\">&#149;</span>${val['name']}</div>\n"
						."\t\t\t\t<div class=\"submenu\">\n";
				else {
					foreach ($val as $vkey => $vval) {
						$val[$vkey] = strtr($vval, $transtable);
						$val[$vkey] = strtr($vval, $transmanual);
					}
					echo "\t\t\t\t\t<div class=\"item\"><span class=\"dot\">&#149;</span><a target=\"$targ\" href=\"${val['href']}\" title=\"${val['title']}\">${val['name']}</a></div>\n";
				}
			}
?>
				</div>
			</div>
<?php
		}
	}

	if (showOnSideBar("checkextra")) {
		$basis['extra'] = strtr($basis['extra'], $transtable);
		if (strlen($basis['extra']) > 0) {
?>
			<div class="part">
			<div class="titlepart"><span class="reddot">&#149;</span>Etc</div>
				<div class="submenu">
					<?php echo $basis['extra']; ?>
				</div>
			</div>
<?php
		}
	}

	write_credits();
?>
		</div>
	</div>
	<div id="maincol">
		<div id="maincolinner">
		<?php
			if ($c != -1)
				write_container("c");
			else if ($s != -1)
				write_container("s");
			else {
				if ($bad_query)
					echo "<div class=\"alert_msg\">$alert_query</div>";
				$tok = explode("-", $basis['mode']);
				foreach ($tok as $t)
					modeInMainCol($t);
			}
		}
		?>
		</div>
	</div>
	</div>
</div></center> <!-- Granny -->
<?php
	$time_end = getmicrotime();
	write_footer();
	echo "<!-- Created in ".round($time_end-$time_start, 3)." seconds -->\n";
	echo "</body>\n</html>\n";
?>
