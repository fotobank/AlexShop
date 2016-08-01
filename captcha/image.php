<?php

	header('Content-type: image/jpeg');

	session_start();

// image config

	$code = random_int(10000,99999);
	$color_r = $color_g = $color_b = 150;

	$_SESSION['captcha_code'] = $code;

	$bg_image = 'blank.jpg';
	$font = './maturasc.ttf';

	$size = 14+random_int(0,10);
	$rotation = random_int(-5,10);
	$pad_x = 50-2*$size;
	$pad_y = 30;

// generate image

	$img_path	= $bg_image;
	$img_size	= getimagesize($img_path);

    list($width, $height) = $img_size;

	$img = imagecreatefromjpeg($img_path);
	$fg = imagecolorallocate($img, $color_r, $color_g, $color_b);

	$a_z = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$bgcode = $a_z[random_int(0, strlen($a_z))];
	$bgrotation = random_int(-30, 30);
	imagettftext($img, 70, $bgrotation, $pad_x+1, $pad_y+5+1, $fg, $font, $bgcode);
	imagettftext($img, 70, $bgrotation, $pad_x-1, $pad_y+5-1, $fg, $font, $bgcode);
	imagettftext($img, 70, $bgrotation, $pad_x, $pad_y+5, imagecolorallocate($img, 255, 255, 255), $font, $bgcode);

	imagettftext($img, $size, $rotation, $pad_x+1, $pad_y+1, $fg, $font, $code);
	imagettftext($img, $size, $rotation, $pad_x-1, $pad_y-1, $fg, $font, $code);
	imagettftext($img, $size, $rotation, $pad_x, $pad_y, imagecolorallocate($img, 255, 255, 255), $font, $code);

	// добавить шум
	/*$dots = $width*$height/8;
	for($i=0;$i<$dots;$i++)
	{
		$dc = imagecolorallocate($img, $color_r, $color_g, $color_b);
		imagesetpixel($img, random_int(0,$width), random_int(0,$height), $dc);
	}*/

	
	imagejpeg($img);