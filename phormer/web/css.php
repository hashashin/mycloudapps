<?php
	define("DEFAULT_STYLE", "white");
	header('Content-type: text/css');
	$c = isset($_GET['theme'])?$_GET['theme']:DEFAULT_STYLE;

	#                0          1       2       3       4       5		6       7       8       9       10      11         12      13      14      15      16      17
	$light = array('#F0F0F0', '#444', '#111', '#444', '#444', '#666', '#333', '#999', '#AAA', '#DDD', '#555', '#111'   , '#222', '#444', '#555', '#444', '#999', '#777', 'top' => '#777');
	$dark  = array('#0F1A0F', '#888', '#AAA', '#AAA', '#888', '#777', '#999', '#444', '#555', '#AAA', '#222', '#111'   , '#666', '#888', '#777', '#999', '#555', '#666', 'top' => '#AAA');
	$black = array('#000000', '#AAA', '#CCC', '#BBB', '#888', '#999', '#999', '#111', '#222', '#222', '#AAA', '#0D0D0D', '#666', '#999', '#AAA', '#AAA', '#444', '#666', 'top' => '#BBB');

	$styles = array('white'  => $light, 'wheat'  => $light,	'silver' => $light,	'dusty'  => $light,	'sky' 	 => $light,
					'bog'    => $dark, 	'purple' => $dark, 	'slate'  => $dark, 	'timber' => $dark,
					'blacky' => $black
				  );
	if(!isset($styles[$c]))
		$c = DEFAULT_STYLE;
	$s = $styles[$c];
	switch($c) {
		case 'wheat':	$s[0] = '#F5DEB3'; break;
		case 'silver':	$s[0] = '#CCCCCC'; break;
		case 'dusty':	$s[0] = '#B3A18F'; break;
		case 'sky':		$s[0] = '#E5F7FF'; break;
		case 'purple':	$s[0] = '#0F0F1A'; break;
		case 'slate':	$s[0] = '#223344'; break;
		case 'timber':	$s[0] = '#33221A'; break;
	}

?>

