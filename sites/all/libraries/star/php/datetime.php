<?php

/**
 * Functions for dates and times.
 */

/**
 * Converts a datetime from whatever format into a timestamp.
 * @param mixed $datetime
 * @return int
 */
function datetime_to_timestamp($datetime) {
  if (is_numeric($datetime)) {
    // it's already (probably) a timestamp:
    return $datetime;
  }
  else if (is_string($datetime)) {
    // try converting the string to a timestamp:
    return strtotime($datetime);
  }
  else if (is_object($datetime)) {
    if ($datetime instanceof DateTime) {
      // a PHP DateTime object, get the timestamp:
      return $datetime->getTimestamp();
    }
    else if ($datetime->is_date) {
      // the datetime has come from XML-RPC, get the timestamp:
      return strtotime($datetime->iso8601);
    }
  }
  return FALSE;
}

/**
 * Get today's date as a Unix timestamp.
 */
function today() {
  return datetime_to_timestamp(date('Y-m-d'));
}

/**
 * Add a number of days to a datetime.  Returns a Unix timestamp.
 * @param mixed $datetime
 * @param int $n_days
 * @return int
 */
function add_days($datetime, $n_days) {
  $ts = datetime_to_timestamp($datetime);
  return $ts + (86400 * $n_days);
}

/**
 * Subtracts a number of days from a datetime.  Returns a Unix timestamp.
 * @param mixed $datetime
 * @param int $n_days
 * @return int
 */
function subtract_days($datetime, $n_days) {
  return add_days($datetime, -$n_days);
}

/**
 * Add a number of months to a datetime.  Returns a Unix timestamp.
 * @param mixed $datetime
 * @param int $n_months
 * @return int
 */
function add_months($datetime, $n_months) {
  $ts = datetime_to_timestamp($datetime);
  $info = getdate($ts);
  return mktime($info['hours'], $info['minutes'], $info['seconds'],
    $info['mon'] + $n_months, $info['mday'], $info['year']);
}

/**
 * Subtracts a number of months from a datetime.  Returns a Unix timestamp.
 * @param mixed $datetime
 * @param int $n_months
 * @return int
 */
function subtract_months($datetime, $n_months) {
  return add_months($datetime, -$n_months);
}

/**
 * Add a number of years to a datetime.  Returns a Unix timestamp.
 * @param mixed $datetime
 * @param int $n_years
 * @return int
 */
function add_years($datetime, $n_years) {
  $ts = datetime_to_timestamp($datetime);
  $info = getdate($ts);
  return mktime($info['hours'], $info['minutes'], $info['seconds'],
    $info['mon'], $info['mday'], $info['year'] + $n_years);
}

/**
 * Subtracts a number of years from a datetime.  Returns a Unix timestamp.
 * @param mixed $datetime
 * @param int $n_years
 * @return int
 */
function subtract_years($datetime, $n_years) {
  return add_years($datetime, -$n_years);
}

/**
 * Format a datetime.
 * @param mixed $datetime
 * @return string or FALSE
 */
function format_datetime($format, $datetime = NULL) {
  if ($datetime === NULL) {
    $datetime = time();
  }
  else if (!$datetime) {
    return FALSE;
  }
  $ts = datetime_to_timestamp($datetime);
  if (!$ts) {
    return FALSE;
  }
  return date($format, $ts);
}

/**
 * Format a datetime as ISO8601 (YYYY-MM-DD hh:mm:ss), if possible.
 * @param mixed $datetime
 * @return string or FALSE
 */
function format_datetime_iso($datetime = NULL, $check_if_zero = FALSE) {
  if ($check_if_zero && datetime_is_zero($datetime)) {
    return '';
  }
  if ($datetime === NULL) {
    $datetime = time();
  }
  return format_datetime('Y-m-d H:i:s', $datetime);
}

/**
 * Format a date as ISO8601 (YYYY-MM-DD), if possible.
 * @param mixed $date
 * @return string
 */
function format_date_iso($date = NULL, $check_if_zero = FALSE) {
  if ($check_if_zero && datetime_is_zero($date)) {
    return '';
  }
  if ($date === NULL) {
    $date = time();
  }
  return format_datetime('Y-m-d', $date);
}

/**
 * Checks if the given variable is a date.
 * @param mixed $var
 * @return bool
 */
function is_date($var) {
  $ts = datetime_to_timestamp($var);
  return $ts !== FALSE;
}

/**
 * Given a date of birth, calculate the current age.
 * @param string|stdClass $date_of_birth
 */
function calc_age($date_of_birth) {
  $ts_birth = datetime_to_timestamp($date_of_birth);
  $year_birth = date('Y', $ts_birth);
  $year_now = date('Y');
  $age = $year_now - $year_birth;
  $birth_date = date('m-d', $ts_birth);
  $todays_date = date('m-d');
  if ($birth_date > $todays_date) {
    $age--;
  }
  return $age;
}

