<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" >
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="PRAGMA" content="no-cache"> 
<meta http-equiv="Expires" content="-1">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="icon" type="image/x-icon" href="/web/WDLogo_16x16.ico"></link>
<title></title>
</head>
<?php
$date = new DateTime();
$r= $date->getTimestamp();

$cmd = "xmldbc -g /system_mgr/time/time_format";
$time_format = trim(shell_exec($cmd));

if($time_format=="") $time_format="12";

$cmd = "xmldbc -g /language";
$language = trim(shell_exec($cmd));

$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
$timezone = $hrs*$sgn;
//echo $hrs*$sgn;

//echo "<script>var TIME_FORMAT=$time_format;var MULTI_LANGUAGE = $language;var TIMEZONE = $timezone;</script>";
echo "<script>var TIMEZONE = $timezone;</script>";
?>
<style>
.setting_div{
	height:650px;
	width:900px;
	border: 0px solid #1E1E1E;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
	float:left;
}

</style>


<!-- tree lib -->
<script type="text/javascript" src="/Dropbox/function/language.js?id=<?=$r; ?>"></script>
<script type="text/javascript" src="/Dropbox/function/dropbox.js?id=<?=$r; ?>"></script>
<link rel="stylesheet" type="text/css" href="/Dropbox/css/style.css?id=<?=$r; ?>">

<script type="text/javascript" src="/Dropbox/jquery/jqueryFileTree/js/jqueryFileTree.js"></script>
<!--<link href="/Dropbox/jquery/jqueryFileTree/css/jqueryFileTree.css" rel="stylesheet" type="text/css"/>-->

