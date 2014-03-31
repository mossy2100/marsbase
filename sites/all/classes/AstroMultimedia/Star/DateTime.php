<?php
namespace AstroMultimedia\Star;

use DateTime as PhpDateTime;
use DateTimeZone;
use Exception;

/**
 * This class is part of the Star Library, and is designed to extend and improve PHP's built-in DateTime class.
 *
 * @todo Abandon the built-in DateTime class, as it has a couple of problems:
 *   1. The year is constrained to 1970..2038.
 *   2. You can't create a date by itself - there's always a time part and a timezone part.
 * Use a Date class which holds a day number, e.g. MJD or Unix Day, and a Time value for the time.
 * Then a DateTime would be comprised of a Date and a Time.
 * Also incorporate support for leap seconds. Date::seconds() should return 86400 or 86401 depending on date.
 */
class DateTime extends PhpDateTime {

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Constants

  // These values are exact, based on average Gregorian calendar month and year lengths.
  const SECONDS_PER_MINUTE  = 60;
  const SECONDS_PER_HOUR    = 3600;
  const SECONDS_PER_DAY     = 86400;
  const SECONDS_PER_WEEK    = 604800;
  const SECONDS_PER_MONTH   = 2629746;
  const SECONDS_PER_YEAR    = 31556952;

  const MINUTES_PER_HOUR    = 60;
  const MINUTES_PER_DAY     = 1440;
  const MINUTES_PER_WEEK    = 10080;
  const MINUTES_PER_MONTH   = 43829.1;
  const MINUTES_PER_YEAR    = 525949.2;

  const HOURS_PER_DAY       = 24;
  const HOURS_PER_WEEK      = 168;
  const HOURS_PER_MONTH     = 730.485;
  const HOURS_PER_YEAR      = 8765.82;

  const DAYS_PER_WEEK       = 7;
  const DAYS_PER_MONTH      = 30.436875;
  const DAYS_PER_YEAR       = 365.2425;

  const WEEKS_PER_MONTH     = 4.348125;
  const WEEKS_PER_YEAR      = 52.1775;

  const MONTHS_PER_YEAR     = 12;

  // Formats
  const ISO_DATE = 'Y-m-d';
  const MYSQL_DATETIME = 'Y-m-d H:i:s';
  const MYSQL_ISO_DATETIME = 'Y-m-d\TH:i:s';

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * The current datetime as an DateTime object.
   *
   * @param bool $utc
   * @return DateTime
   */
  public static function now($utc = FALSE) {
    if ($utc) {
      // Get current datetime in UTC:
      return new self(NULL, 'UTC');
    }
    else {
      // Get current datetime with default timezone:
      return new self();
    }
  }

  /**
   * The current local datetime.
   *
   * @return DateTime
   */
  public static function nowLocal() {
    return self::now(TRUE);
  }

  /**
   * The current datetime in UTC.
   *
   * @return DateTime
   */
  public static function nowUTC() {
    return self::now(FALSE);
  }

  /**
   * Today's date as an DateTime object.
   *
   * @return DateTime
   */
  public static function today() {
    $now = self::now();
    return $now->date();
  }

  /**
   * The current year.
   *
   * @return int
   */
  public static function thisYear() {
    $now = self::now();
    return $now->year();
  }

  /**
   * The current month.
   *
   * @return int
   */
  public static function thisMonth() {
    $now = self::now();
    return $now->month();
  }

  /**
   * Pads a number with '0' characters up to a specified width.
   *
   * @param int $n
   * @param int $w
   * @return string
   */
  public static function zeroPad($n, $w = 2) {
    return str_pad((int) $n, $w, '0', STR_PAD_LEFT);
  }

  /**
   * Converts a 2-digit year to a 4-digit year using MySQL rules.
   * Does not check if year is valid.
   *
   * 0..69     => 2000..2069
   * 70..99    => 1970..1999
   * 100..9999 => 100..9999
   *
   * @param int $year
   * @return int
   */
  public static function year4digit($year) {
    $year = (int) $year;
    return $year < 70 ? ($year + 2000) : ($year < 100 ? ($year + 1900) : $year);
  }

