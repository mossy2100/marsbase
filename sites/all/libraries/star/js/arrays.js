/**
 * JavaScript functions that mimic PHP array functions.
 */

/**
 * Returns true if array 'haystack' contains element with value 'needle'
 * If strict is true then type must also match.
 *
 * @param var needle
 * @param var[] haystack
 * @param bool strict
 * @return bool
 */
function in_array(needle, haystack, strict) {
  var found = false;
  for (var key in haystack) {
    if (haystack[key] == needle && (!strict || strict && typeof(haystack[key]) == typeof(needle))) {
      return true;
    }
  }
  return false;
}

/**
 * Returns a string made up of all the elements of array 'pieces' connected,
 * with the string 'glue' in between them.
 *
 * @param string glue
 * @param string[] pieces
 * @return string
 */
function implode(glue, pieces) {
  if (pieces.length == 0) {
    return "";
  }
  var result = pieces[0];
  for (var i = 1; i < pieces.length; i++) {
    result += glue + pieces[i];
  }
  return result;
}

/**
 * Splits a string on the given separator.
 *
 * @param string separator
 * @param string str
 * @return string[]
 */
function explode(separator, str) {
  return str.split(separator);
}

/**
 * A variation of implode() in which empty strings are ignored.
 * (This is not a PHP function)
 *
 * @param string glue
 * @param string[] pieces
 * @return string
 */
function implode_compact(glue, pieces) {
  if (pieces.length == 0) {
    return '';
  }
  var result = pieces[0];
  for (var i = 1; i < pieces.length; i++) {
    if (pieces[i] != '') {
      result += glue + pieces[i];
    }
  }
  return result;
}

/**
 * A function to display the contents of an array in readable form.
 *
 * @param array arr
 */
function print_r(arr) {
  var str = '';
  for (el in arr) {
    if (arr.hasOwnProperty(el) && typeof(arr[el]) != 'function') {
      str += el + " => (" + typeof(arr[el]) + ") " + arr[el] + "\n";
    }
  }
  alert(str);
}

/**
 * Remove falsy items (false, 0, null, undefined) from an array.
 *
 * @param array arr
 */
function array_filter(arr) {
  var arr2 = [];
  for (var el in arr) {
    if (arr[el]) {
      arr2.push(arr[el]);
    }
  }
  return arr2;
}
