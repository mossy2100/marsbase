<?php
/**
 * Class to encapsulate D7 nodes.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 */

namespace AstroMultimedia\Drupal;

use stdClass;
use Exception;

class Node extends Entity implements InterfaceDomainSpecific {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'node';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'node';

  /**
   * The primary key.
   *
   * @var string
   */
  const PRIMARY_KEY = 'nid';

  /**
   * The name field.
   *
   * @var string
   */
  const NAME_FIELD = 'title';

  /**
   * An array of all nodes, organised by type. Static cache for all() method.
   *
   * @var array
   */
  public static $all;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * Create a new Node object.
   *
   * @param mixed $param
   * @throws \Exception
   * @return self
   */
  public static function create($param = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($param)) {
      // Create new node:
      $node_obj = new $class;

      // It's new:
      $node_obj->setIsNew(TRUE);

      // Default status to published:
      $node_obj->publish(1);

      // Default language to none:
      $node_obj->entity->language = LANGUAGE_NONE;

      // Default user to current user:
      $node_obj->setUid(user_is_logged_in() ? $GLOBALS['user']->uid : 0);

      // The node is valid without a nid:
      $node_obj->setValid(TRUE);
    }
    elseif (is_pint($param)) {
      // nid provided:
      $nid = (int) $param;

      // Only create the new node if not already in the cache:
      if (self::inCache($nid)) {
        return self::getFromCache($nid);
      }
      else {
        // Create new node:
        $node_obj = new $class;

        // Set the nid:
        $node_obj->setNid($nid);
      }
    }
    elseif (is_string($param)) {
      // Node title provided:
      $title = $param;

      // Look up the node:
      $node_obj = self::findByTitle($title);

      // If the node wasn't found, create a new one:
      if (!$node_obj) {
        $node_obj = self::create();

        // Set the title:
        $node_obj->setTitle($title);
      }
    }
    elseif ($param instanceof stdClass) {
      // Drupal node object provided:
      $node = $param;

      // Get the object from the cache if possible:
      if ($node->nid && self::inCache($node->nid)) {
        $node_obj = self::getFromCache($node->nid);
      }
      else {
        $node_obj = new $class;
      }

      // Reference the provided entity object:
      $node_obj->entity = $node;

      // Make sure we mark the node as loaded. It may not have been saved yet, and if we load it, any changes to the
      // node entity would be overwritten.
      $node_obj->loaded = TRUE;
    }

    // If we have a node object, add to cache and return:
    if (isset($node_obj)) {
      $node_obj->addToCache();
      return $node_obj;
    }

