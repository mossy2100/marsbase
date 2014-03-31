<?php
/**
 * Encapsulates a Drupal 7 taxonomy vocabulary.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2012-10-10 16:38
 */

namespace AstroMultimedia\Drupal;

use stdClass;
use Exception;

class Vocabulary extends Bundle {

  /**
   * Static cache of entity objects.
   *
   * @var array
   */
  protected static $cache;

  /**
   * The Drupal vocabulary object.
   *
   * @var stdClass
   */
  protected $entity;

  /**
   * If the vocabulary has been loaded yet.
   *
   * @var bool
   */
  protected $loaded;

  /**
   * If the vid is valid, i.e. refers to an actual vocabulary in the database.
   *
   * @var bool
   */
  protected $valid;

  /**
   * The taxonomy terms in this vocabulary.
   *
   * @var array
   */
  protected $terms;

  /**
   * The taxonomy term class.
   *
   * @var array
   */
  protected $termClass;

  /**
   * Private constructor.
   */
  private function __construct() {}

  /**
   * Create a new Vocabulary object.
   *
   * @todo improve this so it uses lazy loading and caching, like entities.
   *
   * @param null|int|string $vocabulary_key
   * @return Vocabulary
   */
  public static function create($vocabulary_key = NULL) {
    if (is_null($vocabulary_key)) {
      $vocab = NULL;
      $vocab_obj = new Vocabulary();
      $vocab_obj->loaded = TRUE;
    }
    elseif (is_pint($vocabulary_key)) {
      $vocab = taxonomy_vocabulary_load($vocabulary_key);
    }
    elseif (is_string($vocabulary_key)) {
      $vocab = taxonomy_vocabulary_machine_name_load($vocabulary_key);
    }

    if ($vocab) {
      $vocab_obj = new Vocabulary();
      $vocab_obj->entity = $vocab;
      $vocab_obj->loaded = TRUE;
    }
    return $vocab_obj;
  }

  /**
   * Create a new Vocabulary object from a Term class.
   *
   * @param null|int|string $vocabulary_key
   * @return Vocabulary
   */
  public static function createFromTermClass($term_class) {
    // The $vocabulary_key may be the term class name:
    if (!class_exists($term_class) || !$term_class::BUNDLE) {
      throw new Exception("Invalid term class name '$term_class'.");
    }

    // Load the vocabulary:
    $vocab = taxonomy_vocabulary_machine_name_load($term_class::BUNDLE);
    if (!$vocab) {
      throw new Exception("Invalid bundle name '" . $term_class::BUNDLE . "'.");
    }

    $vocab_obj = new Vocabulary();
    $vocab_obj->entity = $vocab;
    $vocab_obj->loaded = TRUE;
    $vocab_obj->termClass = $term_class;
    return $vocab_obj;
  }

  /**
   * Get all the terms in the vocabulary.
   *
   * @return array
   */
  public function terms() {
    if (!isset($this->terms)) {
      if (!$this->entity) {
        throw new Exception("Invalid vocabulary.");
      }

      if (!$this->termClass) {
        throw new Exception("Term class not set.");
      }

      // Get the terms:
      $tree = taxonomy_get_tree($this->entity->vid);
      $this->terms = array();
      // Create Term objects:
      foreach ($tree as $term) {
        $term_class = $this->termClass;
        $this->terms[$term->tid] = $term_class::create($term, $this);
      }
    }
    return $this->terms;
  }

  /**
   * Get the vid.
   *
   * @return int|null
   */
  public function vid() {
    return isset($this->entity->vid) ? (int) $this->entity->vid : NULL;
  }

  /**
   * Get the vocabulary machine name.
   *
   * @override Bundle::machineName()
   * @return string
   */
  public function machineName() {
    return $this->entity->machine_name;
  }

  /**
   * Set the vocabulary machine name.
   *
   * @override Bundle::setMachineName()
   * @param string $machine_name
   * @return Vocabulary
   */
  public function setMachineName($machine_name) {
    $this->entity->machine_name = (string) $machine_name;
    return parent::setMachineName($this->entity->machine_name);
  }

  /**
   * Get the term class.
   *
   * @return string
   */
  public function termClass() {
    return $this->termClass;
  }

  /**
   * Set the term class.
   *
   * @param string
   * @return Vocabulary
   * @throws Exception
   */
  public function setTermClass($class) {
    if (!class_exists($class) || !is_subclass_of($class, '\\AstroMultimedia\\Drupal\\Term')) {
      throw new Exception("'$class' is not a valid class.'");
    }

    $this->termClass = $class;
    return $this;
  }

  /**
   * Check if the vocabulary is domain-specific.
   *
   * @return bool
   */
  public function isDomainSpecific() {
    // Check if the domain_tax module is enabled:
    if (!module_exists('domain_tax')) {
      return FALSE;
    }

    return in_array($this->vid(), variable_get('domain_tax_voc', array()));
  }

  /**
   * Get the terms as a hierarchical tree.
   * @todo update this so that an array of Term objects is returned.
   *
   * @return array
   */
  public function tree() {
    return taxonomy_get_tree($this->vid());
  }

  /**
   * Get the terms for a given vocabulary that are permitted on the current site/domain, as options for a select list
   * or a checkbox tree.
   *
   * @return array
   */
  public function options() {
    $domain_specific = $this->isDomainSpecific();

    // If this is a domain-specific vocabulary, get the allowed tids:
    if ($domain_specific) {
      $allowed_tids = domain_tax_get_allowed_tids(Domain::current()->id(), $this->vid);
    }

    // Get the vocabulary terms as a tree:
    $terms = $this->tree();

    // Collect valid terms:
    $options = array();
    foreach ($terms as $term) {
      if (!$domain_specific || in_array($term->tid, $allowed_tids)) {
        $options[$term->tid] = str_repeat('-', $term->depth) . $term->name;
      }
    }

    return $options;
  }

}
