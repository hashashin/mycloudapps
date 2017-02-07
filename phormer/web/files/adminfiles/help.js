//var shiftDown = false;
var dont_help = false;
var _AIDIN_MAIL = "p@horm.org";

document.onmouseup = captureMousedown;
document.onkeydown = captureKeydown;
document.onkeyup = captureKeyup;

function captureMousedown(e) {
	ReleaseMouse();
	var mc = e?e.which:window.event.button;
	var myX = getMyXY(e, 0);
	if (mc == 1 && (window.screen.width - myX > 20))
		HideHelp();
}

function captureKeydown(e) {
	var k = e?e.which:window.event.keyCode;
	if (k == 27)
		HideHelp();
	if (k == 16) // shift
		shiftDown = true;
}


function captureKeyup(e) {
	var k = e?e.which:window.event.keyCode;
	if (k == 16) // shift
		shiftDown = false;
}


function DontHideHelp() {
	dont_help = true;
}


function HideHelp() {
	if ((dgp('helpBox').style.display != 'block') || (dont_help)) {
		dont_help = false;
		return;
	}
	dgp('helpBox').style.display = 'none';
	dgp('helpBoxLegend').innerHTML = '';
	SwitchSelectIE(1);
	if (!isIE()) {
		window.blur();
		window.focus();
	}
}

function dgp(x) {
	return document.getElementById(x);
}

function ShowHelp(s, eve, lang) {
	if ((dgp('helpBoxLegend').innerHTML == s)) {
		HideHelp();
		return;
	}
	dont_help = false;
	var myX = parseInt(getMyXY(eve, 0));
	var myY = parseInt(getMyXY(eve, 1));
	if (myX < 100)
		myX = 100;
	if (myX + 250 > window.screen.width-50)
		myX = window.screen.width-300;
	dgp('helpBox').style.top  = '' + (myY+10) +'px';
	dgp('helpBox').style.left = '' + (myX-70)+'px';
	dgp('helpBox').style.display = 'block';

	dgp('helpBoxLegend').innerHTML = /* 'Help : ' + */ s;

	var helpMsg;
	switch (lang) {
		case 'it':			helpMsg = GetItHelpMsg(s); break;
		default:			helpMsg = GetHelpMsg(s);
	}
	dgp('helpBoxInner').innerHTML = helpMsg;
	SwitchSelectIE(0);
}


function getMyXY(eve, t) {
	var myY, myX;
	if(!eve)
		var eve=window.event;
	if (document.all){
		myX=eve.clientX;
		var dY = 0;
		if (document.body.scrollTop)
			dY=document.body.scrollTop;
		else
			dY=document.documentElement.scrollTop;
		myY=eve.clientY+dY;
	}
	else {
		myX=eve.pageX;
		myY=eve.pageY;
	}
	return ((t)?myY:myX);
}


function SwitchSelectIE(t) {
	if (isIE()) {
		var SS = document.styleSheets[0];
		if (SS.cssRules)
			inRule = SS.cssRules;
		else if (SS.rules)
			inRule = SS.rules;
		if (dg('helpBox').style.display != 'none')
			t = 0;
		inRule[0].style.visibility = (t)?'visible':'hidden';
	}
}

function isIE() {
	return (navigator.appName.indexOf('Microsoft') != -1) && !window.opera;
}

/****************************************************************************/
/********************** GET HELP MESSAGES FUNCTION **************************/
/****************************************************************************/

/********* Italian Translation, to be implemented **********/
function GetItHelpMsg(s) {
	var r = "";
	var endl = "<br /> &nbsp; ";
	switch (s) {
		default:
		 r = "Italian translation for <b>"+s+"</b> is not yet implemented.";
	}
	return r;
}

