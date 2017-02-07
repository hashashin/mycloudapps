function get_amule_info()
{
	$.ajax({
			url: "/cgi-bin/apkg_mgr.cgi",
			type: "POST",
			async: false,
			cache: false,		
			data: {cmd:"module_Get_One_Info",f_module_name:'aMule'},	
			dataType:"xml",
			success: function(xml)
			{	
				  var my_path = "/"+ $(xml).find("path").text();	
				  var my_version = $(xml).find("version").text();	
				  var my_date = $(xml).find("date").text();	
				  
				  $("#my_date").text(my_date);
				  $("#my_version").text(my_version);
				  $("#my_path").text(my_path);
			}
	});
}

function check_button() 
{
    $.ajax({
			url: "/cgi-bin/aMule_mgr.cgi",
			type: "POST",
			async: false,
			cache: false,		
			data: {cmd:"aMule_Downloads_Info"},	
			dataType:"xml",
			success: function(xml)
			{	
				  	var my_state = $(xml).find("config > state").text();	
				  		  	
					if (my_state == 0)
						$("#But_Launch_IF").removeClass("button_medium").addClass("button_medium_display");
				    else
				        $("#But_Launch_IF").removeClass("button_medium_display").addClass("button_medium");
			}
	});
}

function goto_url()
{ 
//	var xurl = document.URL;
//    var prefix = xurl.substr(0, 5);
      var my_url=document.domain;
//    if (prefix == "https")
//	    my_url = "https://"+document.domain+":4711?pass=admin";
//    else
//	    my_url = "http://"+document.domain+":4711?pass=admin";

	if(my_url.indexOf(":")==-1)
	{
	    my_url = "http://"+document.domain+":4711?pass=admin";
    	window.open(my_url,"aMule");
    }
    else
    {
    	alert("This module does not support IPv6.")
    }
	    	
}

