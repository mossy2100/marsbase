<?php
/**
 * Class to encapsulate D7 users.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 */

namespace AstroMultimedia\Drupal;

use stdClass;
use DateTimeZone;
use Exception;

class User extends Entity implements InterfaceDomainSpecific {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'user';

  /**
   * The bundle name.
   *
   * @var string
   */
  const BUNDLE = 'user';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'users';

  /**
   * The primary key
   *
   * @var string
   */
  const PRIMARY_KEY = 'uid';

  /**
   * The name field.
   *
   * @var string
   */
  const NAME_FIELD = 'name';

  /**
   * The user's timezone.
   *
   * @var DateTimeZone
   */
  protected $timezone;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Constructor.
   */
  protected function __construct() {
    return parent::__construct();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * Create a new User object.
   *
   * @param mixed $param
   * @return self
   * @throws Exception
   */
  public static function create($param = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($param)) {
      // Create new user:
      $user_obj = new $class;

      // It's new:
      $user_obj->entity->is_new = TRUE;

      // Assume active:
      $user_obj->entity->status = 1;

      // Without a uid the user is assumed valid until proven otherwise:
      $user_obj->valid = TRUE;
    }
    elseif (is_pint($param)) {
      // uid provided.
      $uid = (int) $param;

      // Only create the new user if not already in the cache:
      if (self::inCache($uid)) {
        return self::getFromCache($uid);
      }
      else {
        // Create new user:
        $user_obj = new $class;

        // Set the uid:
        $user_obj->entity->uid = $uid;
      }
    }
    elseif (is_string($param)) {
      // Username provided.
      $name = $param;

      // Create new user:
      $user_obj = new $class;

      // Assume active:
      $user_obj->entity->status = 1;

      // Remember the name:
      $user_obj->entity->name = $name;

      // Without a uid the user is assumed valid until proven otherwise:
      $user_obj->valid = TRUE;
    }
    elseif ($param instanceof stdClass) {
      // Drupal user object provided.
      $user = $param;

      // Get the User object:
      if (isset($user->uid) && $user->uid && self::inCache($user->uid)) {
        $user_obj = self::getFromCache($user->uid);
      }
      else {
        $user_obj = new $class;
      }

      // Reference the provided entity object:
      $user_obj->entity = $user;
      
      // Make sure we mark the user as loaded. It may not have been saved yet, and if we load it, any changes to the
      // user entity would be overwritten.
      $user_obj->loaded = TRUE;
    }

    // If we have a user object, add to cache and return:
    if (isset($user_obj)) {
      $user_obj->addToCache();
      return $user_obj;
    }

    throw new Exception("Invalid parameter.");
  }

  /**
   * Get the current user as a User object.
   *
   * @return self
   */
  public static function current() {
    global $user;
    if (!$user) {
      $user = drupal_anonymous_user();
    }
    // Get the calling class:
    $class = get_called_class();
    return $class::create($user);
  }

  /**
   * Find a user by name.
   * Takes the first match found, favouring active over blocked.
   *
   * @param string $name
   * @return User|bool
   */
  public static function findByName($name) {
    $q = "
      select uid
      from users
      where name = :name
      order by status desc, uid asc";
    $rs = db_query($q, array(':name' => $name));
    $rec = $rs->fetchObject();
    return $rec ? self::create($rec->uid) : FALSE;
  }

