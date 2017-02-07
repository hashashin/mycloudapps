var stickToMouse = -1;
var wasX, wasY, wasSklW, wasSklH, wasSklT, wasSklL;
var theRatio = 1;
var dBord = 0;
var shiftDown = false;

function SaveRatio() {
	theRatio = parseFloat(dg('skeleton').style.width)/parseFloat(dg('skeleton').style.height);
}

function MouseDown(x, eve) {
	stickToMouse = x;
	wasX = getMyXY(eve, 0);
	wasY = getMyXY(eve, 1);
	wasSklW = parseInt(dg('skeleton').style.width);
	wasSklH = parseInt(dg('skeleton').style.height);
	wasSklT = parseInt(dg('skeleton').style.top);
	wasSklL = parseInt(dg('skeleton').style.left);
}

function MouseDownTheSkeleton(eve) {
	if (stickToMouse == -1)
		MouseDown(5, eve);
}

function ReleaseMouse() {
	stickToMouse = -1;
}

function ExpandSkl() {
	var ImgW = parseInt(dg('thePhoto').style.width);
	var ImgH = parseInt(dg('thePhoto').style.height);
	var ImgWH = Math.min(ImgW, ImgH);

	dg('skeleton').style.top = Math.round((ImgH-ImgWH)/2)+'px';
	dg('skeleton').style.left = Math.round((ImgW-ImgWH)/2)+'px';
	if (parseInt(dg('skeleton').style.height) != ImgWH) {
		dg('skeleton').style.height = ImgWH+'px';
		dg('skeleton').style.width = ImgWH+'px';
	}
	else {
		dg('skeleton').style.height = dg('thumbPrevCont').style.height;
		dg('skeleton').style.width = dg('thumbPrevCont').style.width;
	}
	UpdateThumbPrev();
}

/*
 * IMPLEMENTED IN HELP.JS SINCE 3.30

document.onkeydown = keyHitDown;
document.onkeyup = keyHitUp;


function keyHitDown(e) {
	var thisKey = e?e.which:window.event.keyCode;
	if (thisKey == 16) // shift
		shiftDown = true;
}


function keyHitUp(e) {
	var thisKey = e?e.which:window.event.keyCode;
	if (thisKey == 16) // shift
		shiftDown = false;
}

*/

function MouseMoveInside(eve) {
	if (stickToMouse == -1)
		return;

	var ImgW = parseInt(dg('thePhoto').style.width);
	var ImgH = parseInt(dg('thePhoto').style.height);

	var SklW = parseInt(dg('skeleton').style.width);
	var SklH = parseInt(dg('skeleton').style.height);
	var SklT = parseInt(dg('skeleton').style.top);
	var SklL = parseInt(dg('skeleton').style.left);

	var dX = getMyXY(eve, 0) - wasX;
	var dY = getMyXY(eve, 1) - wasY;

	var thereY = Math.max(0, Math.min(ImgH-SklH-dBord, wasSklT+dY));
	var thereX = Math.max(0, Math.min(ImgW-SklW-dBord, wasSklL+dX));

	if (stickToMouse == 5) {
		dg('skeleton').style.top = thereY+'px';
		dg('skeleton').style.left = thereX+'px';
	}
	else {
		var SignH = (stickToMouse>=2)?-1:1;
		var SignW = (stickToMouse%2)?-1:1;
		var sd = (shiftDown)?2:1;
		var ValH = Math.min(ImgH-dBord, wasSklH+sd*SignH*(-dY));
		var ValW = Math.min(ImgW-dBord, wasSklW+sd*SignW*(-dX));

		ValH = Math.min(ImgH-dBord, Math.min(ValH, ValW));
		ValW = Math.min(ImgW-dBord, ValH*theRatio);

		if (shiftDown)
			SignH = SignW = 1;

		thereY = Math.max(0, Math.min(ImgH-SklH-dBord, Math.round((wasSklH-ValH)*SignH/sd)+wasSklT));
		thereX = Math.max(0, Math.min(ImgW-SklW-dBord, Math.round((wasSklW-ValW)*SignW/sd)+wasSklL));

		if (ValH>0) {
			if (SignH == 1)
				dg('skeleton').style.top = thereY+'px';
			dg('skeleton').style.height = ValH+'px';
		}
		if (ValW>0) {
			if (SignW == 1)
				dg('skeleton').style.left = thereX+'px';
			dg('skeleton').style.width = ValW+'px';
		}
	}
	UpdateThumbPrev();
}

function UpdateThumbPrev() {
	dg('sklW').value = parseInt(dg('skeleton').style.width);
	dg('sklH').value = parseInt(dg('skeleton').style.height);
	dg('sklL').value = parseInt(dg('skeleton').style.left);
	dg('sklT').value = parseInt(dg('skeleton').style.top);

	dg('kines_l').style.width = dg('sklL').value+'px';

	dg('kines_r').style.width = Math.max(0, ImgW-dg('sklL').value-dg('sklW').value-1)+'px';

	dg('kines_t').style.left = dg('sklL').value+'px';
	dg('kines_t').style.height = dg('sklT').value+'px';
	dg('kines_t').style.width = (1+parseInt(dg('sklW').value))+'px';

	dg('kines_b').style.left = dg('sklL').value+'px';
	dg('kines_b').style.height = Math.max(0, ImgH-dg('sklT').value-dg('sklH').value-1)+'px';
	dg('kines_b').style.width = (1+parseInt(dg('sklW').value))+'px';

	var rr = parseInt(dg('skeleton').style.width) / parseInt(dg('thumbPrevCont').style.width);

	dg('thumbPrev').style.width  = (parseInt(dg('thePhoto').style.width )	/ rr)+'px';
	dg('thumbPrev').style.height = (parseInt(dg('thePhoto').style.height) 	/ rr)+'px';
	dg('thumbPrev').style.left   = (-parseInt(dg('skeleton').style.left) 	/ rr)+'px';
	dg('thumbPrev').style.top    = (-parseInt(dg('skeleton').style.top) 	/ rr)+'px';
}