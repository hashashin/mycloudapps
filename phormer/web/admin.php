<?php
	require_once('funcs.php');
	$time_start = getmicrotime();

	#session_start(); //useless and causes FF in Mac to deal with nothingness!
	global $hasgd;

	$alert_msg = '';
	$ok_msg = '';
	$goafterlogin = '';
	$afterInstall = false;
	$isAdmin = false;

	update_admin_pass_file();

	$ignore = (isset($_GET['ignore']) && strcmp($_GET['ignore'], "all") == 0)
				|| (isset($_GET['page']) && strcmp($_GET['page'], "editxml") == 0 && isset($_GET['cmd']));

	$up = '/'; // for the manner of cookies

	if (!file_exists(ADMIN_PASS_FILE)) {
		$page = (isset($_GET['page']) && (strcmp($_GET['page'], 'doinstall') == 0))?'doinstall':'install';
	}
	else {
		if (isset($_COOKIE['phormer_passwd']) && auth_admin($_COOKIE['phormer_passwd'])) {// AUTH: OK
			$isAdmin = true;
			$page = isset($_GET['page'])?$_GET['page']:'welcome';
		}
		else {
			$invalidpasswd = (!isset($_POST['passwd'])) || (!auth_admin(md5($_POST['passwd'])));
			if ($invalidpasswd) {
				$goafterlogin = isset($_GET['page'])?$_GET['page']:"";
				$page = 'wrong';
				if (isset($_POST['passwd']))
					$alert_msg = "Wrong password!";
			}
			else { // !$invalidpasswd
				$isAdmin = true;
				setcookie('phormer_passwd', md5($_POST['passwd']), time()+3600*24, $up);
				$page = isset($_GET['page'])?$_GET['page']:'welcome';
				if (isset($_GET['page']) && strcmp($_GET['page'], "logout") == 0 && isset($_POST['passwd']))
					$page = 'welcome';

				if (!$ignore) {
					CleanTemp();

					$ca0 = array();
					$ca1 = array();
					$conts = array( "categories"=> "Category",
									"stories" 	=> "Story",
									"photos" 	=> "Photo",
									"basis" 	=> "Basis",
									"comments" 	=> "Comment");

					$alert_msg = "";
					reset($conts);
					foreach($conts as $cfile => $ceach) {
						parse_container("ca0", 	$ceach, "data/$cfile.xml.bku");
						parse_container("ca1", 	$ceach, "data/$cfile.xml");
						if (count($ca0) > count($ca1)) // some items missed
							$alert_msg .= "<tt>data/$cfile.xml</tt> is corrupt. "
										."verify and <a href=\"?page=editxml\">restore</a> it.<br />\n";
					}

					if (strlen($alert_msg))
						$alert_msg .= getHelp("XML Missed");
					else
						GetBackUp();
				}
			}
		}
	}

	if (page_is('install') || page_is('doinstall')) {// came from $_GET['page'] where user is authenticated!
		if (file_exists(ADMIN_PASS_FILE)) {
			$alert_msg = "Use 'change password' menu to change the password!";
			$page = 'welcome';
		}
		if (file_exists("data/photos.xml")) {
			$photos = array();
  			parse_container('photos', 	'Photo', 	'data/photos.xml');
  			if ($photos['lastpid'] > 0) {
  				$alert_msg = "File ".ADMIN_PASS_FILE." (Administrator's Password) is missed!<br /> "
  					."replace one with your raw password inside, there."
  					.getHelp("adminPass.inf missed");
  				$page = 'wrong';
  			}
		}
	}

	if (page_is('doinstall')) {
		if (!isset($_POST['newpasswd'])) {
			$alert_msg = 'All fields are required!';
			$page = 'install';
		}
		else if (strlen($_POST['newpasswd']) < 4) {
			$alert_msg = 'Too short password!';
			$page = 'install';
		}
		else if ((strcmp($_POST['newpasswd'], $_POST['newpasswd2']) != 0)) {
			$alert_msg = "Entered passwords was not identical. try again!";
			$page = 'install';
		}
	}

	if (page_is('dochangepass')) {
		if ((!isset($_POST['curpasswd'])) || (!isset($_POST['newpasswd1'])) || (!isset($_POST['newpasswd2']))) {
			$alert_msg = 'All fields are required!';
			$page = 'changepass';
		}
		else if (!auth_admin(md5($_POST['curpasswd']))) {
			$alert_msg = 'Wrong current password!';
			$page = 'changepass';
		}
		else if (strcmp($_POST['newpasswd1'], $_POST['newpasswd2']) != 0) {
			$alert_msg = 'New passwords are not identical!';
			$page = 'changepass';
		}
		else if (strlen($_POST['newpasswd1']) < 4) {
			$alert_msg = 'Too short password!';
			$page = 'changepass';
		}
	}

	$firsttime = false;

	if (page_is('doinstall') || page_is('dochangepass')) {
		if (page_is('doinstall')) {
			if (!is_writable('.')) {
				clearstatcache();
				die("ERROR: <a href=\".\">Base directory</a> is not writable.<br />\n"
					."Just give them the a+w (write premission to all) access for installation and remove"
					."it when installation finished.");
			}
			$ok_msg .= '<br /> Now, just fill the fields below and start adding photos in the Admin Page!';

			if (! file_exists("temp"))
				if (! mkdir("temp"))
					die("could not create admin/temp directory.");

			if (! file_exists("data"))
				if (! mkdir("data", 0700))
					die("could not create data directory.");

			if (! file_exists("images"))
				if (! mkdir("images"))
					die("could not create images directory.");

			@chmod(".", 		0711);
			@chmod("temp", 		0711);
			@chmod("data", 		0700);
			@chmod("images", 	0711);

			if ($handle = opendir('files/adminfiles/')) {
				while (false !== ($file = readdir($handle)))
					if ($file != "." && $file != ".." && (strcasecmp(substr($file, -4), ".def") == 0)) {
						if (! copy ("files/adminfiles/".$file, "data/".substr($file, 0, -4)))
							die("could not copy admin/files/$file to /data.");
						#unlink("files/adminfiles/".$file); # they might be handy in reinstall
					}
				closedir($handle);
			}

			build_rss();

			$firsttime = true;
		}

		$pass = page_is('doinstall')?$_POST['newpasswd']:$_POST['newpasswd1'];
		write_admin_pass_file($pass);

		setcookie('phormer_passwd', md5($pass), time()+3600*24, $up);
		$_COOKIE['phormer_passwd'] = md5($pass);

		if (page_is('doinstall')) {
			$ok_msg = "Installation Completed!<br />Now, just fill the following fields and start adding photos.";
			$afterInstall = true;
		}
		else
			$ok_msg = "Administrator's password updated successfully!";

		$page = page_is('doinstall')?'basis':(strlen($ok_msg)?'welcome':'changepass');
		if (!$ignore)
			parse_container('basis', 'Basis', 'data/basis.xml');

		if (!chmod(ADMIN_PASS_FILE, 0600))
			$alert_msg = "Could not change adminPass.inf's premission. it might be readable by anyone!";
		else
			$alert_msg = "";
	}

	if (page_is('logout')) {
		setcookie('phormer_passwd', " ", time()+60, $up);
		$_COOKIE['phormer_passwd'] = " ";
		$ok_msg = "Feel free to back, anytime you wished!<br />\n"
				."I'm waiting for you to rephorm me, again! ;)<br />\n";

		GetBackUp();
		$ok_msg .= "PS. Backup caught!<br />\n";
		$page = 'wrong';
	}

	if (!page_is('install')) {
		$basis = array();
		if (!$ignore)
			parse_container('basis', 'Basis', 'data/basis.xml');
	}

	### page is fixed and verified till now,
	### so just do the cmd!

	if (array_search($page, array('welcome', 'wrong', 'doneinside', 'doneoutside', 'logout', 'dochangepass',
								   'doinstall', 'install', 'doinstall',
								  'photos', 'categories', 'stories', 'basis', 'editxml', 'comments',
								  'changepass', 'uninstall', 'configs', 'drafts')) === FALSE) {
		$alert_msg = "Invalid page ($page)! Pick one below:";
		$page = 'welcome';
	}

	$afterfirsttime = isset($_GET['firsttime']);
	$noheader = $afterfirsttime; // ||
	if (!$noheader) {
	switch($page) {
		case 'photos':		$pageNameTitle = "Manage Photos"; 			break;
		case 'categories':	$pageNameTitle = "Manage Categories"; 		break;
		case 'stories':		$pageNameTitle = "Manage Stories"; 			break;
		case 'comments':	$pageNameTitle = "Manage Comments"; 		break;
		case 'drafts':		$pageNameTitle = "Manage Drafts";			break;
		case 'basis':		$pageNameTitle = "Adjust Preferences"; 		break;
		case 'editxml':		$pageNameTitle = "Edit XML files"; 			break;
		case 'doneoutside':
		case 'logout':		$pageNameTitle = "Logging out"; 			break;
		case 'install':		$pageNameTitle = "Installation Process";	break;
		case 'wrong':		$pageNameTitle = "Login Page"; 				break;
		case 'changepass':	$pageNameTitle = "Change Password";			break;
		case 'configs':		$pageNameTitle = "Advanced Configurations";	break;
		case 'uninstall':	$pageNameTitle = "Uninstall Process";		break;
		default: 			$pageNameTitle = "Administration Region"; 	break;
	}
	if (isset($basis['pgname']))
		$pageNameTitle .= " of ".$basis['pgname'];
	$headName = isset($basis['pgname'])?$basis['pgname']:"PhotoGallery";
	if (page_is('basis') && isset($_POST['pgname']))
		$headName = $_POST['pgname'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="files/adminfiles/admin.css">
	<script type="text/javascript" language="javascript" src="files/adminfiles/admin.js"></script>
	<script type="text/javascript" language="javascript" src="files/adminfiles/skeleton.js"></script>
	<script type="text/javascript" language="javascript" src="files/adminfiles/help.js"></script>
	<title><?php echo $pageNameTitle; ?></title>
<?php
	if (isset($basis['icon']) && strlen($basis['icon'])) {
		echo "\t\t<link rel=\"icon\" href=\"".$basis['icon']."\">\n";
		echo "\t\t<link rel=\"shortcut icon\" href=\"".$basis['icon']."\">\n";
	}
?>
</head>

<body onload="javascript:PrepareBody();" <?php if (page_is('wrong')) { ?>
onblur="javascript:blured=true;" onfocus="javascript:if(blured){try{dg('loginAdminPass').focus();blured=false;}catch(e){}}"
<?php } ?>>
<center>

	<div id="helpBoxContainer" class="Helping" style="">
		<table cellpadding="0" cellspacing="0" ondblclick="HideHelp();" onmouseup="DontHideHelp();"	id="helpBox">
			<tr><td colspan="2" rowspan="2" style="background-color: #F9F9F9; ">
				<div class="fieldCont" <?php if (!IsIE()) echo 'style="margin-bottom: -1px;"'; ?>>
					<fieldset>
						<legend><span id="helpBoxLegend"></span>
							&nbsp;<a onclick="HideHelp();">[Dismiss]</a>
						</legend>
						<div id="helpBoxInner"></div>
					</fieldset>
				</div>
				</td>
				<td	width="10px" height="1px">&nbsp;</td></tr>
			<tr><td width="10px" height="55px" class="dotbgful">&nbsp;</td></tr>
			<tr><td width="10px">&nbsp;</td><td width="295px" class="dotbgful" colspan="2">&nbsp;</td></tr>
			<tr><td width="10px">&nbsp;</td><td width="285px">&nbsp;</td><td width="10px">&nbsp;</td></tr>
		</table>
	</div>



<div id="Granny">
	<div id="headerBar">
		<span class="VeryTitle">
			<span class="topHeadAround">&#149;</span>
			<a target="_blank" style="color: black" href=".">&#147;<?php echo $headName; ?>&#148; </a>
		</span>
		 <a style="color: snow;" href="?">Administration Region</a>
	</div>
<?php if (!page_is('doinstall') && !page_is('install') && $isAdmin) { ?>
		<div class="shortCuts">
			<a href="?page=photos"    title="Manage Photos">			<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_photos.gif"   /></a>
			<a href="?page=drafts"    title="Manage Drafts">			<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_drafts.gif"   /></a>
			<a href="?page=categories"title="Manage Categories">		<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_categs.gif"   /></a>
			<a href="?page=stories"   title="Manage Stories">			<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_stories.gif"  /></a>
			<a href="?page=comments"  title="Manage Comments">			<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_comments.gif" /></a>
			<span>::</span>
			<a href="?page=basis"  	  title="Adjust Preferences">		<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_preferences.gif" /></a>
			<a href="?page=configs"   title="Advanced Configurations">	<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_configs.gif" /></a>
			<a href="?page=editxml"   title="XML Editor">				<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_xml.gif" /></a>
			<span>::</span>
			<a href="?page=logout"    title="Log Out">					<img width="20px" height="20px" class="logo" src="files/adminfiles/logo_logout.gif" /></a>
		</div>
<?php } ?>
	<div>

<?php
	}

	if (page_is('welcome')) {
		CleanTemp();

		if (!chmod(ADMIN_PASS_FILE, 0711))
			$alert_msg = "Could not change adminPass.php's premission. it might be readable by anyone!";
?>


		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="clearer"></div>
		<div class="part" style="margin-top: 18px;">
			<div class="title">
				<a style="color: white" href="?page=welcome">Administration Region</a>
				<?php writeHelp("Administration Region"); ?>
			</div>
			<div class="inside">
				<?php
					parse_container('comments', 'Comment', 'data/comments.xml');
					$lastcmnt = (int)$comments['lastiid'];
					end($comments);
					if (isset($basis['lastcmntseen']) && ($lastcmnt > $basis['lastcmntseen'])) {
						$notseen = ($lastcmnt - $basis['lastcmntseen']);
						if ($notseen == 1)
							$notseen = "one";
						echo "<div class=\"method\"><div class=\"note_valid\">"
							."<a href=\"?page=comments\">"
							."&nbsp; &nbsp; You have $notseen new comment".(($notseen != "one")?"s":"")."!"
							."&nbsp;<img class=\"logo\" width=\"39px\" height=\"21px\" src=\"files/adminfiles/logo_newcomment.gif\" />"
							."</a></div></div><br />\n";
						}
					if (strlen($ok_msg))
						echo "<div class=\"method\"><div class=\"note_valid\">$ok_msg</div></div><br />\n";
					if (strlen($alert_msg))
						echo "<div class=\"method\"><div class=\"note_invalid\">$alert_msg</div></div><br />\n";

				?>
				<noscript>
					<div class="method"><div class="note_invalid">Please activate javascript for the proper performance.</div></div>
					<br />
				</noscript>
				<table width="100%" cellspacing="0" cellpadding="0" style="position: relative;"><tr>
					<td width="49%" valign="top">
						<div class="method">
							<span class="name"><span class="dot">&#149;</span>Manage Works:
							</span><br />
							<a href="?page=photos"    ><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_photos.gif"   />Manage Photos</a>
								<span style="color: #789"> [ <a href="?page=photos&cmd=doAdd">Add</a> ]</span>
								<?php writeHelp("Manage Photos"); ?><br />
							<a href="?page=drafts"    ><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_drafts.gif"   />Manage Drafts</a>
								<span style="color: #789"> [ <a href="?page=drafts&cmd=doUpload">Upload</a> ]</span>
								<?php writeHelp("Manage Drafts"); ?><br />
							<a href="?page=categories"><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_categs.gif"   />Manage Categories</a>
								<?php writeHelp("Manage Categories"); ?><br />
							<a href="?page=stories"   ><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_stories.gif"  />Manage Stories</a>
								<?php writeHelp("Manage Stories"); ?><br />
							<a href="?page=comments"  ><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_comments.gif" />Manage Comments</a>
								<?php writeHelp("Manage Comments"); ?><br />
						</div>
					</td>
					<td width="49%" valign="top">
						<div class="method">
							<span class="name"><span class="dot">&#149;</span>Technical Settings:</span><br />
								<a href="?page=basis"     ><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_preferences.gif" />Adjust Preferences</a>
							<?php writeHelp("Adjust Preferences"); ?><br />

							<a href="?page=configs"   ><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_configs.gif" />Advanced Configurations</a>
								<?php writeHelp("Advanced Configurations"); ?><br />
							<a href="?page=editxml"   ><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_xml.gif" />XML Editor</a>
								<?php writeHelp("XML Editor"); ?><br />
							<br style="margin-top: 6px;" />
							<a href="?page=logout"    ><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_logout.gif" />Log Out</a>
								<?php writeHelp("Log Out"); ?><br />
						</div>
					</td>
				</tr></table>
			</div>
		</div>
<?php
	}

	if (page_is('wrong')) {
?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="clearer" style="margin-top: 20px;"></div>
<?php
		$last_ver = GetLastPhormerVersion();
		$msg = "";
		$msg_color = "#C33";

		if ($last_ver == "")
			$msg = "<b>Failed</b>: Unable to connect to <a href=\"http://p.horm.org/er\">Phormer</a> to check for available"
				." updates!";
		else if (strcmp(trim(PHORMER_VERSION), trim($last_ver)) == 0) {
			$msg = "Great! You're running last version of <a href=\"http://p.horm.org/er\">Phormer</a>"
				.", which is ".PHORMER_VERSION."!";
			$msg_color = "#060";
		}
		else { //if (PHORMER_VERSION < $last_ver)
			$msg_color = "#933";
			$msg = "Your Phormer (ver ".PHORMER_VERSION.") is out of date, since the last version "
				." is $last_ver.<br />"
				." <a href=\"http://p.horm.org/er/faq/#update\">Update</a> at Phormer's homepage. "
				."<b>Don't Hesitate -</b> It takes just 3 minutes!";
		}
?>

		<div class="part">
			<div class="title"><a style="color: white" href="?">Welcome!</a></div>
			<div class="inside">

				<div class="method" style="text-align: left">
				<span class="name"><a style="color: black" href="?">Login to Your Phormer</a></span>
					<form method="post" action="admin.php<?php if (strlen($goafterlogin)) echo "?page=$goafterlogin"; ?><?php if (isset($_GET['ignore'])) echo "&ignore=".$_GET['ignore']; ?>">
						<center>
							<?php if (strlen($alert_msg)) echo "<div class=\"note_invalid\">$alert_msg</div><br />"; ?>
							<?php if (strlen($ok_msg)) 	  echo "<div class=\"note_valid\">$ok_msg</div><br />"; ?>
							Administrator's password: <div style="margin-bottom: 10px;" /></div>
							<input id="loginAdminPass" name="passwd" type="password" class="text" size="12"
								   style="font-size: 2em; font-family: courier new, tahoma, helvetica, serif; font-weight: bold;"></input>
							<br /><br />
							<input class="submit" type="submit" value="&nbsp;&nbsp;&nbsp;Login&nbsp;&nbsp;&nbsp;"></input>
						</center>
					</form>
				</div>

				<br /><br />

				<div class="method" style="text-align: left; background: #E8F0F8">
				<span class="name"><a style="color: black" href="?">Update Your Phormer</a>
					<?php writeHelp("Update Message"); ?>
				</span>
					<div style="text-align: center; font-size: 1.0em; margin: 0px 0px 8px; ">
						<?php echo "<span style=\"color: $msg_color;\">$msg</span>"; ?>
					</div>
				</div>

			</div>
		</div>
<?php
	}

	if (page_is('uninstall')) {
		if (!isset($_GET['sure']))
			$_GET['sure'] = 0;

		$ok_msg = "";
		$log_msg = "";
		if ($_GET['sure'] >= 2) {
			$dirs = array('temp', 'images/extraz', 'images', 'data', 'index.xml');
			foreach ($dirs as $dir) {
				if (is_dir($dir)) {
					if ($handle = opendir($dir)) {
						while (false !== ($file = readdir($handle)))
							if ($file != "." && $file != "..")
								unlink($dir."/".$file);
						closedir($handle);
						$log_msg .= "<span class=\"dot\">&#149;</span>Deleted all the files in &quot;<tt>$dir</tt>&quot; Directory.<br/ >\n";
						rmdir($dir);
						$log_msg .= "<span class=\"dot\">&#149;</span>Deleted &quot;<tt>$dir</tt>&quot; Directory.<br />\n";
					}
				} else
					if (file_exists($dir)) { // is file
						unlink($dir);
						$log_msg .= "<span class=\"dot\">&#149;</span>Deleted &quot;<tt>$dir</tt>&quot; File.<br />\n";
					}
			}

			$ok_msg = "Phormer is just uninstalled, successfully<br />\n"
					."This is the very last page of Phormer and it has removed all its files!<br />\n"
					."<br />\n"
					."Now, just <tt>rm -rf *</tt> the installation directory.<br />\n"
					."<br />\n"
					."Have fun!<br />\n"
					."<br />\n";
		}

?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="back2main"><a href="?">&lt;&lt; Admin Page</a></div>
		<div class="clearer" style="margin-top: 27px;"></div>
		<?php
			if (strlen($ok_msg))
				echo "<div class=\"method\" style=\"text-align: left;\"><span class=\"name\">Phormer Uninstalled!</span>"
					."<div style=\"padding: 10px 40px 20px;\">$log_msg</div>"
					."<div class=\"note_valid\">$ok_msg</div></div><br /><br />";
			else {
		?>
		<div class="method" style="text-align: left">
			<span class="name">Uninstall Phormer</span>
			<div class="inside">
				<center>
					Are you
					<?php
						for ($i=0; $i<$_GET['sure']; $i++)
							echo "<span style=\"color: #900; font-weight: bold;\">REALLY</span> ";
					?>
					sure you want to uninstall phormer?!<br />
					<br />
					<span style="color: #C00; font-weight: bold; font-size: 12px;">Note: This command is not reversible! You'll Lose everything!</span>
					<br /><br />
					<form method="post" action="admin.php?page=uninstall&sure=<?php echo ($_GET['sure']+1); ?>">
						<input class="submit" style="background: #FCB;" type="submit" value="&nbsp;&nbsp;&nbsp;Yes, Dump Phormer!&nbsp;&nbsp;&nbsp;"></input>
					</form>
					&nbsp; &nbsp;
					<form method="post" action="admin.php?">
						<input class="submit" type="submit" value="&nbsp;&nbsp;&nbsp;No! Just Back to Main&nbsp;&nbsp;&nbsp;"></input>
					</form>
					<br />
					<br />
				</center>
			</div>
		</div>
<?php
		}
	}

if (page_is('install')) {
?>
		<div class="clearer" style="margin-top: 57px;"></div>
		<div class="method" style="text-align: left">
			<span class="name">First Time Installation</span>
			<div class="inside">
				<form method="post" action="?page=doinstall" onsubmit="javascript:return checkInstallPass();">
					<center>
						<div class="note_valid">
							Welcome to Phormer for the first time! <br />
							Question marks, like <?php writeHelp("help"); ?> helps you. <br />
						</div>
						<br />
						<div style="margin : 10px 160px; text-align: left; padding: 10px 20px; border: 3px double #FFF; background: #E8F0F8; ">
						<?php
							$exts = array("GD" => $hasgd, "ZIP" => $haszip, "EXIF" => $hasexif);
							reset($exts);
							foreach ($exts as $name => $has) {
								$color = ($has)?"#567":"#A43";
								$msg   = ($has)?"Fortunately, this server has $name extension of PHP."
											   :"Unfortunately, this server does not have $name extension of PHP!";
								echo "\t\t\t\t\t\t<span class=\"dot\">&#149;</span>"
									."<span style=\"color:$color;\">$msg</span>".getHelp($name." Extension")."<br />\n";
							}
						?>
						</div>
						<br />

						<table width="50%" cellpadding="3">
<?php
							if (!is_writable('.')) {
								clearstatcache();
								echo "<div class=\"note_error\">"
										."<b>ERROR: "
										."<a href=\".\">Base directory</a> is not writable by PHP server.<br />\n"
										."</b>Just give it the a+w (write premission to all) access for installation and remove "
										."it when installation completed."
									."</div>";
							}
?>
							<?php if (strlen($alert_msg)) echo "<tr><td colspan=\"2\" class=\"note_invalid\">$alert_msg</td></tr>"; ?>
							<tr><td>Enter your desired Admin password:</td><td><input name="newpasswd" id="newpasswd" type="password" class="text" size="20"></input></td></tr>
							<tr><td>The desired Admin password, again:</td><td><input name="newpasswd2" id="newpasswd2" type="password" class="text" size="20"></input></td></tr>
							<tr><td colspan="2" style="text-align: center"><input class="submit" type="submit" value="&nbsp;&nbsp;&nbsp;Install Phormer&nbsp;&nbsp;&nbsp;"></input></td></tr>
						</table>
					</center>
				</form>
			</div>
		</div>
<?php
	}

if (page_is('doneinside')) {
?>
		<div class="clearer" style="margin-top: 57px;"></div>
		<div class="method" style="text-align: left">
			<span class="name">Action Performed</span>
			<div class="inside" style="padding: 0px 100px">
				<?php if (strlen($alert_msg)) echo "<div class=\"note_valid\">$alert_msg</div><br />"; ?>
				<span class="dot">&#149;</span><a href=".">Main Page</a><br />
				<span class="dot">&#149;</span><a href="?page=logout">Logout</a><br />
				<br />
			</div>
		</div>
<?php
	}

if (page_is('doneoutside')) {
?>
		<div class="clearer" style="margin-top: 57px;"></div>
		<div class="method" style="text-align: left">
			<span class="name">Action Performed</span>
			<div class="inside" style="padding: 0px 100px">
				<?php if (strlen($alert_msg)) echo "<div class=\"note_valid\">$alert_msg</div><br />"; ?>
				<span class="dot">&#149;</span><a href="?">Login</a><br />
				<span class="dot">&#149;</span><a href=".">View PhotoGallery</a><br />
				<br />
			</div>
		</div>
<?php
	}

	if (page_is('editxml')) {
		$theSrc = isset($_GET['src'])?$_GET['src']:(isset($_POST['src'])?$_POST['src']:'');
		$cmd = isset($_GET['cmd'])?$_GET['cmd']:'';
		$open = false;
		$f = '';
		$isOpen = (strcmp($cmd, 'open') == 0);
		$isRestore = (strcmp($cmd, 'restore') == 0);
		$isSave = (strcmp($cmd, 'save') == 0);
		if ((strlen($theSrc) > 0) && ($isOpen || $isRestore || $isSave)) {
			if ($isRestore) {
				if (!file_exists($theSrc.".bku"))
					$alert_msg = "No Backup of the file \"$theSrc\" is available :(!";
				else {
					copy($theSrc.".bku", $theSrc);
					$ok_msg = "Backup successfully restored!";
				}
			}
			else if ($isOpen) {
				if (!file_exists($theSrc))
					$alert_msg = "File not exists!";
				else {
					$f = file_get_contents($theSrc);
					$open = true;
					//$f = strtr($f, $transtable);
					$f = htmlspecialchars($f, ENT_COMPAT, 'UTF-8');
				}
			}
			else if ($isSave) {
				copy($theSrc, $theSrc.".bku");
				$f = fopen($theSrc, "w");
				//echo $_POST['txt']."\n";
				//$_POST['txt'] = strtr($_POST['txt'], $transtable);
				fputs($f, $_POST['txt']);
				fclose($f);
				$ok_msg = "Changes Saved successfully!";
			}
		}
		$defAdd = ((strlen($theSrc)>0) && (strlen($alert_msg) == 0))?$theSrc:"data/categories.xml";
?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="back2main"><a href="?">&lt;&lt; Admin Page</a></div>
		<div class="part">
			<div class="title"><a style="color: white" href="?page=editxml">XML Editor:</a></div>
			<div class="inside">
				<?php if (strlen($alert_msg)) echo "<div class=\"method\"><div class=\"note_invalid\">$alert_msg</div></div><br />"; ?>
				<?php if (strlen($ok_msg)) echo "<div class=\"method\"><div class=\"note_valid\">$ok_msg</div></div><br />"; ?>
				<table cellspacing="0" cellpadding="0" width="100%" style="position:relative"><tr><td width="50%">
					<div class="method">
						<span class="name">Edit Content:</span><br />
						<table width="100%" cellspacing="0" cellpadding="0">
							<tr><td>
								<span class="dot">&#149;</span><a href="?page=editxml&cmd=open&src=data%2Fcategories.xml">Categories</a>
							</td><td>
								<span class="dot">&#149;</span><a href="?page=editxml&cmd=open&src=data%2Fstories.xml">Stories</a>
							</td><td>
								<span class="dot">&#149;</span><a href="?page=editxml&cmd=open&src=data%2Fphotos.xml">Photos</a>
							</td>
							</tr><tr>
							<td>
								<span class="dot">&#149;</span><a href="?page=editxml&cmd=open&src=data%2Fcomments.xml">Comments</a>
							</td><td>
								<span class="dot">&#149;</span><a href="?page=editxml&cmd=open&src=data%2Fbasis.xml">Basis</a>
							</td><td>
							</td></tr>
						</table>
						<center><hr size="1" color="#BBB" width="60%" style="margin: 15px 0px;" /></center>
						<form method="get" action="?" onsubmit="return true;">
							<input type="hidden" name="page" value="editxml"></input>
							<input type="hidden" name="cmd" value="open"></input>
							<span class="dot">&#149;</span>XML Source:
							<input name="src" class="text" size="35" type="text" value="<?php echo $defAdd; ?>"></input>
							<br />
							<center><input style="margin-top: 15px;" class="submit" type="submit" value="Open the File"></input></center>
						</form>
					</div>
				</td><td width="50%">
					<div class="method">
						<span class="name">Restore Backup:</span><br />
						<table width="100%" cellspacing="0" cellpadding="0">
							<tr><td>
								<span class="dot">&#149;</span><a onclick="return ConfirmRestore();" href="?page=editxml&cmd=restore&src=data%2Fcategories.xml">Categories</a>
							</td><td>
								<span class="dot">&#149;</span><a onclick="return ConfirmRestore();" href="?page=editxml&cmd=restore&src=data%2Fstories.xml">Stories</a>
							</td><td>
								<span class="dot">&#149;</span><a onclick="return ConfirmRestore();" href="?page=editxml&cmd=restore&src=data%2Fphotos.xml">Photos</a>
							</td>
							</tr><tr>
							<td>
								<span class="dot">&#149;</span><a onclick="return ConfirmRestore();" href="?page=editxml&cmd=restore&src=data%2Fcomments.xml">Comments</a>
							</td><td>
								<span class="dot">&#149;</span><a onclick="return ConfirmRestore();" href="?page=editxml&cmd=restore&src=data%2Fbasis.xml">Basis</a>
							</td><td>
							</td></tr>
						</table>
						<center><hr size="1" color="#BBB" width="60%" style="margin: 15px 0px;" /></center>
						<form method="get" action="?" onsubmit="return ConfirmRestore();">
							<span class="dot">&#149;</span>XML Source:
							<input type="hidden" name="page" value="editxml"></input>
							<input type="hidden" name="cmd" value="restore"></input>
							<input name="src" class="text" size="35" type="text" value="<?php echo $defAdd; ?>"></input>
							<br />
							<center><input style="margin-top: 15px;" class="submit" type="submit" value="Restore Backup"></input></center>
						</form>
					</div>
				</td></tr></table>
				<?php if ($open) { ?>
				<div class="method" style="margin-top: 25px;">
					<span class="name">XML Content of "<tt><?php echo $theSrc; ?></tt>":</span><br />
					<center>
						<form enctype="multipart/form-data" method="post" action="?page=editxml&cmd=save" onsubmit="return ConfirmSave();">
							<textarea class="textarea" rows="10" cols="70" name="txt"><?php echo $f; ?></textarea><br /><br />
							<input class="text" type="hidden" name="src" value="<?php echo $theSrc; ?>"></input>
							<input class="submit" type="submit" value="&nbsp;Save Changes&nbsp;"></input>
							<span style="padding-left: 20px;"></span>
							<input class="reset" type="reset" value="&nbsp;Reset Changes &nbsp;"></input>
						</form>
					</center>
				</div>
				<?php } ?>
			</div>
		</div>
<?php
	}

if (page_is('categories')) {
	$categs = array();
	$edit = false;
	$doDel = false;
	$ok_msg = '';
	$alert_msg = '';
	$cmd = '';
	$cid = '';
	parse_container('categs', 'Category', 'data/categories.xml');
	handle_container('categs', 'Category', 'c');
	if (!strlen($alert_msg) && strlen($cmd))
		save_container('categs', 'Category', 'data/categories.xml');
	print_container('categs', 'Category', 'Categories', 'c');
?>
<?php
	}

if (page_is('stories')) {
	$stories = array();
	$edit = false;
	$doDel = false;
	$ok_msg = '';
	$alert_msg = '';
	$cmd = '';
	$sid = '';
	parse_container('stories', 'Story', 'data/stories.xml');
	handle_container('stories', 'Story', 's');
	if (!strlen($alert_msg) && strlen($cmd))
		save_container('stories', 'Story', 'data/stories.xml');
	print_container('stories', 'Story', 'Stories', 's');
?>

<?php
	}

if (page_is('basis') || page_is('configs')) {
	if (isset($_GET['cmd'])) {
		$cmd = $_GET['cmd'];
		if (strcmp($cmd, 'save') == 0) {
			if (!isset($_POST['lastInput']))
				$alert_msg = "The page is corrupted, come again!";
			else {
				$criminals = array('extra', 'pgname', 'pgdesc', 'auname', 'auemail', 'icon', 'bannedip');
				foreach ($criminals as $key)
					if (isset($_POST[$key]))
						$_POST[$key] = htmlspecialchars($_POST[$key], ENT_QUOTES, "UTF-8");

				$checkboxes = array("checkcateg", "checkstory", "checklink", "checkstat", "checkextra",
									"checkemail", "checkrss", "checkadmin");
				if (page_is('basis'))
					foreach ($checkboxes as $item)
						if (!isset($_POST[$item]))
							$_POST[$item] = "off";

				foreach ($_POST as $key => $value)
					if (strcmp(substr($key, 0, 5), "dummy") != 0)
						$basis[$key] = $value;
				if (page_is('basis')) {
					$basis['links'] = array();
					while (NULL !== array_pop($basis['links']));
					for ($i=0; $i<$_POST['nLink']; $i++)
						array_push($basis['links'],
							array('name'  => $_POST["l${i}n"],
								  'href'  => $_POST["l${i}h"],
								  'title' => $_POST["l${i}t"]));
				}

				save_container('basis', 'Basis', 'data/basis.xml');
				foreach ($criminals as $key)
					if (isset($_POST[$key]))
						$_POST[$key] = strtr($_POST[$key], $transtable);
				$smoothcriminals = array('pgname', 'auname', 'auemail', 'icon');
				foreach ($smoothcriminals as $key)
					if (isset($basis[$key]))
						$basis[$key] = strtr($basis[$key], $transmanual);


				$ok_msg = "Changes to ".(page_is('basis')?"basis":"configurations").", saved successfully!";
				# echo "<pre>".print_r($basis, true)."</pre>";
				if (isset($_GET['firsttime']))
					header('Location:?page=welcome');
			}
		}
		else
			$alert_msg = $cmd.' is not a valid command!';
	}
}

$field_sep = "\t\t\t\t\t\t<tr><td colspan=\"2\"><div class=\"basisTitleHR\">&nbsp;</div></td></tr>\n";

if (page_is('basis')) {
		$disp = "style=\"display:".($afterInstall?"none":"table-row")."\" ";;
?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="back2main"><a href="?">&lt;&lt; Admin Page</a></div>
		<div class="part">
			<div class="title"><a style="color: white" href="?page=basis">Preferences:</a></div>
			<div class="inside">
				<?php if (strlen($alert_msg)) echo "<div class=\"method\"><div class=\"note_invalid\">$alert_msg</div></div><br />"; ?>
				<?php if (strlen($ok_msg)) echo "<div class=\"method\"><div class=\"note_valid\">$ok_msg</div></div><br />"; ?>
				<div class="method">
					<span class="name">Basic Preferences:</span><br />
					<form enctype="multipart/form-data" method="post" action="?page=basis&cmd=save<?php if (isset($firsttime) && ($firsttime)) echo "&firsttime=true"; ?>">
					<center><input style="margin-top: 15px;" class="submit" type="submit" value="Save Changes"></input></center>
					<br />

					<center>
						<table width="80%" cellspacing="0" cellpadding="2" style="text-align: left; position: relative; top: -10px;">

						<?php echo $field_sep; ?>


						<tr><td valign="top">
							<span class="dot">&#149;</span><b>PhotoGallery's Name</b>
							<?php writeHelp("PhotoGallery Name"); ?>:
						</td><td>
							<input name="pgname" class="text" size="30" type="text" value="<?php echo (isset($basis['pgname']))?$basis['pgname']:"My PhotoGallery"; ?>"></input>
						</td></tr>

						<?php # echo $field_sep; ?>


						<tr><td valign="top">
							<span class="dot">&#149;</span><b>PhotoGallery's Description</b>
							<?php writeHelp("PhotoGallery Desc"); ?>:
						</td><td>
							<textarea name="pgdesc" class="textarea" cols="30" type="text" rows="3"><?php echo $basis['pgdesc']; ?></textarea>
						</td></tr>

						<?php echo $field_sep; ?>

						<tr><td valign="top">
							<span class="dot">&#149;</span><b>Author's Name</b>
							<?php writeHelp("Author Name"); ?>:
						</td><td>
							<input name="auname" class="text" size="30" type="text" value="<?php echo $basis['auname']; ?>"></input>
						</td></tr>

						<?php # echo $field_sep; ?>

						<tr><td valign="top">
							<span class="dot">&#149;</span><b>Author's Email Address</b>
							<?php writeHelp("Author Email"); ?>:
						</td><td>
							<input name="auemail" class="text" size="30" type="text" value="<?php echo $basis['auemail']; ?>"></input>
						</td></tr>

						<?php echo $field_sep; ?>

						<tr><td valign="top">
							<span class="dot">&#149;</span><b>Theme</b>
							<?php writeHelp("Theme"); ?>:
						</td><td>
							<span style="padding-left: 7px;"></span>
							<select id="theme" name="theme" class="select" size="1" onchange="javascript:changePrev(this.value, '');">
							<?php
								$defstyles = array( 'white',	'wheat', 	'silver', 	'dusty', 	'sky',
													'bog', 		'purple',	'slate', 	'timber', 	'blacky');
								$styles = $defstyles;
								/* External CSSes */
								if ($handle = opendir('files/externalcss')) {
									while (false !== ($file = readdir($handle))) {
										if ($file != "." && $file != ".." && !is_dir($file) &&
										(strcmp(substr($file, -4), '.css') == 0))
											array_push($styles, $file);
									}
									closedir($handle);
								}

								foreach ($styles as $theme) {
									$sel = (strcmp($theme, $basis['theme']) == 0);
									$ext = in_array($theme, $defstyles)?"":"+ External: ";
									echo "\t\t\t\t\t\t<option ".($sel?"selected=\"selected\"":"")."value=\"$theme\">".$ext.ucfirst($theme)."</option>\n";
								}
							?>
							</select>
							<span style="padding-left: 10px;"></span>
							[<a target="_blank" style="" id="prevmode2" href=".?theme=<?php echo $basis['theme']; ?>">Preview</a>]
						</td></tr>

						<?php if (!$afterInstall) echo $field_sep; ?>

						<tr <?php echo $disp; ?>><td valign="top">
							<span class="dot">&#149;</span><b>Index Page Layout</b>
							<?php writeHelp("Index Mode"); ?>:
						</td><td>
							[<a target="_blank" id="prevmode1" href=".?mode=<?php echo $basis['mode']; ?>">Preview</a>]
						</td></tr>
						<tr <?php echo $disp; ?>><td colspan="2">
								<table style="margin: 20px 0px; background: #9AB; border: 1px solid #678;" width="100%">
									<tr style="background: #BCD">
										<td style="text-align:center" width="180px"><b>SideBar</b></td>
										<td style="text-align:center"><b>Main Column</b></td>
									</tr>
									<tr style="background: #F8FCFF;">
										<td valign="top" style="padding: 15px 0px 20px 30px;" width="180px">
										<?php
											$hr = "\t\t\t\t\t<hr width=\"70%\" size=\"1\" style=\"margin: 10px 0px 10px 10px;\" color=\"#999\" />\n";

											writeSideCheckBox("checkcateg", "Categories List");
											echo $hr;
											writeSideCheckBox("checkstory", "Stories List");
											echo $hr;
											writeSideCheckBox("checklink", "Links list");
											echo $hr;
											writeSideCheckBox("checkstat", "Statistics");
											echo $hr;
											writeSideCheckBox("checkextra", "Extra HTML");
											echo $hr;
											writeSideCheckBox("checkemail", "Email");
											writeSideCheckBox("checkrss", "RSS");
											writeSideCheckBox("checkadmin", "Admin Page");
										?>
										</td>
										<td id="allModes" valign="top" style="text-align:center; padding: 20px 0px 10px;">
										<?php
											$mode = $basis['mode'];
											$tok = explode("-", $mode);
											$n = 0;
											foreach ($tok as $t)
												writeMainColDropDown($t, $n++);
										?>
										<a style="cursor: pointer" onclick="javascript:addMainColDiv();">More Item &#133;</a><br /><br />
										</td>
									</tr>
								</table>
								<input type="hidden" id="mode" name="mode" class="text" value="<?php echo $basis['mode']; ?>"></input>
						</td></tr>

						<?php if (!$afterInstall) echo $field_sep; ?>

						<tr <?php echo $disp; ?>><td colspan="2">
							<span class="dot">&#149;</span><b>External Links &#133; </b>
							<?php writeHelp("External Links"); ?>:
						</td></tr>

						<tr <?php echo $disp; ?>><td colspan="2">
							<table width="100%" cellspacing="0" cellpadding="2" id="allLinkLines">
								<tr style="text-align: center; font-weight: bold;">
									<td> No </td>
									<td> Link Name 	<?php writeHelp("Link Name"); ?></td>
									<td> Link URL 	<?php writeHelp("Link URL"); ?></td>
									<td> Link Title <?php writeHelp("Link Title"); ?></td>
									<td> Add/Del <?php writeHelp("Add/Del Link"); ?></td>
								</tr>
<?php
	$n = 0;
	reset($basis['links']);
	while (list($key, $val) = each($basis['links']))
		writeLinkLine($n++, $val);
	reset($basis['links']);
?>
								</tr>
							</table>
							<input type="hidden" name="nLink" id="nLink" value="<?php echo $n; ?>"></input>
							<input type="hidden" name="lastInput" value="lastInput"></input>
						</td></tr>
					</table>
					<center><input style="margin-top: 15px;" class="submit" type="submit" value="Save Changes"></input></center>
					<div class="basisTitle" style="border-top-width: 0px;"></div>
					</center>
					</form>
				</div>
			</div>
		</div>
<?php
}

if (page_is('configs')) {
	global $basis;
	if (!isset($basis['copyright']))
		$basis['copyright'] = DEFAULT_COPYRIGHT;
?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="back2main"><a href="?">&lt;&lt; Admin Page</a></div>
		<div class="part">
			<div class="title"><a style="color: white" href="?page=configs">Configurations:</a></div>
			<div class="inside">
				<div class="method">
					<span class="name">External Modular Actions
					<?php writeHelp("Modular Actions"); ?>:</span><br />
					<div style="padding: 5px 50px; color: #666; line-height: 200%;">
						<span class="dot">&#149;</span><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_lock.gif" /><a href="?page=changepass">Change Password</a>
							<?php writeHelp("Change Password"); ?><br />
						<span class="dot">&#149;</span><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_death.gif" /><a href="?page=uninstall">Uninstall Phormer</a>
							<?php writeHelp("Uninstall Phormer"); ?><br />
						<!--
						<br />
						<span class="dot">&#149;</span><img width="20px" height="20px" class="logo" src="files/adminfiles/logo_configs.gif" /><a href="#AdvancedConfigurations">Advanced Options</a>
							<?php writeHelp("Advanced Options Link"); ?><br />
						-->
					</div>
				</div>

				<br />
				<?php if (strlen($alert_msg)) echo "<div class=\"method\"><div class=\"note_invalid\">$alert_msg</div></div>"; ?>
				<?php if (strlen($ok_msg)) echo "<div class=\"method\"><div class=\"note_valid\">$ok_msg</div></div>"; ?>

				<a name="AdvancedConfigurations"></a>
				<br style="margin-top: 5px;" />

				<div class="method">
					<span class="name">Advanced Options:</span><br />
					<form enctype="multipart/form-data" method="post" action="?page=configs&cmd=save<?php if (isset($firsttime) && ($firsttime)) echo "&firsttime=true"; ?>">
					<center>
						<br />

						<center><input class="submit" type="submit" value="Save Changes"></input></center>
						<br />

						<table width="80%" cellspacing="0" cellpadding="2" style="text-align: left; position: relative; top: -10px;">

							<?php echo $field_sep; ?>

							<tr><td valign="top">
								<span class="dot">&#149;</span><b>Default Num of Photos in Box Mode</b>
								<?php writeHelp("Default Photo Num in Box"); ?>:
							</td><td>
								&nbsp;&nbsp;
								<select name="defjbn" class="select" size="1">
								<?php
									if (!isset($basis['defjbn']))
										$basis['defjbn'] = 50;
									$values = array(5, 10, 20, 50, 75, 100, 200, 1000);
									foreach ($values as $v) {
										$sel = ($basis['defjbn'] == $v)?" selected=\"selected\"":"";
										echo "\t\t\t\t\t<option value=\"$v\"$sel>$v</option>\n";
									}
								?>
								</select>
							</td></tr>
							<tr><td valign="top">
								<span style="padding-left: 93px;"></span> <b>Photos in Recent Photos</b>
								<?php writeHelp("Default Photo Num in Recents"); ?>:
							</td><td>
								&nbsp;&nbsp;
								<select name="defrpc" class="select" size="1">
								<?php
									if (!isset($basis['defrpc']))
										$basis['defrpc'] = 20;
									$values = array(5, 10, 15, 20, 25, 30, 40, 50, 75, 100, 200, 1000);
									foreach ($values as $v) {
										$sel = ($basis['defrpc'] == $v)?" selected=\"selected\"":"";
										echo "\t\t\t\t\t<option value=\"$v\"$sel>$v</option>\n";
									}
								?>
								</select>
							</td></tr>
							<tr><td valign="top">
								<span style="padding-left: 93px;"></span> <b>Photos in Top Rated/Visited</b>
								<?php writeHelp("Default Photo Num in Tops"); ?>:
							</td><td>
								&nbsp;&nbsp;
								<select name="deftrc" class="select" size="1">
								<?php
									if (!isset($basis['deftrc']))
										$basis['deftrc'] = 10;
									$values = array(5, 10, 15, 20, 25, 30, 40, 50, 75, 100, 200, 1000);
									foreach ($values as $v) {
										$sel = ($basis['deftrc'] == $v)?" selected=\"selected\"":"";
										echo "\t\t\t\t\t<option value=\"$v\"$sel>$v</option>\n";
									}
								?>
								</select>
							</td></tr>
							<tr><td valign="top">
								<span style="padding-left: 93px;"></span> <b>Stories in Story mode</b>
								<?php writeHelp("Default Story Num in mode"); ?>:
							</td><td>
								&nbsp;&nbsp;
								<select name="defrsc" class="select" size="1">
								<?php
									if (!isset($basis['defrsc']))
										$basis['defrsc'] = 5;
									$values = array(1, 3, 5, 10, 20, 50, 1000);
									foreach ($values as $v) {
										$sel = ($basis['defrsc'] == $v)?" selected=\"selected\"":"";
										echo "\t\t\t\t\t<option value=\"$v\"$sel>$v</option>\n";
									}
								?>
								</select>
							</td></tr>
							<tr><td valign="top">
								<span style="padding-left: 93px;"></span> <b>Stories in Sidebar</b>
								<?php writeHelp("Default Story Num in Sidebar"); ?>:
							</td><td>
								&nbsp;&nbsp;
								<select name="defrss" class="select" size="1">
								<?php
									if (!isset($basis['defrss']))
										$basis['defrss'] = 100;
									$values = array(1, 3, 5, 10, 20, 30, 50, 75, 100, 1000);
									foreach ($values as $v) {
										$sel = ($basis['defrss'] == $v)?" selected=\"selected\"":"";
										echo "\t\t\t\t\t<option value=\"$v\"$sel>$v</option>\n";
									}
								?>
								</select>
							</td></tr>

							<?php echo $field_sep; ?>

							<tr><td valign="top">
								<span class="dot">&#149;</span><b>Transparency Percentage</b>
								<?php writeHelp("Transparency Percentage"); ?>:
							</td><td>
								&nbsp;&nbsp;
								<select name="opac" class="select" size="1">
								<?php
									global $basis;
									if (!isset($basis['opac']))
										$basis['opac'] = DEFAULT_OPAC_RATE;
									for ($i=0; $i<=100; $i += 10) {
										$sel = ($i == $basis['opac'])?" selected=\"selected\"":"";
										echo "\t\t\t\t<option$sel value=\"$i\">$i %</option>\n";
									}
								?>
								</select>
							</td></tr>

							<?php echo $field_sep; ?>

							<tr><td valign="top">
								<span class="dot">&#149;</span><b>Jpeg Compression Percentage</b>
								<?php writeHelp("Jpeg Compression Percentage"); ?>:
							</td><td>
								&nbsp;&nbsp;
								<select name="jpegq" class="select" size="1">
								<?php
									global $basis;
									if (!isset($basis['jpegq']))
										$basis['jpegq'] = DEFAULT_JPEG_QUAL;
									for ($i=0; $i<=100; $i += 10) {
										$sel = ($i == $basis['jpegq'])?" selected=\"selected\"":"";
										echo "\t\t\t\t<option$sel value=\"$i\">$i %</option>\n";
									}
								?>
								</select>
							</td></tr>

							<?php

								echo $field_sep;

								write_radio_list("Help System Display", 'Help System',
												 'helplang', array('en', 	'it', 	'off'),
															 array('English', 'Italian', 	"Don't Show"));

								echo $field_sep;

								write_radio_list("Display Author's Email Style", 'Display Email',
												 'showemail', array('link', 	'asis', 	'text', 'hide'),
															  array('Modified', 'As Is', 	'Text', 'Hidden'));

								echo $field_sep;

								write_radio_list("Thumbnails' Links Target Window", 'Thumb Links Target',
												 'linktarget', array('_self', 'default', '_blank'),
															   array('Same', 'Common', 'New'));

								echo $field_sep;

								write_radio_list('Pick Neighbours from ...', 'Pick Neighbours',
												 'pickneigh', array('all', 'categs', 'stories'),
															  array('Both', 'Category', 'Story'));

								echo $field_sep;

								write_radio_list('Word verification ', 'Word Verification',
												 'haswvw', array('yes', 'no'),
														   array('Enable', 'Disable'));

								echo $field_sep;

								write_radio_list('Check for Updates ', 'Check Updates',
												 'updatecheck', array('enable', 'disable'),
																array('Enable', 'Disable'));

							 	echo $field_sep;
							?>

							<tr><td valign="top">
								<span class="dot">&#149;</span><b>Time Differ with Server</b>
								<?php writeHelp("Time Differ"); ?>:
								<script language="javascript" type="text/javascript">
									nowTime = new Date;
									nowTime.setHours(<?php echo date("G"); ?>);
									nowTime.setMinutes(<?php echo date("i"); ?>);
								</script>
							</td><td>
								<input name="timediffer" id="timediffer" class="text" size="10" type="text"
										onchange="javascript:updateTimeDiffer(this.value)"
										onkeyup="javascript:updateTimeDiffer(this.value)"
										value="<?php echo (isset($basis['timediffer']))?$basis['timediffer']:"0*60+0"; ?>"></input>
								<b style="font-size: 1.5em;">&rarr;</b> &nbsp; Time'll be (<span id="timeDiffShow"><?php echo date("H:i", GetTimeWithDiffer()); ?></span>)
								<script language="javascript" type="text/javascript">
									updateTimeDiffer(dg('timediffer').value);
								</script>
							</td></tr>

							<?php echo $field_sep; ?>

							</table>
							<table width="80%" cellspacing="0" cellpadding="2" style="text-align: left; position: relative; top: -10px;">

							<tr><td valign="top">
								<span class="dot">&#149;</span><b>HTML icon URL</b>
								<?php writeHelp("HTML icon URL"); ?>:
							</td><td>
								<input name="icon" class="text" size="64" type="text" value="<?php echo (isset($basis['icon']))?$basis['icon']:""; ?>"></input>
							</td></tr>

							<?php echo $field_sep; ?>

							<tr><td valign="top">
								<span class="dot">&#149;</span><b>Copyright Note</b>
								<?php writeHelp("Copyright Note"); ?>:
							</td><td>
								<textarea name="copyright" class="textarea" cols="40" type="text" rows="4"><?php echo $basis['copyright']; ?></textarea>
							</td></tr>

							<?php echo $field_sep; ?>

							<tr><td valign="top">
								<span class="dot">&#149;</span><b>Extra HTML Code</b>
								<?php writeHelp("Extra HTML Code"); ?>:
							</td><td>
								<textarea name="extra" class="textarea" cols="40" type="text" rows="4"><?php echo $basis['extra']; ?></textarea>
							</td></tr>

							<?php echo $field_sep; ?>

							<tr><td valign="top">
								<span class="dot">&#149;</span><b>Banned Unwanted IPs</b>
								<?php writeHelp("Banned IPs"); ?>:
							</td><td>
								<textarea name="bannedip" class="textarea" cols="40" type="text" rows="4"><?php echo isset($basis['bannedip'])?$basis['bannedip']:''; ?></textarea>
							</td></tr>

							<?php echo $field_sep; ?>

						</table>
						<input type="hidden" name="lastInput" value="lastInput"></input>
						<center><input class="submit" type="submit" value="Save Changes"></input></center>
						<div class="basisTitle" style="border-top-width: 0px;"></div>
					</center>
					</form>
				</div> <!-- method -->
			</div> <!-- inside -->
		</div> <!-- part -->
<?php
}

if (page_is('changepass')) {
?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="back2main"><a href="?">&lt;&lt; Admin Page</a></div>
		<div class="part">
			<div class="title"><a style="color: white" href="?page=changepass">Change Password:</a></div>
			<div class="inside">
				<?php if (strlen($alert_msg)) echo "<div class=\"method\"><div class=\"note_invalid\">$alert_msg</div></div><br />"; ?>
				<?php if (strlen($ok_msg)) echo "<div class=\"method\"><div class=\"note_valid\">$ok_msg</div></div><br />"; ?>
				<div class="method" style="text-align: left">
					<span class="name">Change Administrator's Password</span>
					<div class="inside">
						<form method="post" action="?page=dochangepass" onsubmit="javascript:return checkChangePass();">
							<center>
								<table width="50%" cellpadding="3" style="text-align: left;">
									<tr><td>Current password:</td><td><input name="curpasswd" type="password" class="text" size="20"></input></td></tr>
									<tr><td>New password:</td><td><input name="newpasswd1" id="newpasswd1" type="password" class="text" size="20"></input></td></tr>
									<tr><td>New password, again:</td><td><input name="newpasswd2" id="newpasswd2" type="password" class="text" size="20"></input></td></tr>
									<tr><td colspan="2"></td></tr>
									<tr><td colspan="2" style="text-align: center"><input class="submit" type="submit" value="&nbsp;&nbsp;&nbsp;Change Password&nbsp;&nbsp;&nbsp;"></input></td></tr>
								</table>
							</center>
						</form>
					</div>
				</div> <!-- method -->
			</div> <!-- inside -->
		</div> <!-- part -->
<?php
	}

if (page_is('comments')) {
	$comments = array();
	$ok_msg = '';
	$alert_msg = '';
	$cmd = '';
	$iid = '';
	parse_container('comments', 'Comment', 'data/comments.xml');
	/*
	ksort($comments);
	echo "<div style=\"dir: ltr; text-align: left;\"><pre>";
	print_r($comments);
	echo "</pre></div>";
	*/
	save_container('comments', 'Comment', 'data/comments.xml');
	$stories = array();
	parse_container('stories', 'Story', 'data/stories.xml');
	parse_container('photos', 'Photo', 'data/photos.xml');

	if (isset($_GET['cmd'])) {
		$cmd = $_GET['cmd'];
		if (!isset($_GET['iid']) && !isset($_POST['iid']))
			$alert_msg = "Please enter CommentID as iid!";
		else {
			$iid = ((isset($_GET['iid']))?$_GET['iid']:$_POST['iid'])+0;
			if (strcmp($cmd, 'del') == 0) {
				if (!isset($comments[$iid]) || !is_array($comments[$iid]))
					$alert_msg = "No Comment with this CommentID (cid: $iid) exists!";
				else {
					$ok_msg = $comments[$iid]['name']."'s Comment (CommentID: $iid) deleted successfully!";
					unset($comments[$iid]);
					save_container('comments', 'Comment', 'data/comments.xml');
				}
			}
			else
				$alert_msg = $cmd.' is not a valid command!';
		}
	}

	end($comments);
	$basis['lastcmntseen'] = $comments['lastiid'];
	save_container('basis', 'Basis', 'data/basis.xml');
	reset($comments);

	$n = isset($_GET['n'])?$_GET['n']:10;
	if ($n < 0)
		$n = count($comments);

	$st = isset($_GET['st'])?$_GET['st']:0;
	$st = max($st, 0);
	$st = min($st, count($comments)-1);
?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="back2main"><a href="?">&lt;&lt; Admin Page</a></div>
		<div class="part">
			<div class="title"><a style="color: white" href="?page=comments">Manage Comments:</a></div>
			<div class="inside">
<?php if (strlen($alert_msg)) echo "\t\t\t\t<div class=\"method\"><div class=\"note_invalid\">$alert_msg</div></div><br />"; ?>
<?php if (strlen($ok_msg))    echo "\t\t\t\t<div class=\"method\"><div class=\"note_valid\">$ok_msg</div></div><br />"; ?>
				<div class="method">
					<span class="name">Recent <?php echo $n; ?> Comments  &nbsp;: :&nbsp; Per Page
												[ <a href="?page=comments&n=5">5</a> |
												<a href="?page=comments&n=10">10</a> |
												<a href="?page=comments&n=20">20</a> |
												<a href="?page=comments&n=50">50</a> |
												<a href="?page=comments&n=100">100</a> |
												<a href="?page=comments&n=-1">All</a> ]
												&nbsp;: :&nbsp; [
												<a href="?page=comments&n=<?php echo "$n&st=".($st-$n); ?>">Prev Page</a> |
												<a href="?page=comments&n=<?php echo "$n&st=".($st+$n); ?>">Next Page</a> ]
					</span><br />
					<div style="padding-left: 30px">
					<table width="100%">
<?php
					$c = 0;
					for (end($comments); $c < $st; prev($comments))
						$c++;

					$utoday = GetTimeWithDiffer();

					for ($c = 0; $aival = current($comments); prev($comments)){// a idea value!
						if (is_array($aival)) { 					// might be info!
							$aiid = key($comments);
							if ($c++ >= $n)
								break;
							sscanf($aival['owner'], "%c%d", $tl, $tv);
							$date = $aival['date'];
							if (strcmp($date, "00-01-01 00:00") == 0)
								$date = "";
							else {

								$dates = sscanf($date, "%d-%d-%d %d:%d");
								$udate = mktime($dates[3], $dates[4], 0, $dates[1], $dates[2], $dates[0]);

								$diff = SecsToText($utoday-$udate);
								$date .= " (".$diff.") :: ";
							}

							echo "\t\t\t\t\t\t\t<tr><td class=\"c\" width=\"20%\">";
							if ($tl == 'p')
								thumbBox($tv, '', true, true);
							else
								echo "Story <br /><a href=\".?s=$tv\">\"".$stories[$tv]['name']."\"</a>";
							echo "</td>\n";
							$infoatw = "";
							if (strlen($aival['email']) > 0)
								$infoatw .= "<a href=\"mailto:".$aival['email']."\">@</a>";
							if (strlen($aival['url']) > 0) {
								if (strlen($infoatw) > 0)
									$infoatw .= " | ";
								$infoatw .= "<a href=\"http://".$aival['url']."\">w</a>";
							}
							if (strlen($infoatw) > 0)
								$infoatw = " [ ".$infoatw." ] ";
							$en = textDirectionEn($aival['txt']);
							$dir = $en?"":"r";
							echo "\t\t\t\t\t\t\t<td><span style=\"padding-left: 20px;\" class=\"dot\">&#149;</span>"
								."<span class=\"categinfo\">"
								."$date</span> "
								.$aival['name']."&lrm; {".$aival['ip']."}".$infoatw
								." said: "
								."<span style=\"color: #333; \">"
								." [<a href=\"?page=$page&cmd=del&iid=$aiid\" onclick=\"javascript:return confirmDelete('".$comments[$aiid]['name']."'+'\'s comment');\">Delete</a>]"
								."</span><br />\n"
							."\t\t\t\t\t\t\t<div class=\"categdescnob$dir\">".nl2br($aival['txt'])."</div></td></tr>\n"
							."\t\t\t\t\t\t\t<tr><td colspan=\"2\"><hr coor=\"#BCD\" width=\"80%\" size=\"1\" /></td></tr>\n";
						}
					}
					reset($comments);
?>
					</table>
					</div>
				</div>
			</div>
		</div>
<?php
	}

if (page_is('drafts')) { ?>
<script type="text/javascript" language="javascript" src="files/adminfiles/addphoto.js"></script>
<?php
	$categs = array();
	$stories = array();
	$photos = array();
	$basis = array();
	parse_container('categs', 'Category', 'data/categories.xml');
	parse_container('stories', 'Story', 'data/stories.xml');
	parse_container('photos', 'Photo', 'data/photos.xml');
	parse_container('basis', 'Basis', 'data/basis.xml');

	CleanTemp();

	$cmd = '';
	if (isset($_POST['cmd']))
		$cmd = $_POST['cmd'];
	if (isset($_GET['cmd']))
		$cmd = $_GET['cmd'];

	$doUpload = strcmp($cmd, 'doUpload') == 0;
	$gooddate = date("Y/m/d H:i", GetTimeWithDiffer());
?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="back2main"><a href="?">&lt;&lt; Admin Page</a></div>
<?php if ($doUpload) { ?>
		<div class="clearer" style="margin-top: 15px;"> </div>
		<div class="back2main"><span style="padding-left: 13px; "></span><a href="?page=drafts"><< Manage Drafts</a></div>
<?php } ?>
		<div class="part">
			<div class="title"><a style="color: white" href="?page=drafts">Drafts
				</a><?php writeHelp("Drafts", "#EEE"); ?>:</div>
<?php if (!$doUpload) { ?>
			<div class="inside">
				<div class="method" style="text-align: left; padding-bottom: 15px;">
					<span class="name">Upload
					<?php writeHelp("Upload Photos to Draft"); ?>:
					</span><br />
					<span style="padding-left: 10px;" class="lightdot">&#149;</span>
					<a href="?page=drafts&cmd=doUpload">Upload Mass of Items</a>
				</div> <!-- method -->

				<a name="top"></a>
				<br />
<?php
	}

	$ok_l = "<div class=\"method\"><div class=\"note_valid\">";
	$ok_r = "</div></div><br />";

	if (strlen($cmd) > 0) {
		switch($cmd) {
			case 'doUpload' :
				break;
			case 'del' :
				if (!isset($_GET['draft'])) {
					$alert_msg = "Draft parameter is needed to be passed through $_GET method!";
					break;
				}
				$draft = $_GET['draft'];
				$ok_msg = "";//$draft." file(s) deleted successfully: ";
				$ok_msg .= DeleteFromDrafts("temp/{$draft}_9.jpg");
				break;
			case 'groupdel' :
				reset($_POST);
				echo $ok_l;
				$writtenBefore = false;
				while (list($key, $val) = each($_POST))
					if ((strcmp($val, "on") == 0) && (strcmp(substr($key, 0, 4), "d_cb") == 0)) {
						$draft = rawurldecode(substr($key, 4));
						if ($writtenBefore)
							echo "<br />\n";
						echo DeleteFromDrafts("temp/{$draft}_9.jpg");
						$writtenBefore = true;
					}
				echo $ok_r;
				break;
			case 'groupadd' :
				$_POST = array_reverse($_POST, true);
				reset($_POST);
				echo $ok_l;
				# print_r($_POST);
				while (list($key, $val) = each($_POST))
					if ((strcmp($val, "on") == 0) && (strcmp(substr($key, 0, 4), "d_cb") == 0)) {
						$draft = substr($key, 4);
						$vv = $_POST['d_vv'.$draft];
						$dpath = "temp/{$vv}_9.jpg";

						$seed = random_seed();
						$pid = ++$photos['lastpid'];
						$xmlfile = sprintf("data/p_%06d.xml", $pid);

						if ($hasexif)
							$exif = exif_read_data($dpath, 0, true);

						$photoInfo = "";
						$dateTake = substr($_POST['dateadd'], 0, 10);
						if ($hasexif && ($exif !== FALSE)) {
							$photoInfo = getEXIFData($exif);
							$dt = getEXIFDateTime($exif);
							if (strlen($dt))
								$dateTake = $dt;
						}

						$photo = array('name' 		=> $_POST["d_tt$draft"],
									   'desc' 		=> $_POST["d_td$draft"],
									   'getcmnts' 	=> $_POST['getcmnts'],
									   'dateadd' 	=> $_POST['dateadd'],
									   'datetake' 	=> $dateTake,
									   'photoinfo' 	=> $photoInfo,
									   'pid' 		=> $pid,
									   'categ' 		=> $_POST['categ'],
									   'story' => $_POST['story'],
									   'postfix' 	=> $seed
									   );

						array_push($categs[$photo['categ']]['photo'], $pid);
						array_push($stories[$photo['story']]['photo'], $pid);

						$photos[$pid]  = "0 0/0 ".date("Y/m/d-G:i:s", GetTimeWithDiffer());

						$postfix = "_".$seed;
						$pid6let = sprintf("%06d%s_9.jpg", $pid, $postfix);
						$curppath = PHOTO_PATH.$pid6let;

						copy($dpath, $curppath);
						$ppath = $curppath;
						GenerateAddPhotoRequired($ppath, true); // $gen3 = true;
						DeleteFromDrafts($dpath);
						echo "Photo \"<a href=\".?p=$pid\">".$photo['name']."</a>\" (pid: $pid) added succesfully from file \"{$vv}\"!<br />";
						save_container('photo', 'Photo', $xmlfile);
						save_container('categs', 'Category', 'data/categories.xml');
						save_container('stories', 'Story', 'data/stories.xml');
						save_container('photos', 'Photo', 'data/photos.xml');
					}
				build_rss();
				echo $ok_r;
				break;
			default:
				$alert_msg = $cmd.' is not a valid command!';
		}
	}

	if (!$doUpload) {
?>
				<?php if (strlen($alert_msg)) echo "<div class=\"method\"><div class=\"note_invalid\">$alert_msg</div></div><br />"; ?>
				<br />

				<div class="method" style="text-align: left; padding-bottom: 15px;">
					<span class="name">Manage
					<?php writeHelp("Manage Photos in Draft"); ?>:
					</span><br />

					<form name="TheGlobalForm" enctype="multipart/form-data" method="post" action="?page=drafts" onsubmit="return CheckActionDrafts();">

					<table width="98%" cellpadding="3" cellspacing="1" style="border: 1px solid #AAA; position: relative;">
					<tr style="background-color: #F6F6F6">
						<td colspan="4" style="padding: 10px 0px 3px;">
							<center><table width="90%" style="margin-bottom: 8px;"><tr><td>

							<span style="padding-left: 10px;" class="lightdot">&#149;</span>
							<span style="padding-right: 30px;">Actions:</span>

							</td><td width="60%" style="text-align: left;">

							<label for="groupdel">
								<input id="groupdel" type="radio" class="radio" name="cmd" value="groupdel" checked="checked"
								onclick="javascript:hideElem('QuickAddOptionsRow');">
									Delete Selected <span id="selCount1"></span>&nbsp;Files
									<?php writeHelp("Drafts :: Delete Selected"); ?>
								</input>
							</label>

							&nbsp; &nbsp; &nbsp;
							<br />

							<label for="groupadd">
								<input id="groupadd" type="radio" class="radio" name="cmd" value="groupadd"
								onclick="javascript:tableRowElem('QuickAddOptionsRow');">
									Quick-Add Selected <span id="selCount2"></span>&nbsp;Files
									<?php writeHelp("Drafts :: Quick-add Selected"); ?>
								</input>
							</label>

							</td><td> <input class="submit" type="submit" value=" Perform! "></input>

							</td></tr>
							<tr id="QuickAddOptionsRow" style="display:none; text-align: left;">
							<td colspan="3">
								<div class="method" style="background-color: #F0F8FF; margin-top: 15px; margin-left: 20px;">
									<span class="name" style="background-color: #E0E8EE;">
										<span class="lightdot">&#149;</span>
										Common options for quick-adding photos:
									</span>
									<br />
									<table width="100%" style="position: relative;">
									<tr><td>Time Added<?php writeHelp("Photo: Time Added"); ?>:</td>
										<td><input name="dateadd" id="dateadd" type="text" class="text" size="21" value="<?php echo $gooddate; ?>" autocomplete="off"></input></td>
									</td><td>
										<td>Default Categ<?php writeHelp("Photo: Default Categ"); ?>:</td>
										<td><span style="margin-left: 10px"></span><select name="categ" class="select" size="1">
														<?php
															$categs2 = $categs;
															reset($categs2);
															while (list($cid, $cvals) = each($categs2))
																if (is_array($cvals)) {
																	$prv = (strlen($cvals['pass']))?'* ':'';
																	echo "\t\t\t\t\t\t\t\t<option value=\"$cid\">".$cid.": ".$prv.cutNeck($cvals['name'])."</option>\n";
																}
														?>
													 </input></td>
									</tr><tr>
										<td>Get Comment<?php writeHelp("Photo: Get Comments"); ?>:</td>
										<td><span style="margin-left: 5px "></span><label for="getcmntsye"><input id="getcmntsye" checked="checked" name="getcmnts" value="yes" type="radio" class="radio">Yes</input></label>
											<span style="margin-left: 25px"></span><label for="getcmntsno"><input id="getcmntsno" 					name="getcmnts" value="no"  type="radio" class="radio">No</input></label></td>
									</td><td>
										<td>Default Story<?php writeHelp("Photo: Default Story"); ?>:</td>
										<td><span style="margin-left: 10px"></span><select name="story" class="select" size="1">
														<?php
															$stories2 = array_reverse($stories, true);
															array_pop($stories2);
															array_pop($stories2);
															$stories2 = array(1 => $stories[1]) + $stories2;
															reset($stories2);
															while (list($sid, $svals) = each($stories2))
																if (is_array($svals)) {
																	$prv = (strlen($svals['pass']))?'* ':'';
																	echo "\t\t\t\t\t\t\t\t<option ".($sel?"selected=\"selected\"":"")."value=\"$sid\">".$sid.": ".$prv.cutNeck($svals['name'])."</option>\n";
																}
														?>
													 </input></td></tr>

									</table>
								</div>
							</td>
							</tr></table></center>
						</td>
					</tr>

					<tr style="background-color: #DDD; color: #444;"><td colspan="4" style="padding: 0px 0px 0px 15px;">
						Select:
						&nbsp; [ <a class="qp" onclick="javascript:DraftsSelectBit(0, 1);"> All </a> ]
						&nbsp; [ <a class="qp" onclick="javascript:DraftsSelectBit(0, 0);"> None </a> ]
						&nbsp; [ <a class="qp" onclick="javascript:DraftsSelectBit(1, 1);"> Reverse </a> ]
					</td></tr>

					<tr style="background-color: #9AB; color: white; text-align: center;">
						<td width="10%" style="border: 1px solid #666; ">Select</td>
						<td width="20%" style="border: 1px solid #666; ">Thumbnail
							<?php writeHelp('Manage Drafts: Thumbnail', '#DDD'); ?>		</td>
						<td width="50%" style="border: 1px solid #666; ">Information
							<?php writeHelp('Manage Drafts: Information','#DDD'); ?>	</td>
						<td width="20%" style="border: 1px solid #666; ">Action
							<?php writeHelp('Manage Drafts: Action', '#DDD'); ?>		</td>
					</tr>
<?php
	$n = 0;
	if ($handle = opendir('temp')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && !is_dir($file))
				if (strcmp(substr($file, -6), "_9.jpg") == 0)
					WriteDraftLine(substr($file, 0, strlen($file)-6), $n++);
		}
		closedir($handle);
	}
?>
					<tr style="background-color: #F6F6F6; color: #444;"><td colspan="4" style="padding: 0px 0px 0px 15px; text-align: center;">
						 [ <a href="#top"> Back to Top </a> ]
					</td></tr>
					</table>
					<script language="javascript" type="text/javascript">
						updateSelCount();
					</script>
				</div> <!-- method -->

			</div> <!-- inside -->
<?php } else { // if ($doUpload) ?>
			<div class="inside">
				<div class="method" style="text-align: left; padding-bottom: 15px; background-color: #F0F8FF;">
					<span class="name"><a style="color: #111;" href="?page=drafts&cmd=doUpload">Upload Simultaneously</a>
					<?php writeHelp("Upload Photos to Draft"); ?>:
					</span><br />
				<!-- <div id = "jj" style="border: 1px solid red">hi<br /></div> -->

<?php for ($i=0; $i<20; $i++) {
	$t = 2; ?>
				<div class="method" id="AddBox<?php echo $i; ?>"
					style="padding-top: 8px; margin-bottom: 15px; display:<?php echo ($i < $t)?'block':'none'; ?>">
					<?php $seed = random_seed(); ?>
					<input type="hidden" id="seedv<?php echo $i; ?>" value="<?php echo $seed; ?>"></input>
					<center>
						<div class="note_wrapper" id="upload_uploading_<?php echo $seed; ?>" style="display: none; margin: 35px 0px 25px; position: relative;">
							<span class="note_content" id="upload_note_<?php echo $seed; ?>">
								<img src="files/adminfiles/ind.gif" class="ind" alt="" /> &nbsp; &nbsp; &nbsp;
								<span id="upload_uploading_txt_<?php echo $seed; ?>">Preparing the process...</span>
							</span>
						</div>
					</center>
					<table width="100%" cellpadding="0" cellspacing="0" style="margin: 0px; padding:0px"><tr><td>
						<iframe id="upload_iframe_<?php echo $seed; ?>" class="upload_iframes" src="<?php if ($i < $t ) echo "upload.php?draft=yes&seed=$seed"; ?>"
							frameborder="0"	scrolling="no" marginheight="0" marginwidth="0" height="80" width="100%">
						</iframe>
					</td></tr></table>
				</div>
<?php } ?>
				<input type="hidden" id="boxv" value="<?php echo $t-1; ?>"></input>
				<span class="AddAddBox">[ <a class="qp" onclick="javascrip:AddAddBox()">Add another Add-Box</a> ] </span>
				<br />

				</div> <!-- method -->
				<br />
			</div> <!-- inside -->

<?php } // end of $doUpload ?>
		</div> <!-- part -->
