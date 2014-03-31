<?php
/**
 * Class to encapsulate D7 taxonomy terms.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2012-10-10 16:38
 */

namespace AstroMultimedia\Drupal;

use stdClass;
use Exception;

class Term extends Entity implements InterfaceDomainSpecific {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'taxonomy_term';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'taxonomy_term_data';

  /**
   * The primary key.
   *
   * @var string
   */
  const PRIMARY_KEY = 'tid';

  /**
   * The name field.
   *
   * @var string
   */
  const NAME_FIELD = 'name';

  /**
   * An array of all terms, organised by vocabulary. Static cache for all() method.
   *
   * @var array
   */
  public static $all;

  /**
   * The vocabulary that this term belongs to.
   *
   * @var Vocabulary
   */
  protected $vocabulary;

  /**
   * The term's parents, if any.
   *
   * @var array
   */
  protected $parents;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * Create a new Term object.
   *
   * @param mixed $param
   * @throws Exception
   * @return self
   */
  public static function create($param = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($param)) {
      // Create new term:
      $term_obj = new $class;

      // If the vocabulary is known, reference it in the new entity:
      $vocab_name = $class::BUNDLE;
      if ($vocab_name) {
        $vocab = Vocabulary::create($vocab_name);
        if ($vocab) {
          $term_obj->vocabulary = $vocab;
          $term_obj->entity->vid = $vocab->vid();
        }
      }

      // It's new:
      $term_obj->entity->is_new = TRUE;

      // The new term is valid without a tid:
      $term_obj->valid = TRUE;
    }
    elseif (is_pint($param)) {
      // tid provided:
      $tid = (int) $param;

      // Only create the new term if not already in the cache:
      if (self::inCache($tid)) {
        return self::getFromCache($tid);
      }
      else {
        // Create new term:
        $term_obj = new $class;

        // Set the tid:
        $term_obj->entity->tid = $tid;
      }
    }
    elseif (is_string($param)) {
      // Name provided:
      $name = $param;

      // Look up the term:
      $term_obj = self::findByName($name);

      // If the term wasn't found, create a new one:
      if (!$term_obj) {
        $term_obj = self::create();

        // Set the name:
        $term_obj->entity->name = $name;
      }
    }
    elseif ($param instanceof stdClass) {
      // Drupal term object provided:
      $term = $param;

      // Get the object from the cache if possible:
      if ($term->tid && self::inCache($term->tid)) {
        $term_obj = self::getFromCache($term->tid);
      }
      else {
        $term_obj = new $class;
      }

      // Reference the provided entity object:
      $term_obj->entity = $term;

      // Make sure we mark the term as loaded. It may not have been saved yet, and if we load it, any changes to the
      // term entity would be overwritten.
      $term_obj->loaded = TRUE;
    }

    // If we have a term object, add to cache and return:
    if (isset($term_obj)) {
      $term_obj->addToCache();
      return $term_obj;
    }