body 					{ background: <?php echo $s[0]; ?>; font-size: 11px; margin: 0px; padding: 0px; font-family: Tahoma, Arial, Helvetica, Serif; direction: ltr; }
#Granny					{ width: 760px; text-align: left; margin-bottom: 30px;}
a 						{ color: <?php echo $s[1]; ?>; text-decoration: none;  }
a:hover 				{ color: <?php echo $s[2]; ?>; }
.headerDot				{ font-size: 48px; color: #F30; }
div.topPhorm 			{ color: <?php echo $s['top']; ?>; font-family: georgia; margin-left: 10%; margin-bottom: 30px; background: url('files/cssfiles/<?php echo $c; ?>_vbg1.jpg') bottom; text-align: left; padding: 10px 25px; font-size: 72px;  }
div.topPhorm a, div.topPhorm a:hover	{ color: <?php echo $s['top']; ?>; }
div.topPhormAbout 		{ color: <?php echo $s[4]; ?>; line-height: 125%; font-family: tahoma; letter-spacing: 1px; border-left: 1px dashed #999; padding-left: 40px; margin-left: 12px; font-size: 8pt; }
div.footer 				{ color: <?php echo $s[4]; ?>; background: url('files/cssfiles/<?php echo $c; ?>_vbg0.jpg') top; padding: 20px 40px 20px; margin-right: 10%; margin-top: 10px; font-size: 8pt; text-align: center; line-height: 150%; }
div.footer a			{ color: <?php echo $s['top']; ?>; }
div.footer a:hover 		{ color: <?php echo $s[2]; ?>; }
#sidecol				{ width: 190px; margin-right: 5px; background: url('files/cssfiles/<?php echo $c; ?>_hbg1.jpg') right; float: left; }
#sidecolinner			{ padding: 15px 0px 0px 20px; }
#maincol				{ width: 560px; float: left;}
#maincolinner			{ }

.part 					{ line-height: 100%; background: url('files/cssfiles/<?php echo $c; ?>_hbg1.jpg') right repeat-y; margin: 0px 15px 20px 0px; padding: 10px 0px; text-align: left; }
.item					{ padding: 2px 0px 4px; }
.submenu 				{ margin: 5px 0px 5px 15px; clear: both; color: <?php echo $s[1]; ?>;  }
.part .titlepart	 	{ color: <?php echo $s[3]; ?>; font-size: 8pt; margin: 0px 25px 8px -10px; padding: 5px 5px 5px 10px;
							background: url('files/cssfiles/<?php echo $c; ?>_hbg1.jpg') top right repeat-y; }
.part .titlepart a		{ color: <?php echo $s[3]; ?>; }
.thumbcntarr			{ color: #777; letter-spacing: 0px; }
.categinfo				{ color: #777; padding-left: .3em; font-size: 0.89em; }
.categeach				{ }
.partmain				{ color: <?php echo $s[5]; ?>; line-height: 100%; background: <?php echo $s[0]; ?>; margin: 0px 15px 0px 0px; text-align: left; }
.partmain .submenu		{ clear: both; border: 1px solid <?php echo $s[0]; ?>; }
.partmain .titlepart	{ color: <?php echo $s[1]; ?>; letter-spacing: 5px; margin: 0px 20px 0px 0px; padding: 15px 15px 20px;
							background: url('files/cssfiles/<?php echo $c; ?>_vbg1.jpg') repeat-x bottom center; }
.partmain .titlepart a  { color: <?php echo $s[3]; ?>; }
.partmain .titlepart a:hover  { color: <?php echo $s[5]; ?>; }
.partmain .start		{ color: <?php echo $s[1]; ?>; letter-spacing: 5px; margin: 0px 20px 10px 0px; padding: 0px 15px 10px;  }
.theTitleA				{ color: <?php echo $s[1]; ?>; }
.partmain .midInfo		{ color: <?php echo $s[5]; ?>; border-left: 2px dotted #888480; margin: 0px 28px 20px 17px; padding: 2px 15px 2px 15px; line-height: 125%; text-align: justify; }
.partmain .end			{ text-align: left; clear: both; margin: 0px 20px 20px 0px; background: url('files/cssfiles/<?php echo $c; ?>_vbg0.jpg') top; padding: 17px 20px 30px 20px; }
.titlepartlinkR			{ color: #666; float: right; letter-spacing: 0px; }
.titlepartlinkR a, 		.titlepartlinkL a		{ color: <?php echo $s[5]; ?>; }
.titlepartlinkR a:hover,.titlepartlinkL a:hover	{ color: <?php echo $s[3]; ?>; }
.titlepartlinkL			{ color: #666; float: left; letter-spacing: 0px; }

.oneImageTitle			{ font-family: georgia, sans-serif; font-size: 20px; }
.wholePhoto				{ padding-left: 30px; }
.photoTheImg			{ text-align: center; margin-top: 5px; min-width: 300px; }
.photoTheImg img		{ margin: 5px 5px 30px 0px; border: 1px solid #000000; }
.divClear				{ clear: both; height: 1px; font-size: 1px; }
.photoBox				{ color: <?php echo $s[6]; ?>; border: 1px solid <?php echo $s[7]; ?>; background: url('files/cssfiles/<?php echo $c; ?>_hbg1.jpg') right repeat-y; line-height: 140%;  margin: 12px 17px 15px 0px; float:left; padding: 0px 15px 10px; width:170px; }
.photoBox a				{ color: <?php echo $s[5]; ?>; }
.photoBox a:hover		{ color: <?php echo $s[1]; ?>; }
.titlePhotoBox			{ color: <?php echo $s[4]; ?>; border: 1px solid <?php echo $s[8]; ?>; background: url('files/cssfiles/<?php echo $c; ?>_hbg0.jpg') left repeat-y; position: relative; padding: 2px 10px; top: -7px; left: -7px;  }
.spc					{ font-size: 1px; height: 10px; line-height: 0%; }

#headSlideshow 			{ text-align: center; margin: 2em 0px 0em; font-family: georgia; }
#headSlideshow .dt		{ font-family: georgia, serif; color: <?php echo $s[12]; ?>; font-size: 3em; position: relative; top: 0.2em; }
#slideShow #ss_link1	{ font-family: georgia, sans-serif; font-size: 15px; }
#ss_date 				{ color: <?php echo $s[1]; ?>; text-align: center; font-size: 11px; font-family: tahoma, sans-serif; }

select.rate 			{ background: <?php echo $s[9]; ?>; color: <?php echo $s[10]; ?>; border-color: <?php echo $s[8]; ?>; border-width: 1px; margin: 5px 5px 5px 5px;  font-size: 1em; font-family: tahoma; }
input.submit 			{ background: <?php echo $s[9]; ?>; color: #333; margin: 2px 0px; font-size: 1em; font-family: tahoma; padding: 0px; position: relative; top: -3px; border-color: #777; }
form 					{ margin: 0px; }
.hr						{ border-top: 1px solid #444; width: 80%; font-size: 1px; line-height: 0%; height: 1px; margin: 10px 0px 10px; }

.pvTitle				{ background: url('files/cssfiles/<?php echo $c; ?>_vbg1.jpg') bottom; padding: 10px 35px 15px; color: #888480; font-size: 1.6em; letter-spacing: 2px; font-weight: bold; }
.pvTitle a				{ color: #888; }
.pvTitle a:hover		{ color: #888; }
.pvTitle .dt			{ font-family: serif; color: #6F6860; font-size: 1.6em; }
.pvTitleInfo			{ float: right; font-size: 11px; font-weight: normal; margin: 10px 5px 0px; letter-spacing: 1px; }
.pvTitleInfo a,
.pvTitle .titleName a	{ color: <?php echo $s[5]; ?>; }
.pvTitleInfo a:hover,
.pvTitle .titleName a:hover { color: <?php echo $s[3]; ?>; }

.authFailed				{ width: 80%; margin: 10px auto; line-height: 140%; color: #999; }
.pvEnd					{ background: url('files/cssfiles/<?php echo $c; ?>_vbg0.jpg') top; padding: 15px 35px; color: #888; font-size: 1.6em; letter-spacing: 2px; font-weight: bold; }

.aThumb					{ margin: 5px 0px; background: <?php echo $s[0]; ?>;  overflow: hidden; width: 100px; height: 120px; float: left; }
.firstThumb				{ background: <?php echo $s[0]; ?>; clear: both; width: 100%; }
.aThumb img				{ border: 1px solid <?php echo $s[11]; ?>; margin: 5px; }
.aThumb a				{ color: <?php echo $s[13]; ?>; }
.aThumb a:hover			{ color: <?php echo $s[14]; ?>; }
.thumbDate				{ color: <?php echo $s[12]; ?>; font-size: 0.8em; margin: 2px 0px; }
.thumbNameLine			{ height: 14px; overflow: hidden; }

.small					{ font-size: 0.89em; color: #666462; letter-spacing: 1px; }
.dot 					{ color: #C60; padding-right: 4px; }
.darkdot 				{ color: #B72; padding-right: 5px; }
.reddot 				{ font-size: 14px; padding-right: 5px; color: #C30; }

.alert_msg				{ color: <?php echo $s[1]; ?>; letter-spacing: 1px; text-align: center; background: <?php echo $s[8]; ?>; width: 80%; margin: 5px auto 15px; padding: 10px 15px; border: 3px double  <?php echo $s[16]; ?>; }
.ok_msg					{ color: <?php echo $s[1]; ?>; letter-spacing: 1px; text-align: center; background: <?php echo $s[9]; ?>; width: 80%; margin: 5px auto 15px; padding: 10px 15px; border: 3px double <?php echo $s[16]; ?>; }
.ok_msg_inside			{ color: <?php echo $s[1]; ?>; text-align: justify; padding: 0px 10px 10px; border-left: 1px dashed #851; position: relative; top: 5px; left: 1px; }

.Commenting				{ color: <?php echo $s[14]; ?>; text-align: justify; width: 85%; margin: 0px 30px 20px 0px; padding-top: 0px; line-height: 125%; }
.Commenting a			{ color: <?php echo $s[5]; ?>; }
.Commenting a:hover		{ color: #555; }
.Commenting .title		{ color: <?php echo $s[15]; ?>; background: url('files/cssfiles/<?php echo $c; ?>_vbg1.jpg') bottom; padding: 15px 15px 25px; letter-spacing: 3px; font-size: 1.2em; text-align: left; margin: 0px; position: relative; top: 10px; }
.Commenting .title a	{ color: <?php echo $s[15]; ?>; }
.Commenting table		{ text-align: left; margin-left: auto; margin-right: auto; }
.Commenting input 		{ color: <?php echo $s[15]; ?>; border: 1px solid <?php echo $s[16]; ?>; padding: 1px 3px; font-size: 11px; font-family: tahoma, arial, helvetica, sans-serif; background: url('files/cssfiles/<?php echo $c; ?>_hbg0.jpg') left repeat-y; margin-top: 2px;}
.Commenting textarea	{ color: <?php echo $s[15]; ?>; border: 1px solid <?php echo $s[16]; ?>; background: url('files/cssfiles/<?php echo $c; ?>_vbg0.jpg') top; padding: 2px 5px;  margin-top: 2px; }
.Commenting .cell		{ position: relative; z-index: 10; padding: 10px 20px 5px; margin: 10px 0px 20px 0px; }
.Commenting .under		{ position: absolute; z-index: 2; top: 5px; border: 0px solid red; height: 80px; width: 100%; background: url('files/cssfiles/<?php echo $c; ?>_vbg1.jpg') repeat-x bottom center; }
.Commenting .bcell		{ background: <?php echo $s[0]; ?>; padding: 10px 20px 5px; margin: 10px 20px 20px; }
.Commenting blockquote 	{ color: <?php echo $s[15]; ?>; border-left: 1px dashed #C60; padding: 0px 20px 5px; margin: 5px 5px 10px 1px; line-height: 140%; }
.Commenting blockquote.r{ color: <?php echo $s[15]; ?>; border-left-width: 0px; border-right: 1px dashed #C60; direction: rtl; padding: 0px 20px 5px; margin: 5px 5px 10px 1px; line-height: 140%; }
.Commenting .head		{ position: relative; }
.Commenting .bottitle	{ background: url('files/cssfiles/<?php echo $c; ?>_vbg0.jpg') top; padding: 0px 0px 30px; margin: 0px; position: relative; top: -20px; }

input					{ color: <?php echo $s[15]; ?>; border: 1px solid <?php echo $s[16]; ?>; font-family: tahoma, arial, sans-serif; background: url('files/cssfiles/<?php echo $c; ?>_hbg0.jpg') left repeat-y; padding: 1px 3px; }
input.checkBox			{ background: <?php echo $s[0]; ?>; border: 1px solid #000000; }

.navigation				{ margin: 10px 25px 15px 20px; float: left; }
.navigation .title		{ color: <?php echo $s[14]; ?>; background: url('files/cssfiles/<?php echo $c; ?>_vbg1.jpg') bottom; padding: 10px 5px 10px 15px; letter-spacing: 3px; font-size: 1em; }
.navigation .bottitle	{ position: relative; top: -10px; clear: both; background: url('files/cssfiles/<?php echo $c; ?>_vbg0.jpg') top; padding: 0px 0px 20px; }

#jungleBox				{ width: 400px; height: 400px; margin: 5px auto; overflow: hidden; position: relative; z-index: 0; }
.aThumbInBox			{ background: #000000; overflow: hidden; width: 77px; height: 77px; position: absolute; }
.aThumbInBox img		{ border: 1px solid <?php echo $s[11]; ?>; }

.aStory					{ width: 85%; margin: 0px auto; }
.aStory	.titlepart		{ line-height: 150%; margin-bottom: 0px; padding-bottom: 5px; padding-top: 10px; }
.aStory	.titlepart a	{ color: <?php echo $s[1]; ?>; }
.aStory	.end			{ margin-top: 0px; padding-bottom: 15px; }
.aStory .thumbcntarr	{ color: <?php echo $s[17]; ?>; line-height: 150%; }
.aStory .end 			{ color: <?php echo $s[4]; ?>; }
.aStory .end a			{ color: <?php echo $s[5]; ?>; }
.aStory .end a:hover	{ color: <?php echo $s[6]; ?>; }

.thumb_not_found		{ color: black; width: 75px; height: 75px; border: 1px solid black; }
a.q, label 				{ cursor: pointer; }
input.radio				{ border-width: 0px; position: relative; top: 2px; }
.leaveReply				{ float: right; padding-top: 2px; font-size: 11px; letter-spacing: 0px; }

#ss_title				{ font-size: 1.2em; font-weight: bold; }
#ss_photo				{ border: 1px solid <?php echo $s[7]; ?>; }