<script>
var dropbox_intervalId;
		function _open()
		{
		$( "#folder_selector" ).before( "<div id='spinner_div' style='display:inline-block;height:20px;'><img src='/web/images/SpinnerSun.gif' border='0' width='18' height='18' style='float:left'><div style='display:inline-block;height:20px;float:left;padding-left:5px;'>"+ _T_APP('_common','loading')+ "</div></div>" );
		var flag=0;
		dropbox_open_folder_selecter({
			title: _T('_backup', 'desc1'),
			device: "HDD",
			root: 'root',
			cmd: 'getTree',
			script: '/Dropbox/php/dropbox.php?account='+getCookie("username"),
			effect: 'no_son',
			formname: 'generic',
			textname: 'f_sync_path',
			filetype: 'all',
			checkbox_all: 0,
			showfile: 0,
			chkflag: 1, //for show check box, 1:show, 0:not
			callback: function(){
				$("#spinner_div").remove();
				
				$("#folder_selector input:checkbox").each(function(){
					
					var v = $(this).val();
					var s = $(this).attr("src");//1:in black_list 0:not to set black list 2: subfolder have to set
					var selFlag=0;
					
					
					if(($.inArray( v, _BlackList )==-1 && _chk_path(v)==0 )&& !$(this).hasClass("unsel"))
					{
						$(this).prop("checked",true);
						$(this).addClass("sel");
						selFlag=1;
					}
					
					if(selFlag==0)
					{
						$(this).addClass("bFolder");
					}
					
					/*
					var root = $(this).parent().parent().parent().parent();
					
					if($(root).hasClass('directory expanded'))
					{
						//console.log("[%s][%s]",root,$(root).hasClass('sel'))
						if($(root).next().find('input:checkbox').hasClass('sel'))
						{
							//console.log("has sel")
							$(this).prop("checked",true);
							$(this).removeClass("unsel").removeClass("sel").addClass("sel");
						}
						else
						{
							//console.log("xxxx sel")
							$(this).prop("checked",false);
							$(this).removeClass("unsel").removeClass("sel").addClass("unsel");	
						}
						//console.log("------------------------------");
					}*/
				});
				
				$("#folder_selector input:checkbox").unbind('click');
				$("#folder_selector input:checkbox").click(function(){
					
					if(!this.checked)
					{
						$(this).removeClass("sel").addClass("unsel");
					}
					
					if($(this).parent().parent().hasClass('directory'))	//click root folder
					{
						if(this.checked)
						{
							$(this).removeClass("sel").removeClass("unsel").removeClass("unsel2").addClass("sel");
							var count = $(this).parent().parent().find('UL input:checkbox').length;
							if(count!=0)
							{
								$(this).parent().parent().find('UL input:checkbox').each(function(){
									var _this = $(this);
									_this.prop("checked",true);
									_this.addClass("sel");
								});
							}
						}
						else
						{
							$(this).removeClass("sel").removeClass("unsel").removeClass("unsel2").addClass("unsel");
							var count = $(this).parent().parent().find('UL input:checkbox').length;
							if(count!=0)
							{
								$(this).parent().parent().find('UL input:checkbox').each(function(){
									var _this = $(this);
									_this.prop("checked",false);
									_this.addClass("unsel");
								});
							}
						}
					}
					
					var root = $(this).parent().parent().parent().parent();
					if($(this).parent().parent().parent().parent().hasClass('directory'))	//click sub folder
					{
						var count = $(this).parent().parent().parent().find('input:checkbox').length;
						var sel_count = $(this).parent().parent().parent().find('input:checkbox:checked').length;
						var unsel_count = count - sel_count;
						
						if(count == sel_count)
						{
							//root.find("input:checkbox").eq(0).prop("checked",true);
							//root.find("input:checkbox").eq(0).removeClass("unsel").removeClass("sel").removeClass("unsel2").addClass("sel");
						}
						else if(count==unsel_count)
						{
							//root.find("input:checkbox").eq(0).prop("checked",false);
							//root.find("input:checkbox").eq(0).removeClass("unsel").removeClass("sel").removeClass("unsel2").addClass("unsel");							
						}
						else
						{
							root.find("input:checkbox").eq(0).prop("checked",true);
							root.find("input:checkbox").eq(0).removeClass("unsel").removeClass("sel").removeClass("unsel2").addClass("sel");
							//root.find("input:checkbox").eq(0).prop("checked",false);
							//root.find("input:checkbox").eq(0).removeClass("unsel").removeClass("sel").removeClass("unsel2").addClass("unsel2");								
						}
					}
				});
								
				function _chk_path(path)
				{
					for(i in _BlackList)
					{
						var bfolder = _BlackList[i]+"/";
						//console.log("a=[%s] bfolder=[%s]",path.substring(0,bfolder.length),bfolder )
						if(path.substring(0,bfolder.length)==bfolder)
						{
							return 1;
						}
					}
					
					return 0;
				}					
			},
			afterOK: function() {
				var blackFolders = new Array();
				var removeBlackFolders = new Array();
				$("#folder_selector input:checkbox").each(function(){
					
					var v = $(this).val();
					if(!$(this).hasClass("bFolder") && $(this).hasClass("unsel"))
					{
						blackFolders.push(v);
					}
					else if($(this).hasClass("bFolder") && $(this).hasClass("sel"))
					{
						//should del black folder
						removeBlackFolders.push(v);
					}
					else if($(this).hasClass("bFolder") && !$(this).prop("checked"))
					{
						blackFolders.push(v);
					}
				});
				
				var b = blackFolders.reverse().toString().replace(/,/g,"*");
				var rb = removeBlackFolders.toString().replace(/,/g,"*");
				
				//console.log(b)
				//console.log(rb)
				
				//alert("addBlackList=" + b + "\n" + "delBlackList=" + rb)
				set_dropbox("setBlacklist",b,rb);
			},
			afterCancel: function() {
			}
		});		
		}
