<?php
/**
 * Encapsulates a Drupal field bundle, i.e. a content type, vocabulary, field collection, etc.
 *
 * @file Bundle.module
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2012-11-02 11:15
 */

namespace AstroMultimedia\Drupal;

class Bundle {

  /**
   * The bundle machine name.
   *
   * @var string
   */
  protected $machine_name;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the machine name.
   *
   * @return string
   */
  public function machineName() {
    return $this->machine_name;
  }

  /**
   * Set the machine name.
   *
   * @param string $machine_name
   * @return Bundle
   */
  public function setMachineName($machine_name) {
    $this->machine_name = (string) $machine_name;
    return $this;
  }

}
