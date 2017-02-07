<?php
define("PHORMER_VERSION", "3.31");
define("PHORMER_BUILD_DATE", "13th Jan. 2007");
define("DEBUG_MODE", 0);
#define("ZIP_OPEN_PATH", 'n:\aideen\php\phormer\temp\\'); // DO NOT FORGET FINAL \ (or /) of (temp\)

$thumbCntArr = array(5, 10, 20, 50, 100);

$pathadd = pathinfo($_SERVER['PHP_SELF']);
$addadd = "http://".$_SERVER['SERVER_NAME'].$pathadd["dirname"];
while ((strcmp(substr($addadd, -1), '/') == 0) ||
	   (strcmp(substr($addadd, -1), '\\') == 0))
	$addadd = substr($addadd, 0, -1);
#echo $addadd."!<br>";

global $_GET, $_POST, $_COOKIE, $hasgd, $hasexif;

if (get_magic_quotes_gpc()) {
	$_POST = str_replace("\\\"", "\"", $_POST);
	$_POST = str_replace("\\'", "'", $_POST);
	$_GET = str_replace("\\\"", "\"", $_GET);
	$_GET = str_replace("\\'", "'", $_GET);
}

$hasgd   = in_array("gd",   get_loaded_extensions())?1:0;
$haszip  = in_array("zip",  get_loaded_extensions())?1:0;
$hasexif = in_array("exif", get_loaded_extensions())?1:0;

define("PHOTO_PATH", "images/");
$transtable = get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES);
$transtable = array_flip($transtable);
$transmanual = array('"' => '&quot;', ">" => '&gt;', '<' => '&lt;'); #'>

define("ADMIN_PASS_FILE", 	"data/adminPass.php");
define("ADMIN_OLD_FILE", 	"data/adminPass.inf");
define("UPLOAD_AJAX_FILE", 	"temp/upload_ajax.inf");
define("PHORMER_PATH",		"http://p.horm.org/er");
define("SKL_PHOTO_W", 240);
define("MAX_LINKS", 60);
define("DEFAULT_JPEG_QUAL", 80);
define("DEFAULT_OPAC_RATE", 60);
define("WV_LENGTH", 5);
define("MAX_VISIT_ONLINE", 60);
define("DEFAULT_COPYRIGHT", "&lt;a href=&quot;.&quot;&gt;This PhotoGallery&lt;/a&gt; is powered by \$Phormer.\n"
							."Unless stated otherwise, all photos are taken, edited, and copyright by \$email.\n"
							."Copying or otherwise using these photos requires the permission of author.");

define("THUMB_NOT_FOUND", "<div class=\"thumb_not_found\" /><br />Thumbnail Not Found!</div>");

function GetLastPhormerVersion() {
	if (DEBUG_MODE && strpos($_SERVER['SERVER_NAME'], "localhost") !== FALSE) {
		@$fp = fsockopen("localhost", 80, $errno, $errstr, 30);
		if (!$fp)
			return "";
		$out = "GET /phormer/er/last_version.php HTTP/1.0\r\n"
			 . "Host: localhost\r\n"
			 . "Connection: Close\r\n\r\n";
		fwrite($fp, $out);
	}
	else {
		@$fp = fsockopen("p.horm.org", 80, $errno, $errstr, 30);
		if (!$fp)
			return "";
		$out = "GET /er/last_version.php HTTP/1.0\r\n"
			 . "Host: p.horm.org\r\n"
			 . "Connection: Close\r\n\r\n";
		fwrite($fp, $out);
	}

	$rr = "";
	while ($fp && !feof($fp)) {
		$rr .= fgets($fp, 128);
	}
	$rr = substr($rr, -4);

	fclose($fp);
	return $rr;
}

function retThis($x) { return $x; }

function IsIE() {
	return (stristr($_SERVER['HTTP_USER_AGENT'], "IE") == true);
}

function getHelp($s, $c = "#789") {
	global $basis;
	if (!isset($basis['helplang']) || strcmp($basis['helplang'], 'off')==0)
		$basis['helplang'] = 'en';
	return "&nbsp;<span style=\"color: $c\">(<a class=\"q\" onclick=\"ShowHelp('$s', event, '{$basis['helplang']}');\"> ? </a>)</span>\n";
}

function writeHelp($s, $c = "#789") {
	global $basis;
	if (isset($basis['helplang']) && strcmp($basis['helplang'], 'off')==0)
		return;
	echo "\t\t\t\t".getHelp($s, $c);
}

function showOnSideBar($name) {
	global $basis;
	return !(isset($basis[$name]) && (strcmp($basis[$name], "off") == 0));
}

function writeSideCheckBox($name, $word) {
	global $basis;

	$checked = showOnSideBar($name)?" checked=\"checked\"":"";
	echo "\t\t\t\t<label><input name=\"$name\" type=\"checkbox\" class=\"checkbox\"$checked> $word </input></label><br />\n";
}

function writeMainColDropDown($t, $x) {
	$modes = array( "box" 	=> "Jungle Box",
					"random"	=> "Random Photos",
					"stories" 	=> "Recently Added Stories",
					"first" 	=> "Recently Added Photo",
					"all" 		=> "Recently Added Photos",
					"recent" 	=> "Recently Visited Photos",
					"comment"	=> "Recently Commented Photos",
					"rate" 		=> "Top Rated Photos",
					"hits" 		=> "Top Visited Photos"
					);

	echo "\t<div>\n";
	if ($x)
		echo "\t\t Then <br /><br />\n";
	echo "\t\t<select name=\"dummyDummy\" class=\"select\" size=\"1\" onchange=\"javascript:updateMode()\">\n";
	foreach ($modes as $key => $value) {
		$sel = (strcmp($key, $t) == 0)?" selected=\"selected\"":"";
		echo "\t\t\t<option$sel value=\"$key\">$value</option>\n";
	}
	echo "\t\t</select>&nbsp; <a style=\"cursor: pointer\" "
		."onclick=\"javascript:removeThisNode(parentNode);\">Delete it!</a><br /><br />\n";
	echo "\t</div>\n";
}

function modeInMainCol($s) {
	if (strcmp($s, 'box') == 0)
		write_boxPhotos();
	else if (strcmp($s, 'stories') == 0)
		write_lastStories();
	else if (strcmp($s, 'first') == 0)
		write_firstPhoto();
	else if (strcmp($s, 'all') == 0)
		write_lastPhotos();
	else if (strcmp($s, 'rate') == 0)
		write_belowIndex("below_GetRate", "Top Rated");
	else if (strcmp($s, 'hits') == 0)
		write_belowIndex("below_GetHits", "Most Visited");
	else if (strcmp($s, 'recent') == 0)
		write_belowIndex("below_GetRecency", "Recently Visited");
	else if (strcmp($s, 'random') == 0)
		write_belowIndex("retThis", "Random");
	else if (strcmp($s, 'comment') == 0)
		write_belowIndex("retThis", "Recently Commented");
}



function getImageFileName($pid, $size) {
	$postfix = getPhotoInfo($pid, 'postfix');
	$postfix = (strlen($postfix)?"_":"").$postfix;
	$imgFile = sprintf('%06d%s_%d.jpg', $pid, $postfix, $size);
	return $imgFile;
}

function getPhotoInfo($pid, $prop) {
	$tphoto = array();
	$tphoto = getAllPhotoInfo($pid);
	return isset($tphoto[$prop])?$tphoto[$prop]:"";
}

function getXMLFileName($pid) {
	return sprintf('data/p_%06d.xml', $pid);
}

function getAllPhotoInfo($pid) {
	global $tphoto, $transtable;
	$tphoto = array();
	$xmlfile = getXMLFileName($pid);
	parse_container('tphoto', '', $xmlfile);
	$criminals = array(/* 'name', */'desc', 'photoinfo');
	foreach ($criminals as $key)
		if (isset($tphoto[$key]))
			$tphoto[$key] = strtr($tphoto[$key], $transtable);

	return $tphoto;
}

function thumb_just_img($pid, $size) {
	return PHOTO_PATH.getImageFileName($pid, $size);
}

function write_admin_pass_file($pass) {
	$fh = fopen(ADMIN_PASS_FILE, "w");
	fwrite($fh, '<?php define("ADMIN_PASS_MD5", "'.md5($pass).'"); ?>');
	fclose($fh);
}

function update_admin_pass_file() {
	if (!file_exists(ADMIN_PASS_FILE) && file_exists(ADMIN_OLD_FILE)) {
		write_admin_pass_file(trim(file_get_contents(ADMIN_OLD_FILE)));
		@unlink(ADMIN_OLD_FILE);
	}
}

function auth_admin($passwd = "") {
	update_admin_pass_file();

	if (!strlen($passwd)) {
		global $_COOKIE;
		$passwd = isset($_COOKIE['phormer_passwd'])?$_COOKIE['phormer_passwd']:"";
	}
	$adminPass = "";
	if (!defined("ADMIN_PASS_MD5"))
		include_once(ADMIN_PASS_FILE);

	return (strcmp($passwd, ADMIN_PASS_MD5) == 0);
}


function photo_exists($pid) {
	$xmlfile = sprintf('data/p_%06d.xml', $pid);
	return file_exists($xmlfile);
}

function textDirectionEn($txt){
	global $transtable;

	$en = true;
	for ($i=0; ($i<strlen($txt)) && $en; $i++) {
		$l = $txt[$i];
		$en &= ctype_lower($l) || ctype_upper($l) || ctype_punct($l) || ctype_digit($l) ||
			   ctype_space($l) || (strpos("/\\!@#$%^&*(){}[];\"'", $l) != FALSE ||
			   strpos("ÿþýüûúùöõôóòñðïíîìëêéèçæåäãâáàÝÜÛÚÙÖÕÔÓÒÑÏÎÍÌËÊÉÇÈÆÅÄÃÂÁ", $l) != FALSE ); #'
	}
	return $en;
}

function thumbBox($pid, $a_info = "", $force = false, $isInAdmin = false, $targ="_blank") { ### :)
	global $stories, $categs, $photos, $isAdmin, $basis, $hasgd;

	$xmlName = getXMLFileName($pid);
	if (!file_exists($xmlName)) {
		echo "\t\t<div class=\"thumb_not_found\" style=\"border-width: 0px;\">\n";
		echo "\t\t\tFile $xmlName Does Not exist! \n";
		echo "\t\t</div><br />\n";
		return;
	}

	if (isset($basis['linktarget'])) {
		if (strcmp($basis['linktarget'], "_blank") == 0)
			$targ = "_blank";
		else if (strcmp($basis['linktarget'], "_self") == 0)
			$targ = "_self";
	}
	if ($isInAdmin)
		$targ = "_blank";

	$photo = getAllPhotoInfo($pid);

	if (!$force) {
		if (!canthumb($pid))
			return false;
	}
	$imgFile = PHOTO_PATH.getImageFileName($pid, '3');
	if (!file_exists($imgFile) && !$hasgd)
		$imgFile = PHOTO_PATH.getImageFileName($pid, '9');

	$mtime = textdate($photo['dateadd']);
	$rating = array(0, 0);
	$rating = explode(" ",$photos[$pid]);
	$hits = $rating[0];
	$rate = 0;
	$raters = substr(strrchr($rating[1], '/'), 1);
	@eval("@\$rate =".$rating[1].";");
	$rate = round($rate, 2);
	$theName = $photo['name'];
	$neck = 14;
	if (strlen($theName) == 0)
		if ($isInAdmin)
			$theName = "[Photo #".$pid."]";
		else
			$theName = "&nbsp;";
	if (strlen($theName) > $neck)
		$theName = substr($theName, 0, $neck-3)."&#133;";
	if (strlen($a_info))
		$theName = $a_info;
	$inTitName = $photo['name'];
	$inTitName = (strlen($inTitName) > 0)?($inTitName.": "):"";
	if (file_exists(thumb_just_img($pid, '0')))
		$sizeKB = round(filesize(PHOTO_PATH.getImageFileName($pid, '0'))/1024);
	else if (file_exists(thumb_just_img($pid, '9')))
		$sizeKB = round(filesize(PHOTO_PATH.getImageFileName($pid, '9'))/1024);
	else
		$sizeKB = "";
	if (strlen($sizeKB))
		$sizeKB .= " KB,";

	$shadow = ($isInAdmin || ($basis['opac'] == 100))?"":
					     " style=\"".(IsIE()?"filter:alpha(opacity=".$basis['opac'].");"
					     	:"-moz-opacity:".($basis['opac']/100).";")
					     ."\" onmouseover=\"javascript: LightenIt(this);\" onmouseout=\"javascript: DarkenIt(this);\"";
	echo "\t\t<div class=\"aThumb\"$shadow>\n";
	echo "\t\t\t<center>\n";
	echo "\t\t\t\t<a target=\"$targ\" href=\".?p=$pid\"
		title=\"$inTitName$sizeKB $hits hits and rated $rate by $raters person\">\n";

	if (!file_exists($imgFile) && $hasgd)
		echo "\t\t\t\t\t".THUMB_NOT_FOUND."\n";
	else
		echo "\t\t\t\t\t<img src=\"$imgFile\" height=\"75px\" width=\"75px\" /><br />\n";
	echo "\t\t\t\t\t<div class=\"thumbNameLine\">"
		.($isInAdmin?"<span style=\"padding-left: 5px;\" class=\"dot\">&#149;</span>":"")
		.$theName."</div>\n";
	if (!$isInAdmin)
		echo "\t\t\t\t\t<div class=\"thumbDate\">$mtime</div>\n";
	echo "\t\t\t\t</a>\n";
	echo "\t\t\t</center>\n";
	echo "\t\t</div>\n";
	return true;
}


########################################################### parse

$parse_curTag = "";
$parse_curId = "";
$parse_photoCnt = 0;
$parse_info = "";
$parse_each = "";

