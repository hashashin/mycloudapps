var blured = false;
var MAX_ADD_BOX = 20;
var nowTime;

function PrepareBody() {
	try {
		dg('newpasswd').focus();
	} catch(e) {}
	try {
		dg('loginAdminPass').focus();
	} catch(e) {}


	var alla = document.getElementsByTagName('a');
	for (var i=0; i<alla.length; i++)
		if (alla[i].getAttribute('class') == 'q') {
			var where = alla[i].getAttribute('onclick');
			where = where.substring(10, where.length - 10);
			alla[i].setAttribute('title', 'Help on "'+where+'"');
		}

	var alli = document.getElementsByTagName('input');
	for (var i=0; i<alli.length; i++)
		if (alli[i].className != "submit")
			try { alli[i].onfocus = alli[i].select; } catch(e) {}
	var alli = document.getElementsByTagName('textarea');
	for (var i=0; i<alli.length; i++)
		try { alli[i].onfocus = alli[i].select; } catch(e) {}
}

function getMyXY(eve, t) {
	var tt
	if(!eve)
		var eve=window.event;
	if (document.all)
		tt = document.body.scrollTop?document.body.scrollTop:document.documentElement.scrollTop;
	return t?document.all?eve.clientY+tt:eve.pageY:document.all?eve.clientX:eve.pageX;
}

function wordize(s) {
	return s[0].toUpperCase()+s.substring(1);
}

function dg(x) {
	return document.getElementById(x);
}

function hideElem(x) {
	try {
		dg(x).style.display = 'none';
	} catch(e) {}
}

function showElem(x) {
	try {
		dg(x).style.display = 'block';
	} catch(e) {}
}

function inlineElem(x) {
	try {
		dg(x).style.display = 'inline';
	} catch(e) {}
}

function tableRowElem(x) {
	try {
		dg(x).style;
		dg(x).style.display = 'table-row';
	} catch (e) {
		if (dg(x))
			dg(x).style.display = 'inline';
	}
}

function checkChangePass() {
	var np1 = dg('newpasswd1').value;
	var np2 = dg('newpasswd2').value;
	if (np1 != np2) {
		alert('New passwords are not identic!');
		return false;
	}
	if (np1.length < 4) {
		alert('New password is too short! (less than 4 character)');
		return false;
	}
	return true;
}

function checkInstallPass() {
	var np = dg('newpasswd').value;
	if (np.length < 4) {
		alert('New password is too short! (less than 4 character)');
		return false;
	}
	return true;
}

function checkPrivacyRow() {
	var p = dg('public').checked;
	if (p) {
		dg('password').value = '';
		dg('passwordRow').style.display = 'none';
	}
	else {
		dg('passwordRow').style.display = 'block';
		try {
			dg('passwordRow').style.display = 'table-row';
		} catch (e) {}

	}
}

function checkHasPass() {
	if (dg('name').value == "") {
		alert('"Name" field can not be left blank!');
		return false;
	}
	if ((! dg('public').checked) && (dg('password').value == "")) {
		alert('Either assign a password or make it public!');
		return false;
	}
	return true;
}

function confirmDelete(x) {
	return confirm('Are you sure you want to delete "'+x+'"?');
}

function checkDate() {
	if (dg('date').value.length == 0) {
		alert('Date is required');
		return false;
	}
	re = /^\d{4}\/\d{2}\/\d{2}$/;
	if (! re.test(dg('date').value)) {
		alert('Date must be in YYYY/MM/DD format');
		return false;
	}
	return true;
}

function CheckAddPhotoTime() {
	retime = /^\d{4}\/\d{2}\/\d{2}[ ]\d{2}[:]\d{2}$/;
	re = /^\d{4}\/\d{2}\/\d{2}$/;
	rehit = /^\d+ \d+\/\d+$/;
	if (! re.test(dg('datetake').value)) {
		alert('Date Taken must be in YYYY/MM/DD HH:mm format');
		return false;
	}
	if (! retime.test(dg('dateadd').value)) {
		alert('Date Added must be in YYYY/MM/DD HH:mm format');
		return false;
	}
	if (! rehit.test(dg('hits').value)) {
		alert('Hits must be in HITS RATE_SUM/RATE_COUNT format like 288 12/7');
		return false;
	}
	return true;
}

