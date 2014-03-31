<?php
/**
 * Encapsulates an addressfield address.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2013-01-17 12:19
 */

namespace AstroMultimedia\Drupal;

use Exception;

class Addressfield {

  /**
   * Stores the addressfield parts.
   *
   * @var array
   */
  private $components;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * Country codes for countries for which addressfield stores state/province codes rather than names.
   *
   * @return array
   */
  protected static function _countriesWithKnownStates() {
    return array('US', 'IT', 'BR', 'CA', 'AU');
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Create an addressfield from an array of components.
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
   * Convert the address to a string.
   *
   * @return string
   */
  public function __toString() {
    return $this->toString();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the continent name.
   *
   * @return string
   */
  public function continent() {
    $continents = geotools_continents();
    return $continents[$this->continentCode()];
  }

  /**
   * Get the continent code.
   *
   * @return string
   */
  public function continentCode() {
    return geotools_country_continent($this->countryCode());
  }

  /**
   * Get the country name.
   *
   * @return string
   */
  public function country() {
    // Get the country code:
    $country_code = $this->countryCode();

    if ($country_code) {
      // Lookup the name:
      $country_info = geotools_lookup_country($country_code);
      return $country_info['name'] ?: NULL;
    }

    // Country not set:
    return NULL;
  }

  /**
   * Get the country code.
   *
   * @return string
   */
  public function countryCode() {
    return $this->components['country'];
  }

  /**
   * Set the country by name or code.
   *
   * @param string $country_name_or_code
   * @return self
   */
  public function setCountry($country_name_or_code) {
    $country_info = geotools_lookup_country($country_name_or_code);
    if (!$country_info) {
      watchdog('classes', "Country '%country_name_or_code' not found.", array('%country_name_or_code' => $country_name_or_code));
      return $this;
    }

    // Set the internal field to the code:
    $this->components['country'] = $country_info['code'];
    return $this;
  }

  /**
   * Get the name of the first-level administrative division (state/province).
   *
   * @return string
   */
  public function state() {
    // Check for null value:
    if (in_array($this->components['administrative_area'], array('', '--'))) {
      return NULL;
    }

    // Get the country code:
    $country_code = $this->countryCode();

    // If this is one of the countries that addressfield stores codes for:
    if ($country_code && in_array($country_code, self::_countriesWithKnownStates())) {
      // Lookup the name:
      $state_info = geotools_lookup_state($country_code, $this->components['administrative_area']);
      return $state_info['name'];
    }

    // Get the name from the internal field:
    return $this->components['administrative_area'];
  }

  /**
   * Get the code of the first-level administrative division (state/province).
   *
   * @return string
   */
  public function stateCode() {
    $country_code = $this->countryCode();

    // If this is one of the countries that addressfield stores state codes for
    if (in_array($country_code, self::_countriesWithKnownStates())) {
      // Get the code from the internal field:
      return $this->components['administrative_area'];
    }

    // Lookup the code:
    $state_info = geotools_lookup_state($country_code, $this->components['administrative_area']);
    return $state_info['code'];
  }

  /**
   * Set the first-level administrative division (state/province) by name or by code.
   * This will also work with numeric state codes.
   *
   * @param string $state_name_or_code
   * @throws Exception
   * @return self
   */
  public function setState($state_name_or_code) {
    $country_code = $this->countryCode();

    if (!$country_code) {
      // If the country is not set, see if we can discover it:
      $country_code = geotools_lookup_country_by_state($state_name_or_code);
      if ($country_code) {
        $this->setCountry($country_code);
      }
      else {
        // Multiple or 0 countries matched:
        throw new Exception("Country not found for state/province '$state_name_or_code'.");
      }
    }

    // Lookup the state:
    $state_info = geotools_lookup_state($country_code, $state_name_or_code);
    if (!$state_info) {
      throw new Exception("State/province '$state_name_or_code' in country '$country_code' not found.");
    }

    // If this is one of the countries that addressfield stores codes for:
    if (in_array($country_code, self::_countriesWithKnownStates())) {
      // Set the internal field to the code:
      $this->components['administrative_area'] = $state_info['code'];
    }
    else {
      // Set the internal field to the name:
      $this->components['administrative_area'] = $state_info['name'];
    }

    return $this;
  }

  /**
   * Get the sub_administrative_area (county) component.
   *
   * @return string
   */
  public function county() {
    return $this->components['sub_administrative_area'];
  }

  /**
   * Set the sub_administrative_area (county) component.
   *
   * @param string $value
   * @return self
   */
  public function setCounty($value) {
    $this->components['sub_administrative_area'] = $value;
    return $this;
  }

  /**
   * Get the locality component.
   *
   * @return string
   */
  public function locality() {
    return $this->components['locality'];
  }

  /**
   * Set the locality component.
   *
   * @param string $value
   * @return self
   */
  public function setLocality($value) {
    $this->components['locality'] = $value;
    return $this;
  }

  /**
   * Get the dependent_locality component.
   *
   * @return string
   */
  public function dependentLocality() {
    return $this->components['dependent_locality'];
  }

  /**
   * Set the dependent_locality component.
   *
   * @param string $value
   * @return self
   */
  public function setDependentLocality($value) {
    $this->components['dependent_locality'] = $value;
    return $this;
  }

  /**
   * Get the postal_code component.
   *
   * @return string
   */
  public function postCode() {
    return $this->components['postal_code'];
  }

  /**
   * Set the postal_code component.
   *
   * @param string $value
   * @return self
   */
  public function setPostCode($value) {
    $this->components['postal_code'] = $value;
    return $this;
  }

  /**
   * Get the thoroughfare component.
   *
   * @return string
   */
  public function thoroughfare() {
    return $this->components['thoroughfare'];
  }

  /**
   * Set the thoroughfare component.
   *
   * @param string $value
   * @return self
   */
  public function setThoroughfare($value) {
    $this->components['thoroughfare'] = $value;
    return $this;
  }

  /**
   * Get the premise component.
   *
   * @return string
   */
  public function premise() {
    return $this->components['premise'];
  }

  /**
   * Set the premise component.
   *
   * @param string $value
   * @return self
   */
  public function setPremise($value) {
    $this->components['premise'] = $value;
    return $this;
  }

  /**
   * Get the sub_premise component.
   *
   * @return string
   */
  public function subPremise() {
    return $this->components['sub_premise'];
  }

  /**
   * Set the sub_premise component.
   *
   * @param string $value
   * @return self
   */
  public function setSubPremise($value) {
    $this->components['sub_premise'] = $value;
    return $this;
  }

  /**
   * Get the organisation_name component.
   *
   * @return string
   */
  public function orgName() {
    return $this->components['organisation_name'];
  }

  /**
   * Set the organisation_name component.
   *
   * @param string $value
   * @return self
   */
  public function setOrgName($value) {
    $this->components['organisation_name'] = $value;
    return $this;
  }

  /**
   * Get the name_line component.
   *
   * @return string
   */
  public function name() {
    return $this->components['name_line'];
  }

  /**
   * Set the name_line component.
   *
   * @param string $value
   * @return self
   */
  public function setName($value) {
    $this->components['name_line'] = $value;
    return $this;
  }

  /**
   * Get the first_name component.
   *
   * @return string
   */
  public function firstName() {
    return $this->components['first_name'];
  }

  /**
   * Set the first_name component.
   *
   * @param string $value
   * @return self
   */
  public function setFirstName($value) {
    $this->components['first_name'] = $value;
    return $this;
  }

  /**
   * Get the last_name component.
   *
   * @return string
   */
  public function lastName() {
    return $this->components['last_name'];
  }

  /**
   * Set the last_name component.
   *
   * @param string $value
   * @return self
   */
  public function setLastName($value) {
    $this->components['last_name'] = $value;
    return $this;
  }

  /**
   * Get the data component.
   *
   * @return mixed
   */
  public function data() {
    return $this->components['data'];
  }

  /**
   * Set the data component.
   *
   * @param mixed $value
   * @return self
   */
  public function setData($value) {
    $this->components['data'] = $value;
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Conversion functions.

  /**
   * Convert the addressfield to an array.
   *
   * @return array
   */
  public function toArray() {
    return $this->components;
  }

  /**
   * Convert the address to a string.
   *
   * @param string $glue
   * @return string
   */
  public function toString($glue = ', ') {
    return implode($glue, array_filter(array(
      trim($this->firstName() . ' ' . $this->lastName()),
      $this->name(),
      $this->orgName(),
      $this->thoroughfare(),
      $this->subPremise(),
      $this->premise(),
      $this->dependentLocality(),
      $this->locality(),
      $this->county(),
      trim($this->state() . ' ' . $this->postCode()),
      $this->country(),
    )));
  }

}