    throw new Exception("Invalid parameter: " . var_to_string($param));
  }

  /**
   * Find a node by title.
   *
   * @param string $title
   * @return mixed
   */
  public static function findByTitle($title) {
    // Get the calling class:
    $class = get_called_class();

    // Create a query to find the first matching node. Just get the quickload fields.
    $q = db_select('node', 'n')
      ->fields('n', array('nid', 'title', 'type', 'uid'))
      ->condition('title', $title);

    // Filter by node type if known:
    $node_type = $class::BUNDLE;
    if ($node_type) {
      $q->condition('type', $node_type);
    }

    // Favour published nodes ahead of unpublished, ordered by nid:
    $q->orderBy('status', 'DESC')
      ->orderBy('nid');

    // Take the first matching result:
    $rs = $q->execute();
    $rec = $rs->fetchObject();
    if ($rec) {
      // Create the Node object:
      $node_obj = $class::create($rec->nid);

      // Copy some fields from the database record:
      $node_obj->setTitle($title);
      $node_obj->setNodeType($rec->type);
      $node_obj->setUid($rec->uid);

      // The node has not been loaded:
      $node_obj->loaded = FALSE;

      // The node is not new:
      $node_obj->setIsNew(FALSE);
    }

    return $node_obj;
  }

  /**
   * Get the quick-load fields.
   *
   * @static
   * @return array
   */
  protected static function quickLoadFields() {
    return array('title', 'type', 'uid');
  }

  /**
   * Get all nodes, optionally filtered by node type, published status, and/or domain.
   * The node type is determined by the derived class from where the method is called.
   *
   * @param bool $status
   *   If TRUE, gets all published nodes of the specified type.
   *   If FALSE, gets all unpublished nodes of the specified type.
   *   If NULL, don't filter by status.
   * @param mixed $domain
   *   If int or Domain, only return nodes published on the specified domain.
   *   If NULL, only return nodes published on the current domain.
   *   If FALSE, don't filter by domain.
   * @param string $order_by
   * @throws Exception
   * @return array
   */
  public static function all($status = TRUE, $domain = NULL, $order_by = 'n.title') {
    // Get the derived class and the node type:
    $class = get_called_class();
    $node_type = $class::BUNDLE;

    // Check cache:
    if (!isset(self::$all[$node_type])) {
      // Get the nids:
      $q = db_select('node', 'n')
        ->fields('n', array('nid'));

      // Filter by node type if known:
      if ($node_type) {
        $q->condition('type', $node_type);
      }

      // Restrict to published or unpublished if specified:
      if (isset($status)) {
        $q->condition('status', $status ? 1 : 0);
      }

      // Filter by domain:
      if (module_exists('domain')) {
        // Domain module enabled:
        if ($domain !== FALSE) {
          $domain = Domain::find($domain);
          if (!$domain) {
            throw new Exception("Invalid domain.");
          }

          // Get nodes published on specified domain:
          $q->leftJoin('domain_access', 'da', "n.nid = da.nid");
          $q->condition(
            db_or()
              ->condition(
                db_and()
                  ->condition('da.gid', $domain->id())
                  ->condition('da.realm', 'domain_id'))
              ->condition(
                db_and()
                  ->condition('da.gid', 0)
                  ->condition('da.realm', 'domain_site')));
        }
      }
      else {
        // Domain module not enabled:
        if ($domain) {
          throw new Exception("Domain module not enabled.");
        }
      }

      // Order by:
      $q->orderBy($order_by, 'ASC');

      // Get the nodes:
      $rs = $q->execute();
      self::$all[$node_type] = [];
      foreach ($rs as $rec) {
        self::$all[$node_type][] = $class::create($rec->nid);
      }
    }

    return self::$all[$node_type];
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Constructor.
   */
  protected function __construct() {
    // Create the object:
    parent::__construct();

    // Set the node type:
    $class = get_called_class();
    $this->entity->type = $class::BUNDLE;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load, save, delete

  /**
   * Load the node object.
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
    $node = FALSE;

    // If we have a nid, try to load the node:
    if ($this->entity->nid) {
      // If we want to force a reload, remove the node from the entity cache:
      if ($force) {
        entity_get_controller(self::ENTITY_TYPE)->resetCache([$this->entity->nid]);
      }
      // Load by nid. Drupal caching will prevent reloading of the same node.
      $node = node_load($this->entity->nid);
    }

    // Set the valid flag:
    $this->valid = (bool) $node;

    // If the node was successfully loaded, update fields:
    if ($node) {
      $this->entity = $node;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the node object.
   *
   * @return self
   */
  public function save() {
    // Ensure the node is loaded:
    $this->load();

    // We must set the pathauto flag so any custom alias doesn't get clobbered.
    $this->setPathauto();

    // Save the node:
    node_save($this->entity);

    // It's not new any more:
    $this->entity->is_new = FALSE;

    // In case the node is new, add it to the cache:
    $this->addToCache();

    return $this;
  }

  /**
   * Delete a node.
   */
  public function delete() {
    node_delete($this->nid());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the nid.
   *
   * @return int
   */
  public function nid() {
    return isset($this->entity->nid) ? (int) $this->entity->nid : NULL;
  }

  /**
   * Set the nid.
   *
   * @param int $nid
   * @return self
   */
  public function setNid($nid) {
    $this->entity->nid = isset($nid) ? (int) $nid : NULL;
    // Add the node object to the cache if not already:
    $this->addToCache();
    return $this;
  }

  /**
   * Get the node object.
   *
   * @return stdClass
   */
  public function node() {
    return $this->entity();
  }

  /**
   * Get the node's title.
   *
   * @return string
   */
  public function title() {
    return $this->field('title');
  }

  /**
   * Alias for title().
   *
   * @return string
   */
  public function name() {
    return $this->title();
  }

  /**
   * Set the node's title.
   * Removes any HTML tags and character entities from the title, and trims (with ellipsis) to maximum 255 chars.
   * Title is a required field, so if no value is given, use '- Untitled -'.
   *
   * @param string $title
   * @return self
   */
  public function setTitle($title) {
    $title = isset($title) ? drupal_format_title($title) : NULL;
    if (!$title) {
      $title = '- Untitled -';
    }
    return $this->setField('title', $title);
  }

  /**
   * Get the node's type.
   *
   * Note as a rule we never say just 'type' because it's far too easy to get node type, entity type, field type,
   * relation type, etc., mixed up, which is a source of bugs.
   *
   * @return string
   */
  public function nodeType() {
    // If getting, we could theoretically just return self::BUNDLE here. However, by checking the entity property
    // this function can be used to check if the node referenced by the $entity property is the correct type.
    return $this->field('type');
  }

  /**
   * Set the node's type.
   *
   * @param string $type
   * @return self
   */
  public function setNodeType($type) {
    return $this->setField('type', $type);
  }

  /**
   * Get the node's uid.
   *
   * @return int
   */
  public function uid() {
    return (int) $this->field('uid');
  }

  /**
   * Set the node's uid.
   *
   * @param int
   * @return self
   */
  public function setUid($uid) {
    return $this->setField('uid', $uid);
  }

  /**
   * Get the node's creator.
   *
   * @return User
   */
  public function creator() {
    return User::create($this->uid());
  }

  /**
   * Set the node's creator.
   *
   * @param mixed $user
   * @return self
   * @throws Exception
   */
  public function setCreator($user) {
    $uid = $user instanceof User ? $user->id() : (int) $user;
    if (!$uid) {
      throw new Exception("Invalid user.");
    }
    return $this->setUid($uid);
  }

  /**
   * Get a link to the node's page.
   *
   * @param null|string $label
   * @param bool $absolute
   * @return string
   */
  public function link($label = NULL, $absolute = FALSE, array $options = array()) {
    $label = ($label === NULL) ? $this->title() : $label;
    return parent::link($label, $absolute, $options);
  }

  /**
   * Get the comment setting.
   *
   * 0 = COMMENT_NODE_HIDDEN = Comments for this node are hidden.
   * 1 = COMMENT_NODE_CLOSED = Comments for this node are closed.
   * 2 = COMMENT_NODE_OPEN   = Comments for this node are open.
   *
   * @return int
   */
  public function commentSetting() {
    return (int) $this->field('comment');
  }

  /**
   * Set the comment setting.
   *
   * 0 = COMMENT_NODE_HIDDEN = Comments for this node are hidden.
   * 1 = COMMENT_NODE_CLOSED = Comments for this node are closed.
   * 2 = COMMENT_NODE_OPEN   = Comments for this node are open.
   *
   * @param int
   * @return self
   * @throws Exception
   */
  public function setCommentSetting($comment_setting) {
    $comment_setting = (int) $comment_setting;
    if ($comment_setting < 0 || $comment_setting > 2) {
      throw new Exception("Invalid comment setting.");
    }
    return $this->setField('comment', $comment_setting);
  }

  /**
   * Get the node's comments.
   *
   * @param bool|null $published
   *   NULL for all comments
   *   TRUE for published comments (default)
   *   FALSE for unpublished comments
   * @param string $comment_class
   *   The class to use for the comment objects.
   * @return array
   */
  public function comments($published = TRUE, $comment_class = 'Comment') {
    // Get the comments:
    $q = db_select('comment', 'c')
      ->fields('c', array('cid'))
      ->condition('nid', $this->nid());

    // Set the published condition if specified:
    if (is_bool($published)) {
      $q->condition('status', (int) $published);
    }

    $rs = $q->execute();
    $comments = array();
    foreach ($rs as $rec) {
      $comments[] = $comment_class::create($rec->cid);
    }
    return $comments;
  }

  /**
   * Find out how many comments the node has.
   *
   * @param bool|null $published
   *   NULL for all comments
   *   TRUE for published comments (default)
   *   FALSE for unpublished comments
   */
  public function commentCount($published = TRUE) {
    $q = db_select('comment', 'c')
      ->fields('c', array('cid'))
      ->condition('nid', $this->nid());

    // Set the published condition if specified:
    if (is_bool($published)) {
      $q->condition('status', (int) $published);
    }

    return $q->execute()->rowCount();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Render method

  /**
   * Get the HTML for a node.
   *
   * @param bool $include_comments
   * @param string $view_mode
   * @return string
   */
  public function render($include_comments = FALSE, $view_mode = 'full') {
    $node = $this->node();
    $node_view = node_view($node, $view_mode);
    if ($include_comments) {
      $node_view['comments'] = comment_node_page_additions($node);
    }
    return render($node_view);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Domain methods (domain module integration)

  /**
   * Get ids of the domains this node is published on.
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
    $current_domain_ids = $this->field('domains');

    // Get the domain ids:
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
   * Set the domains this node is published on.
   *
   * If the domain module is not enabled an exception will be thrown.
   * NOTE - if this is a new node, this method will save it.
   *
   * @param array|int|Domain $domains
   * @throws \Exception
   * @return self
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
    $subdomains = array();
    foreach ($domains as $new_domain) {
      if (is_pint($new_domain)) {
        $new_domain = Domain::create($new_domain);
      }
      if ($new_domain instanceof Domain && $new_domain->id()) {
        $domain_id = $new_domain->id();
        $domain_ids[$domain_id] = $domain_id;
        $subdomains[] = $new_domain->siteName();
      }
      else {
        throw new Exception("Invalid parameter. Array of Domain objects or IDs expected.");
      }
    }

    // If the node isn't saved, we have to save it now otherwise the domains won't stick.
    if ($this->isNew()) {
      $this->save();
    }

    // Update the domains fields:
    $this->setField('domains', $domain_ids);
    $this->setField('subdomains', $subdomains);

    return $this;
  }

  /**
   * Check if a node is published on all domains.
   * If the domain module is not enabled an exception will be thrown.
   *
   * @throws Exception
   * @return bool
   */
  public function isPublishedOnAllDomains() {
    // Check if the domain module is enabled:
    if (!module_exists('domain')) {
      throw new Exception("Domain module disabled.");
    }

    // Check if the node is published on all domains:
    return (bool) $this->field('domain_site');
  }

  /**
   * Set if the node is published on all domains.
   * If the domain module is not enabled an exception will be thrown.
   *
   * @throws Exception
   * @param bool $publish
   * @return self
   */
  public function publishOnAllDomains($publish = TRUE) {
    // Check if the domain module is enabled:
    if (!module_exists('domain')) {
      throw new Exception("Domain module disabled.");
    }

    // Set if the node is published on all domains:
    return $this->setField('domain_site', (bool) $publish);
  }

}