function CheckAddPhoto() {
	if (!CheckAddPhotoTime())
		return false;
	if (dg('theImgPath').value == "") {
		alert('You must acquire a photo first!');
		return false;
	}
	return true;
}

function ConfirmDelPhotoID(x) {
	return confirm('Are you sure you want to delete photo number "'+x+'"?');
}

function ConfirmDelPhoto() {
	return ConfirmDelPhotoID(dg('piddel').value);
}

function ConfirmRestore() {
	return confirm('Are you sure you want to restore the backup file?');
}

function ConfirmSave() {
	return confirm('Are you sure you want to save your modification?');
}

function showlinkline(x) {
	dg('linkline'+x).style.display = 'table-row';
}

function hidelinkline(x) {
	//dg('linkline'+x).style.display = 'none';
}

function linkAddBelow(x) {
	x = parseInt(x.id.substr(8, x.id.length));
	//alert(x*10);

	var lets = new Array('n', 'h', 't');
	var def = new Array('', 'http://', '');
	var n = dg('nLink').value;
	x++;
	dg('nLink').value = ++n;

 	var allTable = dg('allLinkLines').childNodes[dg('allLinkLines').childNodes.length - 1];
 	var m = allTable.childNodes.length;
 	for (m--; allTable.childNodes[m].tagName != "TR"; m--)
 		;
	var lastTr = allTable.childNodes[m];

 	var thisTr = lastTr.cloneNode("tr");

 	thisTr.id = 'linkline'+(n-1);
 	m = thisTr.childNodes.length;
 	for (i=0; thisTr.childNodes[i].tagName != "TD"; i++)
 		;
 	thisTr.childNodes[i].innerHTML = (n);

 	for (i++; i<m; i++)
 		if (thisTr.childNodes[i].tagName == "TD")
 			if (thisTr.childNodes[i].childNodes[0].tagName == "INPUT") {
				var s = thisTr.childNodes[i].childNodes[0].id;
				s = "l"+(n-1)+(s.charAt(s.length-1));
 				thisTr.childNodes[i].childNodes[0].name = s;
 				thisTr.childNodes[i].childNodes[0].id = s;
			}

 	allTable.appendChild(thisTr);

	for (var i=n-1; i>x; i--)
		for (var l=0; l<3; l++) {
			var j = i-1;
			dg('l'+i+lets[l]).value = dg('l'+j+lets[l]).value;
			dg('l'+i+lets[l]).style.fontWeight = dg('l'+j+lets[l]).style.fontWeight;
		}
	for (var l=0; l<3; l++)
		dg('l'+x+lets[l]).value = def[l];
	dg('l'+x+'n').style.fontWeight = 'normal';

	//showlinkline(n-1);
}

function linkDelThis(x) {
	x = parseInt(x.id.substr(8, x.id.length));

	var n = dg('nLink').value;
	if (n == 1) {
		alert("You can't omit all the links! One must survive.");
		return;
	}

	if (!confirm('Are you sure you want to delete link "'+dg('l'+x+'n').value+'"?'))
		return;
	var lets = new Array('n', 'h', 't');

	dg('nLink').value = --n;
	for (var i=x; i<n; i++)
		for (var l=0; l<3; l++) {
			var j = i+1;
			dg('l'+i+lets[l]).value = dg('l'+j+lets[l]).value;
			dg('l'+i+lets[l]).style.fontWeight = dg('l'+j+lets[l]).style.fontWeight;
		}
	removeThisNode('linkline'+n);
}

function removeThisNode(node) {
	if (dg(node))
		node = dg(node);
	node.parentNode.removeChild(node);
	updateMode();
}

function addMainColDiv() {
 	var allModes = dg('allModes');

	m = 0;
 	for (; allModes.childNodes[m].tagName != "DIV"; m++)
 		;
	var firstDiv = allModes.childNodes[m];

 	var thisDiv = firstDiv.cloneNode("DIV");
 	thisDiv.innerHTML = "Then<br /><br />"+thisDiv.innerHTML;

	m = allModes.childNodes.length;
 	for (m--; allModes.childNodes[m].tagName != "A"; m--)
 		;
	var aDiv = allModes.childNodes[m];

 	allModes.insertBefore(thisDiv, aDiv);
 	updateMode();
}

