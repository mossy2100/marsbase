<?php

/**
 * Convert a few specific html unicode character entities into similar ASCII characters
 * (specifically: long hyphens, fancy quotes, bullets, ellipses, break tags)
 * @param string $str
 * @return string
 */
function convertHtmlEntities($str) {
  $entities = array('&#8211;', '&#8217;', '&#8220;', '&#8221;', '&#8226;', '&#8230;', '<br>');
  $ascii = array(   '-',       "'",       '�',       '�',       '*',       '...',     "\n");
  return str_replace($entities, $ascii, $str);
}

/**
 * Converts every character in the string to a string of numerical unicode html entities:
 *
 * @param string $str
 * @return string
 */
function htmlEntitiesAll($str) {
  $result = "";
  for ($i = 0; $i < strlen($str); $i++)
    $result .= "&#".ord($str[$i]).";";
  return $result;
}

/**
 * Map for converting Windows/MS Word characters into HTML entities or UTF-8 characters.
 * Created because htmlentities($ch, ENT_NOQUOTES, 'cp1252') is/was incomplete
 * (doesn't have 142, 158 and 159)
 * @return array
 *
 * For Word characters (cp1252 aka Windows-1252) - use for ord 128-159
 * @see http://en.wikipedia.org/wiki/Windows-1252
 * For HTML Latin character entities (ord 160-255)
 * @see http://htmlhelp.com/reference/html40/entities/latin1.html
 * For ISO/IEC 8859-1
 * @see http://en.wikipedia.org/wiki/ISO/IEC_8859-1
 * For Latin Extended-A
 * @see http://www.alanwood.net/unicode/latin_extended_a.html
 */
function get_cp1252_map() {
  /*
   * Fields in the arrays:
   * 0 => UTF-16 Code Point
   * 1 => HTML Character Entity
   * 2 => UTF-8 Character
   */
  return array(
    128 => array(0x20AC,  '&euro;',   '�'),
    130 => array(0x201A,  '&sbquo;',  '�'),
    131 => array(0x0192,  '&fnof;',   '�'),
    132 => array(0x201E,  '&bdquo;',  '�'),
    133 => array(0x2026,  '&hellip;', '�'),
    134 => array(0x2020,  '&dagger;', '�'),
    135 => array(0x2021,  '&Dagger;', '�'),
    136 => array(0x02C6,  '&circ;',   '�'),
    137 => array(0x2030,  '&permil;', '�'),
    138 => array(0x0160,  '&Scaron;', '�'),
    139 => array(0x2039,  '&lsaquo;', '�'),
    140 => array(0x0152,  '&OElig;',  '�'),
    142 => array(0x017D,  '&#381;',   '�'),
    145 => array(0x2018,  '&lsquo;',  '�'),
    146 => array(0x2019,  '&rsquo;',  '�'),
    147 => array(0x201C,  '&ldquo;',  '�'),
    148 => array(0x201D,  '&rdquo;',  '�'),
    149 => array(0x2022,  '&bull;',   '�'),
    150 => array(0x2013,  '&ndash;',  '�'),
    151 => array(0x2014,  '&mdash;',  '�'),
    152 => array(0x02DC,  '&tilde;',  '�'),
    153 => array(0x2122,  '&trade;',  '�'),
    154 => array(0x0161,  '&scaron;', '�'),
    155 => array(0x203A,  '&rsaquo;', '�'),
    156 => array(0x0153,  '&oelig;',  '�'),
    158 => array(0x017E,  '&#382;',   '�'),
    159 => array(0x0178,  '&Yuml;',   '�'),
  );
}

/**
 * Converts strings pasted from MS Windows/Word (Windows-1252)
 * into ASCII with HTML character entities.
 *
 * This was my original version, now not needed.
 * Only difference is that it will remove invalid code points (141, 143, 144, etc.)
 * whereas the new version will leave them in place.
 *
 * @param string $str
 * @return string
function cp1252_to_html($str) {
  $str2 = '';
  // Word characters:
  $map1252 = get_cp1252_map();
  // latin characters - this covers all from 160-255:
  $mapIso = get_html_translation_table(HTML_ENTITIES);
  for ($i = 0; $i < strlen($str); $i++) {
    $ch = $str[$i];
    $j = ord($ch);
    if ($j <= 127) {
      // ASCII, use as-is:
      $str2 .= $ch;
    }
    else if ($j <= 159) {
      // Windows-1252, use character map:
      $str2 .= $map1252[$j] ? $map1252[$j][1] : '';
    }
    else {
      // ISO/IEC 8859-1, use HTML entity:
      $str2 .= $mapIso[$ch];
    }
  }
  return $str2;
}
*/

/**
 * Converts strings pasted from MS Windows/Word (Windows-1252)
 * into ASCII with HTML character entities.
 * @param string $str
 * @return string
 */
function cp1252_to_html($str) {
  return utf8_to_html(cp1252_to_utf8($str));
}

/**
 * Converts strings pasted from MS Windows/Word (Windows-1252) into UTF-8
 * @param string $str
 * @return string
 */
function cp1252_to_utf8($str) {
  return mb_convert_encoding($str, 'UTF-8', 'windows-1252');
}

/**
 * Convert a string encoded as UTF-8 to ASCII with HTML character entities.
 * @param string $str
 * @return string
 */
function utf8_to_html($str) {
  return htmlentities($str, ENT_NOQUOTES, 'UTF-8');
}

