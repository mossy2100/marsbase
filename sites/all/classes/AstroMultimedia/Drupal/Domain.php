<?php
/**
 * Encapsulates a domain as defined by domain module.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2012-11-22 16:06
 */

namespace AstroMultimedia\Drupal;

class Domain {

  /**
   * The domain array as used in the domain module.
   *
   * @var array
   */
  protected $domain_array;

  /**
   * Whether or not the domain has been loaded from the database yet.
   *
   * @var bool
   */
  protected $loaded = FALSE;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * @param $param
   * @return Domain
   */
  public static function create($param = NULL) {
    $domain = new Domain();
    if (is_pint($param)) {
      $domain->domain_array['domain_id'] = $param;
    }
    elseif (is_string($param)) {
      $domain->domain_array['subdomain'] = $param;
    }
    elseif (is_array($param)) {
      $domain->domain_array = $param;
    }
    return $domain;
  }

  /**
   * Get all domains.
   *
   * @return array
   */
  public static function all() {
    $q = db_select('domain', 'd')
      ->fields('d', array('domain_id'))
      ->orderBy('domain_id');
    $rs = $q->execute();
    $domains = array();
    foreach ($rs as $rec) {
      $domains[$rec->domain_id] = Domain::create($rec->domain_id);
    }
    return $domains;
  }

  /**
   * Get the current domain.
   *
   * @return Domain
   */
  public static function current() {
    return self::create(domain_get_domain());
  }

  /**
   * Find a valid (not new) Domain object, given some parameter which could be a Domain object, domain id, or
   * subdomain string.
   *
   * @param mixed $domain
   * @return self
   */
  public static function find($domain) {
    // Default to current domain:
    if (!$domain) {
      return self::current();
    }

    // Get a Domain object:
    if (!($domain instanceof self)) {
      $domain = self::create($domain);
    }

    // Check it's valid:
    if (($domain instanceof self) && $domain->id()) {
      return $domain;
    }

    // Could not find matching domain:
    return NULL;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Private constructor.
   */
  private function __construct() {}

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load/save methods

  /**
   * Load the domain.
   *
   * @return Domain
   */
  public function load() {
    if (!$this->loaded) {
      if ($this->domain_array['domain_id']) {
        $domain = domain_lookup($this->domain_array['domain_id']);
      }
      elseif ($this->domain_array['subdomain']) {
        $domain = domain_lookup(NULL, $this->domain_array['subdomain']);
      }
      if ($domain != -1) {
        // Domain loaded ok:
        $this->domain_array = $domain;
        $this->loaded = TRUE;
      }
    }
    return $this;
  }

  /**
   * Save the domain.
   *
   * @return Domain
   */
  public function save() {
    $this->domain_array = domain_save($this->domain_array);
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the domain id.
   *
   * @return mixed
   */
  public function id() {
    // If we don't have the domain_id but we have the subdomain, we can get the domain_id by loading the domain:
    if (!$this->domain_array['domain_id'] && $this->domain_array['subdomain']) {
      $this->load();
    }
    return (int) $this->domain_array['domain_id'];
  }

  /**
   * Set the domain id.
   *
   * @param int $id
   * @return mixed
   */
  public function setId($id) {
    $this->domain_array['domain_id'] = (int) $id;
    return $this;
  }

  /**
   * Get the subdomain.
   *
   * @return string
   */
  public function subdomain() {
    $this->load();
    return $this->domain_array['subdomain'];
  }

  /**
   * Set the subdomain.
   *
   * @param string $subdomain
   * @return Domain
   */
  public function setSubdomain($subdomain) {
    $this->domain_array['subdomain'] = $subdomain;
    return $this;
  }

  /**
   * Get the site name.
   *
   * @return string
   */
  public function siteName() {
    $this->load();
    return $this->domain_array['sitename'];
  }

  /**
   * Set the site name.
   *
   * @param string $site_name
   * @return Domain
   */
  public function setSiteName($site_name) {
    $this->domain_array['sitename'] = $site_name;
    return $this;
  }

  /**
   * Get the machine name.
   *
   * @return string
   */
  public function machineName() {
    $this->load();
    return $this->domain_array['machine_name'];
  }

  /**
   * Set the machine name.
   *
   * @param string $machine_name
   * @return Domain
   */
  public function setMachineName($machine_name) {
    $this->domain_array['machine_name'] = $machine_name;
    return $this;
  }

  /**
   * Get the is_default field.
   *
   * @return bool
   */
  public function isDefault() {
    $this->load();
    return $this->domain_array['is_default'];
  }

  /**
   * Set the is_default field.
   *
   * @param bool $is_default
   * @return Domain
   */
  public function setIsDefault($is_default) {
    $this->domain_array['is_default'] = $is_default;
    return $this;
  }

  /**
   * Get the weight field.
   *
   * @return int
   */
  public function weight() {
    $this->load();
    return $this->domain_array['weight'];
  }

  /**
   * Set the weight field.
   *
   * @param int $weight
   * @return Domain
   */
  public function setWeight($weight) {
    $this->domain_array['weight'] = $weight;
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Aliases

  /**
   * Get the aliases for this domain.
   *
   * @return array
   */
  public function aliases() {
    return domain_alias_list($this->id());
  }

  /**
   * Update an alias.
   *
   * @param string $pattern
   * @param string $redirect
   * @return array
   */
  public function updateAlias($pattern, $redirect) {
    // Create record array:
    $alias = array(
      'domain_id' => $this->id(),
      'pattern' => $pattern,
      'redirect' => (int) $redirect,
    );
    $pk = array();

    // Find out the alias_id if there is one:
    $q = db_select('domain_alias', 'da')
      ->fields('da', array('alias_id'))
      ->condition('domain_id', $this->id())
      ->condition('pattern', $pattern);
    $rs = $q->execute();
    if ($rs) {
      $rec = $rs->fetch();
      if ($rec) {
        $alias['alias_id'] = $rec->alias_id;
        $pk = 'alias_id';
      }
    }

    // Update the database:
    drupal_write_record('domain_alias', $alias, $pk);
    return $alias;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Conversion functions

  /**
   * Get the array of domain info.
   *
   * @return array
   */
  public function toArray() {
    return $this->domain_array;
  }
}
