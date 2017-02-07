<?php
require_once('funcs.php');

global $hasgd, $hasexif, $haszip;
function write_frame_header() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="files/adminfiles/admin.css">
	<script type="text/javascript" language="javascript" src="files/adminfiles/admin.js"></script>
</head>
<body class="frameBody" onload="javascript:try{document.getElementById('theAddress').focus();}catch(e){}">
<?php
}

function write_in_phr($txt) {
	global $seed;
	$f = fopen("temp/{$seed}.phr", "w");
	fwrite($f, $txt);
	fclose($f);
}

function dieAlert($msg) {
	write_frame_header();
	die('<div class="accessViol">'.$msg.'</div></body></html>');
}

function dieForward($msg, $file="") {
	global $isDraft;

	global $seed;
	write_in_phr("ERROR;");
	$str = "Location:upload.php?seed=$seed&msg=$msg";
	if ($isDraft)
		$str .= "&draft=yes";
	if (strlen($file))
		$str .= "&file=$file";
	header($str);
	die();
}

function AddDir($theFile) {
	global $isZip, $isDir, $zip_msg, $v, $isDraft, $isRemote, $bpath, $ppath, $wer;

	if (!($handle = opendir($theFile)))
		dieForward("ERROR: Could not open directory <tt>theFile</tt> for reading!");
	while (false !== ($file = readdir($handle)))
		if ($file != "." && $file != "..")
			if (is_dir($theFile.$file))
				AddDir($theFile.$file."/");
			else {
				$file_type = strtolower(substr($file, strlen($file)-3));
				if (!strcasecmp($file_type, "jpg") || !strcasecmp($file_type, "zip")) {
					$isZip = strcasecmp($file_type, "zip") == 0;
					AddSingleFile($theFile.$file);
				}
			}
}

