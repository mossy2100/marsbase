<?php
/**
 * Integer division.
 *
 * @param int $n
 * @param int $d
 * @return int
 */
function div($n, $d) {
	return floor($n / $d);
}

/**
 * Correct way to do modulus (better than PHP's % parameter).
 *
 * @param int $n
 * @param int $d
 * @return int
 */
function mod($n, $d) {
  return $n - (div($n, $d) * $d);
}

/**
 * Handy function that returns TRUE if the param is equivalent to a signed integer.
 * Use as an alternative to is_numeric() when you want to know if the value looks like an int, not just a number.
 *
 * "is_sint" means "is signed integer", but it could be an integer, float or string.
 *
 * @param mixed $n
 * @return bool
 */
function is_sint($n) {
  return is_int($n) || (is_string($n) && $n === strval(intval($n))) || (is_float($n) && $n === floatval(intval($n)));
}

/**
 * Handy function that returns TRUE if the param is equivalent to an unsigned integer.
 * "is_uint" means "is unsigned integer", but it could be an integer, float or string.
 *
 * @param mixed $n
 * @return bool
 */
function is_uint($n) {
  return is_sint($n) && $n >= 0;
}

/**
 * Handy function that returns TRUE if the param is equivalent to a positive integer.
 * "is_pint" means "is positive integer", but it could be an integer, float or string.
 *
 * @param mixed $n
 * @return bool
 */
function is_pint($n) {
  return is_sint($n) && $n > 0;
}

/**
 * Handy function that returns TRUE if the param is equivalent to a negative integer.
 * "is_nint" means "is negative integer", but it could be an integer, float or string.
 *
 * @param mixed $n
 * @return bool
 */
function is_nint($n) {
  return is_sint($n) && $n < 0;
}

/**
 * Handy function that returns TRUE if the param is equivalent to (i.e. looks like) 0.
 * It could be the integer 0, or the string '0', or the float 0.0.
 *
 * NOTE, this function is not the same as $n == 0, because it will return FALSE if the param is NULL or FALSE or '' or
 * anything else falsy that that doesn't look like 0.
 * This is also not the same as $n === 0, because it will return TRUE for strings or floats equivalent to 0.
 *
 * @todo test
 *
 * @param mixed $n
 * @return bool
 */
function is_zero($n) {
  return is_sint($n) && $n == 0;
}
