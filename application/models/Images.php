<?php

class Images extends CI_Model
{
	function genThumbnail($target_file)
	{
		$file_parts = pathinfo($this->security->sanitize_filename($target_file));
		$extension = $file_parts['extension'];

		$info = getimagesize($target_file);
		$ratio = min(225 / $info[0], 300 / $info[1]);
		$width = (int)($ratio * $info[0]);
		$height = (int)($ratio * $info[1]);

		switch ($info[2]) {
			case 1:	$image = imagecreatefromgif($target_file); break;
			case 2:	$image = imagecreatefromjpeg($target_file); break;
			case 3:	$image = imagecreatefrompng($target_file); break;
		}

		$resized = imagecreatetruecolor($width, $height);
		imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

		switch ($info[2]) {
			case 1:	imagegif($resized, $target_file); break;
			case 2:	imagejpeg($resized, $target_file, 100); break;
			case 3:	imagepng($resized, $target_file); break;
		}

		$ratio = min(60 / $info[0], 80 / $info[1]);
		$width = (int)($ratio * $info[0]);
		$height = (int)($ratio * $info[1]);

		$resized = imagecreatetruecolor($width, $height);
		imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

		if (!is_dir('/tmp/foj')) mkdir('/tmp/foj');
		$target_file = '/tmp/foj/' . rand() . '.' . $extension;
		switch ($info[2]) {
			case 1:	imagegif($resized, $target_file); break;
			case 2:	imagejpeg($resized, $target_file, 100); break;
			case 3:	imagepng($resized, $target_file); break;
		}

		$encoded = chunk_split(base64_encode(file_get_contents($target_file)));
		switch ($info[2]) {
			case 1:	$encoded = 'data:image/gif;base64,' . $encoded; break;
			case 2:	$encoded = 'data:image/jpeg;base64,' . $encoded; break;
			case 3:	$encoded = 'data:image/png;base64,' . $encoded; break;
		}
		return $encoded;
	}
}
