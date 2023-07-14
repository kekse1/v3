<?php

// 
// Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
// 

//
define('KEKSE_RAW', true);
require_once('./count.php');

//
define('KEKSE_RESIZE_VERSION', '0.0.1');
define('KEKSE_RESIZE_WEBSITE', 'https://github.com/kekse1/resize.php/');
if(!defined('KEKSE_COPYRIGHT')) define('KEKSE_COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');

//
\kekse\error('TODO! ;-)');

//
//bedenke unbedingt SECURITY, s. count.php
//
//example, vorbereitung:
//
/*

// mime_content_type(path) => < https://www.php.net/mime_content_type >
//

//
//BITTE: immer den type-string nur bis zum ersten ";" semikolon (ausschlieszlich), wegen attributen (charset, etc..)!!
//
function getImageFunction($mime_type) {
  switch ($mime_type) {
    case 'image/jpeg':
      return 'imagejpeg';
    case 'image/png':
      return 'imagepng';
    case 'image/gif':
      return 'imagegif';
    default:
      throw new Exception('Unsupported MIME type: ' . $mime_type);
  }
}

$mime_type = mime_content_type('image.jpg');
$image_function = getImageFunction($mime_type);

header('Content-Type: ' . $mime_type);
$image_function($image);


function resizeImage($image, $width, $height) {
  // Get the original image size
  $origWidth = imagesx($image);
  $origHeight = imagesy($image);

  // Calculate the new image size
  $newWidth = $width;
  $newHeight = $height;
  if ($origWidth > $origHeight) {
    $newHeight = round($newWidth * $origHeight / $origWidth);
  } else {
    $newWidth = round($newHeight * $origWidth / $origHeight);
  }

  // Create a new image resource
  $newImage = imagecreatetruecolor($newWidth, $newHeight);

  // Copy the original image to the new image
  imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

  // Return the new image resource
  return $newImage;
}

function readAndOutputImageFile()
{
	//
	//TODO: BUFFERED CHUNK READING, pls!!
	//
	$image_file = fopen('image.jpg', 'r');
	$image_data = fread($image_file, filesize('image.jpg'));
	fclose($image_file);

	$image_mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $image_file);

	header('Content-Type: ' . $image_mime_type);

	echo $image_data;
}

// interessant: statt original mime-type kann ich ja direkt (immer?) 'text/png' verwenden, bspw.! :-D
function createImageFromFileData()
{
	$image = imagecreatefromstring($image_data);
	$new_image = resizeImage($image, 100, 100);

	header('Content-Type: image/jpeg');
	imagejpeg($new_image);
}


*/

?>