<?php
	}

	if (page_is('photos')) {
		$comments = array();
		$categs = array();
		$stories = array();
		$photos = array();
		$basis = array();
		parse_container('comments', 'Comment', 'data/comments.xml');
		parse_container('categs', 'Category', 'data/categories.xml');
		parse_container('stories', 'Story', 'data/stories.xml');
		parse_container('photos', 'Photo', 'data/photos.xml');
		parse_container('basis', 'Basis', 'data/basis.xml');
		$edit = false;
		$isdoAdd = false;
		$ok_msg = '';
		$alert_msg = '';
		$cmd = '';
		$pid = '';
		$photo = array();

		if (isset($_GET['cmd'])) {
			$cmd = $_GET['cmd'];
			$isAdd   = (strcmp($cmd, 'add')   == 0);
			$isEdt   = (strcmp($cmd, 'edt')   == 0);
			$isdoAdd = (strcmp($cmd, 'doAdd') == 0);
			$isdoEdt = (strcmp($cmd, 'doEdt') == 0);
			$isadCat = (strcmp($cmd, 'adddelC') == 0);
			$isadStr = (strcmp($cmd, 'adddelS') == 0);

			if ($isAdd)
				$pid = ++$photos['lastpid'];
			else
				$pid = (isset($_GET ['pid'])?$_GET ['pid']:
					   (isset($_POST['pid'])?$_POST['pid']+0:-1));
			$xmlfile = sprintf("data/p_%06d.xml", $pid);

			if (!($isAdd || $isdoAdd || isset($_GET['pid']) || isset($_POST['pid'])))
				$alert_msg = "Unknown command or Pid.<br />Please enter PhotoID as pid for the command \"".$cmd."\"!";
			else if ($isdoAdd)
				;
			else if (strcmp($cmd, 'del') == 0) {
				$ok_msg = "Photo \"".getPhotoInfo($pid, 'name')."\" (PID: $pid) deleted successfully!";
				deletePhoto($pid);
			}
			else if (!$isAdd && !file_exists($xmlfile))
				$alert_msg = "No photo with this PhotoID (pid: $pid) exists!";
			else if ($isdoEdt) {
					$edit = true;
					parse_container('photo', '', $xmlfile);
					$curppath = PHOTO_PATH.getImageFileName($pid, '9');
					$curthumb = substr_replace($curppath, '3', -5, 1);
			}
			else if ($isAdd || $isEdt) {
				if (!isset($_POST['name']))
					$alert_msg = "No Name! You shall come here from administration page only!";
				else {
					if ($isEdt) {
						parse_container('photo', '', $xmlfile);
						if (($t = array_search($pid, $stories[$photo['story']]['photo'])) !== FALSE)
							unset($stories[$photo['story']]['photo'][$t]);
						if (($t = array_search($pid, $categs[$photo['categ']]['photo'])) !== FALSE)
							unset($categs[$photo['categ']]['photo'][$t]);
						$postfix = isset($photo['postfix'])?$photo['postfix']:'';
					}
					else
						$postfix = $_POST['seed'];

					$photo = array('name' => $_POST['name'], 'desc' => $_POST['desc'],
								   'getcmnts' => $_POST['getcmnts'], 'dateadd' => $_POST['dateadd'],
								   'pid' => $pid, 'photoinfo' => $_POST['photoinfo'],
								   'categ' => $_POST['categ'], 'story' => $_POST['story'],
								   'datetake' => $_POST['datetake']
								   );
					$photo['postfix'] = $postfix;

					if ($isAdd || !in_array($pid, $categs[$photo['categ']]['photo']))
						array_push($categs[$photo['categ']]['photo'], $pid);
					if ($isAdd || !in_array($pid, $stories[$photo['story']]['photo']))
						array_push($stories[$photo['story']]['photo'], $pid);

					if (!isset($photos[$pid]))
						$photos[$pid] = "";
					$t = array();
					$t = explode(" ", $photos[$pid]);
					$photos[$pid]  = $_POST['hits']." ".(isset($t[2])?$t[2]:date("Y/m/d-G:i:s", GetTimeWithDiffer()));

					$postfix = (strlen($postfix)?"_":"").$postfix;
					$pid6let = sprintf("%06d%s_9.jpg", $pid, $postfix);
					$ppath = PHOTO_PATH.$pid6let;
					$curppath = $ppath;
					$curthumb = substr_replace($ppath, '3', -5, 1);

					$reget = ($isEdt && isset($_POST['regetSrc']) && (strcmp($_POST['regetSrc'], 'get') == 0));
					if ($reget)
						for ($ind=0; $ind<10; $ind++)
							if (file_exists(substr_replace($ppath, $ind, -5, 1)))
								unlink(substr_replace($ppath, $ind, -5, 1));

					if ($isAdd || $reget)
						$ppath = substr_replace($_POST['ImgUrl'], '9', -5, 1);

					if (!file_exists($ppath))
						$alert_msg = "The photo is not exist in the <a href=\"$ppath\">expected place</a>!";
					else {
						if ($isAdd || $reget) {
							copy($ppath, $curppath);
							DeleteFromDrafts($ppath);
							$ppath = PHOTO_PATH.$pid6let;
						}
						$gen3 = $isAdd || ($isEdt && (isset($_POST['genThumb'])) &&
													(strcmp($_POST['genThumb'], "gen") == 0));
						GenerateAddPhotoRequired($ppath, $gen3);
						$ok_msg = "Photo \"<a href=\".?p=$pid\">".$photo['name']."</a>\" (pid: $pid) ".($isAdd?"added":"edited")." succesfully!";
						save_container('photo', 'Photo', $xmlfile);
					}
				}
				if (strlen($alert_msg) == 0) {
					save_container('categs', 'Category', 'data/categories.xml');
					save_container('stories', 'Story', 'data/stories.xml');
					save_container('photos', 'Photo', 'data/photos.xml');
				}
				build_rss();
			}  // if !($isAdd || $isEdt) {
			else if (!$isadCat && !$isadStr)
				$alert_msg = $cmd.' is not a valid command!';
			else {
				$isCAdd = (strcmp($_GET['tcmd'], 'add') == 0);
				$isCDel = (strcmp($_GET['tcmd'], 'del') == 0);
				$photo = getAllPhotoInfo($pid);
				$pname = $photo['name'];
				if ($isadCat) {
					$cid = $_GET['cid'];
					$cname = isset($categs[$cid])?$categs[$cid]['name']:'';
					if (!isset($categs[$cid]) || !is_array($categs[$cid]))
						$alert_msg = "No category with this CategoryID ($cid) exists!";
					else if ($isCDel && !in_array($pid, $categs[$cid]['photo']))
						$alert_msg = "The Photo \"$pname\" (pid: $pid) is not in the Category \"$cname\" (cid: $cid)!";
					else if ($isCDel && ($cid == $photo['categ']))
						$alert_msg = "The Photo \"$pname\" (pid: $pid) can not be deleted from its default Category; "
						."Try <a href=\"?page=photos&cmd=doEdt&pid=$pid\">editing it</a>!";
					else if ($isCAdd && in_array($pid, $categs[$cid]['photo']))
						$alert_msg = "The Photo \"$pname\" (pid: $pid) is already added in the Category \"$cname\" (cid: $cid)!";
					else {
						if ($isCAdd) {
							array_push($categs[$cid]['photo'], $pid);
							$ok_msg = "The Photo \"$pname\" (pid: $pid) added to Category \"$cname\" (cid: $cid), successfully!"
									." [<a title=\"Delete it!\" href=\"admin.php?page=photos&cmd=adddelC&tcmd=del&cid=$cid&pid=$pid\">undo!</a>]";
						}
						else {
							unset($categs[$cid]['photo'][array_search($pid, $categs[$cid]['photo'])]);
							$ok_msg = "The Photo \"$pname\" (pid: $pid) removed from Category \"$cname\" (cid: $cid), successfully!"
									." [<a title=\"Add again!\" href=\"admin.php?page=photos&cmd=adddelC&tcmd=add&cid=$cid&pid=$pid\">undo!</a>]";
						}
						save_container('categs', 'Category', 'data/categories.xml');
					}
				}
				else {
					$sid = $_GET['cid'];
					$sname = isset($stories[$sid])?$stories[$sid]['name']:'';
					if (!isset($stories[$sid]) || !is_array($stories[$sid]))
						$alert_msg = "No story with this StoryID ($sid) exists!";
					else if ($isCDel && !in_array($pid, $stories[$sid]['photo']))
						$alert_msg = "The Photo \"$pname\" (pid: $pid) is not in the Story \"$sname\" (sid: $sid)!";
					else if ($isCDel && ($sid == $photo['story']))
						$alert_msg = "The Photo \"$pname\" (pid: $pid) can not be deleted from its default Story; "
							."Try <a href=\"?page=photos&cmd=doEdt&pid=$pid\">editing it</a>!";
					else if ($isCAdd && in_array($pid, $stories[$sid]['photo']))
						$alert_msg = "The Photo \"$pname\" (pid: $pid) is already added in the Story \"$sname\" (sid: $sid)!";
					else {
						if ($isCAdd) {
							array_push($stories[$sid]['photo'], $pid);
							$ok_msg = "The Photo \"$pname\" (pid: $pid) added to Story \"$sname\" (sid: $sid), successfully!"
									." [<a title=\"Delete it!\" href=\"admin.php?page=photos&cmd=adddelS&tcmd=del&cid=$sid&pid=$pid\">undo!</a>]";;
						}
						else {
							unset($stories[$sid]['photo'][array_search($pid, $stories[$sid]['photo'])]);
							$ok_msg = "The Photo \"$pname\" (pid: $pid) removed from Story \"$sname\" (sid: $sid), successfully!"
									." [<a title=\"Add again!\" href=\"admin.php?page=photos&cmd=adddelS&tcmd=add&cid=$sid&pid=$pid\">undo!</a>]";;
						}
						save_container('stories', 'Story', 'data/stories.xml');
					}
				}
			}
		}
		$edit &= !strlen($alert_msg);
		$SklH = 75;
		$SklW = $SklH;
		$n = isset($_GET['n'])?$_GET['n']:7;

		$draft = (isset($_GET['draft']))?$_GET['draft']:"";
		$isDraft = (strlen($draft) > 0);

		$showConsole = !$edit && !$isdoAdd;

		$lastp = $photos['lastpid'];
		$dateTakenPrev = date("Y/m/d");
		$storyPrev = 1;
		$categPrev = 1;
		$cmntsPrev = "yes";
		if (photo_exists($lastp)) {
			$dates = sscanf(getPhotoInfo($lastp, 'dateadd'), "%d/%d/%d %d:%d");
				$dif = 0;
				@ eval('$dif = '.$basis['timediffer'].';');
				$d = time() + $dif*60;
			if ($d - mktime($dates[3], $dates[4], 0, $dates[1], $dates[2], $dates[0])
				< 60*60) // added within last one hour
				$dateTakenPrev = getPhotoInfo($lastp, 'datetake');
			$storyPrev = getPhotoInfo($lastp, 'story');
			$categPrev = getPhotoInfo($lastp, 'categ');
			$cmntsPrev = getPhotoInfo($lastp, 'getcmnts');
		}
		$curHits = '0 0/0';
		if ($edit) {
			$t = array();
			$t = explode(" ", $photos[$pid]);
			$curHits = $t[0]." ".$t[1];
		}
		$cmntsDefault = (strcmp($cmntsPrev, "yes") == 0);
		if ($edit)
			$cmntsDefault = (strcmp($photo['getcmnts'], "yes") == 0);

		$gooddate = date("Y/m/d H:i", GetTimeWithDiffer());


		//print_r($photos);
?>
		<script type="text/javascript" language="javascript" src="files/adminfiles/addphoto.js"></script>
		<script lanugage="javascript" type="text/javascript">
			var hasexif = <?php echo $hasexif; ?>;
		</script>

		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery &gt;&gt; </a></div>
		<div class="back2main"><a href="?">&nbsp;&nbsp;&lt;&lt; Admin Page&nbsp;&nbsp;&nbsp;</a></div>
		<noscript>
			<br />
			<div class="method"><div class="note_invalid">Please activate javascript for the proper performance.</div></div>
		</noscript>
<?php if (!$showConsole) { ?>
		<div class="clearer" style="margin-top: 15px;"> </div>
		<div class="back2main"><span style="padding-left: 13px"></span><a href="?page=photos"><< Manage Photos</a></div>
<?php
	}
	if (strlen($alert_msg)) echo "\t\t\t<div class=\"method\" style=\"margin-top: 20px;\"><div class=\"note_invalid\">$alert_msg</div></div>";
 	if (strlen($ok_msg))    echo "\t\t\t<div class=\"method\" style=\"margin-top: 20px;\"><div class=\"note_valid\">$ok_msg</div></div>";
 	if ($isdoAdd)			echo "\t\t\t<script language=\"javascript\" type=\"text/javascript\">setFakeDate(1);</script>\n";
 	if ($showConsole) {
?>
		<div class="part">
			<div class="title"><a style="color: white" href="?page=photos">Manage Photos:</a></div>
			<div class="inside">
				<table width="100%" cellspacing="0" cellpadding="0" style="position: relative;"><tr>
					<td width="26%" valign="top">
						<div class="method" style="margin-bottom: 5px; padding: 0px 0px 12px 15px;">
							<span class="name">
								<span class="lightdot">&#149;</span>
								Add photo
								<?php writeHelp("Add Photo Link"); ?>:
							</span><br />
							<span class="dot" style="margin-left: -7px;"><b>:</b></span>
							<a href="?page=photos&cmd=doAdd">Add a Photo!</a>
							<span style="color:#999;">&nbsp;[ <a href="?page=drafts">From Drafts </a> ]</span>
						</div>
					</td>
					<td width="24%" valign="top">
						<form method="get" action="?" onsubmit="return true;">
							<input type="hidden" name="page" value="photos"></input>
							<input type="hidden" name="cmd" value="doEdt"></input>
							<div class="method" style="margin-bottom: 5px; padding: 0px 0px 12px 15px;">
								<span class="name">Edit Later Photos
								<?php writeHelp("Edit Photos Link"); ?>:
								</span><br />
								<span class="dot" style="margin-left: -7px;"><b>:</b></span>
								<label>Photo
								<input style="margin: 0px;" name="pid" class="text" size="4" type="text"
									value="<?php echo (strlen($ok_msg) || strlen($alert_msg))?$pid:''; ?>" autocomplete="off"></input>
								</label>
								&nbsp;
								<input class="submit" type="submit" value=" Edit! "></input>
							</div>
						</form>
					</td>
					<td width="52%" valign="top">
						<form method="get" action="?" onsubmit="return true;" >
							<input type="hidden" name="page" value="photos"></input>
							<input type="hidden" name="cmd" value="doEdt"></input>
							<div class="method" style="margin-bottom: 5px; padding: 0px 0px 12px 15px;">
								<span class="name">Add/Delete Photos to Story/Categs
								<?php writeHelp("Add/Del Photos to Categ/Story"); ?>:
								</span><br />
								<span class="dot" style="margin-left: -7px;"><b>:</b></span>
								<select name="tcmd" class="select" type="text"
									onchange="javascript:dg('ToField').style.display=(this.value=='add')?'inline':'none';dg('FromField').style.display=(this.value=='del')?'inline':'none'">
									<option value="add" selected="selected">Add</option>
									<option value="del">Del</option>
								</select>
								&nbsp;
								<label>Photo
								<input style="margin: 0px" name="pid" class="text" size="3" type="text"
										value="<?php echo (strlen($ok_msg) || strlen($alert_msg))?$pid:''; ?>" autocomplete="off"></input>
								</label>

								&nbsp;<label><span id="ToField" style="display:inline">To</span><span id="FromField" style="display:none">From</span>

								<select name="cmd" class="select" type="text">
									<option value="adddelC" selected="selected">Categ</option>
									<option value="adddelS">Story</option>
								</select>
								</label>

								<label>#
								<input style="margin: 0px;" name="cid" class="text" size="3" type="text"
										value="<?php echo (strlen($ok_msg) || strlen($alert_msg))?$cid:''; ?>" autocomplete="off"></input>
								</label>
								&nbsp;
								<input class="submit" type="submit" id="AddDelCSubmit" value=" Do! "></input>
							</div>
						</form>
					</td>
				</tr>
				</table>
				<br style="margin-top: 3px; " />
				<div class="method">
					<span class="name">
						<span class="lightdot">&#149;</span>
						<b>Edit</b> Photo ::
						<?php echo $n; ?>
						Recent :
<?php
							foreach (array(7, 14, 21, 35, 70, 140, 700, 7000) as $k)
								echo "\t\t\t\t\t\t<a href=\"?page=photos&n=$k\">[$k]</a>\n";

?>
					</span><br />

				<div style="margin-left: -10px;">
<?php
	$cur = end($photos);
	for ($i=min(count($photos), $n); $i>0; $i--) {
		$thePid = key($photos);
		if (strcmp("lastpid",  $thePid) != 0) {
			echo "\t<div class=\"aThumbToEdit\">\n";
			thumbBox($thePid, '', true, true);

			echo "\t\t<a href=\"?page=photos&cmd=doEdt&pid=$thePid\">Edit</a>\n"
				."\t\t<span class=\"dot\">::</span>\n"
				."\t\t<a href=\"?page=photos&cmd=del&pid=$thePid\""
				." onclick=\"javascript: return ConfirmDelPhotoID(".$thePid.");\">Delete</a>\n"
				."\t</div>\n";
			$cur = prev($photos);
		}
	}
?>
					<div class="clearer"> </div>
				</div>
				</div>
			</div>
		</div>
<?php
	}
	else { // !showConsole
		if ($edit)
			$seed = isset($photo['postfix'])?$photo['postfix']:"";
		else
			$seed = random_seed();
?>
		<form name="TheGlobalForm" enctype="multipart/form-data" method="post" action="?page=photos&cmd=<?php echo $edit?'edt':'add'; ?>" onsubmit="return CheckAddPhoto<?php echo $edit?'Time':''; ?>();">
		<input name="ImgUrl" id="theImgPath" class="text" size="40" type="hidden" readonly="readonly" value="<?php echo $edit?PHOTO_PATH.getImageFileName($pid, '9'):""; ?>"></input>
		<input name="seed" id="inputSeed" class="text" size="20" type="hidden" readonly="readonly" value="<?php echo $seed; ?>"></input>
		<div class="part" style="margin-top: 22px;">
			<span class="title">&#149; <a style="color: white" href="?page=photos<?php
				echo $edit?"&cmd=doEdt&pid=$pid":'&cmd=doAdd'; ?><?php
				echo $isDraft?"&draft=$draft":""?>"><?php
				echo $edit?"Edit Photo #$pid \"".$photo['name']."\"":
					($isDraft?"Add Draft item $draft":'Add Photo'); ?>:</a></span>
			<?php if ($edit) { ?>
				<input name="pid" class="text" size="6" type="hidden" value="<?php echo $pid; ?>"></input>
				<br /><br />
			<?php } else { ?>
				<div class="inside">
			<?php }
				if (!$isDraft) {
					if ($edit) { ?>
					<center>
					<table width="96%" cellspacing="0" cellpadding="0" style="position: relative; text-align: left;">
					<tr><td width="50%" id="photoinfDiv0">
					<div class="method">
						<span class="name">Step 0 - Photo Information:
						</span>

						<div style="float:left; margin-right: 20px; border-right: 1px dashed #999; padding-right: 20px; ">
						<?php
							$imgFile = PHOTO_PATH.getImageFileName($pid, '3');
							if (!file_exists($imgFile) && !$hasgd)
								$imgFile = PHOTO_PATH.getImageFileName($pid, '9');

							echo "<a href=\".?p=$pid\" target=\"_blank\"><img src=\"$imgFile\" /></a>";

							//thumbBox($pid, '', true, true);
						?>
						</div>
						<br />
						You're editing:<br />

						<?php echo "Photo #$pid : <br /><b>\"".$photo['name']."\"</b>"; ?>

						<br />
						<span class="dot">&#149;</span>
						<a href="?page=photos&cmd=del&pid=<?php echo $pid; ?>"
							onclick="javascript: return ConfirmDelPhotoID(<?php echo $pid; ?>);">
							Delete This Photo!
						</a>
						<br />
					</div>

					</td>
					<td>
				<?php	} /* $edit; */	?>

					<div class="method" id="contUploadNow">
					<span class="name">Step 1 - Locate the Photo
					<?php echo writeHelp("Upload Path"); ?>:
					</span>
					<?php if ($edit) { ?>
						<div style="padding: 15px 20px 17px; line-height: 100%;">
							<label for="regetSrcno"><input id="regetSrcno" type="radio" class="radio" name="regetSrc" value="noget" checked="checked" onclick="javascript:showElem('ThumbnailGenSelector');hideElem('gettingTheFileDiv');TheGlobalForm.genThumb[0].checked=true;hideElem('ThumbnailGenerator');inlineElem('photoinfDiv0')">Keep current file</input></label><br />
							<br />
							<label for="regetSrcye"><input id="regetSrcye" type="radio" class="radio" name="regetSrc" value="get"  onclick="javascript:hideElem('ThumbnailGenSelector');TheGlobalForm.genThumb[1].checked=true;showElem('ThumbnailGenerator');showElem('gettingTheFileDiv');hideElem('photoinfDiv0')">Upload a new file</input></label><br />
						</div>
					<?php } ?>
						<div id="gettingTheFileDiv" style="display: <?php echo $edit?"none":"block"; ?>">
							<center>
								<div class="note_wrapper" id="upload_uploading_<?php echo $seed; ?>" style="display: none; margin: 27px 0px; position: relative;">
									<span class="note_content" id="upload_note_<?php echo $seed; ?>">
										<img src="files/adminfiles/ind.gif" class="ind" alt="" /> &nbsp; &nbsp; &nbsp; <span id="upload_uploading_txt_<?php echo $seed; ?>">Preparing the process...</span>
									</span>
								</div>
							</center>
							<iframe id="upload_iframe_<?php echo $seed; ?>" class="upload_iframes" src="upload.php?seed=<?php echo $seed; ?>" frameborder="0"
								scrolling="no" marginheight="0" marginwidth="0" height="80" width="90%">
							</iframe>
						</div> <!-- gettingTheFileDiv -->
					</div> <!-- method -->

			<?php if ($edit) { ?>
				</td></tr></table>
				</center>
			<?php } else { ?>
				</div> <!-- inside -->
			<?php } ?>


			<div class="inside">
			<?php } /* !$isDraft */ ?>
				<table width="100%" cellspacing="0" cellpadding="0" style="position: relative;"><tr>
					<td width="50%" valign="top">
						<div class="method">
							<span class="name">Step 2.1 - Global data (required)
							<?php writeHelp("Photo: Global Data"); ?>
							:</span><br />
							<table width="100%" cellpadding="3">
								<tr><td>Title<?php writeHelp("Photo: Title"); ?>:</td>
									<td><input id="PhotoTitleId" name="name" type="text" class="text" size="28" value="<?php echo $edit?$photo['name']:($isDraft?$draft:''); ?>" autocomplete="off"></input></td></tr>
								<tr><td valign="top">Description<?php writeHelp("Photo: Description"); ?>:</td>
									<td><textarea cols="17" rows="5" name="desc"><?php echo $edit?$photo['desc']:''; ?></textarea></td></tr>
								<tr><td>Time Added<?php writeHelp("Photo: Time Added"); ?>:</td>
									<td><input name="dateadd" id="dateadd" type="text" class="text" size="21" value="<?php echo $edit?$photo['dateadd']:$gooddate; ?>" autocomplete="off"></input></td></tr>
								<tr><td>Get Comment<?php writeHelp("Photo: Get Comments"); ?>:</td>
									<td><span style="margin-left: 5px "></span><label for="getcmntsye"><input id="getcmntsye" <?php echo ($cmntsDefault)?'checked="checked"':''; ?> name="getcmnts" value="yes" type="radio" class="radio">Yes</input></label>
								  	    <span style="margin-left: 25px"></span><label for="getcmntsno"><input id="getcmntsno" <?php echo ($cmntsDefault)?'':'checked="checked"'; ?> name="getcmnts" value="no"  type="radio" class="radio">No</input></label></td></tr>
							</table>
						</div>
					</td>
					<td width="50%" valign="top">
						<div class="method">
							<span class="name">Step 2.2 - Special features (optional):</span>
							<table width="100%" cellpadding="4" cellspacing="1">
								<tr><td valign="top">Photo info<?php writeHelp("Photo: Photo Info"); ?>:</td>
									<td><textarea cols="16" rows="3" id="photoinfo" name="photoinfo"><?php echo $edit?$photo['photoinfo']:''; ?></textarea></td></tr>
								<tr><td>Default Categ<?php writeHelp("Photo: Default Categ"); ?>:</td>
									<td><span style="margin-left: 10px"></span><select name="categ" class="select" size="1">
													<?php
														$categs2 = $categs;
														reset($categs2);
														while (list($cid, $cvals) = each($categs2))
															if (is_array($cvals)) {
																$prv = (strlen($cvals['pass']))?'* ':'';
																$sel = $edit?($cid == $photo['categ']):($cid == $categPrev);
																echo "\t\t\t\t\t\t\t\t<option ".($sel?"selected=\"selected\"":"")."value=\"$cid\">".$cid.": ".$prv.cutNeck($cvals['name'])."</option>\n";
															}
													?>
												 </input></td></tr>
								<tr><td>Default Story<?php writeHelp("Photo: Default Story"); ?>:</td>
									<td><span style="margin-left: 10px"></span><select name="story" class="select" size="1">
													<?php
														$stories2 = array_reverse($stories, true);
														array_pop($stories2);
														array_pop($stories2);
														$stories2 = array(1 => $stories[1]) + $stories2;
														reset($stories2);
														while (list($sid, $svals) = each($stories2))
															if (is_array($svals)) {
																$prv = (strlen($svals['pass']))?'* ':'';
																$sel = $edit?($sid == $photo['story']):($sid == $storyPrev);
																echo "\t\t\t\t\t\t\t\t<option ".($sel?"selected=\"selected\"":"")."value=\"$sid\">".$sid.": ".$prv.cutNeck($svals['name'])."</option>\n";
															}
													?>
												 </input></td></tr>
								<tr><td>Date Taken<?php writeHelp("Photo: Date Taken"); ?>:</td>
									<td><input id="datetake" name="datetake" type="text" class="text" size="21" onchange="javascript:setFakeDate(0);" value="<?php echo $edit?$photo['datetake']:$dateTakenPrev; ?>"></input></td></tr>
								<tr><td>Hits & Rate<?php writeHelp("Photo: Hits, Rate"); ?>:</td>
									<td><input id="hits" name="hits" type="text" class="text" size="21" value="<?php echo $curHits; ?>" autocomplete="off"></input></td></tr>
							</table>
						</div>
					</td>
				</tr></table>
			</div>
			<a name="genThumb"></a>
			<div class="inside">
				<div class="method">
				<span class="name">Step 3 - Thumbnail<?php writeHelp("Thumbnail"); ?>:</span><br />
					<?php if ($edit) { ?>
					<div id="ThumbnailGenSelector">
						<label for="genThumbnogen"><input type="radio" class="radio" name="genThumb" value="nogen" id="genThumbnogen" checked="checked" onclick="javascript:hideElem('ThumbnailGenerator');showElem('currentThumb');"> Keep current thumbnail</input></label><br />
							<div id="currentThumb" style="margin: 5px 50px 10px;"><img border="1" src="<?php echo $curthumb; ?>" /></div>
						<label for="genThumbgen"><input type="radio" class="radio" name="genThumb" value="gen" id="genThumbgen" onclick="javascript:showElem('ThumbnailGenerator');hideElem('currentThumb');">Generate a new thumbnail</input></label><br />
					</div>
					<?php } ?>
					<center>
					<div class="note_wrapper" id="thumb_note_wrapper_<?php echo $seed; ?>"<?php if ($edit || $isDraft) echo " style=\"display: none;\""; ?>>
						<span class="note_content" id="thumb_note_<?php echo $seed; ?>">You have not uploaded anything! do it first. </span>
					</div>
					<div id="ThumbnailGenerator" style="display: none; padding: 5px; margin: 10px 0px;">
						<center>
						<table width="90%" cellspacing="0" cellpadding="0" style="position: relative; "><tr>
							<td width="65%" valign="top">
							<center>
							<?php
								$opac = 80;
								$opacs = ((stristr($_SERVER['HTTP_USER_AGENT'], "IE") == true)?
										"filter:alpha(opacity=".$opac.");":
						 				"-moz-opacity:".($opac/100).";");
							?>
							<div id="thePhoto" style="background: #CDE url(''); width: 0px; height: 0px; margin: 0px 20px;"
								onmousemove="javascript:MouseMoveInside(event);">
								<div id="kines_l" class="kines" style="top: 0px; 	left: 0px; 	width: 75px; height: 75px;  <?php echo $opacs; ?>"></div>
								<div id="kines_t" class="kines" style="top: 0px; 	left: 0px; 	width: 75px; height: 75px;  <?php echo $opacs; ?>"></div>
								<div id="kines_b" class="kines" style="bottom: 0px; left: 0px; 	width: 75px; height: 75px;  <?php echo $opacs; ?>"></div>
								<div id="kines_r" class="kines" style="top: 0px; 	right: 0px; width: 75px; height: 75px;  <?php echo $opacs; ?>"></div>
								<div id="skeleton" style="top: 0px; left: 0px; width: <?php echo $SklW; ?>px; height: <?php echo $SklH; ?>px; "
									onmouseup="javascript:ReleaseMouse();" ondblclick="javascript:ExpandSkl();" onmousedown="javascript:MouseDownTheSkeleton(event);">
<?php
	$c = array("nw", "ne", "sw", "se");
	for ($i=0; $i<count($c); $i++)
		echo "\t\t\t\t\t\t\t\t\t\t\t<div id=\"c_$c[$i]\" class=\"corners\" onmousedown=\"javascript:MouseDown($i, event);\"></div>\n";
?>
								</div>
							</div>
							</center>
							</td>
							<td valign="top" style="display: none;">
								<div class="method" style="text-align: left;">
									<span class="name">Thumbnail Info:</span>
									<table width="100%" cellpadding="2" cellspacing="2">
											<tr style="display: none;"><td>Lock Ratio:</td><td><input id="keepRatio" class="checkbox" type="checkbox" checked="checked" onchange="javascript:SaveRatio();"></input></td></tr>
											<tr style="display: none;"><td>Picker's height:</td><td><input name="sklH" id="sklH" type="text" class="text" size="5" value="<?php echo $SklH; ?>"></input></td></tr>
										<tr><td>Picker's width :</td><td><input name="sklW" id="sklW" type="text" readonly="readonly" class="textTTRO" size="5" value="<?php echo $SklW; ?>"></input></td></tr>
										<tr><td>Picker's H Pos.:</td><td><input name="sklL" id="sklL" type="text" readonly="readonly" class="textTTRO" size="5" value="0"></input></td></tr>
										<tr><td>Picker's V Pos.:</td><td><input name="sklT" id="sklT" type="text" readonly="readonly" class="textTTRO" size="5" value="0"></input></td></tr>
									</table>
								</div>
								<br />
							</td>
							<td>
								<div class="method" style="text-align: left; ">
									<span class="name"><a style="color:black" href="#reputThumb" onclick="javascript:rethumb();">Thumbnail Preview:</a></span><br />
									<center>
										<div id="thumbPrevCont" style="<?php echo "width: $SklW"."px; height: $SklH"."px;"; ?>">
											<img id="thumbPrev" style="top: 0px; left: 0px;" src="" />
										</div>
									</center>
								</div>
							</td>
						</tr></table>
						</center>
					</div>
					</center>
				</div>
			</div>

			<div class="inside">
				<div class="method" style="padding-bottom: 20px;">
					<span class="name">Step 4 - Submit:</span>
					<center>
						<span id="finallyAdd" style="display:none;">
							<input class="submit" type="submit" value="&nbsp;<?php echo $edit?'Save Changes&nbsp':'Add &nbsp;the&nbsp; Photo &nbsp'; ?>;"></input>
							<span style="padding-left: 20px;"></span>
							<input class="reset" type="reset" value="&nbsp;Reset Changes &nbsp;"></input>
						</span>
						</form>
						<span style="padding-left: 20px;"></span>
						<form enctype="multipart/form-data" method="post" action="?page=photos">
							<input class="reset" type="submit" value="Sorry, Just leave here!"></input>
						</form>
						<div class="clearer"></div>
					</center>
				</div>
			</div>
			<?php
				if ($isDraft) {
					$ppath9 = "temp/{$draft}_9.jpg";
?>					<script type="text/javascript" language="javascript">
						AttemptEXIF('<?php echo $seed; ?>', '<?php echo $ppath9; ?>');
					</script>
<?php			}

				if ($isDraft || ($edit && $hasgd)) {
					if ($isDraft)
						$ppath = "temp/{$draft}_1.jpg";//substr_replace($curppath, '1', -5, 1);
					else
						$ppath = substr_replace($curppath, '4', -5, 1);
					if (file_exists($ppath))
						@list($w, $h) = getimagesize($ppath);
					else {
						$w = 0; $h = 0;
					}
					echo "\t\t\t\t<script type=\"text/javascript\" language=\"javascript\">\n";
					echo "\t\t\t\t\tImgPath = \"$ppath\";\n";
					echo "\t\t\t\t\tImgW = $w;\n";
					echo "\t\t\t\t\tImgH = $h;\n";
					echo "\t\t\t\t\timageUploaded(0);\n";
					if (!$isDraft)
						echo "\t\t\t\t\thideElem('ThumbnailGenerator');\n";
					echo "\t\t\t\t</script>\n";
				}
			?>
		</div>
	</div>
<?php
		}
	}
?>

	<div class="footnote">
		<a href=".">This PhotoGallery</a> is powered by <a href="http://p.horm.org/er">Phormer</a>, <br />
		a simple <a href="http://php.net">PHP</a> Photo Gallery Manager, under
		<a href="http://gnu.org/licenses/gpl.txt">GPL</a>.
	</div>
</div> <!-- Granny -->
</center>
<?php
	$time_end = getmicrotime();
	echo "<!-- Created in ".round($time_end-$time_start, 3)." seconds -->\n";
	echo "</body>\n</html>\n";
?>