function infoCharacterData($parser, $data) {
	global $parse_curTag, $parse_curId, $parse_info, $parse_photoCnt, $$parse_info, $parse_each;
	if ((!strlen($parse_curTag)) && (!strlen(trim($data)))) return;
	if (strcmp($parse_curTag, "Photo") == 0) {
		${$parse_info}[$parse_curId] .= $data;
		return;
	}
	if (strcmp($parse_curTag, 'link') == 0)
		${$parse_info}['links'][$parse_photoCnt]['name'] .= $data;
	else if (!strlen($parse_curId))
		${$parse_info}[$parse_curTag] .= $data;
	else if (strcmp($parse_curTag, 'photo') == 0)
		${$parse_info}[$parse_curId]['photo'][$parse_photoCnt] .= $data;
	else if (strcmp($parse_curTag, $parse_each) != 0)
		${$parse_info}[$parse_curId][$parse_curTag] .= $data;
}

function infoStartElement($parser, $name, $attr) {
	global $parse_curTag, $parse_curId, $parse_info, $parse_each, $parse_photoCnt, $$parse_info;
	if (strcmp("Xmldata", $name) == 0) return;
	$parse_curTag = $name;
	if (strcmp($parse_curTag, 'visitor') == 0)
		$parse_curTag = $attr['id'];

	if (strcmp($parse_curTag, $parse_each) == 0) {
		$parse_curId = $attr['id'];
		if (strcmp($parse_each, "Photo") == 0) {
			${$parse_info}[$parse_curId] = "";
			return;
		}
		else if (strcmp($parse_each, "Visit") == 0) {
			${$parse_info}[$parse_curId] = array();
		}
		else if (strcmp($parse_each, "Comment") != 0) {
			$parse_photoCnt = 0;
			${$parse_info}[$parse_curId]['photo'] = array();
		}
	}
	if (strcmp($parse_curTag, 'link') == 0) { // in basis.xml
		${$parse_info}['links'][$parse_photoCnt] = array();
		${$parse_info}['links'][$parse_photoCnt]['href'] = $attr['href'];
		${$parse_info}['links'][$parse_photoCnt]['title'] = $attr['title'];
		${$parse_info}['links'][$parse_photoCnt]['name'] = "";
		return;
	}
	if (!strlen($parse_curId))
		${$parse_info}[$parse_curTag] = "";
	else if (strcmp($parse_curTag, 'photo') == 0)
		${$parse_info}[$parse_curId]['photo'][$parse_photoCnt] = "";
	else if (strcmp($parse_curTag, $parse_each) != 0)
		${$parse_info}[$parse_curId][$parse_curTag] = "";
}

function infoEndElement($parser, $name) {
	global $parse_curTag, $parse_curId, $parse_info, $parse_each, $parse_photoCnt;
	$parse_curTag = "";
	if (strcmp($name, $parse_each) == 0)
		$parse_curId = "";
	if ((strcmp($name, 'photo') == 0) || (strcmp($name, 'link') == 0))
		$parse_photoCnt++;
}

function parse_container($parse_infoName, $p_each, $xmlfile, $fix = true) {
	#echo "parsing " .$xmlfile. "<br />\n";
	global $parse_curTag, $parse_curId, $parse_info, $parse_each, $$parse_infoName, $alert_msg;
	$parse_info = $parse_infoName;
	$parse_each = $p_each;
	$parse_photoCnt = 0;
	$parse_curTag = "";
	$parse_curId = "";

	if (strcmp($p_each, "Basis") == 0)
		${$parse_info}['links'] = array();

	if (! file_exists($xmlfile))
		die("The file $xmlfile, does not exist!");

	${$parse_info} = array();

	$xmlParser = xml_parser_create();
	xml_set_element_handler($xmlParser,"infoStartElement","infoEndElement");
	xml_set_character_data_handler($xmlParser,"infoCharacterData");
	xml_parser_set_option($xmlParser,XML_OPTION_CASE_FOLDING,false);

	if (!($fp = fopen($xmlfile,"r")))
		die ("Could not open $xmlfile for reading.");

	while (($data = fread($fp,4096)))
		if (!xml_parse($xmlParser,$data,feof($fp)))
			die($xmlfile.":"
					.sprintf("XML error at line %d column %d : %s", xml_get_current_line_number($xmlParser),
						xml_get_current_column_number($xmlParser),  xml_error_string(xml_get_error_code($xmlParser)))
					."<br />\n<a href=\"admin.php?page=editxml&ignore=all\">Restore last back-up</a> is suggested.");

	fclose($fp);
	xml_parser_free($xmlParser);

	$parse_info =& $$parse_infoName;
	reset($parse_info);

	global $transtable, $transmanual;


	/***** START OF ESCAPE CHARACTERS FIXATIONS *****/
	foreach(array('name', 'date', 'dateadd', 'datetake', 'email', 'url', 'desc', 'copyright') as $aval) {
		if (isset($parse_info[$aval]) && !is_array($parse_info[$aval])) {
			$parse_info[$aval] = strtr($parse_info[$aval], $transtable);
			$parse_info[$aval] = strtr($parse_info[$aval], $transmanual);
		}

		reset($parse_info);
		while (list($key,$value) = each($parse_info)) {
			if (is_array($parse_info[$key]) && isset($parse_info[$key][$aval]) && !is_array($parse_info[$key][$aval])) {
				$parse_info[$key][$aval] = strtr($parse_info[$key][$aval], $transtable);
				$parse_info[$key][$aval] = strtr($parse_info[$key][$aval], $transmanual);
			}
		}
	}

	if (isset($parse_info['links']) && is_array($parse_info['links'])) {
		reset($parse_info['links']);
		foreach ($parse_info['links'] as $key => $value)
			if (is_array($value))
				foreach ($value as $name => $item) {
					$parse_info['links'][$key][$name] = strtr($item, $transtable);
					$parse_info['links'][$key][$name] = strtr($item, $transmanual);
				}
	}
	/***** END OF ESCAPE CHARACTERS FIXATIONS *****/
}

function save_container($parse_infoName, $p_each, $xmlfile) {
	global $$parse_infoName;
	$parse_info =& $$parse_infoName;

	/***** START OF ESCAPE CHARACTERS FIXATIONS *****/
	foreach(array('name', 'date', 'dateadd', 'datetake', 'email', 'url', 'desc', 'copyright') as $aval) {
		if (isset($parse_info[$aval]) && !is_array($parse_info[$aval])) {
			$parse_info[$aval] = htmlspecialchars($parse_info[$aval], ENT_QUOTES, "UTF-8");
		}

		reset($parse_info);
		while (list($key,$value) = each($parse_info)) {
			if (is_array($parse_info[$key]) && isset($parse_info[$key][$aval]) && !is_array($parse_info[$key][$aval])) {
				$parse_info[$key][$aval] = htmlspecialchars($parse_info[$key][$aval], ENT_QUOTES, "UTF-8");
			}
		}
	}

	if (isset($parse_info['links']) && is_array($parse_info['links'])) {
		reset($parse_info['links']);
		foreach ($parse_info['links'] as $key => $value)
			if (is_array($value))
				foreach ($value as $name => $item)
					$parse_info['links'][$key][$name] = htmlspecialchars($item, ENT_QUOTES, "UTF-8");
	}
	/***** END OF ESCAPE CHARACTERS FIXATIONS *****/

	$tempfile = "temp/".strtotime("now")."_".random_string()."_".str_replace("/", "_", $xmlfile);

	if (! ($fout = fopen($tempfile,"w")) )
		die("Couldn't open $tempfile for writing.");
	fputs($fout,"<?xml version='1.0' encoding='UTF-8' ?>\n");
	fputs($fout,"<Xmldata>\n");
	reset($parse_info);
	while (list($key, $value) = each($parse_info)) {
		if (is_array($value)) {
			if (strcmp($p_each, "Basis") == 0) {
				reset($parse_info[$key]);
				while (list($inkey,$invalue) = each($parse_info[$key]))
					fputs($fout,"\t<link href=\"${invalue['href']}\" title=\"${invalue['title']}\">${invalue['name']}</link>\n");
			}
			else {
				fputs($fout,"\t<".$p_each." id=\"$key\">\n");
				reset($parse_info[$key]);
				while (list($inkey,$invalue) = each($parse_info[$key])) {
					if (ctype_lower($inkey)) {
						if (is_array($invalue))
							while (list($ininkey, $ininvalue) = each($parse_info[$key][$inkey]))
								fputs($fout,"\t\t<$inkey><![CDATA[$ininvalue]]></$inkey>\n");
						else
							fputs($fout,"\t\t<$inkey><![CDATA[$invalue]]></$inkey>\n");
					}
					else {
						if (strcmp($p_each, "Visit") == 0)
							fputs($fout,"\t\t<visitor id=\"$inkey\"><![CDATA[".$invalue."]]></visitor>\n");
					}
				}
				fputs($fout,"\t</".$p_each.">\n");
			}
		}
		else {
			if (!is_int($key) && ctype_lower($key))
				fputs($fout,"\t<$key><![CDATA[$value]]></$key>\n");
			else if ((strcmp($p_each, "Photo") == 0) && is_int($key))
				fputs($fout,"\t<Photo id=\"$key\"><![CDATA[".$value."]]></Photo>\n");
		}
	}
	fputs($fout,"</Xmldata>\n");
	fclose($fout);

	/* This action is supposed to guarentee synchronizations! */
	if (!copy($tempfile, $xmlfile))
		die("couldn't copy $tempname to $xmlfile.");
	unlink($tempfile);

	parse_container($parse_infoName, $p_each, $xmlfile, false);
}

######################################################## global admin

function getCommentCount($obj, $fullText) {
	$ret = 1;
	if ($fullText) {
		if ($ret == 0)
			$ret = "No Comment";
		else if ($ret == 1)
			$ret = "One Comment";
		else
			$ret = $ret." Comments";
	}
	return $ret;
}


function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function writeLinkLine($x, $arr = "") {
	global $transtable, $transmanual;
	if (!is_array($arr))
		$arr = array('href' => '', 'name' => '', 'title' => '');
	else
		foreach ($arr as $vkey => $vval)
			$arr[$vkey] = strtr($vval, $transmanual);
	echo "\t<tr id=\"linkline$x\">\n\t\t<td>".($x+1)."</td>";
	$m = array('n' => 'name', 'h' => 'href', 't' => 'title');
	reset($m);
	$bold = (strlen($arr['href']) == 0);
	echo "\n\t\t<td><input class=\"textW\" style=\"font-weight:".($bold?"bold":"normal").";\" name=\"l${x}n\" id=\"l${x}n\" value=\"${arr['name']}\"></input></td>";
	echo "\n\t\t<td><input class=\"textW\" onkeyup=\"fixBoldInput($x, this.value);\" onblur=\"fixBoldInput($x, this.value);\" name=\"l${x}h\" id=\"l${x}h\" value=\"${arr['href']}\"></input></td>";
	echo "\n\t\t<td><input class=\"textW\" name=\"l${x}t\" id=\"l${x}t\" value=\"${arr['title']}\"></input></td>";
?>
		<td style="text-align:center">
			<a style="cursor:pointer" onclick="javascript:linkAddBelow(this.parentNode.parentNode);" title="Add a link below this one">Add</a> |
			<a style="cursor:pointer" onclick="javascript:linkDelThis(this.parentNode.parentNode);" title="Delete this Link">Del</a>
		</td>
	</tr>
<?php
}

function page_is($x) {
	global $page;
	return (strcmp($page, $x) == 0);
}

function write_in_ajax($msg = "") {
	$f = fopen(UPLOAD_AJAX_FILE, "w");
	fwrite($f, $msg);
	fclose($f);
}

function makeTheThumb($ppath, $ta, $mood, $tchar) {
	global $basis;
	if (!isset($basis['jpegq']))
		$basis['jpegq'] = DEFAULT_JPEG_QUAL;
	list($w, $h) = getimagesize($ppath);
	$orig = imagecreatefromjpeg($ppath);
	if ((strcmp($mood, 'width') == 0) || (($w > $h) ^ (strcmp($mood, 'min') == 0))) {
		$tw = $ta;
		$th = round($tw*$h/$w);
	}
	else {					// photo is vertical
		$th = $ta;
		$tw = round($th*$w/$h);
	}
	$timg = imagecreatetruecolor($tw, $th);
	$tpath = substr_replace($ppath, $tchar, -5, 1); // _9.jpg => _x.jpg
	imagecopyresampled($timg, $orig, 0, 0, 0, 0, $tw, $th, $w, $h);
	imageinterlace($timg, 1);
	imagejpeg($timg, $tpath, $basis['jpegq']);
	imagedestroy($timg);
	return "ENDED;$tw;$th";
}

function gen_3_thumb($ppath, $sklW, $sklH, $sklT, $sklL, $ta) { // Generate the Square thumb from skeleton
	global $basis;
	list($w, $h) = getimagesize($ppath);
	$orig = imagecreatefromjpeg($ppath);
	$timg = imagecreatetruecolor($ta, $ta);
	$tpath = substr_replace($ppath, '3', -5, 1); // _9.jpg => _3.jpg
	$rr = $w/(($w<$h)?SKL_PHOTO_W:SKL_PHOTO_W*$w/$h);
	//echo $sklL*$rr."|".$rr."|".$sklW*$rr."|".$sklW."<br>";
	imagecopyresampled($timg, $orig, 0, 0, $sklL*$rr, $sklT*$rr, $ta, $ta, $sklW*$rr, $sklH*$rr);
	imageinterlace($timg, 1);
	imagejpeg($timg, $tpath, $basis['jpegq']);
	imagedestroy($timg);
}