/********* English Translation, By Aideen [i.horm.org] **********/
function GetHelpMsg(s) {
	var r = "";
	var endl = "<br /> &nbsp; ";
	switch (s) {
		case "help":
			r = "This is the new Help feature of Phormer!<br />"
				+ "&nbsp; You'll find useful information about each item/menu by \
					clicking on the question mark (<a class=\"q\">?</a>) next to the item.<br />"
			    + "To dismiss this window, either <br /> <ul> "
				+ "<li> Click the <a class=\"bLink\" onclick=\"HideHelp();\">[Dismiss]</a> link </li>"
				+ "<li> Click somewhere else this dialogue box </li>"
				+ "<li> Hit escape (Esc) key on keyboard </li>"
				+ "</ul>";
				break;
		case "Update Message" :
			r = "If you don't want Phormer to check for its update everytime, disable "
				+ "this feature at <b>Advanced Configuration</b> area (of course, after logging in!)";
				break;
		case "Administration Region" :
			r = "This is the control panel of your PhotoGallery. Manage everything from here!";
				break;

		case "Default Photo Num in Box" :
			r = "Default number of Photos when the display mode is Jungle Box. Values higher "
				+ "than 50 will cause long time to load and also delay in slow clients.";
				break;

		case "Default Photo Num in Recents" :
			r = "Default number of Photos in the \"Recently Visited Photos\" Part or "
				+ "\"Recently Added Photos\". "
				+ endl + "Also number of photos in <b>Random</b> mode is equal to what you "
				+ "set here.";
				break;

		case "Default Photo Num in Tops" :
			r = "Default number of Photos in the \"Most Visited Photos\" Part or \"Top Rateds\". ";
				break;

		case "Default Story Num in mode" :
			r = "Default Number of Stories to be displayed when the default mode is \"Story\". Each "
				+ "Story would be displayed in a row with its last added photos.";
				break;

		case "Default Story Num in Sidebar" :
			r = "Default Number of Stories to be listed in sidebar box. Values less than total number "
				+ "of Stories will cause a link to \"list all stories\" option to be displayed." ;
				break;

		case "Transparency Percentage" :
			r = "The percentage of opacity over the thumbnails; which would be purged upon mouse-over. "
				+ endl + "More Percentage will cause more darkness. Thus, To disable this feature, "
				+ " just set this values to 100% (no opacity indeed).";
				break;

		case "Jpeg Compression Percentage" :
			r = "The ratio of Jpeg Compression during generation of thumbnails and resizing images. "
				+ "Higher values will cause more quality but, on the other hand, heavier (size in bytes) "
				+ " files! 70% is suggested. "
				+ endl + "This value will not affect currently created files/thumbs and would be used "
				+ "for future creations. ";
				break;

		case "Thumb Links Target" :
			r = "The way a desired image, when clicking on its thumbnail, opens -- either in a new "
				+ "window of browser or in the same window."
				+ endl + "Options are: <br /> <ul>"
				+ "<li> <b> Same </b> : Never opens a link in a new window -- always on the "
				+       " current window.</li>"
				+ "<li> <b> Common </b> : This's the common method. Open some links in new windows "
				+       " and others (like next and prev thumbs) in the same windows. </li>"
				+ "<li> <b> New </b> : Always opens any link in a new window. </li>"
				+ "</ul>";
				break;
		case "Pick Neighbours" :
			r = "You see, there're a few (about 4) thumbs below each photo, viewed in large size. "
				+ "Those are named <b>Neighbours</b> of that photo. This option, determines the way the "
				+ "Neighbours of a photo shall be picked -- either <br /> <ul> "
				+ "<li> <b> Both </b> : From both that story and category (a random combination of them) </li> "
				+ "<li> <b> Category </b> : From the category that photo belongs to (just the default) </li> "
				+ "<li> <b> Story </b> : From the story that photo belongs to (just the default story of it) </li> "
				+ "</ul>";
				break;
		case "HTML icon URL" :
			r = "If your Photo gallery has an icon, which is placed near the URL in browsers, and/or "
				+ "uses in favoriting action, put its URL (including http://) here."
				+ endl + "Else, If you're not interested in that feature, leave this field empty.";
				break;
		case "Extra HTML Code" :
			r = "Here's the place to put additional HTML codes and scripts you wish to be placed in "
				+ "your website like hit counters, banners, ad-sense and so.";
				break;
		case "Banned IPs" :
			r = "If you want to block some person of leaving comment for your PhotoGallery, just "
				+ "paste their IP addresses here, one per line.";
				break;
		case "Manage Photos" :
			r = "Edit any single Photo. Modifying, Adding, removing and almost every other work "
				+ "with photos should be done in here."
				+ endl + "By clicking on <a>[Add]</a>, you can quickly add a single photo.";
				break;
		case "Manage Drafts" :
			r = "Uploaded photos (many once in bulk), Zip items, Import photos & any other temporary saved "
				+ "(and not showing to public) or mass uploaded items are stored here to be managed.";
				break;
		case "Manage Categories" :
			r = "A <b>Category</b> is a set of photos that have similarity in subject or are relative in a manner "
				+ "rather than time-line. examples of that can be \"Self Portraits\", \"Kitty (My Pet)\" "
				+ ", \"Nature\" and etc. You can add, remove and totally manage categories from here.";
				break;
		case "Manage Stories" :
			r = "A <b>Story</b> is another container of photos, that is useful for grouping shots in a "
				+ " period of time. \"Europe Trip\", \"BirthDay\" and \"Halloween\" can be stories. "
				+ " Actually, stories are like posts of a weblog which may be updated rarely after first "
				+ " update but a new one would be added soon."
				+ endl + "Manage them from here.";
				break;
		case "Manage Comments" :
			r = "Comments are visitors opinions about your photos. Check (and delete which you don't like!) "
				+ "from here.";
				break;
		case "Adjust Preferences" :
			r = "Set the indexing mode of front-page, Theme, links and etc. from here. ";
				break;
		case "Advanced Configurations" :
			r = "Customizable options (anything you may need to change) is there. also Change Password "
				+ "and the option of Uninstalling Phormer!";
				break;
		case "Modular Actions" :
			r = "Listed items in below are external actions you can do with your Phormer, like Password "
				+ "changing or Uninstall.";
				break;
		case "Change Password" :
			r = "Change the single password of Administration from here.";
				break;
		case "Uninstall Phormer" :
			r = "Erase Phormer and all of the datas (image, XML files), from the server! "
				+ endl + "You know, as created images and XML files are written by Phormer (PHP) "
				+ "on server, the owner of those files would be Phormer and thus, you may not easily do "
				+ "<tt>rm -rf</tt> to omit them all. This module tells Phormer to remove anything it has "
				+ "made and then you would be able to erase other files (which you are their owner).";
				break;
		case "XML Editor" :
			r = "Edit the source of XML files, which are the basic storage parts of Phormer. Also "
				+ "restore the missed / corrupted XMLs from here.";
				break;
		case "Log Out" :
			r = "Say goodbye to this session and finalize your authentication in Administration Region.";
				break;
		case "adminPass.inf missed" :
			r = "You may know Phormer stores Administration's Password, raw in a text file in "
				+ "data/adminPass.inf file . Unfortunately, now this file is missed. replace one "
				+ "yourself! i.e. create a adminPass.php file using any text-editor and write your "
				+ "desired password in one line in it and then place it in data/ folder."
				+ endl + "If you've no write access to there, you shall either chmod data/ for "
				+ "a while or write a PHP script to copy your adminPass.php to data/. "
				+ "<a href=\"mailto:"+_AIDIN_MAIL+"\">Aideen</a> in this case may help you. ";
				break;
		case "Check Updates" :
			r = "This options is about the update check that runs everytime at the login page. "
				+ "All it does is to connect to <a href=\"http://p.horm.org/er\">Phormer's homepage</a> "
				+ "and checks if there's a new version of Phormer for you to be updated.";
				break;
		case "XML Missed" :
			r = " Oh!"
				+ endl + "Some XML files, due to bad server actions or synchronous write attemps "
				+ "or ... seems to be corrupt -- has less item than the last back-up of them. "
				+ "<b>Note that it might be because of a not-logouted session "
				+ "</b> (which causes no re-back-up action) and thus <b>everything may still be OK!</b>."
				+ endl + "Verify the contents and then if unfortunately Phormer has detected right, "
				+ "<a href=\"?page=editxml\">restore</a> the backup.";
				break;
		case "GD Extension" :
			r = "GD is an extension of PHP which is required for creating thumbnails and resizing photos. "
				+ endl + "<b>Phormer may not work properly on servers without GD Extensions.</b>";
				break;
		case "ZIP Extension" :
			r = "ZIP is an extension of PHP which is required for unzipping bulk-uploaded photos though "
				+ "a .zip file. Phormer works without ZIP, but you would be unable to upload a .zip file "
				+ "of many photos in draft works.";
				break;
		case "EXIF Extension" :
			r = "EXIF is an extension of PHP for reading additional datas of photos, caught by digital "
				+ "cameras. flash length, focus mode and date taken are of those kind."
				+ endl + "Phormer works without EXIF but is unable to read those data in that case";
				break;
		case "GD Not Found" :
			r = "The extension GD of PHP is not installed on your server. This way, Phormer is unable "
				+ "to create approperiate thumbnails and will use the original photo everywhere needed.";
				break;
		case "Word Verification" :
			r = "By enabling this feature, each visitor has to read an image and write its number "
				+ "on a filed. This is to protect commenting from spammers which are bots that leave "
				+ "numerous comments on photos."
				+ endl + "If your gallery has no spammer, it's a good idea to disable this feature and "
				+ "don't disturb commentors!"
				+ endl + "<b>Note</b> that it would never bother Admin! Thus, if you want to test it, "
				+ "you should log out the administration area, before.";
				break;
		case "Display Email" :
			r = "The way that Email of the author displays (at sidebar and footer). options are:"
				+ "<br /><ul>"
				+ "<li> <b> Modified </b> : Converts @ to [at] and . to [dot], like <b><a "
					+ "href=\"mailto:me[at]here[dot]com\">Email</a> </li></b> "
				+ "<li> <b> As Is </b> : Just a mailto: link, like <b><a href=\"mailto:me@here.com\">Email</a></b> </li> "
				+ "<li> <b> Text </b> : Simple text, like <b>me [at] here [dot] com</b> </li> "
				+ "<li> <b> Hidden </b> : Hides it private and writes nowhere! </li> "
				+ "</ul>"
				+ endl + "Note that your entered email address has to be valid, since it might be used on "
				+ "notifications.";
				break;
		case "PhotoGallery Name" :
			r = "Name of the PhotoGallery which is used above all of pages.";
				break;
		case "PhotoGallery Desc" :
			r = "Description of the PhotoGallery which is written below the name of PhotoGallery at "
				+ "the top of all of the pages. It can be a simple name like <b>\"My Photos with My camera\"</b> "
				+ "or a complicated one like <b>\"Hallucination from an opalescence view\"</b>.";
				break;
		case "Author Name" :
			r = "Name of the author(s) used below the PG and copyright of the photos hold to.";
				break;
		case "Author Email" :
			r = "Email of the administration of the PG, that might be displayed (upon display configuration "
				+ " at advanced configurations) to users and also needed for notifications of new comments (if "
				+ " set).";
				break;

		case "Theme" :
			r = "The color the index page. "
				+ endl + "By clicking on <b>Preview</b> link, you can have a preview of the selected "
				+ "theme without saving it indeed.";
				break;
		case "External Links" :
			r = "This part is the manager of your external Links -- links to other photoblogs or your "
				+ "favotire websites."
				+ endl + "<b>Hint: </b> Links with <b>no Link URL</b> part, would be assumed as headers "
				+ "and can separates links of various types from each other.";
				break;
		case "Link Name" :
			r = "Name of the link which would be the text to appear.";
				break;
		case "Link URL" :
			r = "URL of the link -- destination address of the link, including http://";
				break;
		case "Link Title" :
			r = "Title is the text that pops up when mouse cursor hovers above the link. "
				+ "Feel free to leave it blank if you have no approperiate choice in mind!";
				break;
		case "Add/Del Link" :
			r = "Add a new link below this one / Delete current link.";
				break;
		case "Index Mode" :
			r = "Layout of the index.php page to appear on first page. by clicking on <b>preview</b> "
				+ "link, you can have a preview of your customized layout without saving it indeed."
				+ endl + "<b>Note</b>: SideBar's changes will not be shown in preview";
				break;
		case "Time Differ" :
			r = "The difference of your server's time with your region (local) time. this would be "
				+ "added to server time, so if your server is at GMT 0 and you live in Iran (GMT "
				+ " +3:30) then this field should have 210."
				+ endl + "<b>NOTE:</b> the unit of this field is <b> Minutes </b>"
				+ endl + "<b>HINT:</b> Math expressions like \"3*60+30\" is allowed!";
				break;
		case "Drafts" :
			r = "In <b>Drafts</b> part, you can either <br /><ul>"
				+ "<li><b>Upload Files</b>: Upload several photos, in a zip file or a folder on server.</li>"
				+ "<li><b>Manage Items</b>: Quickly add several photos or delete them.</li>"
				+ "</ul>";
				break;
		case "Upload Photos to Draft" :
			r = "In this part, you can upload your photos to draft folder and add them later. methods "
				+ "of putting one (or more in a bulk) is either <br /><ul>"
				+ "<li><b>From PC</b>: it can be either <b>a single photo</b> or <b>a .zip</b> containing serveral items.</li>"
				+ "<li><b>From Server</b>: an external (from other website, <b>beginning with http:// </b>) photo or a local photo "
				+ "(with relative / absolute addressing) or a folder of local (<b>necessarily ended with /</b>).</li>"
				+ "</ul>"
				+ endl + "<b>NOTE</b>: it's strongly recommended to avoid uploading a .zip file with "
				+ "more than 25 photos in it, as it may take much (~30 seconds) to create their thumbnail "
				+ "and this can beat the entire action. however, in fast servers, you might be able to!";
				break;
		case "Manage Photos in Draft" :
			r = "Here is the place to quick add several photos from drafts folder or delete some of "
				+ "the uploaded ones.";
				break;
		case "Manage Drafts: Thumbnail" :
			r = "This is an automatically generated thumbnail from the center of the image, just in "
				+ "aim of helping you to recognize the file, in a better manner."
				+ endl + "However, in <b>quick add</b>, this thumbnail would be picked!";
				break;
		case "Manage Drafts: Information" :
			r = "These are the least information asked from you to quick add. Anyway, you can even "
				+ "leave these blank, yet!";
				break;
		case "Manage Drafts: Action" :
			r = "These are the actions related to a single image. <br /><ul>"
				+ "<li><b>Detailed add</b>: Like adding a single photo to <b>Manage Photos</b> part, "
				+	"but without require to upload - uses this photo!</li>"
				+ "<li><b>Delete this</b>: Delete this single file, rather than a group of files. "
				+ "</ul>";
				break;
		case "Drafts :: Delete Selected" :
			r = "Here, you can delete several (or just one) of the uploaded items in draft folder. "
				+ "Just pick them up and, select this radio button and finally, press <b>Perform!</b>";
				break;
		case "Drafts :: Quick-add Selected" :
			r = "This is the place to add several photos, once in a glance! These are the step: <br /><ol> "
				+ "<li>Select the desired photos from the list below.</li>"
				+ "<li>If you are interested and have enough time, fill <b>Name</b> and <b>Description</b> fields.</li>"
				+ "<li>Select this radio button.</li>"
				+ "<li>Configure common options (like default category) for them.</li>"
				+ "<li>Finally, press <b>Perform!</b></li>"
				+ "</ol>"
				+ endl + "<b>NOTE</b>: it's strongly recommended to avoid quick-adding "
				+ "more than 20 photos in a while, as it may take much (~30 seconds) to create their thumbnail "
				+ "and this can beat the entire action. however, in fast servers, you might be able to!";
				break;
		case "Upload Path" :
			r = "<b>Upload Path on PC</b>:<br />Browse your photo file from your hard disk. "
				+ endl + "Note that <b>zip files</b> with several photos inside should only be uploaded "
				+ "in drafts area.<br />"
				+ "<br /><b>Upload Path on Server</b>:<br />"
			    + "Either a local path (absolute by / or relative since upload.php directory) to a photo "
				+ "or a link to an external (beginning with http:// ) file on the web."
				+ endl + "Note that <b>import folder</b> is available in <b>drafts</b> mode and on that "
				+ "case, your folder link (surely, local; but either relative or absolute) should end "
				+ "with / to include all photos on that directory";
				break;
		case "Add Photo Link" :
			r = "By clicking on that link, you'll be able to add your photos to the PhotoGallery. "
				+ endl + "If you wish to add an already-uploaded (in drafts) photo, click on "
				+ "<a class=\"q\">From Drafts</a> Link.";
				break;
		case "Edit Photos Link" :
			r = "Here, in this short form field, you can go to edit page of any photo you wish and modify "
				+ "any data you'd sent when was adding it - even it's rating (!) or its source photo!"
				+ endl + "just enter its PhotoID (the number which appears at the end of its link) "
				+ "and then press <b>Edit!</b>";
				break;
		case "Add/Del Photos to Categ/Story" :
			r = "In this short form field, you can do various actions like adding or deleting any photo "
				+ "to/from any story or category. Just fill the photo's id, namely PID (which appears at "
				+ "the end of its link) and then the ID of category or story (which, in the same way, "
				+ "is placed at the end of that container's link)."
				+ endl + "<b>Note</b>: Remember that a photo can't be deleted from its default category "
				+ "or story."
				+ endl + "<b>Hint</b>: Putting a single photo into many (related) categories or stories "
				+ "helps visitors to access it, faster and easier!";
				break;
		case "Photo: Global Data" :
			r = "Essential information about photo has to be entered through these fields."
				+ endl + "Although it's been told that these data are <b>required</b>, but you can leave "
				+ "any of them empty, despite visitors may require them to know.";
				break;
		case "Photo: Title" :
			r = "Title of the photo is a name you let on that photo. a good title can add lots to the "
				+ "worth and meaning of the photo."
				+ endl + "However, if you can't find a proper name for a photo, you can leave it "
				+ "empty - Phormer will show no name for that photo.";
				break;
		case "Photo: Description" :
			r = "Information about the photo, e.g. where photo has taken or who are the people in the "
				+ "photo. Technical information like focus mode, PhotoShop manipulation (if made) should "
				+ "NOT be placed here, but in <b>Photo Info</b> Part."
				+ endl + "<b>Hint</b>: HTML tags are allowed here.";
				break;
		case "Photo: Time Added" :
			r = "The time you're going to add this photo(s) to the gallery. It'll automatically be set to "
				+ "current time and <b>Time Differ</b> with server (if set in <b>Advanced Configuration</b> "
				+ "part) will be added to it by Phormer.";
				break;
		case "Photo: Get Comments" :
			r = "Let visitors leave comments for it (these in drafts) or not.";
				break;
		case "Photo: Photo Info" :
			r = "The technical information of the photo, rather than social ones that should be "
				+ "placed at <b>Photo Desc</b>"
				+ endl + "other informations (like an external script or link) can be set here.";
				break;
		case "Photo: Default Categ" :
			r = "Default <b>Category</b> of the photo, which is the major (nearst in subject) category "
				+ "to the photo."
				+ endl + "You can't remove a photo from its category, but can add it later (in first "
				+ "page of <b>Manage Photos</b> part) to various categories. This category will be "
				+ "used for picking neighbours (if set at advanced configuration area) and <a "
				+ "class=\"q\">[up]</a> link of the photo."
				+ endl + "<b>Note</b>: A photo will be protected (not visible by anyone) if and only "
				+ "if either its <b>Default</b> category or story be passworded."
				+ endl + "<b>Hint<b>: in drafts mode, this default category will apply for all!";
				break;
		case "Photo: Default Story" :
			r = "Same as <b>Default Category</b>, here, the major story (actually the one that photo "
				+ "is taken/occured within) shall be set. later you can add photo to other stories"
				+ endl + "<b>Note</b>: A photo will be protected (not visible by anyone) if and only "
				+ "if either its <b>Default</b> category or story be passworded."
				+ endl + "<b>Hint<b>: in drafts mode, this default story will apply for all!";
				break;
		case "Photo: Date Taken" :
			r = "The date, photo has been taken on that. If you don't remind that, leave it empty.";
				break;
		case "Photo: Hits, Rate" :
			r = "Hit count and rating (sum of all rates / number of raterss) of the photo is here. "
				+ "One may ask it's other of honesty to modify these, manually. The answer (yet is "
				+ "inconvincible somehow) is that, the photo may have been imported (has previously "
				+ "hit counts or got lots of counts due to a test and author wish to dismiss them.";
				break;
		case "Thumbnail" :
			r = "Thumbnail is a small version of the photo which can be either a part of it or "
				+ "reduced / resized of entire photo or a combination of it!"
				+ endl + "Phormer just supports square thumbnails and has provided a handy tool "
				+ "to generate what you do expect of a square thumbnail to be!"
				+ endl + "<b>Hint</b>: by holding shift key on the keyboard, the thumbnail grows / "
				+ "shrinks symmetrically."
				+ endl + "<b>Hint</b>: If the image has not been displayed after upload but picker "
				+ "becomes available, just click on <b>Thumbnail Preview</b> label!"
				+ endl + "<b>Hint</b>: By doing a double-click on thumbnail picker skeleton, the "
				+ "size grows to fix. once again, after that, size shrinks to thumbnail's original!";
				break;
		case "Container Management" :
			r = "This is the place to manage containers. You can add a new container, edit your "
				+ "currently created containers (either categories or stories) or delete them."
				+ endl + "<b>Note</b> that <b>Delete</b> part might be too useful - you can "
				+ "delete only a container or <b>the photos inside that container</b> or &#133; "
				+ "Take a look!";
				break;
		case "Container Name" :
			r = "Name of the container (Category / Story), that would be displayed in list of them "
				+ "and also when is default container of a photo, in that photo's details.";
				break;
		case "Container Description" :
			r = "Notes about the container, like the trip, the story is about to or your idea about "
				+ "the subject of a category.";
				break;
		case "Container Visibility" :
			r = "Whether this container should be listed on containers list (on sidebar) or not. "
				+ endl + "<b>Hint</b>: you may not like your private (passworded) sets to be listed!";
				break;
		case "Container Privacy" :
			r = "This is the option to let you protect photos / sets from anyone's view. All of the "
				+ "photos with a default passworded container (category or story) will be protected "
				+ "also. <b>Note</b> that if you add a photo to, for example, passworded category of "
				+ "A, but its default category remain B, which is public, then the photo is still "
				+ "public. ";
				break;
		case "Container Inheritance" :
			r = "Here, you can set a container (story / category) to be a child of another container. "
				+ endl + "<b>Note</b> that, it's only to display and means nothing else to Phormer.";
				break;
		case "Help System" :
			r = "This is the way, these kind of help dialogue-boxes are supposed to show. The default "
				+ "language is English. Italian translation is going to be added soon!"
				+ endl + "However, professional Phormers may like to disable this feature to be "
				+ "displayed everywhere! ";
				break;
		case "Copyright Note" :
			r = "This is the place to put the <b>Copyright notes</b> which are printed at the bottom "
				+ "of every page. available variables are:<br /><ul>"
				+ "<li><b>$name</b>: Displays Author's name, as set.</li>"
				+ "<li><b>$email</b>: Displays Author's email in the defined (in <b>preferences</b> "
				+ 		"part) format.</li>"
				+ "<li><b>$Phormer</b>: Displays Phormer's link as <a class=\"q\" href=\"http://p.horm.org/er\">"
				+		"Phormer, ver ???</a>, where ??? is your using version.</li>"
				+ "</ul>"
				+ endl + "<b>Hint</b>: HTML tags and marks (like &amp;copy; for &copy;) are allowed."
				+ endl + "<b>Note</b> that these variables are case-sensitive!"
				+ endl + "<b>Ask</b>: It's requested to put $Phormer somewhere in the footnote. "
				+ "Thank you!";
				break;

		default :
			r = "Unfortunately there's no approperiate help message available for this item. <br />"
				+ " &nbsp; Notify <a href=\"mailto:"+_AIDIN_MAIL+"&subject=No Help for "+s+"\">Aideen</a> of that!";
				break;
	}

	return "&nbsp;"+r;
}