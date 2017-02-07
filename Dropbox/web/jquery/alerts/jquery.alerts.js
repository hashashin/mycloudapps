
var _DIALOG="";
(function($) {
	var Overly_Obj="";
	var Loading_Overly="";
	$.alerts = {
		
		alert: function(message, title, size ,callback) {
			if( title == null ) title = 'Alert';
			$.alerts._show(title, message, null, 'alert', size, '',function(result) {
				if( callback ) callback(result);
			});
		},
		
		confirm: function(size,message, title, id,callback) {
			if( title == null ) title = 'Confirm';
			$.alerts._show(title, message, null, 'confirm', size, id,function(result) {
				if( callback ) callback(result);
			});
		},
			
		prompt: function(message, value, title, callback) {
			if( title == null ) title = 'Prompt';
			$.alerts._show(title, message, value, 'prompt','', '',function(result) {
				if( callback ) callback(result);
			});
		},
		
		info: function(module_name,message, title, callback) {
			if( title == null ) title = 'Alert';
			$.alerts._show(title, message, null, 'info', 'INFO','','',function(result) {
				if( callback ) callback(result);
			});
		},
		
		loading: function(message, title, size ,callback) {
			if( title == null ) title = 'loading';
			$.alerts._show(title, message, null, 'loading', size, '',function(result) {
				if( callback ) callback(result);
			});
		},				
		// Private methods
		_show: function(title, msg, value, type, size, id, callback) {

			$.alerts._hide();
			$.alerts._create(size,id, type,title);
			
			switch( type ) {
				case 'loading':
					Loading_Overly = $('.LightningUpdating').overlay({oneInstance:false,expose: '#333333',api:true,closeOnClick:false,closeOnEsc:false});		
					Loading_Overly.load();
					$(".LightningUpdating").css('z-index',10000);
					/*$(".LightningUpdating").css('top','170px');*/
					$(".LightningUpdating").next().css('z-index',9999);
					
					//$(".LightningUpdating").next().css('background-color','#000');

					break;
				case 'alert':
					Overly_Obj = $('.LightningStatusPanel').overlay({oneInstance:false,expose: '#333333',api:true,closeOnClick:false,closeOnEsc:false});		
					Overly_Obj.load();
					$("#popup_ok_button").focus();
					
					$(".LightningStatusPanel").css('z-index',10000);
					$(".LightningStatusPanel").next().css('z-index',9999);
					$(".LightningStatusPanel").next().css('background-color','#000');
					var msg_str = '<ul id="error_dialog_message_list"><li>' + msg + '</li></ul>';
					$("#error_dialog_message").html(msg_str);
					
					var icon_obj = $(".LightningStatusPanelIcon");
					icon_obj.removeClass('LightningStatusPanelIconComplete');
					icon_obj.removeClass('LightningStatusPanelIconCritical');
					icon_obj.removeClass('LightningStatusPanelIconInfo');
					icon_obj.removeClass('LightningStatusPanelIconWaiting');
					icon_obj.removeClass('LightningStatusPanelIconWarning');
					
					if(title==_T('_common','error')) title='warning';
					if(title==_T('_common','success')) title='complete';
					if(title==_T('_common','info')) title='info';
					var _TITLE="";
					switch(title)
					{
						case 'complete':
							_TITLE= _T('_common','completed');
							icon_obj.addClass('LightningStatusPanelIconComplete');
							break
						case 'critical':
							_TITLE= _T('_home','critical');
							icon_obj.addClass('LightningStatusPanelIconCritical');
							break;
						case 'info':
							_TITLE= _T('_common','info');
							icon_obj.addClass('LightningStatusPanelIconInfo');
							break;
						case 'waiting':
							_TITLE= _T('_backup','desc15');
							icon_obj.addClass('LightningStatusPanelIconWaiting');
							break;
						case 'warning':
							_TITLE= _T('_common','error');
							//icon_obj.addClass('LightningStatusPanelIconWarning');
							icon_obj.addClass('LightningStatusPanelIconCritical');
							break;
						case 'loading':
							_TITLE= _T('_common','loading');
							icon_obj.addClass('LightningStatusPanelIconWaiting');
							$('.LightningStatusPanelButton').hide();
							break;
						default:
							_TITLE= _T('_common','completed');
							icon_obj.addClass('LightningStatusPanelIconComplete');
							break;
					}
					
					icon_obj.html(_TITLE);
					
					
					$(".PanelButton button").click( function() {
						callback(true);
						$(".PanelButton button").unbind('click');
						Overly_Obj.close();
					});
					break;
				case 'confirm':
					
					var alertObj = $('.LightningStatusPanel').overlay({oneInstance:false,expose: '#333333',api:true,closeOnClick:false,closeOnEsc:false});		
															 
					alertObj.load();
					
					$("#popup_close_button").focus();
					
					$(".LightningStatusPanel").css('z-index',10000);
					$(".LightningStatusPanel").next().css('z-index',9999);
					$(".LightningStatusPanel").next().css('background-color','#000');
					
					var icon_obj = $(".LightningStatusPanelIcon");
					icon_obj.addClass('LightningStatusPanelIconComplete');
					
					switch(id)
					{
						case 'user':
							icon_obj.addClass('LightningStatusPanelIconUser');
							break;
						case 'group':
							icon_obj.addClass('LightningStatusPanelIconGroup');
							break;
						case 'remote':
							icon_obj.addClass('LightningStatusPanelIconRemote');
							break;
						case 'ssh':
						case 'ssh2':
							icon_obj.addClass('LightningStatusPanelIconSSH');
							break;
						case 'r_share':
						case 'share':
							icon_obj.addClass('LightningStatusPanelIconShare');
							break;
						case 'fw':
							icon_obj.addClass('LightningStatusPanelIconFW');
							break;	
						case 'iso':
							icon_obj.addClass('LightningStatusPanelIconISO');
							break;				
						case 'i':
							icon_obj.addClass('LightningStatusPanelIconI');
							break;		
						case 'format':
							icon_obj.addClass('LightningStatusPanelIconFormat');
							break;	
						case 'scandisk':
							icon_obj.addClass('LightningStatusPanelIconScanDisk');
							break;			
						case 'internalbackup':
						case 'FDownloads':
						case 'HDownloads':
						case 'p2p':
							icon_obj.addClass('LightningStatusPanelIconInfo');
						break;		
	                    case 's3':
							icon_obj.addClass('LightningStatusPanelIconInternalS3');
							break;	
						case 'apps':
							icon_obj.addClass('LightningStatusPanelIconInternalAPPS');
							break;					
						case 'cloud':
							icon_obj.addClass('LightningStatusPanelIconCloud');
							break;
						case 'cloud2':
							icon_obj.addClass('LightningStatusPanelIconCloud2');
							break;
						case 'ipv6':
						case 'warning':
							icon_obj.addClass('LightningStatusPanelIconWarning');
							break;
						default:
							icon_obj.addClass('LightningStatusPanelIconI');
						break;
					}
					
					icon_obj.html(title);
								
					//$(".LightningConfirmPanelBody").html(msg);
					var icon_obj = $(".LightningStatusPanelIcon");
					
					var msg_str = '<ul id="error_dialog_message_list"><li>' + msg + '</li></ul>';
					$("#error_dialog_message").html(msg_str);
					
					switch(id)
					{
						case 'ipv6':
							$("#popup_apply_button").text("Continue");
							$("#jConfirm_apply").css('left','10px');
							break;
						case 'r_share':
							setTimeout("$('#create_folder_tb input[name=f_new_folder]').focus()",100);
							init_select();
							break;
						case 'ssh':
							if(_SSH_PW_STATUS!=0)
							{
								switch(_SSH_PW_STATUS)
								{
									case -1:
										show_error_tip(".tip_pw_error",_T('_mail','msg11'));
										setTimeout("$('#ssh_pw_tb input[name=f_pw]').focus()",100);
										break;
									case -2:
										show_error_tip(".tip_pw_error",_T('_pwd','msg8'));
										setTimeout("$('#ssh_pw_tb input[name=f_pw]').focus()",100);
										break;
									case -3:
										show_error_tip(".tip_pw_error",_T('_pwd','msg9'));
										setTimeout("$('#ssh_pw_tb input[name=f_pw]').focus()",100);
										break;
									case -4:
										show_error_tip(".tip_pw_error",_T('_pwd','msg10'));
										setTimeout("$('#ssh_pw_tb input[name=f_pw]').focus()",100);
										break;
									case -5:
										show_error_tip(".tip_pw_error",_T('_pwd','msg11'));
										setTimeout("$('#ssh_pw_tb input[name=f_pw]').focus()",100);
										break;
									case -6:
										show_error_tip(".tip_pw2_error",_T('_pwd','msg7'));
										setTimeout("$('#ssh_pw_tb input[name=f_pw2]').focus()",100);
										break;
								}
								
								$("#ssh_accept").prop("checked", true);
								$("#ssh_pw_tb").show();
							}
							else
							{
								$("#popup_apply_button").addClass("gray_out");
							}
							$("input:checkbox").checkboxStyle();
							$("input:password").inputReset();
							break;
					}
					
					$("#jConfirm_close button").click( function() {
						alertObj.close();
						$("#jConfirm_close").unbind('click');
						if( callback ) callback(false);
					});
					$("#jConfirm_apply button").click( function() {
						$("#jConfirm_apply").unbind('click');
						if($(this).hasClass('gray_out')) return;
						alertObj.close();
						if( callback ) callback(true);
					});
					break;

				case 'prompt':
					var alertObj = $('.LightningStatusPanel').overlay({oneInstance:false,expose: '#333333',api:true,closeOnClick:false,closeOnEsc:false});		
					alertObj.load();

					$(".LightningStatusPanelIcon").addClass('LightningLabelHeaderFolderIcon').html(title);
					$(".LightningStatusPanel").css('z-index', 10000);
					$(".LightningStatusPanel").next().css('z-index', 9999);
					$(".LightningStatusPanel").next().css('background-color','#000');

					$("#dialog_title").html(msg);
					$("#new_folder_name").val("");
					$("#new_folder_name").focus();
					$("#new_folder_name").inputReset();

					$("#jConfirm_close").click( function() {
						alertObj.close();
						$("#jConfirm_close").unbind('click');
						if( callback ) callback(false);
					});

					$("#jConfirm_apply").click( function() {
						alertObj.close();
						$("#jConfirm_apply").unbind('click');
						if( callback ) callback(true);
					});
				break;
			}
		},
		_hide: function() {
			$(".LightningStatusPanel").remove();
			$(".LightningUpdating").remove();
		},
		_create: function(size,id, type,title) {

				var my_html = "";
				switch( type ) {
					case 'loading':
						my_html =  '<div class="LightningUpdating unselect">';
						if (title == "Retrieving")
							my_html += '<div class="updateStr">' + _T('_common','retrieving') + " ..." + "</div>";
						else if (title == "Wait")
							my_html += '<div class="updateStr">' + _T('_common','wait') + "</div>";	
						else
						my_html += '<div class="updateStr">' + _T('_common','updating') + " ..." + "</div>";
						my_html += '</div>';
						break;
					case 'alert':
						my_html = '<div class="LightningStatusPanel">';
						my_html += '<div class="LightningStatusPanelIcon"></div>';
						my_html += '<div align="center"><div class="Msghr"><hr/></div></div>';
						my_html += '<div class="LightningStatusPanelBody">';
						my_html += '<div id="error_dialog_message"></div>';
						
						my_html += '</div>';
						my_html += '<div class="MsghrBottom2"><hr/></div>';
						my_html += '<div class="PanelButton" id="popup_ok" style=" border:#0000FF 0px solid; ">'+ '<button id="popup_ok_button">' + _T('_button','Ok') +'</button>' +'</div>';
						my_html += '</div>';
					break;
					case 'confirm':
						my_html = '<div class="LightningStatusPanel">';
						my_html += '<div class="LightningStatusPanelIcon"></div>';
						my_html += '<div align="center"><div class="Msghr"><hr/></div></div>';
						my_html += '<div class="LightningStatusPanelBody">';
						my_html += '<div id="error_dialog_message"></div>';
						
						my_html += '</div>';
						my_html += '<div class="MsghrBottom2"><hr/></div>';
						my_html += '<div class="PanelButton2" id="jConfirm_close">'+ '<button id="popup_close_button" type=button>' + _T('_button','Cancel') +'</button>'+'</div>';
						my_html += '<div class="PanelButton" id="jConfirm_apply">'+ '<button id="popup_apply_button" type=button>' + _T('_button','Ok') +'</button>' + '</div>';
						my_html += '</div>';
					break;

					case 'prompt':
						my_html = '<div class="LightningStatusPanel">';
						my_html += '<div class="LightningStatusPanelIcon"></div>';
						my_html += '<div align="center"><div class="Msghr"><hr/></div></div>';
						my_html += '<div class="LightningStatusPanelBody">';
						my_html += '<div id="dialog_title"></div>';
						my_html += '<div><br><input type="text" id="new_folder_name" name="new_folder_name"></div>';
						
						my_html += '</div>';
						my_html += '<div class="MsghrBottom2"><hr/></div>';
						my_html += '<div class="PanelButton2" id="jConfirm_close">'+ '<button id="popup_close_button" type=button>' + _T('_button','Cancel') +'</button>'+'</div>';
						my_html += '<div class="PanelButton" id="jConfirm_apply">'+ '<button id="popup_apply_button" type=button>' + _T('_button','Ok') +'</button>' + '</div>';
						my_html += '</div>';
					break;
				}		
				
			
			$("body").append(my_html);
		}
	}
	
	// Shortuct functions
	jAlert = function(message, title, size ,callback) {
		$.alerts.alert(message, title, size ,callback);
	}
	
	jConfirm = function(size, message, title, id, callback) {
		
		//set default value, alpha.eve 2012/11/23 
		size = size || "";
		message = message || "";
		title = title || "";
   		
   		if( typeof(callback) == 'undefined') 
   		{
   			callback = id;
   			id="";
   		}	
   		
		$.alerts.confirm(size, message, title, id, callback);
	};
		
	jPrompt = function(message, value, title, callback) {
		$.alerts.prompt(message, value, title, callback);
	};

	jLoading = function(message, title, size ,callback) {
		$.alerts.loading(message, title, size ,callback);
	}
	
	jLoadingClose = function(callback) {
		Loading_Overly.close();
		if( callback ) callback(callback);
		//if(_DIALOG!="")
		//	_DIALOG.load()					
		}
})(jQuery);