  /**
   * Returns new DateTime object formatted according to the specified format.
   * @see http://php.net/manual/en/datetime.createfromformat.php
   *
   * @param string $format
   * @param string $time
   * @param DateTimeZone $timezone
   * @return DateTime
   */
  public static function createFromFormat($format, $time, DateTimeZone $timezone = NULL) {
    $dt = parent::createFromFormat($format, $time, $timezone);
    return $dt ? new self($dt) : FALSE;
  }

  /**
   * Get all the month names in English.
   * @todo Add language support.
   *
   * @param int $m
   * @return array
   */
  public static function monthNames($m = NULL) {
    $month_names = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
      'October', 'November', 'December');
    return $m ? $month_names[$m] : $month_names;
  }

  /**
   * Get all the abbreviated month names in English.
   * @todo Add language support.
   *
   * @param int $m
   * @return array
   */
  public static function abbrevMonthNames($m = NULL) {
    $abbrev_month_names = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    return $m ? $abbrev_month_names[$m] : $abbrev_month_names;
  }

  /**
   * Given a month number, name or abbreviated name, get the month number.
   *
   * @param $m
   * @return bool|string
   */
  public static function toMonthNum($m) {
    // Check for 1..12:
    if (is_pint($m)) {
      $m = (int) $m;
      return ($m >= 1 && $m <= 12) ? $m : FALSE;
    }

    // Check for month name:
    $m = ucfirst(strtolower($m));
    $key = array_search($m, self::monthNames());
    if ($key !== FALSE) {
      return $key;
    }

    // Check for abbrev name:
    $key = array_search($m, self::abbrevMonthNames());
    if ($key !== FALSE) {
      return $key;
    }

    return FALSE;
  }

  /**
   * Get the default timezone as an object.
   *
   * @see http://www.php.net/manual/en/function.date-default-timezone-get.php
   *
   * @return DateTimeZone
   */
  public static function defaultTimezone() {
    return new DateTimeZone(date_default_timezone_get());
  }

  /**
   * Set the default timezone.
   *
   * @see http://www.php.net/manual/en/function.date-default-timezone-set.php
   *
   * @param string|DateTimeZone $tz
   * @return DateTimeZone
   * @throws Exception
   */
  public static function setDefaultTimezone($tz) {
    // Accept DateTimeZone objects, convert to string:
    if ($tz instanceof DateTimeZone) {
      $tz = $tz->getName();
    }

    // Set the default timezone. This will generate an E_NOTICE if the timezone isn't valid, and/or an E_WARNING.
    $ok = date_default_timezone_set($tz);

    // Check:
    if (!$ok) {
      throw new Exception("Invalid timezone - valid timezone string or DateTimeZone object expected.");
    }

    // Return the new default timezone:
    return self::defaultTimezone();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Constructor for making dates and datetimes.
   *
   * Time zones may be provided as DateTimeZone objects, or as timezone strings.
   * The $timezone parameter and the current timezone are ignored when the $time parameter either is a UNIX
   * timestamp (e.g. @946684800 or 946684800) or specifies a timezone (e.g. 2010-01-28T15:00:00+02:00).
   *
   * @see http://php.net/manual/en/datetime.construct.php
   *
   * All arguments are optional and some may be used for different things.
   *
   * Usage examples:
   *    $dt = new DateTime();
   *    $dt = new DateTime($datetime_object);
   *    $dt = new DateTime($unix_timestamp);
   *    $dt = new DateTime($unix_timestamp, $timezone);
   *    $dt = new DateTime($datetime_string);
   *    $dt = new DateTime($datetime_string, $timezone);
   *    $dt = new DateTime($year, $month, $day);
   *    $dt = new DateTime($year, $month, $day, $timezone);
   *    $dt = new DateTime($year, $month, $day, $hour, $minute, $second);
   *    $dt = new DateTime($year, $month, $day, $hour, $minute, $second, $timezone);
   *
   * @param string|int $arg0
   *   year, Unix timestamp, or datetime string
   * @param null|DateTimeZone|string|int $arg1
   *   month or timezone
   * @param int $day
   * @param null|DateTimeZone|string|int $arg3
   *   hour or timezone
   * @param int $minute
   * @param int $second
   * @param null|DateTimeZone|string $timezone
   * @throws \Exception
   */
  public function __construct($arg0 = NULL, $arg1 = NULL, $day = NULL, $arg3 = NULL, $minute = NULL, $second = NULL, $timezone = NULL) {
    $n_args = func_num_args();

    // Default timezone:
    $timezone = NULL;
    $post_set_tz = FALSE;

    if ($n_args == 0) {
      // Now:
      $datetime = 'now';
    }
    elseif ($n_args == 1 && $arg0 instanceof PhpDateTime) {
      // PHP DateTime or AstroMultimedia\Star\DateTime object:
      $datetime = $arg0->format(self::ISO8601);
    }
    elseif ($n_args <= 2) {
      if (is_numeric($arg0)) {
        // Args are assumed to be: $unix_timestamp, [$timezone].
        $datetime = '@' . $arg0;

        // If the timezone was provided, with Unix timestamps it has to be set after construction:
        if ($arg1) {
          $post_set_tz = TRUE;
        }
      }
      else {
        // Args are assumed to be: $datetime, [$timezone], as for the DateTime constructor.
        $datetime = $arg0;
      }
      $timezone = isset($arg1) ? $arg1 : NULL;
    }
    elseif ($n_args <= 4) {
      // Args are assumed to be: $year, $month, $day, [$timezone].
      $date = self::zeroPad($arg0, 4) . '-' . self::zeroPad($arg1) . '-' . self::zeroPad($day);
      $time = '00:00:00';
      $datetime = "$date $time";
      $timezone = isset($arg3) ? $arg3 : NULL;
    }
    elseif ($n_args >= 6 && $n_args <= 7) {
      // Args are assumed to be: $year, $month, $day, [$timezone].
      $date = self::zeroPad($arg0, 4) . '-' . self::zeroPad($arg1) . '-' . self::zeroPad($day);
      $time = self::zeroPad($arg3) . ':' . self::zeroPad($minute) . ':' . self::zeroPad($second);
      $datetime = "$date $time";
      $timezone = isset($timezone) ? $timezone : NULL;
    }
    else {
      throw new Exception("Invalid number of parameters.");
    }

    // Support string timezones:
    if (is_string($timezone)) {
      $timezone = new DateTimeZone($timezone);
    }

    // Check we have a valid timezone:
    if (isset($timezone) && !($timezone instanceof DateTimeZone)) {
      throw new Exception("Invalid timezone.", E_USER_WARNING);
    }

    if ($post_set_tz) {
      // Call parent constructor:
      parent::__construct($datetime);

      // Set the timezone:
      $this->setTimezone($timezone);
    }
    else {
      // Call parent constructor:
      parent::__construct($datetime, $timezone);
    }
  }

  /**
   * Convert the datetime to a string.
   *
   * @return string
   */
  public function __toString() {
    return $this->iso();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Formatting

  /**
   * Format the full datetime+timezone in ISO format.
   * e.g. 2013-08-13T11:30:45+1000
   *
   * @return string
   */
  public function iso() {
    return $this->format(self::ISO8601);
  }

  /**
   * Format just the date part in ISO format.
   * e.g. 2013-08-13
   *
   * @return string
   */
  public function isoDate() {
    return $this->format(self::ISO_DATE);
  }

  /**
   * Format the datetime in ISO format.
   * e.g. 2013-08-13T11:30:45
   *
   * @return string
   */
  public function isoDateTime() {
    return $this->format(self::MYSQL_ISO_DATETIME);
  }

  /**
   * Format the datetime suitable for MySQL DATETIME columns.
   * e.g. 2013-08-13 11:30:45
   * No timezone information is included in the format string, so you may want to set the timezone beforehand.
   *
   * @return string
   */
  public function mysql() {
    return $this->format(self::MYSQL_DATETIME);
  }

  /**
   * Format the date using Drupal's format_date() function.
   *
   * @param string $type
   * @param string $format
   * @param null $timezone
   * @param null $langcode
   * @return string
   */
  public function formatDrupal($type = 'medium', $format = '', $timezone = NULL, $langcode = NULL) {
    return format_date($this->timestamp(), $type, $format, $timezone, $langcode);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the date.
   *
   * @return DateTime
   */
  public function date() {
    return new self($this->format('Y-m-d'));
  }

  /**
   * Set the date.
   *
   * @param int $year
   * @param int $month
   * @param int $day
   * @return DateTime
   */
  public function setDate($year, $month, $day) {
    return parent::setDate($year, $month, $day);
  }

  /**
   * Get the time.
   *
   * @return Time
   */
  public function time() {
    return new Time($this->hour(), $this->minute(), $this->second());
  }

  /**
   * Set the time.
   *
   * @param int|Time $hour
   * @param int $minute
   * @param int $second
   * @return DateTime
   */
  public function setTime($hour, $minute = 0, $second = 0) {
    // If a Time object passed, convert to parts:
    if ($hour instanceof Time) {
      list($hour, $minute, $second) = $hour->toArray();
    }

    return parent::setTime($hour, $minute, $second);
  }

  /**
   * Get the timestamp.
   *
   * @return int
   */
  public function timestamp() {
    return parent::getTimestamp();
  }

  /**
   * Set the timestamp.
   *
   * @param int $timestamp
   * @return DateTime
   */
  public function setTimestamp($timestamp) {
    return parent::setTimestamp($timestamp);
  }

  /**
   * Get the timezone.
   *
   * @return DateTimeZone
   */
  public function timezone() {
    return parent::getTimezone();
  }

  /**
   * Set the timezone.
   *
   * @param string|DateTimeZone $tz
   * @return DateTime
   * @throws Exception
   */
  public function setTimezone($tz) {
    // Convert a string timezone to a DateTimeZone object:
    if (is_string($tz)) {
      $tz = new DateTimeZone($tz);
    }

    // Call the parent method:
    return parent::setTimezone($tz);
  }

  /**
   * Get the year as a 4-digit integer.
   *
   * @return int
   */
  public function year() {
    return (int) $this->format('Y');
  }

  /**
   * Set the year.
   *
   * @param int $year
   * @return DateTime
   */
  public function setYear($year) {
    return $this->setDate($year, $this->month(), $this->day());
  }

  /**
   * Get the month as an integer (1..12).
   *
   * @return int
   */
  public function month() {
    return (int) $this->format('n');
  }

  /**
   * Get the name of the month.
   *
   * @return string
   */
  public function monthName() {
    return $this->format('F');
  }

  /**
   * Get the abbreviated name of the month.
   *
   * @return string
   */
  public function abbrevMonthName() {
    return $this->format('M');
  }

  /**
   * Set the month.
   *
   * @param int $month
   * @return DateTime
   */
  public function setMonth($month) {
    return $this->setDate($this->year(), $month, $this->day());
  }

  /**
   * Get the day of the month as an integer (1..31).
   *
   * @return int
   */
  public function day() {
    return (int) $this->format('j');
  }

  /**
   * Set the day of the month.
   *
   * @param int $day
   * @return DateTime
   */
  public function setDay($day) {
    return $this->setDate($this->year(), $this->month(), $day);
  }

  /**
   * Get the hour.
   *
   * @return int
   */
  public function hour() {
    return (int) $this->format('G');
  }

  /**
   * Set the hour.
   *
   * @param int $hour
   * @return DateTime
   */
  public function setHour($hour) {
    return $this->setTime($hour, $this->minute(), $this->second());
  }

  /**
   * Get the minute.
   *
   * @return int
   */
  public function minute() {
    return (int) $this->format('i');
  }

  /**
   * Set the minute.
   *
   * @param int $minute
   * @return DateTime
   */
  public function setMinute($minute) {
    return $this->setTime($this->hour(), $minute, $this->second());
  }

  /**
   * Get the second.
   *
   * @return int
   */
  public function second() {
    return (int) $this->format('s');
  }

  /**
   * Set the second.
   *
   * @param int $second
   * @return DateTime
   */
  public function setSecond($second) {
    return $this->setTime($this->hour(), $this->minute(), $second);
  }

  /**
   * Get the week of the year as an integer (1.. 52).
   *
   * @return int
   */
  public function week() {
    return (int) $this->format('W');
  }

  /**
   * Get the day of the year as an integer (1..366).
   *
   * @return int
   */
  public function dayOfYear() {
    return ((int) $this->format('z')) + 1;
  }

  /**
   * Get the day of the month as an integer (1..31).
   * Alias of day().
   *
   * @return int
   */
  public function dayOfMonth() {
    return $this->day();
  }

  /**
   * Get the day of the week as an integer (1..7).
   * 1 = Monday .. 7 = Sunday
   *
   * @return int
   */
  public function dayOfWeek() {
    return (int) $this->format('N');
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Add periods. These methods return a new DateTime object; they don't modify the calling object.

  /**
   * Add years.
   *
   * @param int $years
   * @return DateTime
   */
  public function addYears($years) {
    $dt = clone $this;
    return $dt->setYear($dt->year() + $years);
  }

  /**
   * Add months.
   *
   * @param int $months
   * @return DateTime
   */
  public function addMonths($months) {
    $dt = clone $this;
    return $dt->setMonth($dt->month() + $months);
  }

  /**
   * Add weeks.
   *
   * @param int $weeks
   * @return DateTime
   */
  public function addWeeks($weeks) {
    return $this->addDays($weeks * 7);
  }

  /**
   * Add days.
   *
   * @param int $days
   * @return DateTime
   */
  public function addDays($days) {
    $dt = clone $this;
    return $dt->setDay($dt->day() + $days);
  }

  /**
   * Add hours.
   *
   * @param int $hours
   * @return DateTime
   */
  public function addHours($hours) {
    $dt = clone $this;
    return $dt->setHour($dt->hour() + $hours);
  }

  /**
   * Add minutes.
   *
   * @param int $minutes
   * @return DateTime
   */
  public function addMinutes($minutes) {
    $dt = clone $this;
    return $dt->setMinute($dt->minute() + $minutes);
  }

  /**
   * Add seconds.
   *
   * @param int $seconds
   * @return DateTime
   */
  public function addSeconds($seconds) {
    $dt = clone $this;
    return $dt->setSecond($dt->second() + $seconds);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Subtract periods. These methods return a new DateTime object; they don't modify the calling object.

  /**
   * Subtract years.
   *
   * @param int $years
   * @return DateTime
   */
  public function subYears($years) {
    return $this->addYears(-$years);
  }

  /**
   * Subtract months.
   *
   * @param int $months
   * @return DateTime
   */
  public function subMonths($months) {
    return $this->addMonths(-$months);
  }

  /**
   * Subtract weeks.
   *
   * @param int $weeks
   * @return DateTime
   */
  public function subWeeks($weeks) {
    return $this->addWeeks(-$weeks);
  }

  /**
   * Subtract days.
   *
   * @param int $days
   * @return DateTime
   */
  public function subDays($days) {
    return $this->addDays(-$days);
  }

  /**
   * Subtract hours.
   *
   * @param int $hours
   * @return DateTime
   */
  public function subHours($hours) {
    return $this->addHours(-$hours);
  }

  /**
   * Subtract minutes.
   *
   * @param int $minutes
   * @return DateTime
   */
  public function subMinutes($minutes) {
    return $this->addMinutes(-$minutes);
  }

  /**
   * Subtract seconds.
   *
   * @param int $seconds
   * @return DateTime
   */
  public function subSeconds($seconds) {
    return $this->addSeconds(-$seconds);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Day counts

  /**
   * Calculate the Julian Day for the datetime.
   *
   * @return float
   */
  function jd() {
    $dt = clone $this;
    $dt->setTimezone('UTC');
    $d = $dt->day();
    $m = $dt->month();
    $y = $dt->year();
    $s = $dt->time()->days();
    return (367 * $y) - floor(7 * ($y + floor(($m + 9) / 12)) / 4) -
      floor(3 * (floor(($y + ($m - 9) / 7) / 100) + 1) / 4) +
      floor(275 * $m / 9) + $d + 1721028.5 + $s;
  }

  /**
   * Calculate the Modified Julian Day for the date part of the datetime.
   *
   * @return int
   */
  function mjd() {
    return $this->jd() - 2400000.5;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Miscellaneous

  /**
   * Clamp the year to a specified range.
   * Either min or max or neither or both can be specified.
   *
   * @param int|null $min_year
   * @param int|null $max_year
   */
  public function clampYear($min_year = NULL, $max_year = NULL) {
    // Clamp to min year, if specified:
    if ($min_year !== NULL && $this->year() < $min_year) {
      $this->setYear($min_year);
    }
    // Clamp to max year, if specified:
    if ($max_year !== NULL && $this->year() > $max_year) {
      $this->setYear($max_year);
    }
  }

  /**
   * Generates a string describing how long ago a datetime was.
   *
   * @return string
   * @throws Exception
   */
  public function aboutHowLongAgo() {
    $ts = $this->timestamp();
    $now = time();

    // Get the time difference in seconds:
    $seconds = $now - $ts;

    // Check if time is in the past:
    if ($seconds < 0) {
      throw new Exception("DateTimes must be in the past.");
    }

    // Now:
    if ($seconds == 0) {
      return 'now';
    }

    // Seconds:
    if ($seconds <= 20) {
      return $seconds == 1 ? 'a second' : "$seconds seconds";
    }

    // 5 seconds:
    if ($seconds < 58) {
      return (round($seconds / 5) * 5) . ' seconds';
    }

    // Minutes:
    $minutes = round($seconds / self::SECONDS_PER_MINUTE);
    if ($minutes <= 20) {
      return $minutes == 1 ? 'a minute' : "$minutes minutes";
    }

    // 5 minutes:
    if ($minutes < 58) {
      return (round($minutes / 5) * 5) . ' minutes';
    }

    // Hours:
    $hours = round($seconds / self::SECONDS_PER_HOUR);
    if ($hours < 48 && $hours % self::HOURS_PER_DAY != 0) {
      return $hours == 1 ? 'an hour' : "$hours hours";
    }

    // Days:
    $days = round($seconds / self::SECONDS_PER_DAY);
    if ($days < 28 && $days % self::DAYS_PER_WEEK != 0) {
      return $days == 1 ? 'a day' : "$days days";
    }

    // Weeks:
    $weeks = round($seconds / self::SECONDS_PER_WEEK);
    if ($weeks <= 12) {
      return $weeks == 1 ? 'a week' : "$weeks weeks";
    }

    // Months:
    $months = round($seconds / self::SECONDS_PER_MONTH);
    if ($months < 24 && $months % self::MONTHS_PER_YEAR != 0) {
      return $months == 1 ? 'a month' : "$months months";
    }

    // Years:
    $years = round($seconds / self::SECONDS_PER_YEAR);
    return $years == 1 ? 'a year' : "$years years";
  }

  /**
   * Calculate the difference in seconds between two datetimes.
   *
   * The signature is identical to DateTime::diff(), except that method returns a DateInterval object.
   * @see DateTime::diff()
   *
   * @param DateTime $dt
   * @param bool $absolute
   *   If TRUE then the absolute value of the difference is returned.
   * @return int
   */
  function diffSeconds(self $dt, $absolute = FALSE) {
    $diff = $this->timestamp() - $dt->timestamp();
    if ($absolute) {
      $diff = abs($diff);
    }
    return $diff;
  }

  /**
   * Calculate the difference in days between two dates.
   *
   * The signature is identical to
   * @see DateTime::diff(), which returns a DateInterval.
   *
   * The result is not necessarily the same as $this->diffSeconds() / self::SECONDS_PER_DAY,
   * or even the floor() or round() of that, because the time parts of the datetimes are discarded my mjd().
   *
   * @param DateTime $dt
   * @param bool $absolute
   *   If TRUE then the absolute value of the difference is returned.
   * @return int|number
   */
  function diffDays(self $dt, $absolute = FALSE) {
    $diff = $this->mjd() - $dt->mjd();
    if ($absolute) {
      $diff = abs($diff);
    }
    return $diff;
  }

}