function chk_color(callback){
	
	wd_ajax({
		async:false,
		url: '/web/images/GraphicBumperSafepoints.png',
		success: function(result){
			if(callback)callback(1);
		},     
		error: function(result){
			if(callback)callback(0);
		}
	});
}
function loadCSS(url, callback)
{
	// adding the script tag to the head as suggested before
	var fileref=document.createElement("link");
	fileref.setAttribute("rel", "stylesheet");
	fileref.setAttribute("type", "text/css");
	fileref.setAttribute("href", url);
	if (typeof fileref!="undefined")
		$("#mainbody").append(fileref);
	//document.getElementsByTagName("head")[0].appendChild(fileref);
}
function page_load()
{
	chk_color(function(color){
		//color: 1:while 0:black
		if(color==0)
		{
			loadCSS("/Dropbox/css/black.css", null);
		}
	});
	dropbox_ready_language();
	//get_Name_Mapping_Hame("");
	show_interval_options();
	
	get_dropbox("getBlacklist");
	
	$("#f_black_list").unbind("click");
	$("#f_black_list").click(function(){
		get_dropbox("getBlacklist",function(){
			_open();
		});
	});
		
	$("#f_browse").unbind("click");
	$("#f_browse").click(function(){
		
		if($(this).hasClass('gray_out')) return;
		
		dropbox_open_folder_selecter({
			title: _T('_backup', 'desc1'),
			device: "HDD",
			root: '/mnt/HD',
			cmd: 'cgi_read_open_tree',
			script: '/cgi-bin/folder_tree.cgi',
			effect: 'no_son',
			formname: 'generic',
			textname: 'f_sync_path',
			filetype: 'all',
			checkbox_all: 2,
			showfile: 0,
			chkflag: 1, //for show check box, 1:show, 0:not
			chk:1,
			single_select:true,
			callback: null,
			afterOK: function() {
				
				var path = $("#folder_selector input:checkbox:checked").val();
				var v = $("#folder_selector input:checkbox:checked");
				if(v.length==0)
				{
					jAlert(_T('_user','msg2'), _T('_common','error'));
					return;
				}
				
				var new_path = translate_path_to_display(path);
				$("#f_sync_path").val(new_path);
				
				new_path = "/shares/" + new_path;
				
				set_dropbox('folder',new_path);
			},
			afterCancel: function() {
			}
		});
	});
	
	/*
    $("#dropbox_switch").click(function(){
		var v = getSwitch('#dropbox_switch');
		
		if(v==1)
			set_dropbox('enable');
		else
			set_dropbox('disable');
	});*/

	$("#sync_button").unbind("click");
    $("#sync_button").click(function(){
		if($(this).hasClass('gray_out')) return;
		set_dropbox($(this).val());	//val: syncnow,pause
		$("#dropbox_status_div").html(_T_APP('_dropbox','s1')); //Syncing
	});
	
	$("#box_signin_button").unbind("click");
    $("#box_signin_button").click(function(){
		if($(this).hasClass('gray_out')) return;
		
		if($(this).attr('rel')=='0')
		{
			jConfirm('M',_T_APP('_dropbox','msg1'),_T_APP('_dropbox','sing_out'),"share",function(r){
				if(r)
				{
			set_dropbox('getAuthorizeURL');
		}
		    });
		}
		else
		{
			//signIn
			$("#account").val(getCookie("username"));
			document.dropbox_form.submit();
		}
	});	

	show_dropbox_info(1);
	
	//clearInterval(dropbox_intervalId);
	dropbox_intervalId = setInterval("show_dropbox_status()",2000);	
}
function page_unload()
{
	clearInterval(dropbox_intervalId);
}
</script>
<body onload="page_load();">
	<div class="setting_div">
		<div class="h1_content header_2">
		    <span class="_text_app" lang="_dropbox" datafld="title2"></span>
			<form name="dropbox_form" id="dropbox_form" method="get" target="_blank" action="/Dropbox/php/dropbox.php" onSubmit="return false;">
				<input type="hidden" name="cmd" value="getAuthorizeURL">
				<input type="hidden" name="sign" id="sign" value="signIn">
				<input type="hidden" name="account" id="account" value="">
			</form>
			<div id="DIV_dropnas">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr style="display:none">
						<td class="tdfield">
							<span class="_text_app" lang="_dropbox" datafld="title"></span>
						</td>
						<td class="tdfield_padding dropbox_onoff" style="display:none">
							<input id="dropbox_switch" name="dropbox_switch" class="onoffswitch" type="checkbox" value="true">
						</td>
					</tr>
				</table>
				<table id="dropbox_setup_tb" border="0" cellpadding="0" cellspacing="0" style="display:none">
					<tr>
						<td class="tdfield">
							<span class="_text_app" lang="_dropbox" datafld="sync_status"></span>
						</td>
						<td class="tdfield_padding">
							<div id="dropbox_status_div" style="width:500px">-</div>
						</td>
					</tr>
					<tr id="button_tr" style="display:none">
						<td class="tdfield">
							<!--<span class="_text_app" lang="_dropbox" datafld="sync_now"></span>-->
						</td>
						<td class="tdfield_padding">
							<button type="button" id="sync_button"></button>
						</td>
					</tr>
					<tr>
						<td class="tdfield">
							<span class="_text_app" lang="_dropbox" datafld="used_space"></span>
						</td>
						<td class="tdfield_padding">
							<div id="space_used_div">-</div>
						</td>
					</tr>
					<tr>
						<td class="tdfield">
							<span class="_text_app" lang="_dropbox" datafld="last"></span>
						</td>
						<td class="tdfield_padding">
							<div id="last_update_div">-</div>
						</td>
					</tr>
				</table>
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td class="tdfield">
							<span class="_text_app" lang="_dropbox" datafld="sing_in_account"></span>
						</td>
						<td class="tdfield_padding">
							<span id="dropbox_account_info"></span>&nbsp;&nbsp;
							<button type="button" id="box_signin_button"><span class="_text_app" lang="_dropbox" datafld="sing_in"></span></button>
						</td>
					</tr>
				</table>
				<table id="dropbox_setup_tb2" border="0" cellpadding="0" cellspacing="0" style="display:none">
					<tr>
						<td class="tdfield">
							<span class="_text_app" lang="_dropbox" datafld="sync_folder"></span>
						</td>
						<td class="tdfield_padding">
							<input class="input_x5" type="text" name="f_sync_path" id="f_sync_path" readOnly > <button type="button" id="f_browse"><span class="_text_app" lang="_button" datafld="browse"></span></button> <button id="save_path_button" class="SaveButton" type="button"><span class="_text_app" lang="_button" datafld="save"></span></button>
						</td>
					</tr>
					<tr>
						<td class="tdfield">
							<span class="_text_app" lang="_dropbox" datafld="sync_interval"></span>
						</td>
						<td class="tdfield_padding">
							<div id="interval_options" class="select_menu"></div>
						</td>
					</tr>
					<tr>
						<td class="tdfield">
							<span class="_text_app" lang="_dropbox" datafld="black_list"></span>
						</td>
						<td class="tdfield_padding">
							<button type="button" id="f_black_list"><span class="_text_app" lang="_dropbox" datafld="add"></span></button>
							<a id="blacklist_conf" class="edit_detail" style="margin-left:10px;display:none;" href="javascript:open_black_diag();">
							    <span class="_text_app" lang="_dropbox" datafld="edit"></span>>>
							</a>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	
	
<!-- blacklist dialog -->
<div id="blacklistDiag" class="WDLabelDiag" style="display:none">
	<div class="WDLabelHeaderDialogue WDLabelHeaderDialogueFolderIcon" id="blacklistDiag_title"><span class="_text_app" lang="_dropbox" datafld="black_list"></span></div>
	<div align="center"><div class="hr"><hr/></div></div>
		
	<!-- blacklistDiag_set-->
	<div id="blacklistDiag_set" style="display:none">	
		<div class="WDLabelBodyDialogue">
			<div class="scroll-pane-group">
			<div id="blacklist_div"></div>
			</div>
		</div> <!-- body end -->
		<div class="hrBottom2"><hr/></div>
		<button type="button" class="ButtonMarginLeft_40px close" ><span class="_text_app" lang="_button" datafld="Cancel"></span></button>
		<button type="button" class="ButtonRightPos2" id="f_blacklist_del"><span class="_text_app" lang="_common" datafld="del"></span></button>
	</div> <!-- blacklistDiag_set end -->
</div> <!-- blacklistDiag end -->
		
</body>
</html>