// jQuery File Tree Plugin
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// Visit http://abeautifulsite.net/notebook.php?article=58 for more information
//
// Usage: $('.fileTreeDemo').fileTree( options, callback )
//
// Options:  root           - root folder to display; default = /
//           script         - location of the serverside AJAX file to use; default = jqueryFileTree.php
//           folderEvent    - event to trigger expand/collapse; default = click
//           expandSpeed    - default = 500 (ms); use -1 for no animation
//           collapseSpeed  - default = 500 (ms); use -1 for no animation
//           expandEasing   - easing function to use on expand (optional)
//           collapseEasing - easing function to use on collapse (optional)
//           multiFolder    - whether or not to limit the browser to one subfolder at a time
//           loadMessage    - Message to display while initial tree loads (can be HTML)
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// TERMS OF USE
// 
// jQuery File Tree is licensed under a Creative Commons License and is copyrighted (C)2008 by Cory S.N. LaViska.
// For details, visit http://creativecommons.org/licenses/by/3.0/us/
//
if(jQuery) (function($){
	
	$.extend($.fn, {
		dropbox_fileTree: function(o, callback) {
			// Defaults
			if( !o ) var o = {};
			if( o.root == undefined ) o.root = '/';
		//	if( o.script == undefined ) o.script = '/cgi-bin/account_mgr.cgi';
		//	if( o.script == undefined ) o.script = 'jqueryFileTree.php';
			if( o.folderEvent == undefined ) o.folderEvent = 'click';
			if( o.expandSpeed == undefined ) o.expandSpeed= 10;
			if( o.collapseSpeed == undefined ) o.collapseSpeed= 10;
			if( o.expandEasing == undefined ) o.expandEasing = null;
			if( o.collapseEasing == undefined ) o.collapseEasing = null;
			if( o.multiFolder == undefined ) o.multiFolder = true;
			if( o.loadMessage == undefined ) o.loadMessage = 'Loading...';
			if( o.formname == undefined ) o.formname = null;
			if( o.textname == undefined ) o.textname = null;
			if( o.chkflag == undefined ) o.chkflag = null;
			if( o.function_id==undefined) o.function_id=null;
			//alpha.amy++
			if( o.effect == undefined ) o.effect = null;
			//alpha.amy--
			//alpha.eve++
			if( o.filetype == undefined ) o.filetype = 'all';
			if( o.checkbox_all == undefined ) o.checkbox_all = '0';	// 0 -> only folder, 1-> only file, 2-> file and folder
			
			if( o.chk == undefined) o.chk = __chkflag;			
			if (o.filter == undefined) o.filter = 0;
			if (o.root_path == undefined) o.root_path = "";
			if (o.w == "undefined") o.w = "";
			//fish+ for scroll bar
			if (o.share == undefined) o.share = "";//amy++ for show share path
			
			/* [+] multi select, max select, error message, Add by Ben */
			if (o.multi_select == undefined) o.multi_select = false; //for checkbox
			if (o.max_select == undefined) o.max_select = -1; //for checkbox, -1: unlimit
			if (o.over_select_msg == undefined) o.over_select_msg = "";
			if (o.single_select == undefined) o.single_select = false; //for checkbox
			/* [-] multi select, max select, error message, Add by Ben */
			o.ID = $(this).attr('id');
			o.scrollID="";
			switch(o.ID)
			{
				case 'img_tree_div':
					o.scrollID = '.isoCreate_scroll_pane';
					//$('.isoCreate_scroll_pane').jScrollPane({autoReinitialise: true});
					break;
				case 'img_tree_div2':
					o.scrollID = '.isoCreate_scroll_pane_right';
					//$('.isoCreate_scroll_pane_right').jScrollPane({autoReinitialise: true});
					break;
				default:
					o.scrollID = '.scroll-pane';
			//$('.scroll-pane').jScrollPane({autoReinitialise: true});
					if(!$(this).parent().hasClass('jspPane'))
					{
			$(this).wrap('<div class="scroll-pane">');
					}
					break;
			}

			o.new_folder = "";//ALPHA_CUSTOMIZE, Add by Ben
	
			//alpha.eve--
			$(this).each( function() {
				function showTree(c, t) {
					__chkflag = o.chk;
					
					//fish+					
					if(__chkflag==0 && o.root != "/")
					{
						if (!$(c).hasClass("add"))			
							chg_path(t,o.formname,o.textname);
					}
						
					//fish+ for photo center
					/*if(t!="/mnt/HD" && o.function_id=="photo")
					{
						var p = t;
						if(t=="/mnt/HD/")
							p = o.filter;
						get_imges(unescape(p));	//in dialog.js
					}*/
					
					$(c).addClass('wait');
					$(".jqueryFileTree.start").remove();
				
				//	$.post(o.script, { dir: t, cmd:'cgi_open_tree', show_file: __file }, function(data) {
					var string = t;
					var new_str = string.substring(string.length-4,string.length);

					/*
						open new folder (by amy++)
					*/
					//if (new_str == "new/")
					if ( $(c).hasClass("directory") && $(c).hasClass("collapsed") && $(c).hasClass("add"))
					{
						//Text:Please input a new folder name
						jPrompt(_T('_network_access', 'input_folder_name'), "", _T('_network_access', 'create_folder_name'), function(r) {
							if (r)
							{
								var ret = $("#new_folder_name").val(); /* ALPHA_CUSTOMIZE, Add by Ben, auto focus and scroll to new folder */

								if (ret.replace(/^\s+|\s+$/g,"") == ""){
									jAlert(_T('_network_access', 'msg7'), "warning"); //Text:folder name cann't be empty.
									$(c).removeClass('wait');
									return;
								}

								//fish+ for safari ,multi-line issue
								var s = "";
								for(var i = 0, j = ret.length; i < j; i++)
								{
									if(ret.charCodeAt(i) != 10)
										s += ret.charAt(i);
								}
								ret = s;

								var flag=Chk_Folder_Name(ret);
								if(flag==1)	//function.js
								{
									jAlert( _T('_network_access','msg3'), "warning");	//Text:The folder name must not include the following characters: \\ / : * ? " < > | 
									$(c).removeClass('wait');
									return;
								}
								else if(flag==2)
								{
									jAlert( _T('_network_access','msg4'), "warning");	//Text:Not a valid folder name.
									$(c).removeClass('wait');
									return;	
								}
								
								if(ret.length > 226)
								{
									jAlert( _T('_network_access','msg5'), "warning");	//Text:Folder name length should be 1-226.
									$(c).removeClass('wait');
									return;
								}
		
								if (ret.replace(/^\s+|\s+$/g,"") == "")
								{
									jAlert( _T('_network_access','msg7'), "warning");	//Text:Folder name cann't be empty.
									$(c).removeClass('wait');
									return;
								}

								if(o.textname!=null)
								{
									if ( o.formname == "generic")
									{
										document.getElementById(o.textname).value="";
									}	
									else
									{
										document.getElementById(o.textname).value="";
									}
								}

								o.new_folder = ret; /* [+] ALPHA_CUSTOMIZE, Add by Ben, auto focus and scroll to new folder */

								//var new_f = string.substring(0,string.length-4) +ret;
								var new_f = unescape(string.substring(0,string.length-4));
								var filename = unescape(ret);
									$.post(o.script, {
										dir: unescape(new_f),
										filename:filename,
										cmd: 'cgi_open_new_folder',
										show_file: __file,
										chk_flag: __chkflag,
										function_id:o.function_id,
										filter_file:o.filter,
										root_path:o.root_path
										},
										function(data) {
											var mkdir_status=$(data).find('mkdir > status').text();
											if(mkdir_status=="error")
											{
												jAlert( _T('_network_access','msg8'), "warning");	//Text:Cannot rename New Folder:A file with the name you specified already exists.Specify a different file name.
											}
											$(c).find('.start').html('');
											$(c).removeClass('wait').append("");
											if( o.root == t ) $(c).find('UL:hidden').show(); else $(c).find('UL:hidden').slideDown({ duration: o.expandSpeed, easing: o.expandEasing });
											//bindTree(c);  //amymark
		
											$(c).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
											showTree( $(c).parent().parent(), string.substring(0,string.length-4) );
											$(c).parent().parent().removeClass('collapsed').addClass('expanded');
											check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/
										});
							}

							$(c).removeClass('wait');
						});
						
						return;
					}

					$.post(o.script, { dir: unescape(t), cmd: o.cmd, show_file: __file ,chk_flag:__chkflag,file_type:o.filetype,function_id:o.function_id,filter_file:o.filter,root_path:o.root_path}, function(data) {
						//alert(data);
						var new_folder = "";
						var new_tree='<ul class="jqueryFileTree" style="display: none;">\n';
						$(data).find('LI').each(function(i){
							if( $(this).hasClass('directory') )
							{
								//alert($(this).html() )
								var new_text =""

								if (o.checkbox_all == 2)//show checkbox,support file and folder to selected
								{
									var v = $(this).find('input').attr("value");
									var s = $(this).find('input').attr("src");
									var c = $(this).find('input').attr("class");
									var t = $(this).text();
									if(c == undefined) c="";
									if (v != undefined)
									{
										new_text+= '<input name="folder_name" value="' + v + '" class="' + c + '" rel="' + t + '" type="checkbox" src="' + s + '">';
										if (o.new_folder == t) /* [+] ALPHA_CUSTOMIZE, Add by Ben, auto focus and scroll to new folder */
											new_text += '<a href="#" rel="' + v + '/" id="get_focus">' + t +'</a>';
										else
											new_text += '<a href="#" rel="' + v + '/">' + t +'</a>';
									}
									else
									{
										v = $(this).find('a').attr("rel");
										if (o.new_folder == t) /* [+] ALPHA_CUSTOMIZE, Add by Ben, auto focus and scroll to new folder */
											new_text += '<a href="#" rel="' + v + '" id="get_focus">' + t +'</a>';
										else
											new_text += '<a href="#" rel="' + v + '">' + t +'</a>';
									}

								}
								else if (o.checkbox_all == 3)//don't show checkbox,support file or folder to selected
								{
									var v = $(this).find('a').attr("rel");
									var t = $(this).text();
									if (o.new_folder == t) /* [+] ALPHA_CUSTOMIZE, Add by Ben, auto focus and scroll to new folder */
										new_text += '<a href="#" rel="' + v + '" id="get_focus">' + t +'</a>';
									else
										new_text += '<a href="#" rel="' + v + '">' + t +'</a>';
								}
								else 
								{
									if((__chkflag==0 && __file==0) || (__chkflag==1 && __file==1))
									{
										var v = $(this).find('a').attr("rel");
										var t = $(this).text();
										new_text += '<a href="#" rel="' + v + '">' + t +'</a>';
										//alert('<a href="#" rel="' + v + '">' + t +'</a>');
									}
									else if(__chkflag==1)
									{
										var v = $(this).find('input').attr("value");
										var s = $(this).find('input').attr("src");
										var c = $(this).find('input').attr("class");
										var t = $(this).text();
										if(c == undefined) c="";
										new_text+= '<input name="folder_name" value="' + v + '" class="' + c + '" rel="' + t + '" type="checkbox" src="' + s + '">';

										if (o.new_folder == t) /* [+] ALPHA_CUSTOMIZE, Add by Ben, auto focus and scroll to new folder */
											new_text += '<a href="#" rel="' + v + '" id="get_focus">' + t +'</a>';
										else
											new_text += '<a href="#" rel="' + v + '/">' + t +'</a>';
									}
								}
								//alert("new_text=\n" + new_text+"\nnew_text=" + $(this).html())
								//var a=$(this).clone()
								//alert("a=\n" + a.html())
								//alert($(this).html() )
								new_folder="";
								if($(this).hasClass('add'))
								{
									//alert("add=" + $(this).html())
									//new_folder=$(this).html()
									var v = $(this).find('a').attr("rel")
									new_folder = '<a href="#" rel="' + v + '">' +_T('_common','new')+'</a>'
									return true;
								}
								new_tree += '<li class="directory collapsed">'+ new_text+'</li>\n'
							}
						});
						$(data).find('LI').each(function(i){
							if( $(this).hasClass('file') )
							{
								//alert($(this).html())
								new_tree += '<li class="file">'+ $(this).html()+'</li>\n'
							}
						});

						if(new_folder!="")
						new_tree += '<li class="directory collapsed add">'+ new_folder +'</li>\n'
						new_tree +='</ul>'
						//alert(new_tree)
						//$(c).removeClass('wait').append(data);
						$(c).find('.start').html('');
						$(c).find('UL').remove(); // cleanup		amy++

						$(c).removeClass('wait').append(new_tree);
						if( o.root == t ) $(c).find('UL:hidden').show(); else $(c).find('UL:hidden').slideDown({ duration: o.expandSpeed, easing: o.expandEasing });													
						//alert(c.html())

						bindTree(c);

						//fish +
						if(__chkflag==0)
						{
							if ( o.function_id == "LBackups_URL") 
							{	
								(LBACKUP_MODIFY_INFO[15]=="" || (typeof LBACKUP_MODIFY_INFO[15] == "undefined"))?LBACKUP_CREATE_INFO[5]="1":LBACKUP_MODIFY_INFO[5]="1";
							}
							chg_path(t,o.formname,o.textname);
						}

						/* [+] multi select, max select, error message, Add by Ben */
						$("input[name=folder_name]").unbind('click');
						$("input[name=folder_name]").click(function(){
							var sel_source_ele = $("input:checkbox:checked[name=folder_name]");

							if (o.multi_select && o.max_select != -1)
							{
								if (sel_source_ele.length > o.max_select)
								{
									if (o.over_select_msg != "")
										jAlert(o.over_select_msg, "warning");

									$(this).prop("checked", false);
								}
							}

							if (!o.multi_select && o.single_select)
							{
								sel_source_ele.prop("checked", false);
								$(this).prop("checked", true);
							}
						});
						/* [-] multi select, max select, error message, Add by Ben */

						//amy+
						$("#tree_container input[name=folder_name]").unbind('click');
						$("#tree_container input[name=folder_name]").click(function(){
							if(this.checked == true)
							{
								var status = tree_check_share(this.value);
								if (status == 0) this.checked = false;
							}	
						});

						$("#container_id input[name=folder_name]").unbind('click');
						$("#container_id input[name=folder_name]").click(function(){
							if(this.checked == true)
							{	
								//var status = cifs_tree_check_share(this.value);
								//if (status == 0) this.checked = false;

								if (o.effect == "no_son")
								{
									var status = cifs_tree_check_upnp(this.value);
									if (status == 0) this.checked = false;
								}
							}	
						});
						
						$("#iso_tree_container input[name=folder_name]").unbind('click');
						$("#iso_tree_container input[name=folder_name]").click(function(){
						
					 		$("#iso_tree_container input:checkbox").attr("checked",false);
					 		this.checked = true;
							if(this.checked == true)
							{	
								var status = iso_tree_check_share(this.value);
								if (status == 0) this.checked = false;
							}	
						});	

						/* fish mark
						if(o.chkflag!=0)
						{
							$("#tree_div input[name=folder_name]").click(function(){
								if(this.checked == true)
								{								
									var ftp_status = ftp_tree_check_share(this.value);
									if (ftp_status == 1) this.checked = false;
								}	
							});
						}
						*/
						
						$("#Backups_tree_div .file a").click(function(){
							
							//var my_path = $(this).attr('rel');
							//mainFrame.document.getElementById(o.textname).value=unescape(my_path);
							
							var hdd_num=HDD_INFO_ARRAY.length;
							var t = $(this).attr('rel');
							for(i=0;i<hdd_num;i++)
							{
								var hdd_info=HDD_INFO_ARRAY[i].split(":");
								
								if(t.indexOf(hdd_info[1])!=-1)
								{
									var str=t;
									str=str.split(hdd_info[1]);
									var new_path=hdd_info[0] + str[1];
									//new_path=new_path.substr(0,new_path.length-1);	//remove end of '/'
								}
							}
							if(t!="/mnt/HD")
							{
								SEL_PATH=unescape(new_path);
								document.getElementById(o.textname).value=unescape(new_path);
							}
						});
						
						$(".show_file .file a").click(function(){
							
							//var my_path = $(this).attr('rel');
							//mainFrame.document.getElementById(o.textname).value=unescape(my_path);

							var hdd_num=HDD_INFO_ARRAY.length;
							var t = $(this).attr('rel');
							for(i=0;i<hdd_num;i++)
							{
								var hdd_info=HDD_INFO_ARRAY[i].split(":");
								
								if(t.indexOf(hdd_info[1])!=-1)
								{
									var str=t;
									str=str.split(hdd_info[1]);
									var new_path=hdd_info[0] + str[1];
									//new_path=new_path.substr(0,new_path.length-1);	//remove end of '/'
								}
							}
							if(t!="/mnt/HD")
							{
								SEL_PATH=unescape(new_path);
								document.getElementById(o.textname).value=unescape(new_path);
							}
							
							
						});

						/* [+] ALPHA_CUSTOMIZE, Add by Ben, auto focus and scroll to new folder */
						if ($("#get_focus").length > 0)
						{
							setTimeout(function() {
								if ($("#get_focus").position().top > $(".dialog_content").scrollTop() && $(".dialog_content").scrollTop() > $(".dialog_content").height())
									$(".dialog_content").scrollTop($("#get_focus").position().top);
								$("#get_focus").focus();
								$("#get_focus").click();
								$("#get_focus").removeAttr("id");
							},
							500);
							o.new_folder = "";
						}
						/* [-] ALPHA_CUSTOMIZE, Add by Ben, auto focus and scroll to new folder */

						//setTimeout(function(){$(o.scrollID).jScrollPane({autoReinitialise: true});}, 300); //ALPHA_CUSTOMIZE

						if(callback) callback();
						
						check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/

					});
					check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/
				}

				function test(c)
				{
					$(c).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
//								showTree( $(c).parent().parent(), string.substring(0,string.length-4) );
								showTree( $(c).parent().parent(), string.substring(0,string.length-4) );
								$(c).parent().parent().removeClass('collapsed').addClass('expanded');
								check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/
				}

				/* [+] ALPHA_CUSTOMIZE, Add by Ben */
				function check_jScroll(jScroll)
				{
					setTimeout(function() {
						var element = jScroll.jScrollPane({autoReinitialise: true});
						var api = element.data('jsp');
						if (!api.getIsScrollableV())
							$(".jspPane").css("top", "0px");
	
						if (!api.getIsScrollableH())
							$(".jspPane").css("left", "0px");
						}, 300);
				}
				/* [-] ALPHA_CUSTOMIZE, Add by Ben */

				//fish+
				function chg_path(t,form_obj,text_obj)
				{
					var hdd_num=HDD_INFO_ARRAY.length;
				
						var new_path = translate_path_to_display(t);
						/* Modify by Ben, 2013/12/10
					for(i=0;i<hdd_num;i++)
					{
						var hdd_info=HDD_INFO_ARRAY[i].split(":");
						if(t.indexOf(hdd_info[1])!=-1)
						{
							var str=t;
							str=str.split(hdd_info[1]);
							var new_path=hdd_info[0] + str[1];
							var last_char = new_path.substr(new_path.length-1,1)
							if (last_char == "/")
								new_path=new_path.substr(0,new_path.length-1);	//remove end of '/'  ;folder
							else	
								new_path=new_path.substr(0,new_path.length);	//remove end of '/'  ;file
						}
					}
						*/
					
					if ( ( t != "/mnt/HD") && ( t !="/mnt/USB") )
					{
						//for iso create image
						if (o.function_id == "iso_create")
						{						
							var len = _upIsoRootPath.length;	
							var show_text = t.substr(len+1,t.length-len+1);
							if (text_obj != null  && show_text != "")
							{								
								document.getElementById("settings_utilitiesIsoSelectPath_text").value=unescape(show_text);
								document.getElementById(text_obj).value=unescape(t);
									return;
							}	
							else
								return;
						}

						SEL_PATH=unescape(new_path);
						if((typeof(new_path) != "undefined" || new_path != null) && text_obj != null)
						{	
							if(form_obj=="generic")
								document.getElementById(text_obj).value=unescape(new_path);
							else
								document.getElementById(text_obj).value=unescape(new_path);
             			}
					}
				}
				//fish end
				function bindTree(t) {
					$(t).find('LI A').bind(o.folderEvent, function() {
						if( $(this).parent().hasClass('directory') ) {
							if( $(this).parent().hasClass('collapsed') ) {
								// Expand
								if( !o.multiFolder ) {
									$(this).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
									$(this).parent().parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
								}
								$(this).parent().find('UL').remove(); // cleanup
								showTree( $(this).parent(), escape($(this).attr('rel').match( /.*\// )) );
//								$(this).parent().removeClass('collapsed').addClass('expanded');  //mark
								//amy++  
								var f = escape($(this).attr('rel').match( /.*\// ));
								if (f.substring(f.length-4,f.length) != "new/")
								{
									$(this).parent().removeClass('collapsed').addClass('expanded');
								}
								//amy--
								check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/
							} else {
								// Collapse
								$(this).parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
								$(this).parent().removeClass('expanded').addClass('collapsed');
								check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/
								
								chg_path( escape($(this).attr('rel')),o.formname,o.textname);
								return;
							}
						} else {						
							h($(this).attr('rel'));
							switch(o.function_id)
							{
								case "iso_create":
									var full_path = escape($(this).attr('rel').match( /.*\// )+$(this).html());
									chg_path( full_path,o.formname,o.textname);
								break;
								
								case "LBackups_URL"://Internal Backup/Source Folder/File,//LBACKUP_MODIFY_INFO[5]:Type, 0->File,1->Folder
									chg_path( escape($(this).attr('rel')),o.formname,o.textname);
									(LBACKUP_MODIFY_INFO[15]="" || (typeof LBACKUP_MODIFY_INFO[15] == "undefined"))?LBACKUP_CREATE_INFO[5]="0":LBACKUP_MODIFY_INFO[5]="0";
								break;
								
								default:
									chg_path( escape($(this).attr('rel')),o.formname,o.textname);
									break;
							}
						}
						check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/

						return false;
					});
					// Prevent A from triggering the # on non-click events					
					if( o.folderEvent.toLowerCase != 'click' )
					{
						$(t).find('LI A').bind('click', function() {
							$('.tree_click_focus').removeClass("tree_click_focus");
							$(this).addClass("tree_click_focus");
							return false;
						});
					}
					
					$("input:checkbox").checkboxStyle();
				}

				if (o.root == "/")
				{
					$(this).find('LI A').each(function(){
						var rel= escape($(this).attr('rel').match( /.*\// ));
						var rel_selected  = rel.substr(0,rel.length -1);
						//alert("rel_selected="+rel_selected + "\n__str=" + __str)
						if (rel_selected == escape(__str) || o.w == "open")
						{			
							if( $(this).parent().hasClass('directory') ) {
								if( $(this).parent().hasClass('collapsed') ) {
									// 	
									if( !o.multiFolder ) {
										$(this).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
										$(this).parent().parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
									}
									$(this).parent().find('UL').remove(); // cleanup
									
									/*
									if( $(this).parent().hasClass('read') )
									{
										read = "yes";
									}
									else
										read = "no";

									*/			
									//showTree( $(this).parent(), escape($(this).attr('rel').match( /.*\// )),read );
	 								showTree( $(this).parent(), escape($(this).attr('rel').match( /.*\// )));
									var f = escape($(this).attr('rel').match( /.*\// ));
									if (f.substring(f.length-4,f.length) != "new/")
									{
										$(this).parent().removeClass('collapsed').addClass('expanded');
									}
									//amy--	
								} else {
									// Collapse
									$(this).parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
									$(this).parent().removeClass('expanded').addClass('collapsed');
									check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/
	
									return;
								}
							} else {
								h($(this).attr('rel'));
							}
							check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/

							return false;
						}
					}); 
				}
				else
				{
					// Loading message
					$(this).html('<ul class="jqueryFileTree start"><li class="wait">' + o.loadMessage + '<li></ul>');
					// Get the initial file list
					showTree( $(this), escape(o.root) );
					check_jScroll($(o.scrollID)); /* ALPHA_CUSTOMIZE*/
				}
			});
		}
	});
	
})(jQuery);

