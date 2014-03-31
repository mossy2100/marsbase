<?php
/**
 * Encapsulates a geofield.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2013-01-17 12:19
 */

namespace AstroMultimedia\Drupal;

use Exception;

class Geofield {

  /**
   * Stores the geofield parts.
   *
   * @var array
   */
  protected $components;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Create a geofield from an array of components.
   *
   * @param array $components
   * @throws Exception
   */
  public function __construct($components = NULL) {
    if (is_null($components)) {
      $this->components = array();
    }
    elseif (is_array($components)) {
      $this->components = $components;
    }
    else {
      throw new Exception('Invalid parameter.');
    }
  }

  /**
   * Convert to a string.
   *
   * @return string
   */
  public function __toString() {
    return $this->toString();
  }

  /**
   * Convert to a string.
   *
   * @return string
   */
  public function toString() {
    return $this->lat() . ', ' . $this->long();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the wkt component.
   *
   * @return mixed
   */
  public function wkt() {
    return $this->components['wkt'];
  }

  /**
   * Set the wkt component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setWkt($value) {
    $this->components['wkt'] = $value;
    return $this;
  }

  /**
   * Get the geoType component.
   *
   * @return mixed
   */
  public function geoType() {
    return $this->components['geo_type'];
  }

  /**
   * Set the geoType value.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setGeoType($value) {
    $this->components['geo_type'] = $value;
    return $this;
  }

  /**
   * Get the latitude component.
   *
   * @return mixed
   */
  public function lat() {
    return $this->components['lat'];
  }

  /**
   * Set the latitude component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setLat($value) {
    $this->components['lat'] = $value;
    return $this;
  }

  /**
   * Get the longitude component.
   *
   * @return mixed
   */
  public function long() {
    return $this->components['lon'];
  }

  /**
   * Set the longitude component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setLong($value) {
    $this->components['lon'] = $value;
    return $this;
  }

  /**
   * Set the latitude and longitude components.
   *
   * @param float $lat
   * @param float $long
   * @return Geofield
   */
  public function setLatLong($lat, $long) {
    $this->setLat($lat);
    $this->setLong($long);
    return $this;
  }

  /**
   * Get the left component.
   *
   * @return mixed
   */
  public function left() {
    return $this->components['left'];
  }

  /**
   * Set the left component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setLeft($value) {
    $this->components['left'] = $value;
    return $this;
  }

  /**
   * Get the top component.
   *
   * @return mixed
   */
  public function top() {
    return $this->components['top'];
  }

  /**
   * Set the top component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setTop($value) {
    $this->components['top'] = $value;
    return $this;
  }

  /**
   * Get the right component.
   *
   * @return mixed
   */
  public function right() {
    return $this->components['right'];
  }

  /**
   * Set the right component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setRight($value) {
    $this->components['right'] = $value;
    return $this;
  }

  /**
   * Get the bottom component.
   *
   * @return mixed
   */
  public function bottom() {
    return $this->components['bottom'];
  }

  /**
   * Set the bottom component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setBottom($value) {
    $this->components['bottom'] = $value;
    return $this;
  }

  /**
   * Get the srid component.
   *
   * @return mixed
   */
  public function srid() {
    return $this->components['srid'];
  }

  /**
   * Set the srid component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setSrid($value) {
    $this->components['srid'] = $value;
    return $this;
  }

  /**
   * Get the accuracy component.
   *
   * @return mixed
   */
  public function accuracy() {
    return $this->components['accuracy'];
  }

  /**
   * Set the accuracy component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setAccuracy($value) {
    $this->components['accuracy'] = $value;
    return $this;
  }

  /**
   * Get the source component.
   *
   * @return mixed
   */
  public function source() {
    return $this->components['source'];
  }

  /**
   * Set the source component.
   *
   * @param mixed $value
   * @return Geofield
   */
  public function setSource($value) {
    $this->components['source'] = $value;
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Conversion functions.

  /**
   * Convert the geofield to an array.
   *
   * @return array
   */
  public function toArray() {
    return array(
      'wkt' => $this->wkt(),
      'geo_type' => $this->geoType(),
      'lat' => $this->lat(),
      'lon' => $this->long(),
      'left' => $this->left(),
      'top' => $this->top(),
      'right' => $this->right(),
      'bottom' => $this->bottom(),
      'srid' => $this->srid(),
      'accuracy' => $this->accuracy(),
      'source' => $this->source(),
    );
  }

}