function deletePhoto($pid) {
	global $photo, $categs, $stories, $comments, $photos;

	if (!is_array($categs)) {
		$categs = array();
		parse_container('categs', 	'Category', 'data/categories.xml');
	}
	if (!is_array($stories)) {
		$stories = array();
		parse_container('stories', 	'Story', 	'data/stories.xml');
	}

	if (!is_array($comments)) {
		$comments = array();
		parse_container('comments', 'Comment', 	'data/comments.xml');
	}

	if (!is_array($photos)) {
		$photos = array();
		parse_container('photos', 	'Photo', 	'data/photos.xml');
	}

	$xmlfile = sprintf("data/p_%06d.xml", $pid);
	parse_container('photo', '', $xmlfile);
	$postfix = isset($photo['postfix'])?('_'.$photo['postfix']):"";
	unlink($xmlfile);
	for ($i=0; $i<10; $i++) {
		if (file_exists($filename = PHOTO_PATH.sprintf('%06d%s_%d.jpg', $pid, $postfix, $i)))
			@unlink($filename);
	}

	unset($categs [$photo['categ']][array_search($pid, $categs [$photo['categ']])]);
	unset($stories[$photo['story']][array_search($pid, $stories[$photo['story']])]);
	unset($photos[$pid]);

	reset($categs);
	while (list($key, $val) = each($categs))
		if (isset($val['photo']) && is_array($val['photo']))
			if (($t = array_search($pid, $val['photo'])) !== FALSE)
				unset($categs[$key]['photo'][$t]);

	reset($stories);
	while (list($key, $val) = each($stories))
		if (isset($val['photo']) && is_array($val['photo']))
			if (($t = array_search($pid, $val['photo'])) !== FALSE)
				unset($stories[$key]['photo'][$t]);

	reset($comments);
	while (list($key, $val) = each($comments))
		if (is_array($val))
			if (strcmp($val['owner'], "p".$pid) == 0)
				unset($comments[$key]);

	save_container('comments', 'Comment', 'data/comments.xml');
	save_container('categs', 'Category', 'data/categories.xml');
	save_container('stories', 'Story', 'data/stories.xml');
	save_container('photos', 'Photo', 'data/photos.xml');

	build_rss();
}

function handle_container($contArr, $contName, $contChar) {
	global $edit, $ok_msg, $alert_msg, $cmd, $$contArr, $cid, $doDel;
	$contId = $contChar.'id';
	$conts =& $$contArr;
	if (isset($_GET['cmd'])) {
		$cmd = $_GET['cmd'];
		$isAdd = (strcmp($cmd, 'add') == 0);
		$isEdt = (strcmp($cmd, 'edt') == 0);
		if (!isset($_GET[$contId]) && !isset($_POST[$contId]))
			$alert_msg = "Please enter $contName"."ID as $contId!";
		else {
			$cid = ((isset($_GET[$contId]))?$_GET[$contId]:$_POST[$contId])+0;
			if (strcmp($cmd, 'doEdt') == 0) {
				$edit = true;
				if (!isset($conts[$cid]['name']))
					$alert_msg = "No $contName with this $contName"."ID ($cid) exists!";
			}
			else if (strcmp($cmd, 'doDel') == 0) {
				$doDel = true;
				if (!isset($conts[$cid]['name']))
					$alert_msg = "No $contName with this $contName"."ID ($cid) exists!";
			}
			else if (strcmp($cmd, 'del') == 0) {
				$howto = isset($_POST['howto'])?$_POST['howto']:"";
				if (!isset($conts[$cid]) || !is_array($conts[$cid]))
					$alert_msg = "No $contName with this $contName"."ID (".$cid.") exists!";
				else if (!isset($_POST['howto']))
					$alert_msg = "The method of removation is not posted!";
				else if (($cid == 1) && strcmp($howto, 'empty') != 0)
					$alert_msg = "You can not delete Default $contName!";

				else {
					$subc = $conts[$cid]['sub'];
					$field = ($contChar == 'c')?'categ':'story';
					global $photo;
					$photo = array();
					if ($subc == -1)
						$subc = 1;

					if ((strcmp($howto,'justc') == 0) || (strcmp($howto,'whole') == 0)) {
						reset($conts);
						while (list($acid, $acvals) = each($conts)) {
							if (is_array($acvals) && ($acvals['sub'] == $cid))
								$conts[$acid]['sub'] = $conts[$cid]['sub'];			//to save the connectedness!
						}
						reset($conts);
					}

					if ((strcmp($howto,'empty') == 0) || (strcmp($howto,'justc') == 0)) {
						reset($conts[$cid]['photo']);
						while (list($key, $pid) = each($conts[$cid]['photo'])) {
							if (!in_array($pid, $conts[$subc]['photo']))
								array_push($conts[$subc]['photo'], $pid);
							if ($cid == getPhotoInfo($pid, $field)) {
								$xmlfile = sprintf("data/p_%06d.xml", $pid);
								parse_container('photo', '', $xmlfile);
								$photo[$field] = $subc;
								save_container('photo', 'Photo', $xmlfile);
							}
						}
						$conts[$cid]['photo'] = array();
						if (strcmp($howto,'empty') == 0)
							$ok_msg = "$contName ".$conts[$cid]['name']."'s photos got erased successfully";
						else {
							$ok_msg = "$contName \"".$conts[$cid]['name']."\" ($contName"."ID: $cid) deleted successfully!";
							unset($conts[$cid]);
						}
					}
					else if ((strcmp($howto,'whole') == 0) || (strcmp($howto,'justin') == 0))  {
						reset($conts[$cid]['photo']);
						while (list($key, $pid) = each($conts[$cid]['photo']))
							if ($cid == getPhotoInfo($pid, $field)) {
								$ok_msg .= "Photo $pid (".getPhotoInfo($pid, 'name').") Deleted Successfully.<br />\n";
								deletePhoto($pid);
							}
						if (strcmp($howto,'whole') == 0) {
							unset($conts[$cid]);
							$ok_msg .= "$contName \"".$conts[$cid]['name']."\" ($contName"."ID: $cid) deleted successfully!<br />\n";
						}
					}
				}
			}
			else if ($isEdt || $isAdd) {
				if (!isset($_POST['name']) || (strlen($_POST['name']) == 0))
					$alert_msg = 'The "Name" filed is required!';
				else if ($isAdd && isset($conts[$cid]))
					$alert_msg = "The $contName \"".$_POST['name']."\" is already added as this $contName"."ID ($cid)!";
				else {
					$p = array();
					if ($isEdt)
						$p = $conts[$cid]['photo'];
					$conts[$cid] = array('name' => $_POST['name'], 'desc' => $_POST['desc'],
										 'list' => $_POST['list'], 'pass' => $_POST['pass'],
										 'sub' => $_POST['sub'], 'photo' => ($isEdt)?$conts[$cid]['photo']:array());
					foreach (array('date', 'getcmnts') as $tk => $tv)
						if (isset($_POST[$tv]))
							$conts[$cid][$tv] = $_POST[$tv];
					if (strcmp($cmd, 'add') == 0) {
						//$conts[$cid]['hits'] = 0;
						$conts['last'.$contId] = $cid;
					}
					$ok_msg = "$contName \"".$conts[$cid]['name']."\" ".((strcmp($cmd, 'add') == 0)?"added":"edited")." succesfully!";
				}
			}
			else
				$alert_msg = $cmd.' is not a valid command!';
		}
	}
	$edit &= !strlen($alert_msg);
}

function print_container($contArr, $contName, $contNames, $contChar) {
	global $page, $ok_msg, $alert_msg, $$contArr, $cid, $edit, $doDel;
	$conts =& $$contArr;
	$ccid = $contChar.'id';
?>
		<div class="back2mainR"><a target="_blank" href=".">&nbsp;View Gallery >> </a></div>
		<div class="back2main"><a href="?"><< Admin Page</a></div>
		<div class="part">
			<div class="title">
				<a style="color: white" href="?page=<?php echo $page; ?>">
					Manage <?php echo $contNames; ?>
					<?php writeHelp('Container Management', '#EEE'); ?>:
				</a>
			</div>
			<div class="inside">
<?php if (strlen($alert_msg)) echo "\t\t\t\t<div class=\"method\"><div class=\"note_invalid\">$alert_msg</div></div><br />"; ?>
<?php if (strlen($ok_msg))    echo "\t\t\t\t<div class=\"method\"><div class=\"note_valid\">$ok_msg</div></div><br />"; ?>
				<div class="method">
<?php
	if ($doDel) {
		$theName = $contName." ".$conts[$cid]['name'];
		$directN = 0;
		reset($conts[$cid]['photo']);
		$field = ($contChar == 'c')?'categ':'story';

		while (list($key, $val) = each($conts[$cid]['photo']))
			if ($cid == getPhotoInfo($val, $field))
				$directN++;
?>
					<span class="name">Delete <a href=".?<?php echo $contChar."=".$cid; ?>"><?php echo $theName; ?></a> </span><br />
					<div style="padding: 5px 30px 15px">
						<form method="post" action="?page=<?php echo $page."&".$contChar."id=".$cid; ?>&cmd=del" onsubmit="javascript:return confirmDelete('<?php echo $conts[$cid]['name']; ?>');">
							<span class="dot">&#149;</span>Which action do you wish to do?<br />
							<table width="100%" style="margin-left: 20px;">
							<tr><td width="25%"><label><input name="howto" value="empty" type="radio" class="radio" checked="checked" >&nbsp;<b>Clear The <?php echo $contName; ?>:</b></input></label></td>
								<td> Clears <?php echo $theName; ?> of all its own (direct) photos, <b>nothing will be removed</b>.</td></tr>
							<tr><td><label><input name="howto" value="justc" type="radio" class="radio"       >&nbsp;<b>Delete Only <?php echo $contName; ?>:</b></input></label></td>
								<td> Delete <?php echo $theName; ?> but <b>NOT</b> its photos (parent or default <?php echo $contName;?> will inherited them).</td></tr>
							<tr><td><label><input name="howto" value="justin" type="radio" class="radio"                   >&nbsp;<b>Delete photos inside:</b></input></label></td>
								<td> <b style="color: #900">Delete <?php echo $directN; ?> direct photos</b> inside <?php echo $theName; ?> from Phormer, but saves itself.</td></tr>
							<tr><td><label><input name="howto" value="whole" type="radio" class="radio"                   >&nbsp;<b>Delete with photos:</b></input></label></td>
								<td> <b style="color: #900">Delete <?php echo $theName; ?> and its <?php echo $directN; ?> direct photos</b> - a combination of two prior cases!</td></tr>
							</table>
							<br />
							<input class="submit" type="submit" value="&nbsp;&nbsp;&nbsp;Delete it!&nbsp;&nbsp;&nbsp;"></input>
							<span style="padding-left: 20px;"></span>
						</form>
						<form method="post" action="?page=<?php echo $page; ?>">
							<input class="reset" type="submit" value="&nbsp;&nbsp;&nbsp;Oops, get Back!&nbsp;&nbsp;&nbsp;"></input>
						</form>
					</div>
<?php
	} else {
?>
				<span class="name">Add a New <?php echo $contName; ?></span><br />
					<span style="padding-left: 10px;"></span>
					<span class="dot">&#149;</span>
						<a href="#add">Add a New <?php echo $contName; ?></a>
				</div>

				<br/ >

				<div class="method">
					<span class="name">Current <?php echo $contNames; ?> List
					<span class="thumbcntarr" style="color: #666" >
					<?php
						global $thumbCntArr;
						for($i=0; $i<count($thumbCntArr); $i++)
							echo "\t\t\t&nbsp;[<a href=\"admin.php?page=$page&n=".$thumbCntArr[$i]."\">".$thumbCntArr[$i]."</a>]\n"
					?>
					</span>
					</span>
					<br />
					<div style="padding-left: 30px">
<?php
					reset($conts);
					$n = isset($_GET['n'])?$_GET['n']:-1;
					while (list($accid, $accval) = each($conts)) 		//a ceratin container value!
						if (is_array($accval)) { 					// might be info!
							if ($n-- == 0) {
								echo "\t\t\t\t\t<center><a href=\"admin.php?page=$page&n=-1\">View all categories</a></center><br />\n";
								break;
							}
							echo "\t\t\t\t\t\t\t<div style=\"position: relative;\">"
									."<span style=\"color: #333; position: absolute; top: 0px; right: 80px; \">"
								." <a href=\"?page=$page&cmd=doEdt&$ccid=$accid#add\">Edit it</a>"
								." :: <a href=\"?page=$page&cmd=doDel&$ccid=$accid\">Delete / Clear</a>"
								."</span>\n"
								."<span class=\"dot\">&#149;</span>"
								."<a name=\"$accid\" href=\"./?$contChar=$accid\">".$accval['name']."</a> "
								."<span class=\"categinfo\">["
									.count($accval['photo']).' Photos'
									.((strcmp($contName, "Story") == 0)?
										" ".'since '.$accval['date']
										.", ".((strcmp($accval['getcmnts'], "yes") == 0)?
											getCommentCount('s'.$accid, true)
											:'Doesnt get comments')
										:'')
									.", ".(strlen($accval['pass'])?'Protected by "'.$accval['pass'].'"':'Public')
									." & ".(strcmp($accval['list'], 'list') == 0?'Listed':'Not listed')
								."]</span> "
							."\t\t\t\t\t\t\t\t<div class=\"categdesc\">".nl2br($accval['desc'])."</div>\n"
							."</div>";
						}
					reset($conts);
?>

					</div>
				</div>
				<a name="add"></a>
				<div class="method" style="margin-top: 25px;">
					<span class="name"><?php echo $edit?('Edit the '.$contName.' "'.$conts[$cid]['name'].'"'):"<a style=\"color: black\" href=\"?page=$page\">Add a new ".$contName.'</a>'; ?></span><br />
					<center>
						<form method="post" action="?page=<?php echo $page; ?>&cmd=<?php echo $edit?'edt':'add'; ?>" onsubmit="javascript:return (checkHasPass()<?php echo (strcmp($contName, "Story") == 0)?'&& checkDate()':''; ?>);">
						<input type="hidden" name="<?php echo $ccid; ?>" value="<?php echo $edit?$cid:($conts['last'.$ccid]+1); ?>"></input>
						<table width="70%" cellpadding="5" style="position: relative; text-align: left; ">
							<tr><td>Name<?php writeHelp("Container Name"); ?>:</td>
								<td><input id="name" name="name" type="text" class="text" size="32" value="<?php echo $edit?$conts[$cid]['name']:''; ?>" autocomplete="off"></input></td></tr>
							<tr><td valign="top">Description<?php writeHelp("Container Description"); ?>:</td>
								<td><textarea cols="37" rows="5" name="desc"><?php echo $edit?$conts[$cid]['desc']:''; ?></textarea></td></tr>
							<?php if (strcmp($contName, "Story") == 0) { ?>
								<tr><td>Date:</td><td><input id="date" name="date" type="text" class="text" size="32" value="<?php echo $edit?$conts[$cid]['date']:date('Y/m/d'); ?>"></input></td></tr>
								<tr><td>Get Comments:</td><td><span style="margin-left: 5px"></span><input <?php echo ($edit && isset($conts[$cid]['getcmnts']) && !strcmp($conts[$cid]['getcmnts'], "yes"))?'checked="checked" ':''; ?>name="getcmnts" value="yes" type="radio" class="radio">Yes</input>
																						<span style="margin-left: 22px"></span><input <?php echo ($edit && isset($conts[$cid]['getcmnts']) && !strcmp($conts[$cid]['getcmnts'], "yes"))?'':'checked="checked" '; ?>name="getcmnts" value="no" type="radio" class="radio">No</input></td></tr>
							<?php } else echo "\n"; ?>
							<tr><td>Visibility<?php writeHelp("Container Visibility"); ?>:</td>
								<td><span style="margin-left: 5px"> </span><label for="listRadioYe"><input id="listRadioYe" <?php echo ($edit && !strcmp($conts[$cid]['list'], "list") == 0)?'':'checked="checked" '; ?>name="list" value="list" type="radio" class="radio">Listed</input></label>
									<span style="margin-left: 42px"></span><label for="listRadioNo"><input id="listRadioNo" <?php echo ($edit && !strcmp($conts[$cid]['list'], "list") == 0)?'checked="checked" ':''; ?>name="list" value="hide" type="radio" class="radio">Not Listed</input></label></td></tr>
							<tr><td>Privacy<?php writeHelp("Container Privacy"); ?>:</td>
								<td>	<span style="margin-left: 5px"> </span><label for="public"     ><input                  <?php echo ($edit && (strlen($conts[$cid]['pass'])))?'':'checked="checked" '; ?>name="passRadio" value="" type="radio" class="radio" id="public" onclick="javascript:checkPrivacyRow();">Public</input></label>
										<span style="margin-left: 42px"></span><label for="passworded" ><input id="passworded"  <?php echo ($edit && (strlen($conts[$cid]['pass'])))?'checked="checked" ':''; ?>name="passRadio" value="" type="radio" class="radio" onclick="javascript:checkPrivacyRow();">Passworded</input></label></td></tr>
							<tr id="passwordRow" <?php echo ($edit && (strlen($conts[$cid]['pass'])))?'':'style="display: none"'; ?>><td>Password:</td><td><input name="pass" id="password" type="text" class="text" autocomplete="off" size="20" value ="<?php echo $edit?$conts[$cid]['pass']:''; ?>"></input></td></tr>
							<tr><td>Child of<?php writeHelp("Container Inheritance"); ?>:</td><td>
								<span style="margin-left: 10px"></span>
								<select class="select" name="sub" type="text">
									<option value="-1" <?php echo ($edit && ($conts[$cid]['sub'] != -1))?"":"selected=\"selected\""; ?>>No Inheritance</option>
								<?php
									reset($conts);
									while (list($acid, $acvals) = each($conts))
										if (is_array($acvals) && !($edit && ($acid == $cid))) {
											$sel = $edit?($acid == $conts[$cid]['sub']):false;
											echo "\t\t\t\t\t\t\t\t<option ".($sel?"selected=\"selected\" ":"")."value=\"$acid\">".$acid.": ".$acvals['name']."</option>\n";
										}
									reset($conts);
								?>
								</select></td></tr>
							<tr><td colspan="2" style="text-align: center"> </td></tr>
							<tr><td colspan="2" style="text-align: center">
								<input class="submit" type="submit" value="&nbsp;&nbsp;&nbsp;<?php echo $edit?'Save Changes':'Add '.$contName; ?>&nbsp;&nbsp;&nbsp;"></input>
								<span style="padding-left: 20px;"></span>
								<input class="reset" type="Reset" value="&nbsp;&nbsp;&nbsp;Reset Changes&nbsp;&nbsp;&nbsp;"></input>
							</td></tr>
						</table>
						</form>
					</center>
<?php
		}
?>
				</div>
			</div>
		</div>
<?php
}

