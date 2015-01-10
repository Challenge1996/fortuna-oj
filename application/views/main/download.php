<?php
	Header("Content-Type: $filetype");
	Header("Accept-Ranges: bytes");
	Header("Content-Length: " . filesize($file));
	Header("Content-Disposition: filename=$filename");
	readfile($file);