  /**
   * Find a user by email address.
   * Takes the first match found, favouring active over blocked.
   *
   * @param string $mail
   * @return User|bool
   */
  public static function findByMail($mail) {
    $q = "
      select uid
      from users
      where mail = :mail
      order by status desc, uid asc";
    $rs = db_query($q, array(':mail' => $mail));
    $rec = $rs->fetchObject();
    return $rec ? self::create($rec->uid) : FALSE;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load/save/delete

  /**
   * Load the user object.
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
    $user = FALSE;

    // If we have a uid, try to load the user:
    if ($this->entity->uid) {
      // If we want to force a reload, remove the user from the entity cache:
      if ($force) {
        entity_get_controller(self::ENTITY_TYPE)->resetCache([$this->entity->uid]);
      }
      // Load by uid. Drupal caching will prevent reloading of the same user.
      $user = user_load($this->entity->uid);
    }
    elseif (isset($this->entity->name) && $this->entity->name) {
      // If we want to force a reload, remove all users from the entity cache:
      if ($force) {
        entity_get_controller(self::ENTITY_TYPE)->resetCache();
      }
      // Load by name:
      $user = user_load_by_name($this->entity->name);
    }
    elseif (isset($this->entity->mail) && $this->entity->mail) {
      // If we want to force a reload, remove all users from the entity cache:
      if ($force) {
        entity_get_controller(self::ENTITY_TYPE)->resetCache();
      }
      // Load by mail:
      $user = user_load_by_mail($this->entity->mail);
    }

    // Set the valid flag:
    $this->valid = (bool) $user;

    // If the user was successfully loaded, update fields:
    if ($user) {
      $this->entity = $user;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the user object.
   *
   * @return self
   */
  public function save() {
    // Save the user:
    $this->entity = user_save($this->entity);

    // It's not new any more:
    $this->entity->is_new = FALSE;

    // If the user is new then we should add it to the cache:
    $this->addToCache();

    return $this;
  }

  /**
   * Delete a user.
   */
  public function delete() {
    user_delete($this->uid());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the quick-load fields.
   *
   * @static
   * @return array
   */
  protected static function quickLoadFields() {
    return array('name', 'mail');
  }

  /**
   * Get the uid.
   *
   * @return int
   */
  public function uid() {
    if (!$this->entity->uid) {
      $this->load();
    }
    return isset($this->entity->uid) ? (int) $this->entity->uid : NULL;
  }

  /**
   * Set the uid.
   *
   * @param int $uid
   * @return self
   */
  public function setUid($uid) {
    $this->entity->uid = isset($uid) ? (int) $uid : NULL;
    // Add the user object to the cache if not already:
    $this->addToCache();
    return $this;
  }

  /**
   * Get the user object.
   *
   * @return stdClass
   */
  public function user() {
    return $this->entity();
  }

  /**
   * Get the user's name.
   *
   * @return string
   */
  public function name() {
    return $this->field('name');
  }

  /**
   * Set the user's name.
   *
   * @param string $name
   * @return self
   */
  public function setName($name) {
    return $this->setField('name', ellipsis_trim(trim($name), 60));
  }

  /**
   * Get the user's mail.
   *
   * @return string
   */
  public function mail() {
    return trim($this->field('mail'));
  }

  /**
   * Set the user's mail.
   *
   * @param string
   * @return self
   */
  public function setMail($mail) {
    return $this->setField('mail', trim($mail));
  }

  /**
   * Get the user's picture.
   *
   * @return string
   */
  public function picture() {
    $picture = $this->field('picture');

    // If we only have the fid, load the file object:
    if (is_pint($picture)) {
      $picture = file_load($picture);
      // Remember it:
      $this->setField('picture', $picture);
    }

    return $picture;
  }

  /**
   * Set the user's picture.
   *
   * @param string $source
   * @return self
   */
  public function setPicture($source) {
    // Check the source file exists:
    if (!file_exists($source)) {
      throw new Exception("File not found: $source");
    }

    // We need a uid, so if we don't have one, save the user now:
    if (!$this->uid()) {
      $this->save();
    }

    // Get the image info:
    $image_info = image_get_info($source);
    if (!$image_info) {
      throw new Exception("File is not an image: $source");
    }

    // Get the destination path:
    $picture_directory =  file_default_scheme() . '://' . variable_get('user_picture_path', 'pictures');
    file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
    $filename = 'picture-' . $this->uid() . '-' . REQUEST_TIME . '.' . $image_info['extension'];
    $destination = file_stream_wrapper_uri_normalize("$picture_directory/$filename");

    // Copy the file:
    if (!@copy($source, $destination)) {
      throw new Exception("Could not copy file: $source");
    }

    // Build file object.
    $picture = new stdClass();
    $picture->uid = $this->uid();
    $picture->filename = $filename;
    $picture->uri = $destination;
    $picture->filemime = $image_info['mime_type'];
    $picture->filesize = $image_info['file_size'];
    $picture->status = FILE_STATUS_PERMANENT;

    // Save the file:
    $picture = file_save($picture);
    file_usage_add($picture, 'user', 'user', $this->uid());

    // Delete the previous picture, if there was one:
    $this->deletePicture();

    // Update the picture field:
    return $this->setField('picture', $picture);
  }

  /**
   * Delete the user's picture, if present.
   *
   * @return bool
   *   If the picture was deleted.
   */
  public function deletePicture() {
    // If there's a picture, delete it:
    $picture = $this->picture();
    if ($picture) {
      file_usage_delete($picture, 'user', 'user', $this->uid());
      file_delete($picture);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Get a link to the user's profile.
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
   * Get the user's timezone.
   *
   * @return DateTimeZone
   */
  public function timezone() {
    $tz = $this->field('timezone');
    if (!isset($this->timezone) && $tz) {
      $this->timezone = new DateTimeZone($tz);
    }
    return $this->timezone;
  }

  /**
   * Set the user's timezone.
   *
   * @param mixed $tz
   * @return self
   */
  public function setTimezone($tz) {
    if (isset($tz)) {
      if ($tz instanceof DateTimeZone) {
        $tz = $tz->getName();
      }
      if (!is_string($tz)) {
        throw new Exception("DateTimeZone object or timezone string expected.");
      }
      return $this->setField('timezone', $tz);
    }
    return $this->setField('timezone', NULL);
  }

  /**
   * Get the user's data.
   * If key is specified, just get the value of that data item. Otherwise get the whole array.
   *
   * @param string $key
   * @return array
   */
  public function data($key = NULL) {
    $data = $this->field('data');
    if (is_string($data)) {
      $data = @unserialize($data);
    }
    // Check for a key:
    if (is_array($data) && $key) {
      return isset($data[$key]) ? $data[$key] : NULL;
    }
    // Return the whole data array:
    return $data ?: NULL;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Role-related methods.

  /**
   * Get the user's roles.
   *
   * @return array
   */
  public function roles() {
    $roles = $this->field('roles');
    $role_objects = array();
    foreach ($roles as $rid => $name) {
      $role_objects[] = Role::create($rid, $name);
    }
    return $role_objects;
  }

  /**
   * Add a role to the user.
   *
   * @param mixed $role
   * @return self
   */
  public function addRole($role) {
    if (!$role instanceof Role) {
      $role = Role::create($role);
    }

    $this->entity->roles[$role->rid()] = $role->name();

    return $this;
  }

  /**
   * Remove a role from the user.
   *
   * @param mixed $role
   * @return self
   */
  public function removeRole(Role $role) {
    if (!$role instanceof Role) {
      $role = Role::create($role);
    }

    unset($this->entity->roles[$role->rid()]);
  }

  /**
   * Check if the user has a role.
   *
   * @param mixed $role
   * @return bool
   */
  public function hasRole($role) {
    if (!$role instanceof Role) {
      $role = Role::create($role);
    }

    $this->load();

    if (isset($this->entity->roles) && is_array($this->entity->roles)) {
      foreach ($this->entity->roles as $rid => $name) {
        if ($rid == $role->rid()) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * Check if the user is the current logged-in user.
   *
   * @return bool
   */
  public function isCurrent() {
    global $user;
    return $user && $user->uid && ($this->uid() == $user->uid);
  }

  /**
   * Check if the user is an administrator.
   *
   * @return bool
   */
  public function isAdmin() {
    return $this->hasRole('administrator');
  }

  /**
   * Check if the user is the superuser.
   *
   * @return bool
   */
  public function isSuperUser() {
    return $this->uid() == 1;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Domains (domain module integration)

  /**
   * Get the ids of the domains this user is enabled on.
   * If the domain module is not enabled an exception will be thrown.
   *
   * @return array
   * @throws Exception
   */
  public function domainIds() {
    // Check if the domain module is enabled:
    if (!module_exists('domain')) {
      throw new Exception("Domain module disabled.");
    }

    // Get the current domains:
    $current_domain_ids = $this->field('domain_user');

    // Convert to array of ints:
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
   * Set which domains this user has access to.
   * If the relevant domain module is not enabled an exception will be thrown.
   * NOTE - if this is a new user, this method will save it.
   *
   * @param array|int|Domain $domains
   * @return self
   * @throws Exception
   */
  public function setDomains($domains) {
    // Check if the domain module is enabled:
    if (!module_exists('domain')) {
      throw new Exception("Domain module disabled.");
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
      if ($new_domain instanceof Domain && $new_domain->id()) {
        $domain_id = $new_domain->id();
        $domain_ids[$domain_id] = $domain_id;
      }
      else {
        throw new Exception("Invalid parameter. Array of Domain objects or IDs expected.");
      }
    }

    // If the user isn't saved, we have to save it now otherwise the domains won't stick (untested with users).
    if ($this->isNew()) {
      $this->save();
    }

    // Update the domains fields:
    $this->setField('domain_user', $domain_ids);

    return $this;
  }

  /**
   * Check if a user has access to all domains.
   * This option isn't available for users, hence return FALSE.
   *
   * @return bool
   */
  public function isPublishedOnAllDomains() {
    // This option is not available for users.
    return FALSE;
  }

  /**
   * Set if the user has access to all domains.
   * This option isn't available for users, hence throw exception.
   *
   * @param bool $publish
   * @return self
   * @throws Exception
   */
  public function publishOnAllDomains($publish = TRUE) {
    // This option is not available for users.
    throw new Exception("It isn't possible to assign all domains to users.");
  }

}
