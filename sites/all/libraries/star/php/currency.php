<?php
/**
 * Contains function to convert currencies using webservicex.
 * @requires curl.php
 */


function convert_currency($amount, $from_currency, $to_currency) {
  $url = "http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency=$from_currency&ToCurrency=$to_currency";
	$result = downloadUrl($url);
	$rate = strip_tags($result);
	return $amount * $rate;
}

function downloadUrl($url) {
  // is curl installed?
  if (!function_exists('curl_init')) {
    die('CURL is not installed!');
  }
  // create a new curl resource
  $ch = curl_init();
  /*
   Here you find more options for curl:
   http://www.php.net/curl_setopt
   */
  // set URL to download
  curl_setopt($ch, CURLOPT_URL, $url);
  // remove header? 0 = yes, 1 = no
  curl_setopt($ch, CURLOPT_HEADER, 0);
  // should curl return or print the data? true = return, false = print
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // timeout in seconds
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  // download the given URL, and return output
  $output = curl_exec($ch);
  // close the curl resource, and free system resources
  curl_close($ch);
  // print output
  return $output;
}