function AddSingleFile($theFile) {
	global $isZip, $isDir, $zip_msg, $v, $isDraft, $isRemote, $bpath, $ppath, $wer;

	#echo $theFile;

	$ext = $isZip?"zip":"jpg";
	$file_name = basename($theFile);
	if ($isDraft) {
		$b = substr($file_name, 0, -4)."_9.".$ext;
		for ($n=""; file_exists("temp/".$b); $b = "_".$b)
			;
		$ppath = $bpath = "temp/".$b;
	}

	$wer = $theFile;
	if ($isRemote) {
		$fr = fopen($wer, "r");
		$fw = fopen($ppath, "w");
		while ($data = fread($fr, 4096))
			fwrite($fw, $data);
	}
	else
		copy($wer, $ppath);


	if ($isZip) {
		$zip_msg = "";
		$v = array_merge_recursive($v, extractZip($ppath));
		@unlink($ppath);
		if (strlen($zip_msg) && !$isDir)
			dieForward("ERROR: ".$zip_msg);
	}
	else
		array_push($v, $ppath);
}

	if (!auth_admin())
		dieAlert('ERROR: Access Denied<br />Only Admin Shall Access This Page.');

	if ((!isset($_GET['seed'])) || (strlen($_GET['seed']) != 5))
		dieAlert('ERROR: Has NOT got the proper seed');
	$seed = $_GET['seed'];

	$mod = isset($_GET['mod'])?$_GET['mod']:'frame';
	$isDraft = (isset($_GET['draft']) && (strcmp($_GET['draft'], "yes") == 0))?1:0;

	if (strcmp($mod, 'exif') == 0) {
		$ex = ";";
		if (isset($_GET['file']) && file_exists($_GET['file'])) {
			$ppath = $_GET['file'];
			if ($hasexif)
				$exif = exif_read_data($ppath, 0, true);
			if ($hasexif && ($exif !== FALSE))
				$ex = getEXIFDateTime($exif).";".getEXIFData($exif);
		}
		echo "<ajax>!$ex</ajax>";
		die();
	}

	if (strcmp($mod, 'listen') == 0) {
		$src = "temp/{$seed}.phr";
		$txt = file_exists($src)?file_get_contents($src):"EMPTY";
		die("<ajax>".$txt."</ajax>");
	}

	if (strcmp($mod, "delphr") == 0) {
		$src = "temp/{$seed}.phr";
		unlink($src);
		die("<ajax>DELED</ajax>");
	}

	if (strcmp($mod, 'getfile') == 0) {
		$ppath = "temp/{$seed}_9.jpg";

		if (!isset($_GET['uptype']))
			dieForward('ERROR: Has not got the Upload Type');
		$uptype = $_GET['uptype'];

		if (strcmp($uptype, "server") == 0) {
		    $isRemote = strcmp(substr($_POST['theAddress'], 0, 7), "http://") == 0;

			$theFile = $_POST['theAddress'];
			$file_name = basename($_POST['theAddress']);

			if (is_dir(substr($theFile, 0, -1)) && substr($theFile, -1) == "*")
				$theFile = substr($theFile, 0, -1);

			if (!$isRemote && !file_exists($theFile))
				dieForward("ERROR: The file \"$file_name\" does not exist!", $_POST['theAddress']);

			$file_type = strtolower(substr($file_name, strlen($file_name)-3));
			$isZip = false;
			$isDir = false;

			if (is_dir($theFile) && substr($theFile, -1) != "/")
				$theFile .= "/";

			if (substr($theFile, -1) == "/") {
				$isDir = true;
				if (!is_dir($theFile))
					dieForward("ERROR: $theFile is not a directory!");
				if (!$isDraft)
					dieForward("ERROR: Folders are only allowed to be uploaded in Drafts area!");
			}
			else if (strcasecmp($file_type, "jpg") != 0)
				if (strcasecmp($file_type, "zip"))
					dieForward("ERROR: The file \"$file_name\" is neither a Jpeg (.jpg) nor a Zip (.zip) file!", $_POST['theAddress']);
				else {
					if (!$isDraft)
						dieForward("ERROR: Zip files are only allowed to be uploaded in Drafts area!");
					$isZip = true;
					if (!$haszip)
						dieForward("ERROR: This server doesn't have \"zip\" module to extract .zip files!");
				}

			write_in_phr("START;");

			$v = array();
			$bpath = "";

			if ($isDir)
				AddDir($theFile);
			else
				AddSingleFile($theFile);

			if (count($v) == 0)
				dieForward("Error: No jpeg file found to be added!");

			$txt = "ENDED;;";
			if ($hasgd) {
				write_in_phr("THUMB;");
				foreach ($v as $ppath) {
					#echo $txt;
					write_in_phr("THUMB;".(++$n).";".count($v).";".substr($ppath, 5));
					$txt = GenerateDraftRequireds($ppath);
				}
			}
			else
				$txt = "ENDED;;";
			$r = $txt.";".($isDir?substr($theFile, 0, -1):$file_name);
			if ($isDraft)
				$r .= ";".$bpath;
			if ($isDraft && ($isZip || $isDir))
				$r .= ";".count($v);
			write_in_phr($r);
		}
		elseif (strcmp($uptype, "pc") == 0) {
			$file_name = $_FILES['theFile']['name'];
			$file_type = $_FILES['theFile']['type'];
			$file_size = $_FILES['theFile']['size'];
			$file_tmpN = $_FILES['theFile']['tmp_name'];
			if (!isset($file_size) || ($file_size == 0))
				dieForward("ERROR: Either no file or a <a href=\"".PHORMER_PATH."/faq/%23largefile\">large file</a> ( > 2MB) was selected.");
			$isZip = false;
			if (strpos($file_type, "jpeg") === FALSE) {
				if (strpos($file_type, "zip") === FALSE)
					dieForward("ERROR: The file \"$file_name\" is neither a Jpeg (.jpg) nor a Zip (.zip) file!");
				else {
					$isZip = true;
					if (!$isDraft)
						dieForward("ERROR: Zip files are only allowed to be uploaded in Drafts area!");
					if (!$haszip)
						dieForward("ERROR: This server doesn't have \"zip\" module to extract .zip files!");
				}
			}
			$ext = $isZip?"zip":"jpg";
			if ($isDraft||1) {
				$b = substr($file_name, 0, -4)."_9.".$ext;
				for ($n=""; file_exists("temp/".$b); $b = "_".$b)
					;
				$bpath = "temp/".$b;
				$ppath = $bpath;
			}

			$v = array();
			write_in_phr("START;");
			if (move_uploaded_file($file_tmpN, $ppath)) {
				if ($isZip) {
					$zip_msg = "";
					$v = extractZip($ppath);
					@unlink($ppath);
					if (strlen($zip_msg))
						dieForward("ERROR: ".$zip_msg);
					write_in_phr("UNZIP;".count($v));
				}
				else
					array_push($v, $ppath);
				$txt = "ENDED;;";
				if ($hasgd) {
					$n = 0;
					foreach ($v as $ppath) {
						write_in_phr("THUMB;".(++$n).";".count($v).";".substr($ppath, 5));
						$txt = GenerateDraftRequireds($ppath);
					}
				}
				else
					$txt = "ENDED;;";
				$r = $txt.";".$file_name;
				if ($isDraft||1)
					$r .= ";".$bpath;
				if (1|| ($isDraft && $isZip))
					$r .= ";".count($v);
				write_in_phr($r);
			}
		}
		else
			dieForward("ERROR: Not a valid upload type");

		dieAlert("The file \"$file_name\" <a href=\"$ppath\">uploaded</a> succesfully!");
	}
	if (strcmp($mod, 'frame') == 0) {
		write_frame_header();
?>
<?php
	global $transtable;
	if (isset($_GET['msg']))
		echo "<div class=\"accessViol\" style=\"margin: 0px 0px 5px; width: 100%;\"><center>"
			.$_GET['msg']
			."</center></div>";
	else
		echo "<div style=\"height: 8px; font-size: 8px\">&nbsp;</div>";
?>
		<table cellpadding="5" width="100%">
		<tr>
		<!-- onsubmit="parent.uploadSubmitted(<?php echo "'".$seed."', ".$hasgd; ?>,1);" -->
			<td valign="top" width="40%">
				<form name="FilePCForm" enctype="multipart/form-data" method="post"
						action="upload.php?mod=getfile&uptype=pc&seed=<?php echo $seed; if ($isDraft) echo "&draft=yes"; ?>">
				<b>Path on your machine</b><?php if ($isDraft) echo " (jpg/zip)"; ?>:
				</td><td valign="top">
				<input name="theFile" id="fileAddr" type="file" size="44" class="fileInput"
					onchange="FilePCForm.submit();parent.uploadSubmitted(<?php echo "'".$seed."', ".$hasgd.", ".$isDraft; ?>,1);"></input>
				<!-- <input class="submit" type="submit" value="Upload the file"></input> -->
				</form>
			</td>
		</tr>
		<tr>
			<td>
			<form name="FileServerForm" enctype="multipart/form-data" method="post"
				action="upload.php?mod=getfile&uptype=server&seed=<?php echo $seed; if ($isDraft) echo "&draft=yes"; ?>"
				onsubmit="parent.uploadSubmitted(<?php echo "'".$seed."', ".$hasgd.", ".$isDraft; ?>,0);">
				... or <b>Path on server</b><?php if ($isDraft) echo " (jpg/zip/folder)"; ?>:
				</td><td>
				<input class="textTT" id="theAddress" name="theAddress" type="text" size="31" class="fileInput"
					   value="<?php echo isset($_GET['file'])?$_GET['file']:""; ?>" onfocus="javascript:this.select();"></input>
				<!-- onblur="FileServerForm.submit();parent.uploadSubmitted(<?php echo "'".$seed."', ".$hasgd; ?>,0);" -->
				<input class="submit" type="submit" value="&nbsp;Add file<?php if ($isDraft) echo "(s)"; ?> &nbsp;"></input>
			</form>
			</td>
		</tr>
		</table>
	</body>
	</html>
<?php
	die(0);
	} // end of mod frame
	dieAlert('ERROR: No proper mod has been selected;');
?>