function updateMode() {
 	var allModes = dg('allModes');

	m = allModes.childNodes.length;
	r = "";
 	for (i=0; i<m; i++)
 		if (allModes.childNodes[i].tagName == "DIV") {
			k = 0;
		 	for (; allModes.childNodes[i].childNodes[k].tagName != "SELECT"; k++)
 				;
 			r = r+'-'+allModes.childNodes[i].childNodes[k].value;
		}

	r = r.substr(1, r.length);
	dg('mode').value = r;
	changePrev(dg('mode').value, '');
}

function fixBoldInput(x, val) {
	dg('l'+x+'n').style.fontWeight = (val.length > 0)?'normal':'bold';
}

function changePrev() {
	var mode = dg('mode').value;
	var theme = dg('theme').value;
	dg('prevmode1').setAttribute("href", './?mode='+mode+'&theme='+theme);
	dg('prevmode2').setAttribute("href", './?mode='+mode+'&theme='+theme);
}

function rethumb_fill(ImgPath) {
	dg('thumbPrev').src = ImgPath;
	dg('thePhoto').style.backgroundImage = "url('"+ImgPath+"')";
}

function rethumb() {
	var ImgPath = dg('thumbPrev').src;
	dg('thumbPrev').src = '';
	dg('thePhoto').style.backgroundImage = "url('"+"')";
	setTimeout("rethumb_fill(ImgPath)", 1000);
}

function ToggleAdvPref() {
	var pref = dg('AdvPref');
	if (pref.style.display == 'block') {
		pref.style.display = 'none';
		dg('ShowHideAdvPref').innerHTML = 'Show';
	}
	else {
		pref.style.display = 'block';
		dg('ShowHideAdvPref').innerHTML = 'Hide';
	}
}

function updateTimeDiffer(x) {
	try {
		x = eval(x);
	} catch(e) { x = 0; }

	var wantTime = new Date;
	wantTime.setTime(nowTime.getTime()+x*1000*60);

	var hours, minutes;

	for (hours = wantTime.getHours()+""; hours.length < 2; hours = "0"+hours)
		;
	for (minutes = wantTime.getMinutes()+""; minutes.length < 2; minutes = "0"+minutes)
		;
	dg('timeDiffShow').innerHTML = hours+":"+minutes;
}

function CheckDateDrafts() {
	retime = /^\d{4}\/\d{2}\/\d{2}[ ]\d{2}[:]\d{2}$/;
	if (dg('dateadd').value.length == 0) {
		alert('Date is required');
		return false;
	}
	if (! retime.test(dg('dateadd').value)) {
		alert('Date Added must be in YYYY/MM/DD HH:mm format');
		return false;
	}
	return true;

}

function CheckActionDrafts() {
	if (parseInt(dg('selCount1').innerHTML ) == 0) {
		alert("At least one file should be selected!");
		return false;
	}

	if (dg('groupdel').checked)
		return confirm("Are you sure you want to delete these " + dg('selCount1').innerHTML + " file(s)?");
	else // groupadd
		return CheckDateDrafts();
	return true;
}

function CheckDeleteThisDrafts(which) {
	return confirm("Are you sure you want to delete file "+which + "?");
}

function updateSelCount() {
	var r = 0;
	var alli = document.getElementsByTagName('input');
	for (var i=0; i<alli.length; i++)
		if (alli[i].className == "checkbox") {
			if (alli[i].checked == true)
				r ++;
		}

	dg('selCount1').innerHTML = r;
	dg('selCount2').innerHTML = r;
}

function DraftsSelectBit(_and, _xor) {
	var alli = document.getElementsByTagName('input');
	for (var i=0; i<alli.length; i++)
		if (alli[i].className == "checkbox") {
			v = alli[i].checked;
			v &= _and;
			v ^= _xor;
			alli[i].checked = v;
	}
	updateSelCount();
}

function AddAddBox() {
	dg('boxv').value = parseInt(dg('boxv').value) + 1;
	if (dg('boxv').value >= MAX_ADD_BOX)
		alert("Maximum adding boxed reached, refresh page!");
	else {
		var i = dg('boxv').value;
		var that = dg();
		var seed = dg('seedv'+i).value;
		//alert(seed);
		dg('upload_iframe_'+seed).setAttribute('src', 'upload.php?draft=yes&seed='+seed);
		showElem('AddBox'+i);
	}
}