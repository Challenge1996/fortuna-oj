<?php
	Header("Content-Type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Content-Length: " . filesize($file));
	Header("Content-Disposition: attachment; filename=$filename");
	fpassthru(fopen($file, 'rb'));
