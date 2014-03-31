<?php
/**
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 */
function get_web_page($url) {
  $options = array(
    CURLOPT_RETURNTRANSFER => TRUE,     // return web page
    CURLOPT_HEADER         => FALSE,    // don't return headers
    CURLOPT_FOLLOWLOCATION => TRUE,     // follow redirects
    CURLOPT_ENCODING       => "",       // handle all encodings
    CURLOPT_USERAGENT      => "spider", // who am i
    CURLOPT_AUTOREFERER    => TRUE,     // set referer on redirect
    CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
    CURLOPT_TIMEOUT        => 120,      // timeout on response
    CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
  );
  $ch = curl_init($url);
  curl_setopt_array($ch, $options);
  $content = curl_exec($ch);
  $err = curl_errno($ch);
  $errmsg = curl_error($ch);
  $header = curl_getinfo($ch);
  curl_close($ch);
  $header['errno']   = $err;
  $header['errmsg']  = $errmsg;
  $header['content'] = $content;
  return $header;
}

/**
 * Convert currencies using webservicex.
 * @param float $amount
 * @param string $from_currency
 * @param string $to_currency
 * @return float
 */
function convert_currency($amount, $from_currency, $to_currency) {
  $url = "http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency=$from_currency&ToCurrency=$to_currency";
  $result = get_web_page($url);
  if ($result['errno']) {
    return FALSE;
  }
  $rate = strip_tags($result['content']);
  return $amount * $rate;
}
