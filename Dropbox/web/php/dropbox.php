<?php

$date = new DateTime();
$r= $date->getTimestamp();

$cmd = $_REQUEST['cmd'];
$DropboxAPI = new DropboxAPI;

switch ($cmd) {
	case "delBlacklist":
		$DropboxAPI->delBlacklist();
		break;
	case "getBlacklist":
		$DropboxAPI->getBlacklist();
		break;
	case "setBlacklist":
		$DropboxAPI->setBlacklist();
		break;
	case "getTree":
		$DropboxAPI->getTree();
		break;
	case "enable":
		$DropboxAPI->enable();
		break;
	case "disable":
		$DropboxAPI->disable();
		break;
	case "stopAuth":
		$DropboxAPI->stopAuth();
		break;
	case "getInfo":
		$DropboxAPI->getInfo();
		break;
	case "getStatus":
		$DropboxAPI->getStatus();
		break;
	case "syncNow":
		$DropboxAPI->syncNow();
		break;
	case "pause":
		$DropboxAPI->pause();
		break;
	case "setSyncInterval":
		$DropboxAPI->setSyncInterval();
		break;
	case "setSyncFolder":
		$DropboxAPI->setSyncFolder();
		break;
	case "unlinkDev":
		$DropboxAPI->unlinkDev();
		break;
	case "getAuthorizeURL":
	
		$sign = $_GET['sign'];
		$account = $_GET['account'];
		
		if($sign=="signIn")
		{
			$callBack_URL = "http://". $_SERVER['HTTP_HOST']."/Dropbox/php/dropbox.php?cmd=callback&account=".$account;
			
			$authorizeURL = $DropboxAPI->getAuthorizeURL($callBack_URL);
			if($authorizeURL=="none")
			{
				echo "Connection Error.";
				return;
			}
			
			//echo "Location: $authorizeURL";
			header("Location: $authorizeURL");
			exit;
		}
		else
		{
			//signOut
			$DropboxAPI->unlinkDev();
			header("Status: 200");
		}
		
		break;
	case "callback":
		//http://2.66.65.33/web/php/dropbox.php?cmd=callback&uid=62174540&oauth_token=SFzEkRoxJgwOonFt
		
		$not_approved = false;
		if (!empty($_GET))
		{
			$not_approved = $_GET['not_approved'];
		}
		else
		{
			$not_approved = $_POST['not_approved'];
		}
		
		if ($not_approved != true)
		{
			//echo "enable\n";
			$DropboxAPI->enable();
		}
		else
		{
			//echo "stop\n";
			$DropboxAPI->disable();
		}
		
		echo "<script>window.close();</script>";
		
		break;
}



class DropboxAPI{
	public function getBlacklist()
	{
		$account = $_REQUEST['account'];
		$xmlPath = "/tmp/dBlack_$account.xml";
		
		@unlink($xmlPath);
		
		$cmd = "dropnasctl -j $account --black_list_get -x $xmlPath >/dev/null";
		system($cmd);
		
		if (file_exists($xmlPath))
		{
			print file_get_contents($xmlPath);
		}
		else
		{			
			print "<config><list></list></config>";
		}
	}

	public function setBlacklist()
	{
		$account = $_REQUEST['account'];
		$blackPath = urldecode($_REQUEST['blackPath']);
		
		$blackPath_array=explode("*",$blackPath);
		foreach($blackPath_array as $index => $value)
		{
			//echo "student $index is: $value\n";
			
			$cmd = "dropnasctl -j $account --black_list_add \"$value\" >/dev/null";
			system($cmd);
		}
		
		$delBlackPath = urldecode($_REQUEST['delBlackPath']);
		$delBlackPath_array=explode("*",$delBlackPath);
		foreach($delBlackPath_array as $index => $value)
		{
			//echo "student $index is: $value\n";
			
			$cmd = "dropnasctl -j $account --black_list_rm \"$value\" >/dev/null";
			system($cmd);
		}
		
		$cmd = "dropnasctl -j $account --black_list_commit >/dev/null";
		system($cmd);
		
		header("Status: 200");
	}
	
	public function delBlacklist()
	{
		$account = $_REQUEST['account'];
		$blackPath = urldecode($_REQUEST['blackPath']);
		
		$blackPath_array=explode("*",$blackPath);
		foreach($blackPath_array as $index => $value)
		{
			//echo "student $index is: $value\n";
			
			$cmd = "dropnasctl -j $account --black_list_rm $value >/dev/null";
			system($cmd);
		}
		
		$cmd = "dropnasctl -j $account --black_list_commit >/dev/null";
		system($cmd);
		
		header("Status: 200");
	}
	
