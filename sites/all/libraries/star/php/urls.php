<?php
/**
 * A bunch of useful functions for playing with URLs.
 *
 * Note: there is no support for backslash '\' directory separators, as used in Windows.
 * (Since Windows supports either, and Unix requires forward slashes '/',
 * as a rule of thumb always use forward slashes as path separators in both URLs and paths.)
 * Use convertSlashes() if necessary.
 *
 * @requires strings.php
 */


/**
 * Replaces backslashes in a string to forward slashes.
 *
 * @param string $path
 * @return string
 */
function convertSlashes($path) {
  return str_replace('\\', '/', $path);
}

/**
 * Get the current protocol.
 *
 * @return string
 */
function getProtocol() {
  // $_SERVER['SERVER_PROTOCOL'] looks something like 'HTTP/1.1'
  $protocol = $_SERVER['SERVER_PROTOCOL'];
  // remove the '/1.1':
  $p = strpos($protocol, '/');
  if ($p !== false) {
    $protocol = substr($protocol, 0, $p);
  }
  // make lower case:
  return strtolower($protocol);
}

/**
 * Get the current host name.
 */
function getHost($removeWww = FALSE) {
  $host = $_SERVER['HTTP_HOST'];
  if (strpos($host, "www.") === 0) {
    $host = substr($host, 4);
  }
  return $host;
}

/**
 * Finds the base URL of the host (querystring not included).
 * @return string
 */
function getBaseUrl() {
  $protocol = getProtocol();
  $host = getHost();
  $url = "$protocol://$host";
  return $url;
}

/**
 * Finds the document root of the host, with no trailing / or \.
 *
 * @return string
 */
function getDocumentRoot() {
  return rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
}

/**
 * Gets the full URL of the current script, i.e. result should match the browser's address bar.
 *
 * @return string
 */
function getCurrentUrl() {
  $url = getBaseUrl() . $_SERVER['REQUEST_URI'];
  return $url;
}

/**
 * Converts a local URL to a local path.  Returns false if not a local file.
 * @param string $url
 * @return string
 */
function url2path($url) {
  $docRoot = $_SERVER['DOCUMENT_ROOT'];
  $baseUrl = getBaseUrl();
  $url = removeQueryString($url);
  if (str_begins_with($url, $baseUrl)) {
    return str_replace($baseUrl, $docRoot, $url);
  }
  else {
    return $docRoot . ($url[0] == '/' ? '' : '/') . $url;
  }
}

/**
 * Converts a local path to a local URL.  Returns false if not a local file.
 * @param string $path
 * @return string
 */
function path2url($path) {
  $path = convertSlashes($path);
  $docRoot = getDocumentRoot();
  $baseUrl = getBaseUrl();
  if (str_begins_with($path, $docRoot)) {
    return str_replace($docRoot, $baseUrl, $path);
  }
  else {
    return false;
  }
}

/**
 * Removes the last part (from the last '/' forward) from a path or URL.
 * This could be the filename or a subfolder.
 *
 * @param string $path
 * @return string
 */
function removeLastPathPart($path) {
  $p = strrpos($path, '/');
  return $p === false ? '' : substr($path, 0, $p);
}

/**
 * Removes the first part (up to and including the first '/') from a path or URL.
 *
 * @param string $path
 * @return string
 */
function removeFirstPathPart($path) {
  $p = strpos($path, '/');
  return $p === false ? '' : substr($path, $p + 1);
}

/**
 * Resolves a relative URL into an absolute URL.
 *
 * @param string $url
 * @return string
 */
function resolveUrl($url) {
  $urlParts = parse_url($url);
  if (!$urlParts['scheme']) {
    // A relative URL:
    $currentUrl = getCurrentUrl();
    // remove the filename and querystring from the current URL:
    $dir = removeLastPathPart($currentUrl);
    while (str_begins_with($url, "../")) {
      $url = removeFirstPathPart($url);
      $dir = removeLastPathPart($dir);
    }
    $url = "$dir/$url";
  }
  return $url;
}

/**
 * Removes the querystring from a URL.
 *
 * @param string $url
 * @return string
 */
function removeQueryString($url) {
  $p = strrpos($url, '?');
  return $p === false ? $url : substr($url, 0, $p);
}

/**
 * Adds a querystring parameter to a URL.
 *
 * @param string $url
 * @param string $key
 * @param string $value
 * @param bool $encodeValue
 * @return string
 */
function addQueryStringParameter($url, $key, $value, $encodeValue = false) {
  if ($encodeValue) {
    $value = urlencode($value);
  }
  return $url . (inStr('?', $url) ? '&' : '?') . $key . '=' . $value;
}

