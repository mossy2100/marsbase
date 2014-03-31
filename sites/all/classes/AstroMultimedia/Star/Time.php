<?php
namespace AstroMultimedia\Star;

/**
 * User: shaun
 * Date: 2012-09-17
 * Time: 8:09 PM
 *
 * Class to encapsulate a time of day or duration.
 */
class Time {

  /**
   * The number of seconds.
   *
   * @var int
   */
  protected $seconds = 0;

  /**
   * Constructor.
   *
   * @param int $hour
   * @param int $minute
   * @param int $second
   */
  public function __construct($hour = 0, $minute = 0, $second = 0) {
    $this->setTime($hour, $minute, $second);
  }

  /**
   * Convert the time to an array, with hours, minutes and seconds.
   *
   * @return array
   */
  public function toArray() {
    $seconds = $this->seconds;
    $hours = floor($seconds / DateTime::SECONDS_PER_HOUR);
    $seconds -= $hours * DateTime::SECONDS_PER_HOUR;
    $minutes = floor($seconds / DateTime::SECONDS_PER_MINUTE);
    $seconds -= $minutes * DateTime::SECONDS_PER_MINUTE;
    return array(
      'hour'   => $hours,
      'minute' => $minutes,
      'second' => $seconds,
    );
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the time.
   *
   * @return Time
   */
  public function time() {
    return $this;
  }

  /**
   * Set the time.
   *
   * @param int $hour
   * @param int $minute
   * @param int $second
   * @return Time
   */
  public function setTime($hour, $minute = 0, $second = 0) {
    $this->seconds = ($hour * DateTime::SECONDS_PER_HOUR) + ($minute * DateTime::SECONDS_PER_MINUTE) + $second;
    return $this;
  }

  /**
   * Get the hour.
   *
   * @return int
   */
  public function hour() {
    $a = $this->toArray();
    return $a['hour'];
  }

  /**
   * Set the hour.
   *
   * @param int $hour
   * @return Time
   */
  public function setHour($hour) {
    $a = $this->toArray();
    return $this->setTime($hour, $a['minute'], $a['second']);
  }

  /**
   * Get the minute.
   *
   * @return int
   */
  public function minute() {
    $a = $this->toArray();
    return $a['minute'];
  }

  /**
   * Set the minute.
   *
   * @param int $minute
   * @return Time
   */
  public function setMinute($minute) {
    $a = $this->toArray();
    return $this->setTime($a['hour'], $minute, $a['second']);
  }

  /**
   * Get the second.
   *
   * @return int
   */
  public function second() {
    $a = $this->toArray();
    return $a['second'];
  }

  /**
   * Set the second.
   *
   * @param int $second
   * @return Time
   */
  public function setSecond($second) {
    $a = $this->toArray();
    return $this->setTime($a['hour'], $a['minute'], $second);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // The time in various units

  /**
   * Get the total seconds.
   *
   * @return int
   */
  public function seconds() {
    return $this->seconds;
  }

  /**
   * Set the total seconds.
   *
   * @param int $seconds
   * @return Time
   */
  public function setSeconds($seconds) {
    $this->seconds = (int) $seconds;
    return $this;
  }

  /**
   * Get the time in minutes.
   *
   * @return float
   */
  public function minutes() {
    return $this->seconds / DateTime::SECONDS_PER_MINUTE;
  }

  /**
   * Get the time in hours.
   *
   * @return float
   */
  public function hours() {
    return $this->seconds / DateTime::SECONDS_PER_HOUR;
  }

  /**
   * Get the time in days.
   *
   * @return float
   */
  public function days() {
    return $this->seconds / DateTime::SECONDS_PER_DAY;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Format

  /**
   * Format a Time.
   * Uses date() format codes.
   *
   * @param string $format
   * @return string
   */
  public function format($format) {
    // Easy way is to use \DateTime::format().
    $dt = new DateTime();
    $dt->setTime($this);
    return $dt->format($format);
  }

}