/**
 * Convert a string encoded as ISO-8859-1 (aka Latin-1) to into UTF-8.
 * @param string $str
 * @return string
 */
function latin_to_utf8($str) {
  return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
}

/**
 * Convert a string encoded as UTF-8 to ASCII with HTML character entities.
 * @param string $str
 * @return string
 */
function html_to_utf8($str) {
  return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Convert a string to UTF-8.
 *
 * List of most popular character encodings:
 * @see http://w3techs.com/technologies/overview/character_encoding/all
 *
 * @param string $str
 * @param string $orig_encoding
 * @return string
 */
function convert_to_utf8($str, &$orig_encoding = NULL) {
  if (!$str) {
    return '';
  }

  // These are the popular encodings used on the web by more than 0.1% of websites, which are also supported by the
  // multibyte extension.
  // A better version of this function would use iconv(), which supports many more encodings.
  $encodings = array(
    'UTF-8',
    'ISO-8859-1',
    'Windows-1251',
//    'GB2312', // Not supported by mb_string extension
    'EUC-CN', // Alternative to GB2312
    'SJIS',
    'Windows-1252',
//    'GBK', // Not supported by mb_string extension
    'ISO-8859-2',
    'EUC-JP',
//    'Windows-1256', // Not supported by mb_string extension
    'ISO-8859-15',
    'ISO-8859-9',
    'EUC-KR',
//    'Windows-1250', // Not supported by mb_string extension
//    'Windows-1254', // Not supported by mb_string extension
    'BIG-5',
//    'Windows-874', // Not supported by mb_string extension
    'ASCII',
//    'TIS-620', or 'ISO-8859-11', // Not supported by mb_string extension
  );

  // Detect the encoding of the string:
  $orig_encoding = mb_detect_encoding($str, $encodings, TRUE);

  // Convert to UTF-8 if the encoding was successfully detected:
  return ($orig_encoding && $orig_encoding != 'UTF-8') ? mb_convert_encoding($str, 'UTF-8', $orig_encoding) : $str;
}

/**
 * Remove diacritics from letters in a string.
 * @todo add handling for character codes here http://www.alanwood.net/unicode/latin_extended_a.html
 * @param string $text
 * @return string
 */
function remove_diacritics($text) {
  $text = cp1252_to_html($text);
  echo $text;
  $map = array(
    '/&(.)(acute|grave|caron|circ|tilde|uml|ring|cedil|slash);/' => '$1',
    '/&szlig;/' => 'ss',
    '/&(..)lig;/' => '$1',
    '/&ETH;/' => 'D',
    '/&eth;/' => 'd',
    '/&THORN;/' => 'P',
    '/&thorn;/' => 'p',
    '/&#381;/' => 'Z',
    '/&#382;/' => 'z',
  );
  $text = preg_replace(array_keys($map), array_values($map), $text);
  return $text;
}

/**
 * Converts some html entities into simpler ASCII equivalents.
 * @param string $text
 * @return string
 */
function simplify_chars($text) {
  $text = cp1252_to_html($text);
  $map = array(
    '&fnof;' => 'f',
    '&hellip;' => '...',
    '&circ;' => '^',
    '&lsquo;' => "'",
    '&rsquo;' => "'",
    '&ldquo;' => '"',
    '&rdquo;' => '"',
    '&bull;' => '-',
    '&ndash;' => '-',
    '&mdash;' => '-',
    '&tilde;' => '~',
    '&trade;' => 'TM',
    '&copy;' => '(c)',
  );
  $text = str_replace(array_keys($map), array_values($map), $text);
  return $text;
}

/**
 * Convert lists created from UTF-8 bullets and break tags in a block of HTML into <ul>'s.
 * (You might like to run something like cp1252_to_utf8() or html_to_utf8() first to convert all
 * bullets to UTF-8)
 * @param string $value
 * @return string
 */
function convert_bullets_to_lists($value) {
  // Look for two different Unicode bullets:
  $bullet1 = chr(226) . chr(128) . chr(162);
  $bullet2 = chr(194) . chr(183);

  // break into list items:
  $parts3 = explode($bullet1, $value);
  $parts = array();
  foreach ($parts3 as $part) {
    $parts = array_merge($parts, explode($bullet2, $part));
  }

  // reassemble:
  $in_list = FALSE;
  $str = $parts[0];
  if (count($parts) > 1) {
    for ($i = 1; $i < count($parts); $i++) {
      if (!$in_list) {
        $str .= "<ul>\n";
        $in_list = TRUE;
      }
      // split on break tags:
      $parts[$i] = str_replace(array("<br>", "<BR>"), "<br />", $parts[$i]);
      $parts2 = explode("<br />", $parts[$i]);

      // trim the parts:
      foreach ($parts2 as $key2 => $value2) {
        $parts2[$key2] = trim($value2);
      }

      // debug($parts2);

      $list_item = $parts2[0];
      // add the list item:
      $str .= "<li>$list_item</li>\n";
      // add any text after the list:

      // If there are any other parts in the array that aren't just whitespace,
      // it's the end of the list:
      if (count(array_filter($parts2)) > 1) {
        $str .= "</ul>\n" . implode("<br />\n", array_slice($parts2, 1));
        $in_list = FALSE;
      }
    }
    if ($in_list) {
      $str .= "</ul>\n";
    }
  }
  return $str;
}