/**
 * Returns the latest (max) datetime in the params as a timestamp.
 * @return int A timestamp.
 */
function datetime_max() {
  $datetimes = func_get_args();
  if (empty($datetimes)) {
    return FALSE;
  }
  $max = 0;
  foreach ($datetimes as $value) {
    $ts = datetime_to_timestamp($value);
    if ($ts > $max) {
      $max = $ts;
    }
  }
  return $max;
}

/**
 * Returns the earliest (min) datetime in the params as a timestamp.
 * @return int A timestamp.
 */
function datetime_min() {
  $datetimes = func_get_args();
  if (empty($datetimes)) {
    return FALSE;
  }
  $min = PHP_INT_MAX;
  foreach ($datetimes as $value) {
    $ts = datetime_to_timestamp($value);
    if ($ts < $min) {
      $min = $ts;
    }
  }
  return $min;
}


////////////////////////////////////////////////////////////////////////////////////////////////////
// Date comparison functions

/**
 * Returns TRUE if the two dates are equal. They can be in different formats.
 * @param mixed $d1
 * @param mixed $d2
 * @return bool
 */
function date_eq($d1, $d2) {
  return $d1 === $d2 || unix_day_count($d1) === unix_day_count($d2);
}

function date_ne($d1, $d2) {
  return !date_eq($d1, $d2);
}

function date_lt($d1, $d2) {
  return unix_day_count($d1) < unix_day_count($d2);
}

function date_le($d1, $d2) {
  return unix_day_count($d1) <= unix_day_count($d2);
}

function date_gt($d1, $d2) {
  return unix_day_count($d1) > unix_day_count($d2);
}

function date_ge($d1, $d2) {
  return unix_day_count($d1) >= unix_day_count($d2);
}


////////////////////////////////////////////////////////////////////////////////////////////////////
// Datetime comparison functions

/**
 * Returns TRUE if the two datetimes are equal. They can be in different formats.
 * @param mixed $dt1
 * @param mixed $dt2
 * @return bool
 */
function datetime_eq($dt1, $dt2) {
  return $dt1 === $dt2 || datetime_to_timestamp($dt1) === datetime_to_timestamp($dt2);
}

function datetime_ne($dt1, $dt2) {
  return !datetime_eq($dt1, $dt2);
}

function datetime_lt($dt1, $dt2) {
  return datetime_to_timestamp($dt1) < datetime_to_timestamp($dt2);
}

function datetime_le($dt1, $dt2) {
  return datetime_to_timestamp($dt1) <= datetime_to_timestamp($dt2);
}

function datetime_gt($dt1, $dt2) {
  return datetime_to_timestamp($dt1) > datetime_to_timestamp($dt2);
}

function datetime_ge($dt1, $dt2) {
  return datetime_to_timestamp($dt1) >= datetime_to_timestamp($dt2);
}

/**
 * Define a constant for the maximum value for a 32-bit integer.
 * Use this instead of PHP_INT_MAX, which doesn't work on Linux for some reason.
 * @var int
 */
define('INT_MAX', 2147483647);

/**
 * Simply return the maximum Unix timestamp on a 32-bit system.
 */
function datetime_biggest() {
  return INT_MAX;
}

/**
 * Simply return the minimum Unix timestamp on a 32-bit system.
 */
function datetime_smallest() {
  return -INT_MAX - 1;
}

/**
 * Find the difference between two datetimes in seconds.
 * @param mixed $datetime1
 * @param mixed $datetime2
 * @return int
 */
function time_diff($datetime1, $datetime2) {
  return datetime_to_timestamp($datetime1) - datetime_to_timestamp($datetime2);
}

/**
 * Calculate the Unix day count from a datetime.
 * (day 0 = 1970-01-01)
 * @param mixed $datetime
 * @return int
 */
function unix_day_count($datetime) {
  return floor(datetime_to_timestamp($datetime) / 86400);
}

/**
 * Calculate the difference between two dates in days.
 * @param $datetime1
 * @param $datetime2
 * @return int
 */
function date_diff($datetime1, $datetime2) {
  return unix_day_count($datetime1) - unix_day_count($datetime2);
}

/**
 * Checks if the given date string is equal to the beginning of the Unix epoch,
 * i.e. 1970-01-01 00:00:00
 * @param mixed $datetime
 * @return bool
 */
function is_beginning_epoch($datetime) {
  return datetime_eq($datetime, '1970-01-01 00:00:00');
}

/**
 * Returns true if a datetime is NULL, 0, invalid, or equivalent to Unix timestamp 0
 * (i.e. 1970-01-01)
 * @param mixed $dt
 */
function datetime_is_zero($dt) {
  if (!$dt) {
    return TRUE;
  }
  $ts = datetime_to_timestamp($dt);
  if (!$ts) {
    return TRUE;
  }
  return is_beginning_epoch($dt);
}