######################################################## RSS

function bannedIP($ip) {
	global $basis;
	$bans = explode("\n", $basis['bannedip']);
	foreach ($bans as $banip) {
		if (strcmp($banip, $ip) == 0)
			return true;
	}
	return false;
}

function build_rss() {
	global $basis, $photos, $nphotos, $categs, $stories, $addadd;
	$nphotos = count($photos)-1;

	$filename = 'index.xml';
    $h = fopen($filename, 'w');

	fputs ($h, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
	fputs ($h, "<rss version=\"2.0\">\n");
	fputs ($h, "<channel>\n");
	fputs ($h, "\t<title>${basis['pgname']}</title>\n");
	fputs ($h, "\t<link>$addadd/</link>\n");
	fputs ($h, "\t<description>${basis['pgdesc']}</description>\n");
	fputs ($h, "\t<language>en</language>\n");
	fputs ($h, "\t<generator>http://p.horm.org/er</generator>\n");
	fputs ($h, "\t<lastBuildDate>".date("r")."</lastBuildDate>\n");
	fputs ($h, "\t<managingEditor>${basis['auemail']}</managingEditor>\n");

	if ($nphotos > 0) {
		end($photos);
		$neck = 10;
		$n = min($neck, $nphotos);

		for ($i=0; ($i<$n) && (strcmp(key($photos), 'lastpid') != 0);) {
			if (canthumb(key($photos))) {
				$i++;
				$pid = key($photos);
				$photo = getAllPhotoInfo($pid);
				$dates = sscanf($photo['dateadd'], "%d/%d/%d %d:%d");
				$mtime = date("r", mktime($dates[3], $dates[4], 0, $dates[1], $dates[2], $dates[0]));
				$src = thumb_just_img($pid, 9);
				$desc = "<![CDATA[<img src=\"$addadd/$src\" alt=\""
					.htmlspecialchars($photo['desc'], ENT_QUOTES, "UTF-8")
					."\" />]]>";
				fputs ($h, "\t<item>\n");
					fputs ($h, "\t\t<title>".htmlspecialchars($photo['name'], ENT_QUOTES, "UTF-8")."</title>\n");
					fputs ($h, "\t\t<link>$addadd/?p=$pid</link>\n");
					fputs ($h, "\t\t<guid isPermaLink=\"true\">$addadd/?p=$pid</guid>\n");
					fputs ($h, "\t\t<description>\n");
					fputs ($h, "\t\t\t$desc\n");
					fputs ($h, "\t\t</description>\n");
					fputs ($h, "\t\t<pubDate>$mtime</pubDate>\n");
					fputs ($h, "\t\t<category>".htmlspecialchars($categs[$photo['categ']]['name'], ENT_QUOTES, "UTF-8")."</category>\n");
				fputs ($h, "\t</item>\n");
			}
			prev($photos);
		}
	}

	fputs ($h, "</channel>\n");
	fputs ($h, "</rss>\n");
    fclose($h);
}

#################################################################################################
############################# ABOVE WAS COMMON.PHP IN PRIOR VERSIONS ############################
#################################################################################################

function get_email_address() {
	global $basis;

	$emailat = $basis['auemail'];
	if ($basis['showemail'] != 'asis')
		$emailat = str_replace(array("@", "."), array("[at]", "[dot]"), $basis['auemail']);
	return $emailat;
}

function get_email_link($name="") {
	global $basis;

	if (!isset($name) || !strlen($name))
		$name = $basis['auname'];

	if (!isset($basis['showemail']))
		$basis['showemail'] = 'link';

	$emailat = get_email_address();

	$email = $name;
	if (strcmp($basis['showemail'], 'text') == 0)
		$email = $name." ($emailat)";
	else if (strcmp($basis['showemail'], 'link') == 0)
		$email = "<a href=\"mailto:$emailat?subject=${basis['pgname']} Photos\">$name</a>";
	else if (strcmp($basis['showemail'], 'asis') == 0)
		$email = "<a href=\"mailto:$emailat?subject=${basis['pgname']} Photos\">$name</a>";
	return $email;
}

function write_footer() {
	global $basis, $transtable;

	if (!isset($basis['copyright']))
		$basis['copyright'] = DEFAULT_COPYRIGHT;
?>
	<div style="clear:both;"></div>
	<div style="width: 100%;">
		<div class="footer">
			<?php
				$copyright = strtr($basis['copyright'], $transtable);
				$name = $basis['auname'];
				$email = get_email_link();
				$Phormer = "<a href=\"http://p.horm.org/er\">Phormer, version ".PHORMER_VERSION."</a>";

				$copyright = str_replace(array('$name', '$email', '$Phormer'),
										 array( $name ,  $email ,  $Phormer),
										 $copyright);
				echo nl2br($copyright);
			?>
		</div>
	</div>
<?php
}

function write_headers($title) {
	global $basis, $p, $s, $hasexif;
	$theme = $basis['theme'];
	if (strcmp(substr($theme, -4), '.css') == 0) // i.e. is external
		$theme = "files/externalcss/".$theme;
	else
		$theme = "css.php?theme=$theme";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Language" content="en-us" />
		<link rel="stylesheet" type="text/css" href="<?php echo $theme; ?>" />
		<link rel="alternate" type="application/rss+xml" title="RSS" href="index.xml" />
		<script language="javascript" type="text/javascript" src="files/phorm.js"></script>
		<script lanugage="javascript" type="text/javascript">
			var DarkenVal = <?php echo $basis['opac']; ?>
		</script>
<?php
	if (isset($basis['icon']) && strlen($basis['icon'])) {
		echo "\t\t<link rel=\"icon\" href=\"".$basis['icon']."\">\n";
		echo "\t\t<link rel=\"shortcut icon\" href=\"".$basis['icon']."\">\n";
	}
?>
		<title><?php echo $title ?></title>
	</head>
	<body<?php if (($p != -1) || ($s != -1)) echo " onload=\"prepareBody();\""; ?>>
<?php
}

function write_top() {
	global $basis;
?>
	<div class="topPhorm">
		<span class="headerDot">&#149;&nbsp;</span>
		<a href="."><?php echo $basis['pgname']; ?></a>
		<div class="topPhormAbout">
			<?php echo nl2br($basis['pgdesc']); ?>
		</div>
	</div>
	<center><div id="Granny">
<?php
}

function cutNeck($s, $neck = 20) {
	if (strlen($s) > $neck)
		return substr($s, 0, $neck-3)."&#133; ";
	else
		return $s;
}

function dfsCategStory($contName, $cid, $depth) {
	global $basis, $_GET;
	global $$contName, $rpn, $wStories;
	$conts =& $$contName;
	$clet = strtolower(substr($contName, 0, 1));
	if ($wStories >= $basis['defrss']-2)
		return;

	reset($conts);
	while (list($acid, $acval) = each($conts)) {
		if (is_array($acval) && ($acval['sub'] == $cid) && (strcmp($acval['list'], 'list') == 0)) {
			echo "\t\t<div class=\"item\">\n";
			for ($i=0; $i<$depth; $i++)
				echo "\t\t\t<span style=\"padding-left: 10px;\"></span>\n";
			$date = (isset($acval['date']))?"[${acval['date']}]":"";
			$isCur = (isset($_GET[$clet]) && (strcmp($acid, $_GET[$clet]) == 0));

			$acval['name'] = cutNeck($acval['name'], 20);

			echo "\t\t\t<span class=\"categeach\"><a "
				."href=\".?$clet=$acid".($rpn == $basis['defrpc']?"":"&rpn=$rpn")
				."\" title=\"".strip_tags(html_entity_decode($acval['desc']))." $date\">";

			$dot = (strlen($acval['pass']) > 0)?"<span style=\"font-size: 1.2em; position: relative; top: 3px;\">*</span>":"&#149;";

			if ($isCur)
				echo "<span class=\"reddot\" style=\"font-size: 1em; padding-right: 4px;\">$dot</span>";
			else
				echo "<span class=\"dot\">$dot</span>";
			echo $acval['name']
				."<span class=\"categinfo\">"
				."[".count($acval['photo'])."]</span> ";
			echo "</a></span>\n";
			echo "\t\t</div>\n";
			if (strcasecmp($contName, 'stories') == 0)
				$wStories++;
			if ($wStories < $basis['defrss']-2)
				dfsCategStory($contName, $acid, $depth+1);
		}
	}

	reset($conts);
	if ($cid != -1)
		while (list($acid, $acval) = each($conts))
			if ($acid == $cid) {
				//next($conts);
				return;
			}
}

function cont_cmp($a, $b) {
	return strcmp($a['name'], $b['name']);
}

function write_radio_list($desc, $hName, $fi, $values, $valNames) {
	$sl_desc = str_replace(" ","", $desc);
?>
							<tr><td valign="top">
								<a name="<?php echo $sl_desc; ?>"></a>
								<span class="dot">&#149;</span><b><?php echo $desc; ?></b>
								<?php writeHelp($hName); ?>:
							</td><td>
								&nbsp;&nbsp;
<?php
	global $basis;
	if (!isset($basis[$fi]))
		$basis[$fi] = $values[0];

	$n = count($values);
	for ($i=0; $i<$n; $i++) {
		$sel = ($basis[$fi] == $values[$i])?" checked=\"checked\"":"";
		echo "\t\t\t\t\t\t\t\t<label><input type=\"radio\" class=\"radio\" "
			."name=\"$fi\" value=\"{$values[$i]}\" $sel>{$valNames[$i]}</input></label>\n";
	}
?>
							</td></tr>
<?php
}

function write_conts($contarr, $contName) {
	//global ${$contarr};  <span class=\"thumbcntarr\"> :: ".count(${$contarr})."</span>
	global $categs, $stories, $wStories, $basis;
?>
	<div class="part">
		<div class="titlepart">
			<span class="reddot">&#149;</span>
<?php
	echo "<a href=\".".((strcasecmp($contName, "stories") == 0)?"?mode=stories":"")."\">"
			.$contName
		."</a>"
		."</div>\n";
	echo "\t\t<div class=\"submenu\">\n";

	if (strcasecmp($contName, "stories") != 0)
		uasort(${$contarr}, "cont_cmp");

	dfsCategStory($contarr, -1, 0, 0);
	if ((strcasecmp($contarr, 'stories') == 0) && ($wStories >= $basis['defrss']))
		echo "\t\t\t<br /><a href=\".?alls=1\">[Show all Stories]</a>\n";
?>
		</div>
	</div>
<?php
}

function write_credits() {
global $basis, $comments;
?>
	<div class="part">
		<div class="titlepart"><span class="reddot">&#149;</span>Etc</div>
		<div class="submenu">
<?php
	global $basis;
	if (showOnSideBar("checkemail")) {
		if (isset($basis['showemail'])&&
		 	((strcmp($basis['showemail'], 'asis') == 0) || (strcmp($basis['showemail'], 'link') == 0))) {
		 		$email = get_email_link('Email');
?>
			<div class="item"><span class="dot">&#149;</span>&nbsp;<?php echo $email; ?></div>
<?php
		 }
	}

	if (showOnSideBar("checkrss")) {
?>
			<div class="item"><span class="dot">&#149;</span>&nbsp;<a href="index.xml" title="RSS Feed">RSS</a></div>
<?php
	}

	if (showOnSideBar("checkadmin")) {
		$notseen = "";
		$lastcmnt = (int)$comments['lastiid'];
		if (isset($basis['lastcmntseen']) && ($lastcmnt > $basis['lastcmntseen']))
			$notseen = " (".($lastcmnt - $basis['lastcmntseen']).")";
?>
			<div class="item"><span class="dot">&#149;</span>&nbsp;<a href="admin.php"
				title="Login to the Administration Region">Admin Page <?php echo $notseen; ?></a></div>
<?php
	}
?>
		</div>
		<br />
		<div class="titlepart"><span class="reddot">&#149;</span>Powered by</div>
		<div class="submenu">
			<div class="item"><span class="dot">&#149;</span>&nbsp;<a href="http://p.horm.org/er" title="Rephorm Your Phormer Pharm!">Phormer <?php echo PHORMER_VERSION; ?></a></div>
		</div>

	</div>
<?php
}

function get_new_sessid() {
	$ret = "";
	for ($i=0; $i < 32; $i ++)
		$ret .= chr(ord('a')+rand(0, 25));
	return $ret;
}

function get_phormer_sessid() {
	$r = "";
	if (isset($_COOKIE['phormer_sessid']))
		$r = $_COOKIE['phormer_sessid'];
	else
		$r = get_new_sessid();
	setcookie('phormer_sessid', $r, time()+MAX_VISIT_ONLINE);
	return $r;
}

function haveAVisit() {
	global $visits;

	if(!isset($visits['archive']))
		$visits['archive'] = array();
	if(!isset($visits['online']))
		$visits['online'] = array();


	$today = "d".date("Ymd");
	if (!isset($visits['archive'][$today]))
		$visits['archive'][$today] = 0;
	$visits['archive'][$today]++;

	$ip = "s".$_SERVER['REMOTE_ADDR'];

	/* I hope it doesn't cause any bug! '*/

	$ip .= get_phormer_sessid();

	$now = GetTimeWithDiffer();
	$visits['online'][$ip] = $now;

	foreach($visits['online'] as $ip => $time)
		if ($now - $time > MAX_VISIT_ONLINE)
			unset($visits['online'][$ip]);

	save_container('visits', 	'Visit', 	'data/visits.xml');
}

function visitsToday() {
	global $visits;

	return $visits['archive']['d'.date("Ymd")];
}

function visitsYesterday() {
	global $visits;

	$yes = date("Ymd", strtotime("yesterday"));
	if (!isset($visits['archive']['d'.$yes]))
		return "";
	else
		return $visits['archive']['d'.$yes];
}

function visitsThisMonth() {
	global $visits;

	$r = visitsToday();
	for ($i=1; $i<=30; $i++) {
		$day = "d".date("Ymd", strtotime("-$i day"));
		if (isset($visits['archive'][$day]))
			$r += $visits['archive'][$day];
	}
	return $r;
}

function visitsTotal() {
	global $photos;

	$r = 0;
	foreach($photos as $pid => $val)
		if (is_int($pid)) {
			$a = explode(" ", $val);
			$r += $a[0];
		}
	return $r;
}

function write_stats() {
	global $photos, $stories, $visits;

	end($photos);
	$pid = key($photos);
	reset($photos);

	$lastUpdate = "Never before";
	if (photo_exists($pid)) {
		$d = getPhotoInfo($pid, "dateadd");
		$dates = sscanf($d, "%d/%d/%d %d:%d");
		$udate = mktime($dates[3], $dates[4], 0, $dates[1], $dates[2], $dates[0]);

		$utoday = GetTimeWithDiffer();
		$lastUpdate = SecsToText($utoday-$udate);
	}
?>
			<div class="part">
			<div class="titlepart"><span class="reddot">&#149;</span>Statistics</div>
				<div class="submenu">
<?php if (showOnSideBar("checkstory")) { ?>
					<div class="item"><span class="dot">&#149;</span> Stories: <?php echo count($stories)-1; ?></div>
<?php } ?>
					<div class="item"><span class="dot">&#149;</span> Photos: <?php echo count($photos)-1; ?></div>
					<div class="item" style="line-height: 130%"><span class="dot">&#149;</span> Last Update: <br />
							&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <?php echo $lastUpdate; ?></div>
					<br />
					<div class="item"><span class="dot">&#149;</span> Visitors Online: <?php echo count($visits['online']); ?> </div>
					<div class="item"><span class="dot">&#149;</span> Today Hits: <?php echo visitsToday(); ?> </div>
<?php if (strlen($t = visitsYesterday())) { ?>
					<div class="item"><span class="dot">&#149;</span> Yesteday's: <?php echo $t; ?> </div>
<?php } ?>
					<div class="item"><span class="dot">&#149;</span> This Month: <?php echo visitsThisMonth(); ?> </div>
					<div class="item" style="line-height: 130%"><span class="dot">&#149;</span> Photo Hits:
							<!-- <br /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; -->
							<?php echo number_format(visitsTotal()); ?></div>
				</div>
			</div>
<?php
}

function writeNextz($p) {
	global $photos, $categs, $stories, $basis;
	end($photos);
	while(key($photos) != $p)
		prev($photos);
	do {
		if (!prev($photos)) {  break; }
	} while (!canthumb(key($photos)));
	$prev = key($photos);
	if (!$prev)
		{ $prev = $p; reset($photos); }

	next($photos);
	do {
		if (!next($photos)) break;
	} while (!canthumb(key($photos)));
	$next = key($photos);
	if (!$next) $next = $p;

	$photo = getAllPhotoInfo($p, "./");
?>
	<div class="navigation">
		<div class="title"><span class="darkdot">&#149; </span>Prev.</div>
			<?php thumbBox($prev, "", false, false, "_self"); ?>
		<div class="bottitle">&nbsp;</div>
	</div>

	<div class="navigation">
		<div class="title" style="text-align: center">
			<span class="darkdot">&#149;</span>
			Random Neighbours
			<span class="darkdot">&#149;</span>
		</div>
<?php
	$arr = array();
	if (!isset($basis['pickneigh']))
		$basis['pickneigh'] = 'all';

	if ((strcmp($basis['pickneigh'], 'categs' ) == 0) || (strcmp($basis['pickneigh'], 'all') == 0))
		$arr = array_merge($arr, array_values($categs[$photo['categ']]['photo']));
	if ((strcmp($basis['pickneigh'], 'stories') == 0) || (strcmp($basis['pickneigh'], 'all') == 0))
		$arr = array_merge($arr, array_values($stories[$photo['story']]['photo']));

	if (strcmp($basis['pickneigh'], 'all') == 0)
		$arr = array_values(array_unique($arr));

	$nc = count($arr);
	srand(time());
	$outed = array();
	$targ = "_self";
	if (isset($basis['linktarget']) && strcmp("_blank", $basis['linktarget']) == 0)
		$targ = "_blank";
	for ($i=0; $i<4; $i++) {
		$rp = rand(0, $nc-1);
		while (!canthumb($arr[$rp]))
			$rp = ($rp+1)%$nc;
		if ($nc > 4)
			for ($j=0; $j<$i; $j++)
				if (($rp == $outed[$j]) || ($arr[$rp] == $p)) {
					do {
						$rp = ($rp+1)%$nc;
					} while (!canthumb($arr[$rp]));
					$j = -1;
				}
		$outed[$i] = $rp;
		thumbBox($arr[$rp], "", false, false, $targ);
	}
?>
		<div class="bottitle">&nbsp;</div>
	</div>

	<div class="navigation">
		<div class="title" style="text-align: right">Next<span class="darkdot"> &#149;</span></div>
			<?php thumbBox($next, "", false, false, "_self"); ?>
		<div class="bottitle">&nbsp;</div>
	</div>
	<div class="divClear"></div>
<?php
}

function random_string() {
	#srand(time());
	$ret = "";
	for ($i=0; $i<WV_LENGTH; $i++)
		$ret .= chr(rand(ord('0'), ord('9')));
	return $ret;
}

function random_seed() {
	#srand(time());
	do {
		$ret = "";
		for ($i=0; $i < 5; $i ++)
			$ret .= chr(ord('a')+rand(0, 25));
	} while (file_exists("temp/{$ret}_9.jpg"));
	return $ret;
}

function RecursiveDepthToPx($depth) {
	$ret = 0;
	if ($depth < 5)
		$ret = $depth*40;
	else if ($depth < 10)
		$ret = 200 + ($depth - 5) * 30;
	else
		$ret = 350 + ($depth - 10) * 20;

	return $ret;
}

function writeRecursiveCommenting($t, $c, $r, $key, $depth) {
	global $comments, $isAdmin, $basis, $_COOKIE;

	$val = $r[$key];

	/* write this */
		echo "<div style=\"position: relative; margin-left: ".(20+RecursiveDepthToPx($depth))."px; \">";
		echo "<div class=\"divClear\"></div>";
		echo "<div style=\"position: relative;\">";
			echo "<div class=\"under\"></div></div>\n";
		echo "<div class=\"cell\">\n";
		echo "<div class=\"head\">\n";
		echo "<span class=\"leaveReply\">[<a href=\"#leaveComment\" onclick=\"javascript:doReply('$key')\">"
			." Reply </a>]</span>";

		$name = trim($val['name']);
		$www = trim($val['url']);
		if (strtolower(substr($www, 0, 7)) == "http//")
			$www = substr($www, 6);
		if (strtolower(substr($www, 0, 7)) != "http://")
			$www = "http://".$www;
		$email = trim($val['email']);


		if (strlen($name) == 0) $name = "Anonymous";
		if (strcmp($name, "ADMIN") == 0) {
			$name = "<b>Admin</b> ({$basis['auname']})";
			$www = ".";
			$email = get_email_address();
		}
		if ($isAdmin)
			$name .= "&lrm; :: {".$val['ip']."}";

		$haswww = strcmp($www, "http://") != 0;

		echo "<a name=\"$key\" href=\"#$key\"><span class=\"dot\">&#149;</span></a>&nbsp;&nbsp;".$name;

		if ($haswww || (strlen($email)))
			echo " [ ";
		if ($haswww) {
			echo "<a href=\"$www\">W</a>";
			if (strlen($email))
				echo " | ";
		}
		if (strlen($email)) {
			$emailat = str_replace(array("@", "."), array("[at]", "[dot]"), $email);
			echo "<a href=\"mailto:".$emailat."\">@</a>";
		}
		if ($isAdmin) {
			echo " | <a href=\".?$t=$c&cmd=delcmnt&cmntid=$key#cmnts\">";
			echo "del</a>";
		}
		if ($haswww || (strlen($email)))
			echo " ]";
		$dates = sscanf($val['date'], "%d-%d-%d %d:%d");
		if ($dates[0] == 0)
			$mtime = "";
		else
			$mtime = " on ".date("M jS \o\f y \a\\t H:i", mktime($dates[3], $dates[4], 0, $dates[1], $dates[2], $dates[0]));
		echo $mtime." said:";
		echo "</div>\n";

		$en = textDirectionEn($val['txt']);
		$dir = $en?"":" class=\"r\"";
		echo "<blockquote$dir>".nl2br($val['txt'])."\n";
		echo "</blockquote>\n";
		echo "</div>\n";
		echo "</div>";

	/* write child */
	while (list($akey, $aval) = each($r))
		if ($aval['reply'] == $key)
			writeRecursiveCommenting($t, $c, $r, $akey, $depth+1);
}


function writeCommenting($t, $c) {
	global $alert_msg, $r;
	$cname = ($t == 's')?'Story':'Photo';
?>
	<center>
	<a name="Commenting"></a>
	<div class="Commenting">
		<div class="title" style="width: 100%">
			<span class="leaveReply" style="padding-right: 5px; letter-spacing :0px;">
				[<a href="#hide" onclick="toggle('allComments', 'contractComments', this);"> Hide all </a> ]
			</span>
			<span class="reddot" style="font-size: 14px">&#149;</span>
			<a href="#Commenting">Comments on this <?php echo $cname; ?></a>
		</div>
		<div id="contractComments" class="bcell" style="display: none; text-align: center">
			&#133; Comments Contracted &#133;
		</div>
		<div id ="allComments" style="display: block;">
<?php
	global $comments, $isAdmin, $basis, $_COOKIE;
	$own = $t.$c;

	reset($comments);
	$r = array();
	while (list($key, $val) = each($comments))
		if (strcmp($key, "lastiid") != 0)
			if (strcmp($val['owner'], $own) == 0) {
				if (!isset($val['reply']))
					$val['reply'] = 0;
				$r[$key] = $val;
			}
	reset($r);

	while (list($key, $val) = each($r))
		if ($val['reply'] == 0)
			writeRecursiveCommenting($t, $c, $r, $key, 0);
	if (count($r) == 0)
		echo "<div class=\"bcell\">No Comment yet.</div>\n";
?>
		</div>
		<div class="bottitle">&nbsp;</div>
		<a name="leaveComment"></a>
		<div class="title"><span class="reddot">&#149;</span><a href="?<?php echo "$t=$c"; ?>"> Leave your own comment</a></div>
		<div class="bcell">
			<form action="<?php echo ".?$t=$c#cmnts"; ?>" method="post"<?php if (!$isAdmin) echo ' onsubmit="return checkWV();"'; ?>>
			<table cellspacing="2" cellpadding="2" width="60%">
<?php if ($isAdmin)	{  ?>
			<tr><td width="40%">
				<label for="ComIsAdminYe"><input id="ComIsAdminYe" type="radio" class="radio" name="byadmin" value="yes" checked="checked"
					   onclick="javascript:hideElem('ComNameTR');hideElem('ComEmailTR');hideElem('ComWebTR');hideElem('ComWVTR');">As Admin</input></label>
			</td><td width="60%">
				<label for="ComIsAdminNo"><input id="ComIsAdminNo" type="radio" class="radio" name="byadmin" value="no"
					   onclick="javascript:tableRowElem('ComNameTR');tableRowElem('ComEmailTR');tableRowElem('ComWebTR');tableRowElem('ComWVTR');">As Other</input></label>
			</td></tr>
<?php }
	$defDisp = " style=\"display: ".($isAdmin?"none":"table-row")." ;\"";
	foreach (array('name', 'email', 'url', 'reply') as $item) {
		$def = "def";
		${$def.$item} = "";
		if (isset($_COOKIE['phorm_cmnt_'.$item]))
			${$def.$item} = $_COOKIE['phorm_cmnt_'.$item];
		if (strlen($alert_msg) && isset($_POST[$item]))
			${$def.$item} = $_POST[$item];
	}
?>
					<tr id="ComNameTR" <?php echo $defDisp; ?>>
						<td width="40%">  Name:   </td>
						<td width="60%"><input type="text" size="20" name="name" value="<?php echo $defname; ?>"></td>
					</tr>
					<tr id="ComEmailTR" <?php echo $defDisp; ?>>
						<td>  Email:  </td>
						<td><input type="text" size="20" name="email" value="<?php echo $defemail; ?>"></td>
					</tr>
					<tr id="ComWebTR" <?php echo $defDisp; ?>>
						<td>  Webpage:</td>
						<td><input type="text" size="20" name="url" value="<?php echo $defurl; ?>"></td>
					</tr>
<?php
	if (!$isAdmin && hasWV()) {
					$basis = array();
					parse_container('basis', 'Basis', 'data/basis.xml');
					$basis['wvw'] = random_string();
					save_container('basis', 'Basis', 'data/basis.xml');
?>
					<tr id="ComWVTR" <?php echo $defDisp; ?>>
						<td valign="bottom">
							Word verification:
						</td>
						<td>
							<script language="javascript" type="text/javascript">
								md5 = "<?php echo md5($basis['wvw']); ?>";
							</script>
							<input id="wvinput" type="text" size="10" name="wvw" autocomplete="off">
							<img id="wvwimg" src="wv.php?rand=<?php echo rand(1, 1000000000); ?>" style="position: relative; top: 4px;" />
						</td>
					</tr>
<?php
	}
	$showRep = (strlen($defreply) > 0) && (strcmp($defreply, "0") != 0)
									   && (strlen($alert_msg) > 0);
?>
					<tr id="ComReplyTR"<?php echo $showRep?"":" style=\"display: none;\""; ?>>
						<td>  Reply to Comment: </td>
						<td>
							<span class="leaveReply" style="padding-right: 5px">
								[<a href="#" id="viewComment"> View That </a>] &nbsp;
								[<a href="#leaveComment" onclick="javascript:doReply('0');"> New Thread </a>]
							</span>
							<input id="cmntReply" type="text" size="4" name="reply" value="<?php echo $defreply; ?>">
						</td>
					</tr>

					<tr><td colspan="2">
						<textarea id="cmntTextArea" rows="6" cols="40" type="text" name="txt"><?php
							if (strlen($alert_msg) && isset($_POST['txt'])) echo $_POST['txt'];
						?></textarea>
					</td></tr>
					<tr><td colspan="2" style="text-align: center">
						<input type="hidden" name="cmd" value="addcmnt<?php echo $t; ?>"></input>
						<input type="submit" value=" &nbsp; Submit Comment &nbsp; "></input>
					</td></tr>
			</table>
			</form>
		</div>
		<div class="bottitle">&nbsp;</div>
	</div>
	</center>
<?php
}

function hasWV() {
	global $basis;
	return (!isset($basis['haswvw']) || strcmp($basis['haswvw'], "no") != 0);
}

function unauthorized($clet, $cid, $p) {
	$cname = ($clet == 'c')?"category":"story";
	echo "<div class=\"pvTitle\"><span class=\"reddot\">&#149;</span>Authentication Failed.</div>\n";
	echo "<div class=\"authFailed\">\n";
	echo "This is a private photo which is being stored in a private $cname.<br />\n";
	echo "Login to view it.<br />\n";
	echo "<form action=\".?$clet=$cid&cmd=login&done=$p\" method=\"POST\">\n";
	echo "<center>Password for $cname #$cid: &nbsp; <input name=\"pass\" type=\"password\" style=\"position: relative; top: 5px; margin: 5px auto;\" size=\"10\"></input></center>\n";
	echo "</form>\n";
	echo "</div>\n";
	echo "<div class=\"pvEnd\">&nbsp;</div>\n";
}

function auth_failed_div($clet, $cid, $cname) {
	global $alert_msg;
?>
	<div class="partmain">
		<div class="titlepart"><span class="reddot">&#149;</span>Authentication Failed</div>
		<div class="midInfo">
			<?php
				if (strlen($alert_msg) > 0)
					echo "<div class=\"alert_msg\">$alert_msg</div>\n";
				echo "This $cname (#$cid) is a private one. <br />\n";
			?>
			<div style="text-align: center; padding: 5px 0px;">
			<form action='<?php echo ".?$clet=$cid&cmd=login"; ?>' method='POST'>
				Password : &nbsp; <input name="pass" type="password" style="position: relative; top: 5px; margin: 5px auto;" size="10"></input>
			</form>
			</div>
		</div>
		<div class="end"></div>
	</div>
<?php
}

function write_container($clet) {
	global $photos, $nphotos, $categs, $stories, $n, $ns, $$clet, $isAdmin, $alert_msg;
	global $transtable;
	$cname = ($clet == "c")?"Category":"Story";
	$cid = $$clet;
	$contarr  = ($clet == "c")?"categs":"stories";
	$contPage = ($clet == "c")?"categories":"stories";
	$cidid 	  = ($clet == "c")?"cid":"sid";
	$cont = array();
	$cont = ${$contarr}[$cid];
	if (!checkThePass($clet, $cid))
		auth_failed_div($clet, $cid, $cname);
	else {
		$ss_and = "$clet=$cid&n=$n";
		if ($ns > 0)
			$ss_and .= "&ns=$ns";
?>
	<div class="partmain" id="slideShow">
		<div class="titlepart">
			<span class="leaveReply">
				[ <a href=".?feat=slideshow&<?php echo $ss_and; ?>">SlideShow</a> ]
			</span>
			<?php
				if ($isAdmin)
					echo "\t\t\t\t<span class=\"pvTitleInfo\" style=\"position: relative; top: -8px;\">["
						."<a href=\"admin.php?page=$contPage&cmd=doEdt&$cidid=$cid#add\">Edit</a>]</span>\n";
			?>
			<span class="reddot">&#149;</span>
			<?php
				if ($clet == 's') {
					$dates = sscanf($cont['date'], "%d/%d/%d");
					$mtime = date("F jS y", mktime(0, 0, 0, $dates[1], $dates[2], $dates[0]));
				}
				else
					$mtime = '';
				echo "<span style=\"font-size: 1.2em;\"><a href=\".?$clet=$cid\" class=\"theTitleA\">"
					.$cont['name']."</a></span> <span class=\"small\">$mtime</span>";
			?>
			<span class="thumbcntarr">
			<?php
				global $thumbCntArr;
				for($i=0; $i<count($thumbCntArr); $i++)
					echo "\t\t\t&nbsp;[<a href=\".?$clet=$cid&n=".$thumbCntArr[$i]."\">".$thumbCntArr[$i]."</a>]\n";
			?>
			</span>
		</div>
		<?php if (strlen($cont['desc'])) { ?>
		<div class="midInfo">
			<?php
				$desc = strtr($cont['desc'], $transtable);
				$desc = nl2br($desc);
				echo $desc."\n<br />\n";
			?>
		</div>
		<?php } ?>
		<div class="start">
<?php
	$np = count($cont['photo']);

	if ($ns != 0)
		echo "<span class=\"titlepartlinkL\">[ <a href=\".?$clet=$cid&ns=".max(0, $ns-$n).($n == DEFAULT_N?"":"&n=$n")."\">Previous Photos</a> ]<br />&nbsp;</span>";
	if ($ns+$n<=$np)
		echo "<span class=\"titlepartlinkR\">[ <a href=\".?$clet=$cid&ns=".($ns+$n).($n == DEFAULT_N?"":"&n=$n")."\">Next Photos</a> ]<br />&nbsp;</span>";
?>
		</div>
		<div class="submenu">
		<?php
			sort($cont['photo']);
			end($cont['photo']);
			$tns = 0;
			for ($i=0; $i<$ns; ) {
				#echo $i."+".$tns."+".current($cont['photo'])."!<br />";
				if (photo_exists(current($cont['photo'])))
					if (canthumb(current($cont['photo'])))
						$i++;
					prev($cont['photo']);
				if (++$tns>$np)
					break;
			}
			for ($i=0; ($i<$n);) {
				if ($tns++>$np)
					break;
				if (photo_exists(current($cont['photo'])))
					if (thumbBox(current($cont['photo'])))
						$i++;
				prev($cont['photo']);
			}
		?>
		</div>
		<div class="end">
<?php
	if ($ns != 0)
		echo "<span class=\"titlepartlinkL\">[ <a href=\".?$clet=$cid&ns=".max(0, $ns-$n).($n == DEFAULT_N?"":"&n=$n")."\">Previous Photos</a> ]<br />&nbsp;</span>";
	if ($ns+$n<=$np)
		echo "<span class=\"titlepartlinkR\">[ <a href=\".?$clet=$cid&ns=".($ns+$n).($n == DEFAULT_N?"":"&n=$n")."\">Next Photos</a> ]<br />&nbsp;</span>";
?>
		</div>
<?php
	global $ok_msg, $alert_msg;
	if (strlen($alert_msg))
		echo "<div class=\"alert_msg\">$alert_msg</div>\n";
	else if (strlen($ok_msg))
		echo "<div class=\"ok_msg\">$ok_msg</div>\n";
	if (isset($cont['getcmnts']) && (strcmp($cont['getcmnts'], 'yes') == 0))
		writeCommenting('s', $cid);
	}
}

function dropthebox($pid, $x) {
	global $photos, $basis;
	$imgFile = PHOTO_PATH.getImageFileName($pid, '3');
	$rating = array();
	$rating = explode(" ",$photos[$pid]);
	$hits = $rating[0];
	$rate = 0;
	$raters = substr(strrchr($rating[1], '/'), 1);
	eval("@\$rate =".$rating[1].";");
	$rate = round($rate, 2);
	$photo = getAllPhotoInfo($pid, "./");
	$theName = $photo['name'];
	$title = $photo['name'].": $hits hits and rated $rate by $raters";
?>
	<div class="aThumbInBox" style="<?php echo (stristr($_SERVER['HTTP_USER_AGENT'], "IE") == true)?"filter:alpha(opacity=".$basis['opac'].");":"-moz-opacity:".($basis['opac']/100).";"; ?>;
		<?php echo "top: ".rand(0, 325)."px; left: ".rand(0, 325)."px; z-index: ".$pid.";"; ?>;"
		onmouseover="javascript: this.style.zIndex=10000;LightenIt(this);"
		id="ThumbInBox<?php echo $x; ?>"
		onmouseout="javascript: this.style.zIndex=<?php echo $pid;?>;DarkenIt(this, 0.45);">
	<center>
		<a href=".?p=<?php echo $pid; ?>" title="<?php echo $title; ?>">
			<img src="<?php echo $imgFile; ?>" /><br />
		</a>
	</center>
	</div>
<?php
}

function write_boxPhotos() {
	global $stories, $basis, $rsn, $rss, $photos, $nphotos, $n, $thumbCntArr;
	$neck = (isset($_GET['n']))?$n:$basis['defjbn'];
?>
	<div class="partmain">
		<div class="titlepart">
			<span class="reddot">&#149;</span>Jungle of the Shots
			<span class="thumbcntarr">
			<?php
				$thumbCntArr = array(10, 20, 50, 100, 200);
				for($i=0; $i<count($thumbCntArr); $i++)
					echo "\t\t\t&nbsp;[<a href=\".?mode=box&n=".$thumbCntArr[$i]."\">".$thumbCntArr[$i]."</a>]\n"
			?>
				:: <a href="javascript:reshuffle();">Reshuffle now</a>
			</span>
		</div>

		<div class="submenu">
			<div id="jungleBox">
		<?php
			end($photos);
			$arr = array();
			for ($i=0; ($i < $nphotos) && ($i < $neck); prev($photos))
				if ((strcmp(key($photos), 'lastpid') != 0) && canthumb(key($photos))) {
					array_push($arr, key($photos));
					$i++;
				}
			$arr = array_reverse($arr);
			$x = 0;
			foreach ($arr as $pid)
				dropthebox($pid, $x++);
		?>
			<input type="hidden" id="thumbscount" value="<?php echo $x; ?>"></input>
			</div>
		</div>
		<div class="end"></div>
	</div>
<?php
}

function canStoryBox($sid) {
	global $stories;
	if ((!checkThePass("s", $sid)) || (count($stories[$sid]['photo']) < 1))
		return false;
	return true;
}

function storyBox($sid) {
	global $stories;
	if (!canStoryBox($sid))
		return false;

	$ps = array_rand(array_flip($stories[$sid]['photo']), min(4, count($stories[$sid]['photo'])));
?>
	<div class="aStory">
		<div class="titlepart">
			<span class="dot" style="font-size: 14px;">&#149;</span>
			<a href=".?s=<?php echo $sid; ?>">
				<?php echo $stories[$sid]['name']; ?>
			<span class="thumbcntarr">
				since <?php echo textdate($stories[$sid]['date']); ?>
				:
				<?php echo $stories[$sid]['desc']; ?>
			</span>
			</a>
		</div>
		<div class="submenu">
<?php
	if (count($ps) <= 1)
		$ps = array($ps);
	foreach ($ps as $pid)
		thumbBox($pid);
?>
		</div>
		<div class="end" style="text-align: right;">
			[ <a href=".?s=<?php echo $sid; ?>">
			View all <?php echo count($stories[$sid]['photo']); ?> photos
			of <?php echo $stories[$sid]['name']; ?>
			</a> ]
<?php
?>
		</div>
	</div>
<?php
	return true;
}

function write_lastStories() {
	global $stories, $nstories, $basis, $rsn, $rss;
?>
	<div class="partmain">
		<div class="titlepart">
			<span class="reddot">&#149;</span>Recent Stories
			<span class="thumbcntarr">
			<?php
				global $thumbCntArr;
				for($i=0; $i<count($thumbCntArr); $i++)
					echo "\t\t\t&nbsp;[<a href=\".?mode=stories&rsn=".$thumbCntArr[$i].($rss == 0?"":"&rss=$rss")."\">".$thumbCntArr[$i]."</a>]\n"
			?>
			</span>
		</div>

		<div class="submenu">
		<?php
			$rsn = min($rsn, $nstories);
			for ($i=0; $i<$rss;) {
				if (canStoryBox(key($stories)))
					$i++;
				next($stories);
			}

			for ($i=0; ($i<$rsn) && (strcmp(key($stories), 'lastsid') != 0);) {
				if (storyBox(key($stories)))
					$i++;
				next($stories);
			}
		?>
		</div>
		<div class="end">
<?php
		if ($rss != 0)
			echo "<span class=\"titlepartlinkL\">[ <a href=\".?mode=stories&rss=".max(0, $rss-$rsn).($rsn == $basis['defrsc']?"":"&rsn=$rsn")."\">Previous Recent Stories</a> ]<br />&nbsp;</span>";
		if ($rss+$rsn<$nstories)
			echo "<span class=\"titlepartlinkR\">[ <a href=\".?mode=stories&rss=".($rss+$rsn).($rsn == $basis['defrsc']?"":"&rsn=$rsn")."\">Next Recent Stories</a> ]<br />&nbsp;</span>";
?>
		</div>
	</div>
<?php
}

function below_GetRate($exp) {
	$rate = 0;
	$raters = substr(strrchr($exp[1], '/'), 1);
	@eval("@\$rate =".$exp[1].";");
	return round($rate, 2)*100000000+$raters;
}

function below_GetHits($exp) {
	return $exp[0];
}

function below_GetRecency($exp) {
	if (!isset($exp[2]))
		return -(365*30*24*60*60+$exp[0]);
	else {
		$dates = sscanf($exp[2], "%d/%d/%d-%d:%d:%d");
		$udate = mktime($dates[3], $dates[4], $dates[5], $dates[1], $dates[2], $dates[0]);
		return $udate-GetTimeWithDiffer();
	}
}

function phormer_w_cmp($a, $b) {
	if ($a[1] == $b[1])
		return 0;
	else
		return (($a[1] > $b[1])?-1:1);
}

function write_belowIndex($func, $obj_name) {
	global $rps, $rpn, $trn, $trs, $rsn, $rss;
	global $photos, $nphotos, $basis, $stories, $comments;

	$isRecent  = (strcmp($obj_name, "Recently Visited") == 0);
	$isRandom  = (strcmp($obj_name, "Random") == 0);
	$isComment = (strcmp($obj_name, "Recently Commented") == 0);
?>
	<div class="partmain">
		<div class="titlepart">
			<a name="tr"></a>
			<span class="reddot">&#149;</span><?php echo (($isRecent||$isComment)?"":"Random ").$obj_name; ?> Photos
			<span class="thumbcntarr">
			<?php
				global $thumbCntArr;
				for($i=0; $i<count($thumbCntArr); $i++)
					echo "\t\t\t&nbsp;[<a href=\".?trn=".$thumbCntArr[$i].($trs == 0?"":"&trs=$trs")."#tr\">"
					.$thumbCntArr[$i]
					."</a>]\n"
			?>
			</span>
		</div>
		<div class="submenu">
<?php
			global $tphoto;
			$tphoto = array();

			$r = array();

			#srand(time());
			if ($isComment) {
				$threshhold = 200;

				end($comments);
				$rating = array();
				while (count($r) < $threshhold) {
					$value = current($comments);
					if (isset($value['owner']) && ($value['owner'][0] == 'p')) {
						$exp = substr($value['owner'], 1);
						$dates = sscanf($value['date'], "%d-%d-%d %d:%d");
						$udate = mktime($dates[3], $dates[4], 0, $dates[1], $dates[2], $dates[0]);
						$d = $udate-GetTimeWithDiffer();
						array_push($r, array(0=>$exp, $d));
					}
					if (prev($comments) === FALSE) break;
				}
			}
			else {
				$threshhold = 1000;
				$prob = ($nphotos<$threshhold)?100:round(100*($threshhold/$nphotos));

				reset($photos);
				$rating = array();
				while (list($key, $value) = each($photos))
					if (strcmp($key, 'lastpid') != 0) {
						if (rand(0, 100) < $prob) {
							$exp = explode(" ",$value);
							array_push($r, array(0=>$key, $func($exp)));
						}
					}
			}

			if ($isRandom)
				shuffle($r);
			else if (!$isComment)
				usort($r, "phormer_w_cmp");


			reset($r);
			$cnt = 0;
			$prob = ($isRecent||$isComment)?100:80;
			for ($i=0; ($cnt<$trn) && isset($r[$i+$trs]); $i++)
				if ((($nphotos-$i-$trs) < 1.5*($trn-$cnt)) || (rand(0, 100) < $prob)) {
					$a_info = "";
					if (($isRecent||$isComment) && ($r[$i+$trs][1] > -365*30*24*60*60))
							$a_info = SecsToText(-1*$r[$i+$trs][1]);
					if (thumbBox($r[$i+$trs][0], $a_info))
						$cnt++;
				}

?>
		</div>
		<div class="end">
<?php
		if ($trs != 0 && !$isRandom)
			echo "<span class=\"titlepartlinkL\">[ <a href=\".?trs=".max(0, $trs-$trn).
				($trn == $basis['deftrc']?"":"&trn=$trn")."#tr\">Previous $obj_name Photos</a> ]<br />&nbsp;</span>";
		if (isset($r[$trs+$trn])) {
			$nextWord = $isRandom?"Other":"Next";
			echo "<span class=\"titlepartlinkR\">[ <a href=\".?trs=".($trs+$trn).
				($trn == $basis['deftrc']?"":"&trn=$trn")."#tr\">$nextWord $obj_name Photos</a> ]<br />&nbsp;</span>";
		}
		unset($r);
?>
		</div>
	</div>

<?php
}

function write_firstPhoto() {
	global $photos, $nphotos, $basis, $stories, $_SERVER, $hasgd;
	end($photos);
	while (!canthumb(key($photos)))
		prev($photos);
	$pid = key($photos);
	$photo = getAllPhotoInfo($pid);
	$imgAddress = PHOTO_PATH.getImageFileName($pid, 5);
	$ppath = PHOTO_PATH.getImageFileName($pid, 9);
	if (!file_exists($imgAddress))
		if ($hasgd)
			makeTheThumb($ppath, 480, 'max', '5');
		else {
			$imgAddress = PHOTO_PATH.getImageFileName($pid, 4);
			if (!file_exists($imgAddress))
				if ($hasgd)
					makeTheThumb($ppath, 240, 'min', '4');
				else
					$imgAddress = PHOTO_PATH.getImageFileName($pid, 9);
		}
	$wh = "";
	$wdiv = "100%";
	if ($hasgd) {
		list($w, $h) = getimagesize($imgAddress);
		$wh = "width=\"${w}px\" height=\"${h}px\" ";
		$wdiv = "${w}px";
	}
?>
	<div class="partmain">
		<div class="titlepart">
			<span class="leaveReply">
				[ <a href=".?feat=slideshow">SlideShow</a> ]
			</span>
			<span class="reddot">&#149;</span>Last Photo:
			<a href=".?p=<?php echo $pid; ?>"><?php echo $photo['name']; ?></a>
		</div>
		<div class="photoTheImg">
			<br />
			<center>
			<div class="firstThumb" style="width: <?php echo $wdiv; ?>;
						 <?php if ($basis['opac'] != 100)
						 echo ((stristr($_SERVER['HTTP_USER_AGENT'], "IE") == true)?
						 "filter:alpha(opacity=".$basis['opac'].");":
						 "-moz-opacity:".($basis['opac']/100).";"); ?>"
						 onmouseover="javascript: LightenIt(this);" onmouseout="javascript: DarkenIt(this);">
				<a title="Click to view larger size of &quot;<?php echo $photo['name']; ?>&quot;" href=".?p=<?php echo $pid; ?>">
					<img src="<?php echo $imgAddress; ?>" <?php echo $wh; ?>/>
				</a>
			</div>
			</center>
		</div>
		<div class="end">
		</div>
	</div>
<?php
}

function write_lastPhotos() {
	global $rps, $rpn, $trn, $trs, $rsn, $rss;
	global $photos, $nphotos, $basis, $stories;
?>
	<div class="partmain">
		<div class="titlepart">
			<span class="leaveReply">
				[ <a href=".?feat=slideshow">SlideShow</a> ]
			</span>

			<span class="reddot">&#149;</span>Recently Added Photos
			<span class="thumbcntarr">
			<?php
				global $thumbCntArr;
				for($i=0; $i<count($thumbCntArr); $i++)
					echo "\t\t\t&nbsp;[<a href=\".?rpn=".$thumbCntArr[$i].($rps == 0?"":"&rps=$rps")."\">".$thumbCntArr[$i]."</a>]\n"
			?>
			</span>
		</div>
		<div class="submenu">
		<?php
			end($photos);
			$rpn = min($rpn, $nphotos);
			for ($i=0; $i<$rps; $i++)
				prev($photos);

			for ($i=0; ($i<$rpn) && (strcmp(key($photos), 'lastpid') != 0);) {
				if (thumbBox(key($photos)))
					$i++;
				prev($photos);
			}
		?>
		</div>
		<div class="end">
<?php
		if ($rps != 0)
			echo "<span class=\"titlepartlinkL\">[ <a href=\".?rps=".max(0, $rps-$rpn).($rpn == $basis['defrpc']?"":"&rpn=$rpn")."\">Previous Recent Photos</a> ]<br />&nbsp;</span>";
		if ($rps+$rpn<$nphotos)
			echo "<span class=\"titlepartlinkR\">[ <a href=\".?rps=".($rps+$rpn).($rpn == $basis['defrpc']?"":"&rpn=$rpn")."\">Next Recent Photos</a> ]<br />&nbsp;</span>";
?>
		</div>

<?php
}

function write_actions($cname, $clet, $cid) {
	global $$cname, $isAdmin;
?>
	<div class="part">
		<div class="titlepart"><span class="reddot">&#149;</span>Navigation</div>
		<div class="submenu">
			<div class="item"><span class="dot">&#149;</span>&nbsp;<a href=".">Home</a></div>
<?php
			if (strlen(${$cname}[$cid]['pass']) > 0 && !$isAdmin)
				echo "<div class=\"item\"><span class=\"dot\">&#149;</span>&nbsp;<a href=\".?$clet=$cid&cmd=logout\">Logout</a></div>";
?>
		</div>
	</div>
<?php
}

function checkThePass($conChar, $contId) {
	global $categs, $stories, $isAdmin, $_COOKIE;
	$thePass = ($conChar == 'c')?$categs[$contId]['pass']:$stories[$contId]['pass'];
	if ($isAdmin || (strlen($thePass) == 0))
		return true;
	if (isset($_COOKIE['pass_'.$conChar.$contId]) && (strcmp($_COOKIE['pass_'.$conChar.$contId], md5($thePass)) == 0))
		return true;
	return false;
}

function canthumb($pid) {
	global $stories, $categs, $photos, $isAdmin;
	if (!photo_exists($pid))
		return false;
	$photo = getAllPhotoInfo($pid);
	return checkThePass("s", $photo['story']) && checkThePass("c", $photo['categ']);
}

function textdate($d) {
	$dates = sscanf($d, "%d/%d/%d");
	$today = getdate();
	$utoday = mktime(0, 0, 0, $today["mon"], $today["mday"], $today["year"]);
	$udate = mktime(0, 0, 0, $dates[1], $dates[2], $dates[0]);
	$dayspast = round(($utoday-$udate)/(24*60*60));
	switch ($dayspast) {
		case 0: return 'Today';
		case 1: return 'Yesterday';
		case 2: case 3: case 4: case 5: case 6:
			return $dayspast." days ago";
		case 7 :
			return "one week ago";
		default:
			return date("F jS \o\f y", $udate);
	}
}

function numSuffSFromat($d, $s) {
	if ($d == 1)
		return "one $s ago"; // or "last $s"!
	else
		return "$d {$s}s ago";
}

function SecsToText($d) {
	if ($d < 60)
		$ret = numSuffSFromat(round($d), "second");
	else if ($d < 60*60)
		$ret = numSuffSFromat(round($d/60), "minute");
	else if ($d < 24*60*60)
		$ret = numSuffSFromat(round($d/(60*60)), "hour");
	else if ($d < 30*24*60*60)
		$ret = numSuffSFromat(round($d/(24*60*60)), "day");
	else
		$ret = numSuffSFromat(round($d/(30*24*60*90)), "month");

	if (strcmp($ret, "last day") == 0)
		$ret = "yesterday";

	return $ret;
}

function CleanTemp() {
	### Cleans the temporary files
	/* As version 3.30, it may contains draft items. */
	if ($handle = opendir('temp')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && !is_dir($file)) {
				$ext = substr($file, -3);
				if (strcmp($ext, "jpg") != 0) {
					@unlink("temp/".$file);
					continue;
				}

				// a photo found
				$basen = substr($file, 0, strlen($file)-6);
				$req_path = "temp/{$basen}_9.jpg";
				if (!file_exists($req_path)) {
					$basen = substr($file, 0, strlen($file)-4);
					$req_path = "temp/{$basen}_9.jpg";
					if (file_exists($req_path))
						@unlink("temp/".$file);
					else
						rename("temp/$file", $req_path);
				}
				if (!file_exists("temp/{$basen}_1.jpg") ||
					!file_exists("temp/{$basen}_3.jpg"))
					GenerateDraftRequireds($req_path);
			}
		}
		closedir($handle);
	}

	return ;
}

