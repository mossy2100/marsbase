<?php
function remote_file_info($url) {
	// returns an array which might look something like this:
	/*
	Array
	(
		[Date] => Fri, 11 Jun 2004 03:13:25 GMT
		[Server] => Apache/1.3.31 (Unix) mod_ssl/2.8.18 OpenSSL/0.9.6m PHP/4.3.6
		[Last-Modified] => Wed, 12 May 2004 05:52:31 GMT
		[ETag] => "4b4f5-129e-40a1bb9f"
		[Accept-Ranges] => bytes
		[Content-Length] => 4766
		[Connection] => close
		[Content-Type] => image/jpeg
	)
	*/
	// returns false if file not found

	$head = "";
	$url_p = parse_url($url);
	$fp = fsockopen($url_p["host"], 80, $errno, $errstr, 20);
	if (!$fp) {
		return false;
	}

	fputs($fp, "HEAD ".$url." HTTP/1.1\r\n");
	fputs($fp, "HOST: dummy\r\n");
	fputs($fp, "Connection: close\r\n\r\n");
	$headers = "";
	while (!feof($fp)) {
		$headers .= fgets($fp, 128);
	}
	fclose($fp);

	$return = false;
	$arr_headers = explode("\n", $headers);

	// if the file wasn't there, return false:
	if (trim($arr_headers[0]) == "HTTP/1.1 404 Not Found") {
		return false;
	}

	$result = array();
	foreach($arr_headers as $header) {
		$colonPos = strpos($header, ':');
		if ($colonPos !== false) {
			$key = substr($header, 0, $colonPos);
			$value = substr($header, $colonPos + 1);
			$result[trim($key)] = trim($value);
		}
	}
	return $result;
}

/**
 * Moves a file to a new location.
 * @param string $oldPath
 * @param string $newPath
 * @return bool
 */
function move_file($oldPath, $newPath) {
	if (copy($oldPath, $newPath))	{
		unlink($oldPath);
		return true;
	}
	return false;
}

function get_extension($filename) {
	// extracts the file extension (without dot, e.g. 'jpg') from the filename or path:
	$dotpos = strrpos($filename, ".");
	if ($dotpos === false) {// no extension
		return "";
	} else {
		return substr($filename, $dotpos + 1);
	}
}

function remove_extension($filename) {
	// returns the filename or path with the extension removed (including the dot)
	$dotpos = strrpos($filename, ".");
	if ($dotpos === false) { // no extension
		return $filename;
	}	else {
		return substr($filename, 0, $dotpos);
	}
}

function change_extension($filename, $new_ext) {
	return remove_extension($filename) . ".$new_ext";
}

/**
 * Similar to mkdir, except will create the whole sequence of subdirectories if required in order for $dir to exist.
 * Only works on Unix-type systems at this stage.
 * @todo make this work on Windows also.
 * @param string $src_path
 * @param string $dst_path
 * @return bool
 */
function mkdirpath($dir) {
  $dir = str_replace("\\", "/", $dir);
  $folders = explode("/", $dir);
  $dir2check = '';
  foreach ($folders as $folder) {
    $dir2check = $dir2check . '/' . $folder;
    if (file_exists($dir2check)) {
      if (!is_dir($dir2check)) {
        throw new Exception("copy_mkdir: $dir2check not a directory.");
      }
    } else {
      if (!mkdir($dir2check)) {
        return FALSE;
      }
    }
  }
  return TRUE;
}

/**
 * Same as copy, except if destination folder does not exist, it will be created.
 * Only works on Unix-type systems at this stage.
 * @todo make this work on Windows also.
 * @param string $src_path
 * @param string $dst_path
 * @return bool
 */
function copy_mkdir($src_path, $dst_path) {
  // ensure that the target folder exists:
  $pi = pathinfo($dst_path);
  mkdirpath($pi['dirname']);
  // copy the file:
  return copy($src_path, $dst_path);
}

/**
 * Completely empty a directory, including all subfolders. (like rm -r)
 * @param string $dir
 * @return bool
 */
function empty_dir($dir) {
  $files = scandir($dir);
  foreach ($files as $file) {
    if ($file == '.' || $file == '..') {
      continue;
    }
    $path = "$dir/$file";
    if (is_file($path)) {
      if (!unlink($path)) {
        return FALSE;
      }
    } else if (is_dir($path)) {
      if (!empty_dir($path)) {
        return FALSE;
      }
      if (!rmdir($path)) {
        return FALSE;
      }
    }
  }
  return TRUE;
}
