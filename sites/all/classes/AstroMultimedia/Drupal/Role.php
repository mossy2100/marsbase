<?php
namespace AstroMultimedia\Drupal;

/**
 * Encapsulates a user role.
 */
class Role {

  /**
   * Role object cache.
   *
   * @var array
   */
  protected static $cache;

  /**
   * The role id.
   *
   * @var int
   */
  protected $rid;

  /**
   * The role name.
   *
   * @var string
   */
  protected $name;

  /**
   * If the role is loaded.
   *
   * @var bool
   */
  protected $loaded;

  /**
   * If the role is valid.
   *
   * @var bool
   */
  protected $valid;

  /**
   * Constructor.
   *
   * @param null|int|string $role_param
   * @param null|string $role_name
   */
  protected function __construct($role_param = NULL, $role_name = NULL) {
    if (is_pint($role_param)) {
      $this->setRid($role_param);
      if (is_string($role_name)) {
        $this->setName($role_name);
      }
    }
    elseif (is_string($role_param)) {
      $this->setName($role_param);
    }
  }

  /**
   * Create a new Role object.
   *
   * @param null|int|string $role_param
   * @param null|string $role_name
   * @return Role
   */
  public static function create($role_param = NULL, $role_name = NULL) {
    // If provided with a rid, check the object cache:
    if (is_pint($role_param) && isset(self::$cache[$role_param])) {
      return self::$cache[$role_param];
    }
    // Create a new object:
    return new self($role_param, $role_name);
  }

  /**
   * Load the role.
   *
   * @return Role
   */
  public function load() {
    // Avoid reloading:
    if ($this->loaded) {
      return $this;
    }

    // Default result:
    $role = FALSE;

    // If we have a rid, try to load the role:
    if ($this->rid) {
      $role = role_load($this->rid);
    }
    elseif ($this->name) {
      $role = role_load($this->name);
    }

    if ($role) {
      // Copy the fields:
      foreach ($role as $key => $value) {
        $this->$key = $value;
      }
    }

    return $this;
  }

  /**
   * Get the role id.
   *
   * @return int
   */
  public function rid() {
    return (int) $this->load()->rid;
  }

  /**
   * Set the role id.
   *
   * @param int $rid
   * @return Role
   */
  public function setRid($rid) {
    $this->rid = isset($rid) ? (int) $rid : NULL;
    return $this;
  }

  /**
   * Get the role name.
   *
   * @return string
   */
  public function name() {
    return $this->load()->name;
  }

  /**
   * Set the role name.
   *
   * @param string $name
   * @return Role
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * Check if the role is loaded.
   *
   * @return bool
   */
  public function loaded() {
    return $this->loaded;
  }

  /**
   * Check if the rid is valid.
   *
   * @return bool
   */
  public function valid() {
    $this->load();
    return $this->valid;
  }

}