function GetBackUp() {
	$conts = array("categories", "stories", "photos", "basis", "comments");
	foreach ($conts as $theSrc)
		@copy('data/'.$theSrc.'.xml', 'data/'.$theSrc.'.xml'.'.bku');
	CleanTemp();
}

function GetTimeWithDiffer() {
	global $basis;

	if (!isset($basis['timediffer']))
		$basis['timediffer'] = 0;
	$dif = 0;
	@ eval('$dif = '.$basis['timediffer'].';');
	$d = time() + $dif*60;
	return $d;
}

/*************** EXIF Functions ********************/
function getDateTime3($s) {
	$t = array();
	$t2 = array();
	$t = explode(" ", $s);
	$sep =": /-";
	for ($i=0; $i<strlen($sep); $i++) {
		$t2 = explode($sep[$i], $t[0]);
		if (count($t2) == 3)
			break;
	}

	$s = "";
	if ((count($t2) == 3) && ($t2[0] > 0))
		$s = date("Y/m/d", mktime(0, 0, 0, $t2[1], $t2[2], $t2[0]));
	return $s;
}

function getEXIFDateTime($exif)	{
	$s = "";
	if (isset($exif['EXIF']['DateTimeOriginal']))
		$s = getDateTime3($exif['EXIF']['DateTimeOriginal']);
	if (strlen($s) == 0)
		if (isset($exif['EXIF']['DateTime']))
			$s = getDateTime3($exif['EXIF']['DateTime']);
	return $s;
}

