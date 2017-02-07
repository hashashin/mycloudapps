<?php
    header("Content-type: image/gif");
    $im     = imagecreate(100, 20);
    $trans = imagecolorallocate($im, 0xFF, 0x00, 0xFF);
    $black = imagecolorallocate($im, 0x00, 0x00, 0x00);
    $white = imagecolorallocate($im, 0xEE, 0xEE, 0xEE);
    $gray  = imagecolorallocate($im, 0x88, 0x88, 0x88);

    imagefill($im, 0, 0, $trans);
	imagecolortransparent($im, $trans);

	require_once('funcs.php');
	$basis=array();
	parse_container('basis', 'Basis', 'data/basis.xml');

	$string = $basis['wvw'];

    for ($i=0, $p = -4; $i<strlen($string); $i++) {
    	$x = $p += 7+rand(1, 10);
    	$y = rand(0, 4);
	    imagestring($im, 5, $x, $y, $string[$i], $black);
	    imagestring($im, 5, $x+2, $y+0, $string[$i], $black);
	    imagestring($im, 5, $x+2, $y+2, $string[$i], $black);
	    imagestring($im, 5, $x+0, $y+0, $string[$i], $black);
	    imagestring($im, 5, $x+0, $y+2, $string[$i], $black);
	    imagestring($im, 5, $x+1, $y+1, $string[$i], $white);
	}

/**
	$len = 10;
	$n = 0;
	for ($i=0; $i<$n; $i++) {
		$sx = rand(-$len, $len);
		$sy = rand(-$len, $len);
		$x = rand($sx, imagesx($im)-$sx);
		$y = rand($sy, imagesy($im)-$sy);
		imageline($im, $x, $y, $x+$sx, $y+$sy, $i%2?$black:$white);
	}
/**/

    imagegif($im);
    imagedestroy($im);
?>