/**
 * Parse the querystring from a URL.
 *
 * @param string $url
 * @return string
 */
function parseQueryString($url) {
  $parts = explode('?', $url);
  if (!$parts[1]) {
    return array();
  }
  parse_str($parts[1], $querystring);
  return $querystring;
}

/**
 * Returns code to include a given JavaScript file in a web page.
 * If this is a local file, appends a querystring parameter 'mtime' with the modified time of the file.
 * This will cause the browser to reload the file if it gets changed.
 *
 * @param string $url
 * @return string
 */
function getIncludeJavaScriptXhtml($url) {
  $url = resolveUrl($url);
  $path = url2path($url);
  if (file_exists($path)) {
    $mtime = filemtime($path);
    $url = addQueryStringParameter($url, 'mtime', $mtime);
  }
  $code = "<script src='$url'></script>\n";
  return $code;
}


/**
 * Prints the code to include a given JavaScript file in a web page.
 *
 * @param string $url A relative or absolute URL to the JavaScript file.
 */
function includeJavaScript($url) {
  $code = getIncludeJavaScriptXhtml($url);
  echo $code;
}

/**
 * Returns code to include a given CSS file in a web page.
 * If this is a local file, appends a querystring parameter 'mtime' with the modified time of the file.
 * This will cause the browser to reload the file if it gets changed.
 *
 * @param string $url
 * @return string
 */
function getIncludeCssXhtml($url) {
  $url = resolveUrl($url);
  $path = url2path($url);
  if (file_exists($path)) {
    $mtime = filemtime($path);
    $url = addQueryStringParameter($url, 'mtime', $mtime);
  }
  $code = "<link type='text/css' rel='stylesheet' href='$url' />\n";
  return $code;
}

/**
 * Prints the code to include a given CSS file in a web page.
 *
 * @param string $url A relative or absolute URL to the CSS file.
 */
function includeCss($url) {
  $code = getIncludeCssXhtml($url);
  echo $code;
}

/**
 * Look and see if this page has been requested by itself.
 * Compares path as well as query string.
 */
function selfRequest() {
  // get request info:
  $request_uri = parse_url($_SERVER['REQUEST_URI']);
//  debug($request_uri);
  $request['path'] = $request_uri['path'];
  $request['host'] = $request_uri['host'] ? $request_uri['host'] : $_SERVER['HTTP_HOST'];
  parse_str($request_uri['query'], $request['query']);
  ksort($request['query']);
//  debug($request);

  // get referer info:
  if (isset($_SERVER['HTTP_REFERER'])) {
    $referer_uri = parse_url($_SERVER['HTTP_REFERER']);
//    debug($referer_uri);
    $referer['path'] = $referer_uri['path'];
    $referer['host'] = $referer_uri['host'] ? $referer_uri['host'] : $_SERVER['HTTP_HOST'];
    parse_str($referer_uri['query'], $referer['query']);
    ksort($referer['query']);
  }
  else {
    $referer = NULL;
  }
//  debug($referer);

  return $request === $referer;
}

/**
 * Check if the provided value is a valid domain/subdomain/host name.
 * Does not check length. Does not check if it starts with a letter.
 *
 * @param string $name
 * @return bool
 */
function is_domain_name($name) {
  $rx = "/^([a-z0-9\-]+\.)+[a-z]{2,6}$/i";
  return preg_match($rx, $name) == 1;
}

/**
 * Check if a URL includes only a host, i.e. no path, query, fragment, etc.
 * Only supports http:// and https://
 *
 * @param string $url
 * @return string|bool
 *   Returns the host name (with no http or other cruft) or FALSE.
 */
function url_is_host_only($url) {
  $host = trim_http(rtrim(trim($url), '/'));
  return is_domain_name($host) ? $host : FALSE;
}

/**
 * Check if a URL matches a host.
 * Allows scheme (http, https or nothing), host, port, query, and/or fragment.
 *
 * @param string $url
 * @param string $host
 * @return bool
 */
function url_matches_host($url, $host) {
  return (bool) preg_match("|^(https?\://)?([a-z0-9\-]+\.)*$host([\:\/\?\#].*)?$|", $url);
}

/**
 * Get just the host from a URL.
 *
 * @param string $url
 * @return string
 */
function url_get_host($url) {
  $n_matches = preg_match("|^(?:https?\://)?((?:[a-z0-9\-]+\.)+[a-z]{2,6})(?:[\:\/\?\#].*)?$|", $url, $matches);
  return $n_matches ? $matches[1] : FALSE;
}