function getval($s) {
	$t = 0;
	@eval("\$t = $s;");
	return $t;
}

function getEXIFData($exif) {
	$s = "";
	// Thanks to Siavosh Benabas (sbenabas[at]gmail[dot]com) for his great information
	if (isset($exif['IFD0']['Model']))
		$s .= "Camera: ".$exif['IFD0']['Model']."\n";
	if (isset($exif['EXIF']['FNumber']))
		$s .= "F Number: ".getval($exif['EXIF']['FNumber'])."\n";
	if (isset($exif['EXIF']['FocalLength']))
		$s .= "Focal Length: ".getval($exif['EXIF']['FocalLength'])."\n";
	if (isset($exif['EXIF']['ExposureTime'])) {
		$v = $exif['EXIF']['ExposureTime'];
		$t = getval($v);
		$t = ($t>=1)?$t:$v;
		$s .= "Exposure Time: ".$t."\n";
	}
/*** Removed as Siavosh Suggested
	if (isset($exif['EXIF']['ShutterSpeedValue']))
		$s .= "Shutter Speed Value: ".$exif['EXIF']['ShutterSpeedValue']." s\n";
	if (isset($exif['EXIF']['ApertureValue']))
		$s .= "Aperture Value: ".getval($exif['EXIF']['ApertureValue'])."\n";
*/
	if (isset($exif['EXIF']['Flash'])) {
		$flash = $exif['EXIF']['Flash'];
		if ($flash == "1")
			$flash = "Yes";
		$s .= "Flash: $flash\n";
	}
	return $s;
}

