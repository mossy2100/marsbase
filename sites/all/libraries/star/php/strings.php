<?php
/**
 * Useful string functions.
 *
 * @author Shaun Moss <shaun@astromultimedia.com>
 */

/**
 * Constant for the convert_case() function.
 *
 * Note: CASE_LOWER and CASE_UPPER defined in PHP core as follows:
 *   const CASE_LOWER = 0;
 *   const CASE_UPPER = 1;
 */
const CASE_CAMEL = 2;
const CASE_TITLE = 3;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Bonus echo functions.

/**
 * Echo a string with a newline and optionally wrapped in tags.
 *
 * @param string $str
 * @param string $tag
 */
function echoln($str = '', $tag = NULL) {
  if ($tag) {
    $str = "<$tag>$str</$tag>";
  }
  echo "$str\n";
}

/**
 * Echo a string (optionally wrapped in tags) with a break tag and a newline.
 *
 * @param string $str
 * @param string $tag
 */
function echobr($str = '', $tag = NULL) {
  if ($tag) {
    $str = "<$tag>$str</$tag>";
  }
  echo "$str<br>\n";
}

/**
 * Echo a horizontal rule.
 */
function echohr() {
  echo "<hr>\n";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Append functions.

/**
 * Append one string to another.
 *
 * @param string $str
 * @param string $str_to_append
 */
function append(&$str, $str_to_append = '') {
  $str .= $str_to_append;
}

/**
 * Append one string with a newline to another string, i.e. append a line.
 *
 * @param string $str
 * @param string $str_to_append
 */
function appendln(&$str, $str_to_append = '') {
  $str .= "$str_to_append\n";
}

/**
 * Append one string with a break tag and a newline to another string, i.e. append an HTML line.
 *
 * @param string $str
 * @param string $str_to_append
 */
function appendbr(&$str, $str_to_append = '') {
  $str .= "$str_to_append<br>\n";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Probably don't need this one any more.
function extractFilename($path) {
  $filename = $path;
  $n = strrpos($path, "/");
  if ($n !== FALSE)
    $filename = substr($filename, $n + 1);
  return $filename;
}

/**
 * Checks if a character is a vowel.
 *
 * @param string $ch
 * @return bool
 */
function isVowel($ch) {
  return in_array(strtolower($ch), array("a", "e", "i", "o", "u"));
}

/**
 * Checks if a character is a consonant.
 *
 * @param string $ch
 * @return bool
 */
function isConsonant($ch) {
  return ctype_alpha($ch) && !isVowel($ch);
}


function plural($str, $n = 0, $returnNum = FALSE) {
  // if $n == 1, returns $str (which should be singular form)
  // if $n != 1, returns the plural form of $str
  // Please note this function covers most but not all English plurals.
  if ($n == 1) {
    $result = $str;
  }
  else {
    // find plural form:
    $len = strlen($str);
    $lastCh = $str{$len - 1};
    $secondLastCh = $str{$len - 2};
    $last2Chars = $lastCh.$secondLastCh;
    if ($lastCh == ".")
    {
      // it's an abbreviation, no change:
      $result = $str;
    }
    else if ($last2Chars == 'is') // e.g. synopsis -> synopses
    {
      // change 'is' to 'es':
      $result = substr($str, 0, $len - 2).'es';
    }
    else if (
      in_array($lastCh, array('s', 'z', 'x')) ||
      in_array($last2Chars, array('ch', 'sh')) ||
      in_array($str, array('echo', 'embargo', 'hero', 'potato', 'tomato', 'torpedo', 'veto')))
    {
      // add 'es':
      $result = $str.'es';
    }
    else if ($lastCh == 'f') // e.g. elf
    {
      // change 'f' to 'ves':
      $result = substr($str, 0, $len - 1).'ves';
    }
    else if ($last2Chars == 'fe') // e.g. life
    {
      // change 'fe' to 'ves':
      $result = substr($str, 0, $len - 2).'ves';
    }
    else if ($lastCh == "y" && !isVowel($secondLastCh))
    {
      // ends in a consonant followed by 'y', change to 'ies':
      $result = substr($str, 0, $len - 1)."ies";
    }
    else
    {
      // most other cases, add 's':
      $result = $str."s";
    }
  }
  // return result:
  if ($returnNum)
    return $n.' '.$result;
  else
    return $result;
}

/**
 * Replaces Unix (\r\n) and old Mac (\r) newlines with Windows newlines (\n).
 *
 * @param string $str
 * @return string
 */
function simpleNewlines($str) {
  $str = str_replace("\r\n", "\n", $str);
  $str = str_replace("\r", "\n", $str);
  return $str;
}

/**
 * Converts all newlines, whether from Windows, Mac or Unix, into HTML break tags plus \n.
 *
 * @param string $str
 * @return string
 */
function nl2brs($str) {
  return str_replace("\n", "<br>\n", simpleNewlines($str));
}

/**
 * This function is handy for addresses that have been entered into a multiline textbox,
 * which you want to convert to a string with no newlines or breaks.
 *
 * @param string $str
 * @return string
 */
function nl2commas($str) {
  return str_replace(array(",\n", "\n"), ', ', simpleNewlines($str));
}

/**
 * Backslashes newlines and carriage returns. Useful for outputting strings to JavaScript.
 *
 * e.g.
 *     echo "var str = '".nl2slashn("This string has\nlinefeeds in it.")."';";
 * is the same as
 *     echo "var str = 'This string has\\nlinefeeds in it.';";
 * which renders in JavaScript as
 *     var str = 'This string has\nlinefeeds in it.';
 *
 * @param string $str
 * @return string
 */
function nl2slashn($str) {
  $str = str_replace("\n", "\\n", $str);
  $str = str_replace("\r", "\\r", $str);
  $str = str_replace("\t", "\\t", $str);
  return $str;
}


function addslashes_nl($str) {
  // same as addslashes but also converts newlines and carriage returns to backslash codes:
  return nl2slashn(addslashes($str));
}

/**
 * Removes all characters from a string that do not match the specified char type.
 *
 * @see http://www.php.net/manual/en/book.ctype.php
 *
 * @param string $str
 * @param string $ctype
 *   Matches one of the ctype functions: 'alnum', 'alpha', 'cntrl', 'digit', 'graph', 'lower',
 *     'print', 'punct', 'space', 'upper', 'xdigit'.
 * @return string
 */
function stripNonMatchingChars($str, $ctype) {
  $str = (string) $str;
  if ($str == '') {
    return '';
  }
  $result = '';
  $fn = 'ctype_' . $ctype;
  for ($i = 0; $i < strlen($str); $i++)  {
    if ($fn($str[$i])) {
      $result .= $str[$i];
    }
  }
  return $result;
}

/**
 * Removes all non-digit characters from a string.
 *
 * @param string $str
 * @return string
 */
function stripNonDigits($str) {
  return stripNonMatchingChars($str, 'digit');
}

/**
 * Removes all non-letters from a string.
 *
 * @param string $str
 * @return string
 */
function stripNonAlpha($str) {
  return stripNonMatchingChars($str, 'alpha');
}

/**
 * Removes all non-alphanumeric characters from a string.
 *
 * @param string $str
 * @return string
 */
function stripNonAlnum($str) {
  return stripNonMatchingChars($str, 'alnum');
}

/**
 * Removes all whitespace characters from a string.
 *
 * @param string $str
 * @return string
 */
function stripWhitespace($str) {
  return stripNonMatchingChars($str, 'space');
}

/**
 * Returns true if $str contains one or more digits.
 *
 * @param string $str
 * @return bool
 */
function containsDigits($str) {
  for ($i = 0; $i < strlen($str); $i++)  {
    if (ctype_digit($str{$i})) {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * Splits $name into:
 *     - social title
 *     - first name
 *     - middle name(s)
 *     - last name (including nobiliary particles)
 * Note, this function is designed for western-style names,
 * i.e. it is not suited for names that begin with the family name, e.g. Chinese
 *
 * @todo Add support for middle name.
 * @todo Add support for Jr./Sr.
 * @todo Add support for roman numerals following name (i.e. Charles Emerson Winchester III)
 *
 * @param string $name
 * @param string $title
 * @param string $firstName
 * @param string $middleName
 * @param string $lastName
 */
function splitName($name) {
  // social titles:
  $socialTitles = array('mr', 'mrs', 'miss', 'ms', 'dr', 'prof');

  // words that belong in the surname:
  $nobiliaryParticles = array('a', 'bat', 'ben', 'bin', 'da', 'das', 'de', 'del', 'della', 'dem',
    'den', 'der', 'des', 'di', 'do', 'dos', 'du', 'ibn', 'la', 'las', 'le', 'li', 'lo', 'mac',
    'mc', 'op', "'t", 'te', 'ten', 'ter', 'van', 'ver', 'von', 'y', 'z', 'zu', 'zum', 'zur');

  // parse name into words:
  $names = explode(' ', $name);
  foreach ($names as $key => $name) {
    $names[$key] = trim($name);
  }
  $names = array_filter($names);

  // how many?
  $nNames = count($names);

  // look for title:
  $title = '';
  foreach ($socialTitles as $st) {
    if (strtolower($names[0]) == $st || strtolower($names[0]) == $st . '.') {
      $title = array_shift($names);
      $nNames--;
      // remove the full-stop if present:
      if (str_ends_with($title, '.')) {
        $title = substr($title, 0, strlen($title) - 1);
      }
      break;
    }
  }

  if ($nNames == 1)  {
    // only one word:
    if ($title) {
      // if there's a title, assume that the name is the last name:
      $lastName = $names[0];
    }
    else {
      // assume it's the first name:
      $firstName = $names[0];
    }
  }
  else {
    // go through names from right to left building the surname:
    $firstName = $names[0];
    $lastName = &$names[$nNames - 1];
    for ($i = $nNames - 2; $i >= 0; $i--) {
      if (in_array(strtolower($names[$i]), $nobiliaryParticles)) {
        $lastName = $names[$i] . ' ' . $lastName;
        unset($names[$i]);
      }
      else {
        break;
      }
    }
  }
  // result:
  $names = array_values($names);
  $names['title'] = $title;
  $names['first'] = $firstName;
  $names['last'] = $lastName;
  return $names;
}

/**
 * Convert a string to a boolean.
 * Case-insensitive.
 *
 * @param string $str
 * @return bool
 */
function str2bool($str) {
  return in_array(strtolower($str), array('1', 't', 'true', 'y', 'yes', 'on'));
}

/**
 * Convert something to a boolean, with special non-PHP handling for strings.
 *
 * @param mixed $x
 * @return bool
 */
function toBool($x) {
  return (is_string($x) && !is_numeric($x)) ? str2bool($x) : ((bool) $x);
}

/**
 * Converts a boolean value to a string, either 'True' or 'False'.
 * Useful for outputting bools in JavaScript.
 *
 * @param bool $bool
 * @return string
 */
function bool2str($bool) {
  return $bool ? 'True' : 'False';
}

/**
 * Converts a boolean value to either 'Yes' or 'No'.
 * Useful for display boolean values in a web page.
 *
 * @param bool $bool
 * @return string
 */
function bool2yn($bool) {
  return $bool ? 'Yes' : 'No';
}

/**
 * Converts a string to a bit.  Same as str2bool except that result is 1 or 0.
 * Useful for converting booleans for database entry.
 *
 * @param string $str
 * @return int
 */
function str2bit($str) {
  return (int) str2bool($str);
}

function expandYN($ch, $Y = 'Yes', $N = 'No', $default = 'N') {
  if ($ch === TRUE || $ch == 'Y') {
    return $Y;
  }
  elseif ($ch === FALSE || $ch == 'N' || $default == 'N') {
    return $N;
  }
  else {
    return $default;
  }
}

/**
 * If $str begins with http:// or https://, then this is removed and the resulting string returned.
 *
 * @param string $str
 * @return string
 */
function trim_http($str) {
  $str = trim($str);
  $lower = strtolower($str);
  if (str_begins_with($lower, 'http://')) {
    $str = substr($str, 7);
  }
  elseif (str_begins_with($lower, 'https://')) {
    $str = substr($str, 8);
  }
  return $str;
}

/**
 * If $str begins with "www.", this is removed and the resulting string returned.
 *
 * @param string $str
 * @return string
 */
function trim_www($str) {
  $str = trim($str);
  $lower = strtolower($str);
  if (str_begins_with($lower, 'www.')) {
    $str = substr($str, 4);
  }
  return $str;
}

/**
 * If $str doesn't begin with 'http://', then this is added and the resulting string returned.
 *
 * @param string $str
 * @return string
 */
function add_http($str) {
  $str = trim($str);
  $lower = strtolower($str);
  if (!str_begins_with($lower, 'http://') && !str_begins_with($lower, 'https://')) {
    $str = "http://$str";
  }
  return $str;
}

/**
 * Takes a URL entered into a form field and checks the http:// prefix.
 * If $str == 'http://', then an empty string is returned.
 * Otherwise, if the string does not begin with http:// or https:// then http:// is appended.
 *
 * @param string $str
 * @return string
 */
function url2db($str) {
  if ($str == 'http://' || $str == 'https://') {
    return '';
  }
  elseif ($str != '') {
    return add_http($str);
  }
  return $str;
}

/**
 * Converts a database field into a URL for display in a form field.
 * Simply, if $str == '', the result is 'http://' - this provides a prompt for the user.
 *
 * @param string $str
 * @return string
 */
function db2url($str) {
  return $str ? db2html($str) : 'http://';
}

function gibberish($nParagraphs, $minWordsPerParagraph, $maxWordsPerParagraph) {
  for ($p = 0; $p < $nParagraphs; $p++) {
    $words = '';
    $nWords = rand($minWordsPerParagraph, $maxWordsPerParagraph);
    // paragraph is $nWords of gibberish:
    for ($n = 0; $n < $nWords; $n++) {
      $wordLen = rand(1, 12);
      $word = '';
      for ($c = 0; $c < $wordLen; $c++) {
        $ch = chr(rand(97, 122));
        if ($c == 0)
          $ch = strtoupper($ch);
        $word .= $ch;
      }
      $words[] = $word;
    }
    $paragraphs[] = implode(' ', $words).".";
  }
  return implode("\r\n\r\n", $paragraphs);
}

function colourStr($red, $green, $blue) {
  // returns a hexadecimal colour string (e.g F3BC3E) given values for red, green, blue (0..255)
  return strtoupper(str_pad(base_convert($red, 10, 16), 2, '0', STR_PAD_LEFT).
    str_pad(base_convert($green, 10, 16), 2, '0', STR_PAD_LEFT).
    str_pad(base_convert($blue, 10, 16), 2, '0', STR_PAD_LEFT));
}

/**
 * Checks if $needle is in $haystack.
 *
 * @param string $haystack
 * @param string $needle
 * @param bool $case_sensitive
 * @return bool
 */
function in_str($haystack, $needle, $case_sensitive = TRUE) {
  if ($case_sensitive) {
    return strpos($haystack, $needle) !== FALSE;
  }
  return strpos(strtolower($haystack), strtolower($needle)) !== FALSE;
}

function html2db($str) {
  // This is used to convert fields submitted using a form into a format suitable for
  // entry into the database.
  // First the slashes are removed, then some html entities are converted,
  // then the slashes are replaced.
  return addslashes(trim(convertHtmlEntities(stripslashes($str))));
}

function post2html($str) {
  // for displaying fields in a form that have already been sent through post with magic-quotes on/added:
  return htmlSpecialChars(stripslashes($str), ENT_QUOTES);
}

function rec2db($rec) {
  foreach ($rec as $key => $field)
    $rec[$key] = html2db($field);
  return $rec;
}

function db2html($str) {
  // This is used to convert database strings into a format useful for displaying in form fields.
  // Basically just htmlSpecialChars with both single and double quotes converted to html entities.
  return htmlSpecialChars($str, ENT_QUOTES);
}

/**
 * Returns n left-most characters from $str.
 *
 * @param string $str
 * @param int $n
 * @return string
 */
function left($str, $n) {
  return substr($str, 0, $n);
}

/**
 * Returns n right-most characters from $str.
 *
 * @param string $str
 * @param int $n
 * @return string
 */
function right($str, $n) {
  return substr($str, -$n);
}

/**
 * Returns TRUE if $str begins with $substr.
 *
 * @param string $str
 * @param string $substr
 * @param bool $ignoreCase
 * @return bool
 */
function str_begins_with($str, $substr, $ignoreCase = FALSE) {
  if ($ignoreCase) {
    $str = strtolower($str);
    $substr = strtolower($substr);
  }
  return left($str, strlen($substr)) == $substr;
}

/**
 * Returns TRUE if $str ends with $substr.
 *
 * @param string $str
 * @param string $substr
 * @param bool $ignoreCase
 * @return bool
 */
function str_ends_with($str, $substr, $ignoreCase = FALSE) {
  if ($ignoreCase) {
    $str = strtolower($str);
    $substr = strtolower($substr);
  }
  return right($str, strlen($substr)) == $substr;
}

/**
 * If $str begins with $substr, then $substr is removed from the beginning of $str.
 *
 * @param string $str
 * @param string $substr
 * @param bool $ignoreCase
 * @return string
 */
function str_trim_left($str, $substr, $ignoreCase = FALSE) {
  return str_begins_with($str, $substr, $ignoreCase) ? substr($str, strlen($substr)) : $str;
}

/**
 * Will return a string in the form of "A, B, C & D", constructed from the supplied array.
 *
 * @param array $arr
 * @param string $conj
 * @return string
 */
function makeList($arr, $conj = "&") {
  if (count($arr) == 0) {
    return "";
  }
  elseif (count($arr) == 1) {
    return $arr[0];
  }
  elseif (count($arr) == 2) {
    return "{$arr[0]} $conj {$arr[1]}";
  }
  else {
    $first = array_shift($arr);
    return "$first, " . makeList($arr);
  }
}

function editDistance($s, $t) {
  // note - I did not realise there was a levenshtein function built into PHP
  // when I made this one!

  /*
  ORIGINAL CODE FROM http://www.merriampark.com/ld.htm
  '  Levenshtein distance (LD) is a measure of the similarity between two strings,
  '  which we will refer to as the source string (s) and the target string (t). The
  '  distance is the number of deletions, insertions, or substitutions required to
  '  transform s into t. For example, If s is "test" and t is "test", then LD(s,t) = 0,
  '  because no transformations are needed. The strings are already identical. If s is
  '  "test" and t is "tent", then LD(s,t) = 1, because one substitution
  '  (change "s" to "n") is sufficient to transform s into t. The greater
  '  the Levenshtein distance, the more different the strings are.
  '  Levenshtein distance is named after the Russian scientist Vladimir
  '  Levenshtein, who devised the algorithm in 1965. If you can't spell or pronounce
  '  Levenshtein, the metric is also sometimes called edit distance.
  */

  // Step 1
  $n = strlen($s);
  $m = strlen($t);
  if ($n == 0) {
    return $m;
  }
  if ($m == 0) {
    return $n;
  }

  // Step 2
  for ($i = 0; $i <= $n; $i++) {
    $d[$i][0] = $i;
  }
  for ($j = 0; $j <= $m; $j++) {
    $d[0][$j] = $j;
  }

  // Step 3
  for ($i = 1; $i <= $n; $i++) {
    $s_i = $s{$i - 1};  // the $i'th character of $s
    // Step 4
    for ($j = 1; $j <= $m; $j++) {
      $t_j = $t{$j - 1}; // the $j'th character of $t

      // Step 5
      $cost = $s_i == $t_j ? 0 : 1;

      // Step 6
      $d[$i][$j] = min($d[$i - 1][$j] + 1, $d[$i][$j - 1] + 1, $d[$i - 1][$j - 1] + $cost);
    }
  }

  // Step 7
  return $d[$n][$m];
}

/**
 * Returns str with only alphanumeric or hyphens, any other chars removed.
 *
 * @param string $str
 * @return string
 */
function makeDomainWord($str) {
  $out = "";
  for ($i = 0; $i < strlen($str); $i++) {
    $ch = $str{$i};
    if (ctype_alnum($ch) || ($ch == '-' && $out != ''))
      $out .= $ch;
  }

  // Trim any trailing hyphens:
  while ($out[strlen($out) - 1] == '-') {
    $out = left($out, strlen($out) - 1);
  }

  return strtolower($out);
}

/**
 * Detects if a string is HTML.
 * @param string $text
 * @return bool
 */
function is_html($text) {
  return $text != strip_tags($text);
}

/**
 * Normalizes break tags and trims any from the end.
 *
 * @todo Update to use preg_replace() and test - this function looks wrong.
 *
 * @param string $text
 * @return string
 */
function trim_break_tags($text) {
  $text = str_replace(array('<br />', '<br/>', '<br>', '<BR />', '<BR/>', '<BR>'), '<br>', $text);
  $text = trim($text);
  while (str_ends_with($text, '<br>')) {
    $text = trim(left($text, strlen($text) - 6));
  }
  return $text;
}

////////////////////////////////////////////////////////////////////////////////
// Functions for converting variables into a string representation.

/**
 * Convert a variable to a string, usually for output to the browser.
 * A bit nicer than PHP's default var_dump(), var_export() or serialize().
 * 
 * @param mixed $value
 * @return string
 */
function var_to_string($value, $indent = 0, $objects = array(), $html = FALSE) {
  if (is_null($value)) {
    return 'NULL';
  }
  elseif (is_bool($value)) {
    return $value ? 'TRUE' : 'FALSE';
  }
  elseif (is_string($value)) {
    return "'" . htmlspecialchars(addslashes($value)) . "'";
  }
  elseif (is_array($value)) {
    return array_to_string($value, $indent, $objects, $html);
  }
  elseif (is_object($value)) {
    if (in_array($value, $objects, TRUE)) {
      return "((Circular Reference))";
    }
    else {
      $objects[] = $value;
      if ($value instanceof Query) {
        return query_to_string($value);
      }
      else {
        return object_to_string($value, $indent, $objects, $html);
      }
    }
  }
  else {
    // int or float:
    return (string) $value;
  }
}

/**
 * Indents a flat JSON string to make it more human-readable.
 *
 * @param string $json The original JSON string to process.
 * @return string Indented version of the original JSON string.
 */
function format_json($json) {
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = TRUE;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        }
        elseif (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }

    return $result;
}

/**
 * Collapses all sequences of whitespace characters to single spaces, just like browsers do with HTML.
 *
 * @param string $text
 * @return string
 */
function collapse_whitespace($text) {
  return preg_replace('/\s+/', ' ', $text);
}

/**
 * Useful function to convert between different casing conventions.
 *
 * @param string $str
 *   The string to convert.
 * @param int $from_case
 *   One of the CASE constants above.
 * @param int $to_case
 *   One of the CASE constants above.
 * @return string
 */
function convert_case($str, $from_case, $to_case) {
  // Get an array of words, doesn't matter whether upper, lower or mixed case.
  switch ($from_case) {
    case CASE_LOWER:
    case CASE_UPPER:
      $words = explode('_', $str);
      break;

    case CASE_CAMEL:
    case CASE_TITLE:
      // Keep track of whether the character is a digit, since a switch from
      // letter to digit or vice-versa will be treated as a word boundary:
      $prev_is_digit = FALSE;
      $n = 0;
      $words = array();
      for ($i = 0; $i < strlen($str); $i++) {
        $ch = $str[$i];
        $is_digit = ctype_digit($ch);
        // Detect word boundary:
        if ($i == 0 || ctype_upper($ch) || ($prev_is_digit != $is_digit)) {
          $n++;
        }
        // Let's initialise the new word so we don't get a notice:
        if (!isset($words[$n])) {
          $words[$n] = '';
        }
        // Add the character to the word:
        $words[$n] .= $ch;
        // Next character:
        $prev_is_digit = $is_digit;
      }
      break;
  }

  // Convert from array of words into string:
  switch ($to_case) {
    case CASE_LOWER:
      return implode('_', array_map('strtolower', $words));

    case CASE_UPPER:
      return implode('_', array_map('strtoupper', $words));

    case CASE_CAMEL:
      $words = array_map('strtolower', $words);
      $first_word = array_shift($words);
      return $first_word . implode(array_map('ucfirst', $words));

    case CASE_TITLE:
      return implode(array_map('ucfirst', array_map('strtolower', $words)));
  }
}

/**
 * If the string $str is longer than $max_size (bytes, not characters), trim it and add an ellipsis so the total length
 * is $max_size.
 *
 * Supports UTF-8 strings.
 *
 * @param string $str
 * @param string $max_size
 * @return string
 */
function ellipsis_trim($str, $max_size) {
  if (strlen($str) <= $max_size) {
    return $str;
  }

  // Reduce $max_size by 3 to allow for the ellipsis:
  $max_size -= 3;

  // Trim to $max_size characters (not bytes).
  // The string may still be bigger than $max_size bytes because some characters could be multibyte.
  $str = mb_substr($str, 0, $max_size, 'UTF-8');

  // Remove characters from the end of the string until the size is $max_size or less:
  while (strlen($str) > $max_size) {
    $str = mb_substr($str, 0, -1, 'UTF-8');
  }

  // Append the ellipsis, which adds 3 bytes:
  return  $str . '...';
}

/**
 * Format an amount of memory with unit prefix.
 * Useful for php.ini settings.
 * The 'B' for 'bytes' is not included; only the prefix ('k' for kilo, 'M' for mega, etc.), if required.
 *
 * @param string $mem
 * @param int $decimals
 *   Number of decimal places, default to 0. Same as in number_format().
 * @return string
 */
function format_memory($mem, $decimals = 0) {
  $unit = array('', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');
  $i = floor(log($mem, 1000));
  return number_format($mem / pow(1024, $i), $decimals, '.', '') . $unit[$i];
}

/**
 * Replace hyphens with non-breaking hyphens.
 *
 * @param string $str
 * @return string
 */
function nobr_hyphens($str) {
  return str_replace('-', '&#x2011;', $str);
}

/**
 * Reformat some HTML.
 *
 * @param string $html
 * @return string
 */
function tidyHTML($html) {
  // load our document into a DOM object
  $dom = new DOMDocument();
  // we want nice output
  $dom->preserveWhiteSpace = FALSE;
  $dom->loadHTML($html);
  $dom->formatOutput = TRUE;
  return ($dom->saveHTML());
}