    throw new Exception("Invalid parameter " . var_to_string($param) . ".");
  }

  /**
   * Find a term by name.
   *
   * @param string $name
   * @return mixed
   */
  public static function findByName($name) {
    // Create a query to find the first matching term:
    $q = db_select('taxonomy_term_data', 'td')
      ->fields('td', array('tid', 'vid', 'description'))
      ->condition('name', $name)
      ->orderBy('tid');

    // If the vocabulary is known, include it in the query:
    $class = get_called_class();
    $vocab_name = $class::BUNDLE;
    if ($vocab_name) {
      $vocab = Vocabulary::create($vocab_name);
      if ($vocab) {
        $q->condition('vid', $vocab->vid());
      }
    }

    $rs = $q->execute();

    // Take the first matching result:
    $rec = $rs->fetchObject();
    if ($rec) {
      // Create the Term object:
      $term_obj = $class::create($rec->tid);
      // Copy some fields from the database record:
      $term_obj->entity->name = $name;
      $term_obj->entity->description = $rec->description;
      $term_obj->entity->vid = (int) $rec->vid;
      $term_obj->vocabulary = $vocab ?: Vocabulary::create($rec->vid);
      // The term has been loaded:
      $term_obj->loaded = TRUE;
      // The term is not new:
      $term_obj->entity->is_new = FALSE;
    }

    return $term_obj;
  }

  /**
   * Get all terms, optionally filtered by vocabulary, and/or domain.
   * The vocabulary is determined by the derived class from where the method is called.
   *
   * @param bool $status
   *   This parameter isn't relevant for terms, but kept for consistency with Nodes and Users.
   * @param mixed $domain
   *   If NULL, only return nodes published on the current domain.
   *   If int or Domain, only return nodes published on the specified domain.
   *   If FALSE, don't filter by domain.
   * @param string $order_by
   * @throws Exception
   * @return array
   */
  public static function all($status = TRUE, $domain = NULL, $order_by = 'td.weight') {
    // Get the derived class and the vocabulary name:
    $class = get_called_class();
    $vocab_name = $class::BUNDLE;

    // Check cache:
    if (!isset(self::$all[$vocab_name])) {
      // Get the tids:
      $q = db_select('taxonomy_term_data', 'td')
        ->fields('td', array('tid'));

      // Filter by vocabulary if known:
      if ($vocab_name) {
        $q->join('taxonomy_vocabulary', 'tv', 'td.vid = tv.vid');
        $q->condition('tv.machine_name', $vocab_name);
      }

      // Filter by domain if requested:
      if (module_exists('domain_tax')) {
        if ($domain !== FALSE) {
          // Get the domain to filter on:
          $domain = Domain::find($domain);
          if (!$domain) {
            throw new Exception("Invalid domain.");
          }

          // Get terms published on specified domain:
          $q->condition(db_or()
              ->condition('td.tid',
                db_select('domain_tax', 'dt')
                  ->fields('dt', ['tid'])
                  ->condition('domain_id', [$domain->id(), 0]),
                'IN'
              )
              ->condition('td.tid',
                db_select('domain_tax', 'dt')
                  ->fields('dt', ['tid']),
                'NOT IN'
              )
          );
        }
      }
      else {
        // Domain taxonomy module not enabled:
        if ($domain) {
          throw new Exception("Domain taxonomy module not enabled.");
        }
      }

      // Order by:
      $q->orderBy($order_by, 'ASC');

      // Get the terms:
      $rs = $q->execute();
      self::$all[$vocab_name] = [];
      foreach ($rs as $rec) {
        self::$all[$vocab_name][] = $class::create($rec->tid);
      }
    }

    return self::$all[$vocab_name];
  }

  /**
   * Get tids of terms that are children of the provided term or tid.
   *
   * @param mixed $parent
   * @throws Exception
   * @return array
   */
  public static function childIdsOf($parent = NULL) {
    // Get the parent tid from the provided parameter:
    if (!$parent) {
      $parent_tid = 0;
    }
    elseif ($parent instanceof Term) {
      $parent_tid = $parent->id();
    }
    elseif (is_pint($parent)) {
      $parent_tid = (int) $parent;
    }
    else {
      throw new Exception("Invalid parent term.");
    }

    // Construct the query:
    $q = db_select('taxonomy_term_data', 'td')
      ->fields('td', array('tid'))
      ->orderBy('td.weight');
    $q->leftJoin('taxonomy_term_hierarchy', 'th', "td.tid = th.tid");
    $q->condition('th.parent', $parent_tid);

    // Limit to vocab if known:
    $class = get_called_class();
    $vocab_name = $class::BUNDLE;
    if ($vocab_name) {
      $vocab = Vocabulary::create($vocab_name);
      $q->condition('td.vid', $vocab->vid());
    }

    // Get the terms:
    $rs = $q->execute();
    $tids = array();
    foreach ($rs as $rec) {
      $tids[$rec->tid] = $rec->tid;
    }
    return $tids;
  }

  /**
   * Get all terms that are children of the provided term or tid.
   *
   * @param mixed $parent
   * @throws Exception
   * @return array
   */
  public static function childrenOf($parent = NULL) {
    $class = get_called_class();
    $tids = $class::childIdsOf($parent);

    // Get the terms:
    foreach ($tids as $tid) {
      $terms[$tid] = $class::create($tid);
    }
    return $terms;
  }

  /**
   * Get all top-level terms.
   *
   * @return array
   */
  public static function allTopLevel() {
    return self::childrenOf(0);
  }

  /**
   * Get a set of all the parents of all the provided terms.
   *
   * @param array $terms
   * @param bool $include_original
   *   If TRUE the result will include all terms from the $terms array; otherwise not.
   * @return array
   *   Keys are tids, values are terms.
   */
  public static function multipleAncestors(array $terms, $include_original = FALSE) {
    $terms2 = array();
    // For each term:
    foreach ($terms as $term) {

      // Include the original if requested:
      if ($include_original) {
        $terms2[$term->tid()] = $term;
      }

      // Get the ancestors:
      $ancestors = $term->ancestors();
      foreach ($ancestors as $ancestor) {
        if ($include_original || !in_array($ancestor, $terms)) {
          $terms2[$ancestor->tid()] = $ancestor;
        }
      }
    }

    ksort($terms2);
    return $terms2;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Constructor.
   */
  protected function __construct() {
    // Create the object:
    parent::__construct();

    // Set the vocabulary:
    $class = get_called_class();
    $this->vocabulary = Vocabulary::create($class::BUNDLE);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load, save, delete

  /**
   * Load the term object.
   *
   * @param bool $force
   * @return self
   */
  public function load($force = FALSE) {
    // Avoid reloading:
    if ($this->loaded && !$force) {
      return $this;
    }

    // Default result:
    $term = FALSE;

    // If we have a tid, try to load the term:
    if ($this->entity->tid) {
      // If we want to force a reload, remove the term from the entity cache:
      if ($force) {
        entity_get_controller(self::ENTITY_TYPE)->resetCache([$this->entity->tid]);
      }
      // Load by tid. Drupal caching will prevent reloading of the same term.
      $term = taxonomy_term_load($this->entity->tid);
    }

    // Set the valid flag:
    $this->valid = (bool) $term;

    // If the term was successfully loaded, update fields:
    if ($term) {
      $this->entity = $term;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the term object.
   *
   * @return self
   */
  public function save() {
    // Ensure the term is loaded if not already:
    $this->load();

    // Save the term:
    taxonomy_term_save($this->entity);

    // It's not new any more:
    $this->entity->is_new = FALSE;

    // If the term is new, the tid has now been set, so add it to the cache:
    $this->addToCache();

    return $this;
  }

  /**
   * Delete a term.
   */
  public function delete() {
    taxonomy_term_delete($this->tid());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the tid.
   *
   * @return int
   */
  public function tid() {
    return $this->entity->tid ? (int) $this->entity->tid : NULL;
  }

  /**
   * Set the tid.
   *
   * @param int $tid
   * @return self
   */
  public function setTid($tid) {
    $this->entity->tid = isset($tid) ? (int) $tid : NULL;
    // Add the term object to the cache if not already:
    $this->addToCache();
    return $this;
  }

  /**
   * Get the term object.
   *
   * @return stdClass
   */
  public function term() {
    return $this->entity();
  }

  /**
   * Get the term's name.
   *
   * @return string
   */
  public function name() {
    return $this->field('name');
  }

  /**
   * Set the term's name.
   *
   * @param string $name
   * @return self
   */
  public function setName($name) {
    return $this->setField('name', ellipsis_trim(trim($name), 255));
  }

  /**
   * Get the term's description.
   *
   * @return string
   */
  public function description() {
    return $this->field('description');
  }

  /**
   * Set the term's description.
   *
   * @param string $description
   * @return self
   */
  public function setDescription($description) {
    return $this->setField('description', $description);
  }

  /**
   * Get the term's vocabulary id.
   *
   * @return int
   */
  public function vid() {
    return (int) $this->field('vid');
  }

  /**
   * Set the term's vocabulary id.
   *
   * @param int $vid
   * @return self
   */
  public function setVid($vid) {
    return $this->setField('vid', $vid);
  }

  /**
   * Get the term's vocabulary.
   * We don't use the BUNDLE of the derived class, we use the vid which is more reliable.
   *
   * @return Vocabulary
   */
  public function vocabulary() {
    if (!isset($this->vocabulary)) {
      $this->vocabulary = Vocabulary::create($this->vid());
    }
    return $this->vocabulary;
  }

  /**
   * Get the term's weight.
   *
   * @return int
   */
  public function weight() {
    return $this->field('weight');
  }

  /**
   * Set the term's weight.
   *
   * @param int $weight
   * @return self
   */
  public function setWeight($weight) {
    return $this->setField('weight', $weight);
  }

  /**
   * Get the term's parents.
   *
   * @throws Exception
   * @return array
   */
  public function parents() {
    $class = get_called_class();
    if ($class == __CLASS__) {
      throw new Exception("Must be called from a class derived from Term.");
    }

    // Cache the result:
    if (!isset($this->parents)) {
      $this->parents = array();
      if ($this->tid()) {
        // Get the parent tids:
        $parents = taxonomy_get_parents($this->tid());
        foreach ($parents as $parent) {
          $this->parents[$parent->tid] = $class::create($parent->tid);
        }
      }
    }

    return $this->parents;
  }

  /**
   * Get the term's parents, grandparents, etc...
   *
   * @throws Exception
   * @return array
   */
  public function ancestors() {
    $class = get_called_class();
    if ($class == __CLASS__) {
      throw new Exception("Must be called from a class derived from Term.");
    }

    // Cache the result:
    if (!isset($this->ancestors)) {
      $this->ancestors = array();
      if ($this->tid()) {
        // Get the parent tids:
        $ancestors = taxonomy_get_parents_all($this->tid());
        foreach ($ancestors as $ancestor) {
          // Don't include this term:
          if ($ancestor->tid != $this->tid()) {
            // Add to array:
            $this->ancestors[$ancestor->tid] = $class::create($ancestor->tid);
          }
        }
      }
    }

    return $this->ancestors;
  }

  /**
   * Get the term's children.
   *
   * @throws Exception
   * @return array
   */
  public function children() {
    $class = get_called_class();
    if ($class == __CLASS__) {
      throw new Exception("Must be called from a class derived from Term.");
    }

    // Cache the result:
    if (!isset($this->children)) {
      $this->children = array();
      if ($this->tid()) {
        // Get the child tids:
        $children = taxonomy_get_children($this->tid());
        foreach ($children as $child) {
          $this->children[$child->tid] = $class::create($child->tid);
        }
      }
    }

    return $this->children;
  }

  /**
   * Get the term's children, grandchildren, etc...
   *
   * @throws Exception
   * @return array
   */
  public function descendants() {
    $class = get_called_class();
    if ($class == __CLASS__) {
      throw new Exception("Must be called from a class derived from Term.");
    }

    // Cache the result:
    if (!isset($this->descendants)) {
      $this->descendants = array();
      // Get the child tids:
      $children = $this->children();
      foreach ($children as $child) {
        $this->descendants[$child->tid()] = $child;
        $child_descendants = $child->descendants();
        foreach ($child_descendants as $child_descendant) {
          $this->descendants[$child_descendant->tid()] = $child_descendant;
        }
      }
    }

    return $this->descendants;
  }

  /**
   * Set the term's parent terms.
   *
   * @param array $parents
   * @return self
   */
  public function setParents(array $parents) {
    $parent_tids = array();
    foreach ($parents as $parent) {
      if ($parent instanceof Term) {
        $parent_tids[] = $parent->tid();
      }
      elseif (is_pint($parent)) {
        $parent_tids[] = $parent;
      }
      elseif (is_array($parent) && $parent['tid']) {
        $parent_tids[] = $parent['tid'];
      }
    }

    return $this->setField('parent', $parent_tids);
  }

  /**
   * Set the term's parent term.
   *
   * @param Term|int $parent
   * @return self
   */
  public function setParent($parent) {
    return $this->setParents(array($parent));
  }

  /**
   * Get the first parent (immediate parent, not ancestor) of the term. There is usually only one anyway.
   *
   * @return self|null
   */
  public function parent() {
    $parents = $this->parents();
    return array_shift($parents);
  }

  /**
   * Return the tid of the parent term.
   * The result matches the value in taxonomy_term_hierarchy, i.e. if the term has no parent, return 0.
   *
   * @return int
   */
  public function parentTid() {
    $parent = $this->parent();
    return $parent ? $parent->tid() : 0;
  }

  /**
   * If the term has a parent or not.
   *
   * @return bool
   */
  public function hasParent() {
    return (bool) $this->parent();
  }

  /**
   * If the term is top level, i.e. has no parent.
   *
   * @return bool
   */
  public function isTopLevel() {
    return !count($this->parents());
  }

  /**
   * If this term is the parent of another term.
   *
   * @param Term $term
   * @return bool
   */
  public function isParentOf(Term $term) {
    $parent = $term->parent();
    return $parent ? $this->equals($parent) : FALSE;
  }

  /**
   * If this term is the child of another term.
   *
   * @param Term $term
   * @return bool
   */
  public function isChildOf(Term $term) {
    return $term->isParentOf($this);
  }

  /**
   * If this term is the ancestor of another term.
   *
   * @param Term $term
   * @return bool
   */
  public function isAncestorOf(Term $term) {
    $ancestors = $term->ancestors();
    foreach ($ancestors as $ancestor) {
      if ($ancestor->equals($this)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * If this term is the descendant of another term.
   *
   * @param Term $term
   * @return bool
   */
  public function isDescendantOf(Term $term) {
    return $term->isAncestorOf($this);
  }

  /**
   * Get a link to the term's page.
   *
   * @param null|string $label
   * @param bool $absolute
   * @param array $options
   * @return string
   */
  public function link($label = NULL, $absolute = FALSE, array $options = array()) {
    $label = ($label === NULL) ? $this->name() : $label;
    return parent::link($label, $absolute, $options);
  }

  /**
   * Get the depth.
   *
   * @return int
   */
  public function depth() {
    $this->load();
    return $this->entity->depth;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Domain methods (domain_tax module integration)

  /**
   * Get the ids of the domains this term is published on.
   * If the domain taxonomy module is not enabled an exception will be thrown.
   *
   * @throws Exception
   * @return array
   */
  public function domainIds() {
    // Check if the domain_tax module is enabled:
    if (!module_exists('domain_tax')) {
      throw new Exception("Domain taxonomy module disabled.");
    }

    // Get the current domains:
    $current_domain_ids = $this->field('domain_tax');

    // Collect domain ids:
    $domain_ids = array();
    if (is_array($current_domain_ids)) {
      foreach ($current_domain_ids as $domain_id) {
        if ($domain_id) {
          $domain_ids[$domain_id] = $domain_id;
        }
      }
    }
    return $domain_ids;
  }

  /**
   * Set the domains this term is published on.
   * If the domain taxonomy module is not enabled an exception will be thrown.
   *
   * @param array|int|Domain $domains
   * @throws Exception
   * @return self
   */
  public function setDomains($domains) {
    // Check if the domain_tax module is enabled:
    if (!module_exists('domain_tax')) {
      throw new Exception("Domain taxonomy module disabled.");
    }

    // Allow for a single domain to be passed:
    if (!is_array($domains)) {
      $domains = array($domains);
    }

    // Collect the domain ids and check that we only have Domain objects or domain ids:
    $domain_ids = array();
    foreach ($domains as $new_domain) {
      if (is_pint($new_domain)) {
        $new_domain = Domain::create($new_domain);
      }
      if ($new_domain instanceof Domain) {
        $domain_id = $new_domain->id();
        $domain_ids[$domain_id] = $domain_id;
      }
      else {
        throw new Exception("Invalid parameter. Array of Domain objects or IDs expected.");
      }
    }

    // Update the field:
    $this->setField('domain_tax', $domain_ids);

    // It's weird that this line is needed, but it is:
    $this->setField('domain_tax_all', $this->isPublishedOnAllDomains());

    return $this;
  }

  /**
   * Check if a term is published on all domains.
   * If the domain taxonomy module is not enabled an exception will be thrown.
   *
   * @throws Exception
   * @return bool
   */
  public function isPublishedOnAllDomains() {
    // Check if the domain_tax module is enabled:
    if (!module_exists('domain_tax')) {
      throw new Exception("Domain taxonomy module disabled.");
    }

    // Get the current domains:
    $domains = $this->field('domain_tax');
    $published_all_domains = $this->field('domain_tax_all');

    // Check if the term is published on all domains:
    return $published_all_domains || (is_array($domains) && array_key_exists(0, $domains));
  }

  /**
   * Set if the term is published on all domains.
   * If the domain taxonomy module is disabled an exception will be thrown.
   *
   * @throws Exception
   * @param bool $publish
   * @return Node
   */
  public function publishOnAllDomains($publish = TRUE) {
    // Check if the domain_tax module is enabled:
    if (!module_exists('domain_tax')) {
      throw new Exception("Domain taxonomy module disabled.");
    }

    // Set if the term is published on all domains:
    $domains = $this->domainIds();
    if ($publish) {
      $domains[0] = '0';
    }
    else {
      unset($domains[0]);
    }

    // Update fields:
    $this->setField('domain_tax', $domains);
    $this->setField('domain_tax_all', (bool) $publish);

    return $this;
  }

}