/*************** Drafts Functions ******************/
function order_draft_get_3($ppath) {
	$tpath = substr_replace($ppath, '1', -5, 1); // _9.jpg => _1.jpg
	list($imgW, $imgH) = getimagesize($tpath);
	$sklS = min($imgW, $imgH);
	$sklT = ($imgH-$sklS)/2;
	$sklL = ($imgW-$sklS)/2;
	gen_3_thumb($ppath, $sklS, $sklS, $sklT, $sklL, 75);
}

function GenerateDraftRequireds($ppath) {
	set_time_limit(30);
	$txt = makeTheThumb($ppath, SKL_PHOTO_W, 'min', '1');
	order_draft_get_3($ppath);
	return $txt;
}

function GenerateAddPhotoRequired($ppath, $gen3) {
	global $_POST, $hasgd;

	if (!$hasgd)
		return;

	set_time_limit(30);

	list($imgW, $imgH) = getimagesize($ppath);
	$zer = 0;
	if ($imgW > $imgH) 	// horizontal
		$zer = ($imgW > 640)*640;
	else 				// vertical
		$zer = ($imgW > 480)*480;

	if ($zer)
		makeTheThumb($ppath, $zer, 'width', '0');
	makeTheThumb($ppath, 240, 'width', 	'1');
	makeTheThumb($ppath, 120, 'max', 	'2');
	makeTheThumb($ppath, 240, 'min', 	'4');
	if ($gen3) {
		$tpath = substr_replace($ppath, '4', -5, 1); // _9.jpg => _1.jpg
		list($imgW, $imgH) = getimagesize($tpath);
		$sklS = min($imgW, $imgH);
		$sklW = isset($_POST['sklW'])?$_POST['sklW']:$sklS;
		$sklH = isset($_POST['sklH'])?$_POST['sklH']:$sklS;
		$sklT = isset($_POST['sklT'])?$_POST['sklT']:(($imgH-$sklS)/2);
		$sklL = isset($_POST['sklL'])?$_POST['sklL']:(($imgW-$sklS)/2);
		gen_3_thumb($ppath, $sklW, $sklH, $sklT, $sklL, 75);
	}
}