	public function getTree(){
		$dir = urldecode($_REQUEST['dir']);

		if($dir=="root")
		{
			$dir = "/";
		}
		
		$account = $_REQUEST['account'];
		
		$XMLFile = "/tmp/dTree_$account.xml";
		
		@unlink($xmlPath);

		$cmd = "dropnasctl -j $account --dump_folder_tree $dir -x $XMLFile >/dev/null";
		system($cmd);
		//print $cmd;
		//return;
		if (file_exists($XMLFile))
		{
			$xml=simplexml_load_file($XMLFile);
			
			foreach($xml->tree->folder as $child)
			{
				$file = $child->name;
				$in_black_list = $child->in_black_list; //1:in black_list 0:not to set black list 2: subfolder have to set
				$path = $child->path;
				
				$checked="";
				$class="";
				//if($in_black_list=="2")
				//{
				//	$class="unsel2";
				//}
				//if($in_black_list=="1")
				//{
				//	$checked="checked";
				//}
				$chkbox = "<input type='checkbox' name='folder_name' value=\"".htmlentities($path)."\" class=\"$class\" rel=\"$path\" " . $checked ." src=\"$in_black_list\">";
				print "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
				print "<li class=\"directory collapsed\">".$chkbox."<a href=\"#\" rel=\"" . htmlentities($path) . "/\">" . htmlentities($file) . "</a></li>";
				print "</ul>";
			}
		}
	}
	public function getInfo(){
		
		$account = $_GET['account'];
		$xmlPath = "/var/www/xml/dropbox_$account.xml";
		
		@unlink($xmlPath);
		
		$cmd = "dropnasctl --status -j $account -x $xmlPath >/dev/null";
		system($cmd);
		
		header('Content-type: text/xml');
		if (file_exists($xmlPath))
		{
			if ( '' == file_get_contents( $xmlPath ) )
			{
			    // file is empty
			    print "<config><is_auth_valid>0</is_auth_valid></config>";
			} 
			else
			{
			print file_get_contents($xmlPath);
		}
		}
		else
		{			
			print "<config><is_auth_valid>0</is_auth_valid></config>";
		}
	}
	
	public function syncNow(){
		$account = $_GET['account'];
		$cmd = "dropnasctl --syncnow -j \"$account\" >/dev/null";
		system($cmd);
		
		header("Status: 200");
	}
	
	public function pause(){
		$account = $_GET['account'];
		$cmd = "dropnasctl --pause -j \"$account\" >/dev/null";
		system($cmd);
		
		header("Status: 200");
	}
		
	public function setSyncInterval($interval){
		$interval = $_GET['interval'];
		$account = $_GET['account'];
		$cmd = "dropnasctl --setdownperiod $interval -j \"$account\" >/dev/null";
		system($cmd);
		
		header("Status: 200");
	}
	public function setSyncFolder($folder){
		$folder = escapeshellarg($_GET['folder']);
		$account = $_GET['account'];
		$cmd = "dropnasctl --setsyncfolder $folder -j \"$account\" >/dev/null";
		system($cmd);
		
		header("Status: 200");
	}
	public function unlinkDev(){
		$account = $_GET['account'];
		$cmd = "dropnasctl --unlink -j \"$account\" >/dev/null";
		system($cmd);
	}
	public function enable(){
		$account = $_COOKIE['username'];
		$cmd = "dropnasctl --getaccesstoken -j \"$account\"> /dev/null";
		system($cmd);
		$cmd = "dropnasctl --enable -j \"$account\" > /dev/null";
		system($cmd);
		
		/*
		$cmd = "xmldbc -S /var/run/xmldb_sock_dropnas_conf -g /local_folder";
		$local_folder = shell_exec($cmd);
		
		if($local_folder=="/mnt/HD/HD_a2/dropbox" || $local_folder=="/shares/dropbox")
		{
			$cmd = "smbif -c dropbox";
			$ret = shell_exec($cmd);
			if($ret=="1")	//not exist
			{
				shell_exec("smbif -a /mnt/HD/HD_a2/dropbox");
			}
		}*/
		//header("Status: 200");	
	}
	public function disable(){
		$account = $_GET['account'];
		$cmd = "dropnasctl --disable -j \"$account\" > /dev/null";
		system($cmd);
		
		//header("Status: 200");
	}	
	public function stopAuth(){
		system("dropnasctl --stopauth > /dev/null");
		
		echo "<script>opener.show_dropbox_status();window.close();</script>";
	}
	
	public function getAuthorizeURL( $callBack_URL){
		
		$account = $_GET['account'];
		//$filePath = "/tmp/authUrl_".$account;
		//unlink($filePath);
		
		$cmd = "dropnasctl --getaccessurl '$callBack_URL' -j $account | sed 's/authorize url: //g' | sed '/^$/d'";
		exec($cmd,$ret);
		
		$url = $ret[0];
		
		if( strlen($url)==0 || !strstr($url, "https") ) 
		{
			$url="none";
		}
		return $url;
		
		/*
		$fp = fopen($filePath, "r");
		if($fp)
		{
		    while (($buffer = fgets($fp, 4096)) !== false) {
		    	
		    	if(strstr($buffer, "authorize url: "))
		    	{
		    		sscanf($buffer, "%s %s %s", $d1, $d2, $url);
		    		fclose($fp);
		    		return $url;
		    	}
		    }
		    if (!feof($fp)) {
		        //echo "Error: unexpected fgets() fail\n";
		        return 'none';
		    }
		    fclose($fp);
		}
		else
		{
			return 'none';
		}
		*/
	}
}
?>

<!--
<script type="text/javascript" src="/web/jquery/js/jquery-1.9.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="/web/css/style.css?id=<?=$r; ?>">
<script language="javascript" src="/web/function/language.js?id=<?=$r; ?>"></script>
<script type="text/javascript" src="/web/function/wd_ajax.js"></script>

<body onload="ready_language();">
<div class="b2">
	<div class="wd_logo">
		<div class="wd_dev"></div>
	</div>
</div>
<div align="center" style="padding-top:100px;">
	<table border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td>
				<img border='0' src='/web/images/SpinnerSun.gif'>
			</td>
			<td style="padding-left:15px;"><span class="_text" lang="_ddns" datafld="connect"></span></td>
		</tr>
	</table>
</div>

</body>-->