function WriteDraftLine($x, $n) {
	global $_POST;

	$y = rawurlencode($x);

	$bgcolor = ($n % 2)?"#C0D0E0":"#D0E0F0";
	$thumb = "temp/{$x}_3.jpg";
	$large = "temp/{$x}_9.jpg";
	$dot = "<span class=\"lightdot\">&#149;</span>";

	$checked = (isset($_POST["cmd"]) && isset($_POST["d_cb$x"]) && strcmp($_POST["d_cb$x"], "on") == 0)
				?' checked="checked"':'';

	echo "<tr style=\"background-color: $bgcolor;\">\n";
		echo "\t\t\t<td style=\"text-align: center; margin-left: 0px;\"><input id=\"d_cb$y\" name=\"d_cb$y\" class=\"checkbox\" type=\"checkbox\" "
							  ."$checked onclick=\"javascript:updateSelCount();\"></input> &nbsp; </td>\n";
		echo "<td style=\"text-align: center\">";
			echo "<input name=\"d_vv$y\" value=\"$x\" type=\"hidden\"></input>";
			echo "<a href=\"$large\" title=\"Click to view larger size of $thumb\">";
				if (!file_exists($thumb))
					echo "\t\t\t\t\t".THUMB_NOT_FOUND."\n";
				else
					echo "<img src=\"$thumb\" height=\"75px\" width=\"75px\" style=\"margin: 5px 4px;\" />";
			echo "</a><br />\n";
		echo "</td>\n";
		echo "<td style=\"padding: 0px 10px;\"><table>";
			echo "<tr><td><label for=\"d_tt$y\">Name:		</label></td><td><input id=\"d_tt$y\" name=\"d_tt$y\" type=\"text\" class=\"text\" "
								."size=\"30\" value=\"$x\"></input></td></tr>";
			echo "<tr><td valign=\"top\"><label for=\"d_td$y\">Description:</label></td><td><textarea id=\"d_td$y\" name=\"d_td$y\" class=\"textarea\" "
								."rows=\"2\" cols=\"30\"></textarea></td></tr>";
		echo "</table></td>\n";

		echo "<td style=\"padding-left: 10px;\">";
			echo "$dot<a href=\"?page=photos&cmd=doAdd&draft=$x\">Detailed Add</a><br />\n";
			echo "$dot<a href=\"?page=drafts&cmd=del&draft=$x\" onclick=\"return CheckDeleteThisDrafts('$x')\">Delete this</a>";
		echo "</td>\n";
	echo "</tr>\n";
}

function DeleteFromDrafts($ppath) {
	$ret= "";
	$extradumps = array(substr_replace($ppath, '9', -5, 1), // this!
						substr_replace($ppath, '1', -5, 1),
						substr_replace($ppath, '3', -5, 1),
						substr_replace($ppath, '.txt', -6),
						substr_replace($ppath, '.phr', -6));
	foreach ($extradumps as $thumpath)
		if (file_exists($thumpath)) {
			@unlink($thumpath);
			if (!file_exists($thumpath))
				$ret .= "File <tt>$thumpath</tt> Deleted, successfully.<br />\n";
			else
				$ret .="<span style=\"color:#900;\">Could not delete file <tt>$thumpath</tt>!</span><br />\n";
		}
	return $ret;
}

function extractZip($ppath) {
	global $zip_msg;

	$ret = array();

	$zip = FALSE;
	@$zip = zip_open($ppath);

	$zipdump = "zipdump";
	for ($n=""; file_exists("temp/{$zipdump}.zip"); $zipdump = "_".$zipdump)
		;

	/**
	 * following trick is needed in EasyPHP while zip_open gets only local absolute path!
	 */

	if (defined("ZIP_OPEN_PATH")) {
		copy ($ppath, "temp/{$zipdump}.zip");
		$zip = zip_open(ZIP_OPEN_PATH."{$zipdump}.zip");
	}

	if (!$zip) {
		if (file_exists("temp/{$zipdump}.zip"))
			unlink("temp/{$zipdump}.zip");
		$zip_msg .= "Could not open zip file in <tt>$ppath</tt>! Read <a href=\"".PHORMER_PATH."/faq/%23zipopen\">ZipOpen</a> in Phormer's FAQ";
	}
	else {
		while ($zip_entry = zip_read($zip)) {
			$file_name = zip_entry_name($zip_entry);
			$fname = basename($file_name);
			if (strcasecmp(substr($fname, -4), ".jpg") != 0)
				continue;
			$b = substr($fname, 0, -4)."_9.jpg";
			for ($n=""; file_exists("temp/".$b); $b = "_".$b) ;
			$fname = "temp/".$b;

			#echo "Name:               " . zip_entry_name($zip_entry) . "<br />\n";
			#echo "Actual Filesize:    " . zip_entry_filesize($zip_entry) . "<br />\n";
			#echo "Compressed Size:    " . zip_entry_compressedsize($zip_entry) . "<br />\n";
			#echo "Compression Method: " . zip_entry_compressionmethod($zip_entry) . "<br />\n";

			if (zip_entry_open($zip, $zip_entry, "r")) {
				$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				$handle = fopen($fname, "w");
				fwrite($handle, $buf);
				fclose($handle);
				array_push($ret, $fname);
				#echo "File Contents:\n";
				#$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				#echo "$buf\n";
				#zip_entry_close($zip_entry);
			}
			echo "<br />\n";

		}
		zip_close($zip);
	}

	if (file_exists("temp/{$zipdump}.zip"))
		unlink("temp/{$zipdump}.zip");

	return $ret;
}

?>
