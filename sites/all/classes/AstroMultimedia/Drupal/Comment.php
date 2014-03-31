<?php
/**
 * Comment class.
 */

namespace AstroMultimedia\Drupal;

use stdClass;
use Exception;

class Comment extends Entity {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'comment';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'comment';

  /**
   * The primary key
   *
   * @var string
   */
  const PRIMARY_KEY = 'cid';

  /**
   * Reference to the comment's node.
   *
   * @var Node
   */
  protected $node;

  /**
   * Reference to the comment's creator.
   *
   * @var User
   */
  protected $user;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * Create a new Comment object.
   *
   * @param mixed $param
   * @return Comment
   */
  public static function create($param = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($param)) {
      // Create new comment:
      $comment_obj = new $class;

      // It's new:
      $comment_obj->entity->is_new = TRUE;

      // Default status to published:
      $comment_obj->entity->status = 1;

      // Default language to none:
      $comment_obj->entity->language = LANGUAGE_NONE;

      // Default to current user:
      if (user_is_logged_in()) {
        global $user;
        $comment_obj->entity->uid = $user->uid;
        $comment_obj->entity->name = $user->name;
        $comment_obj->entity->mail = $user->mail;
      }

      // The comment is valid without a cid:
      $comment_obj->valid = TRUE;
    }
    elseif (is_pint($param)) {
      // cid provided:
      $cid = (int) $param;

      // Only create the new comment if not already in the cache:
      if (self::inCache($cid)) {
        return self::getFromCache($cid);
      }
      else {
        // Create new comment:
        $comment_obj = new $class;

        // Set the cid:
        $comment_obj->entity->cid = $cid;
      }
    }
    elseif ($param instanceof stdClass) {
      // Drupal comment object provided:
      $comment = $param;

      // Get the Comment object:
      if (isset($comment->cid) && $comment->cid && self::inCache($comment->cid)) {
        $comment_obj = self::getFromCache($comment->cid);
      }
      else {
        $comment_obj = new $class;
      }

      // Reference the provided entity object:
      $comment_obj->entity = $comment;

      // Make sure we mark the comment as loaded. It may not have been saved yet, and if we load it, any changes to the
      // comment entity would be overwritten.
      $comment_obj->loaded = TRUE;
    }

    // If we have a comment object, add to cache and return:
    if (isset($comment_obj)) {
      $comment_obj->addToCache();
      return $comment_obj;
    }

    throw new Exception("Invalid parameter.");
  }

  /**
   * Get the quick-load fields.
   *
   * @static
   * @return array
   */
  protected static function quickLoadFields() {
    return array('subject', 'nid', 'uid');
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Constructor.
   */
  protected function __construct() {
    return parent::__construct();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load/save/etc. methods

  /**
   * Load the comment object.
   *
   * @param bool $force
   * @return Comment
   */
  public function load($force = FALSE) {
    // Avoid reloading:
    if ($this->loaded && !$force) {
      return $this;
    }

    // Default result:
    $comment = FALSE;

    // If we have a cid, try to load the comment:
    if ($this->entity->cid) {
      // If we want to force a reload, remove the comment from the entity cache:
      if ($force) {
        entity_get_controller(self::ENTITY_TYPE)->resetCache([$this->entity->cid]);
      }
      // Load by cid. Drupal caching will prevent reloading of the same comment.
      $comment = comment_load($this->entity->cid);
    }

    // Set the valid flag:
    $this->valid = (bool) $comment;

    // If the comment was successfully loaded, update fields:
    if ($comment) {
      $this->entity = $comment;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the comment object.
   *
   * @return Comment
   */
  public function save() {
    // Save the comment:
    comment_save($this->entity);

    // It's not new any more:
    $this->entity->is_new = FALSE;

    // If the comment is new then we should add it to the cache:
    $this->addToCache();

    return $this;
  }

  /**
   * Delete a comment.
   */
  public function delete() {
    comment_delete($this->cid());
  }

  /**
   * OO wrapper for comment_submit.
   */
  public function submit() {
    comment_submit($this->entity);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the cid.
   *
   * @return int
   */
  public function cid() {
    isset($this->entity->cid) ? (int) $this->entity->cid : NULL;
  }

  /**
   * Set the cid.
   *
   * @param int $cid
   * @return Comment
   */
  public function setCid($cid) {
    $this->entity->cid = isset($cid) ? (int) $cid : NULL;

    // Add the comment object to the cache if not already:
    $this->addToCache();

    return $this;
  }

  /**
   * Get the comment object.
   *
   * @return stdClass
   */
  public function comment() {
    $this->load();
    return $this->entity;
  }

  /**
   * Get the comment's subject.
   *
   * @return string
   */
  public function subject() {
    return $this->field('subject');
  }

  /**
   * Set the comment's subject.
   *
   * @param string
   * @return Comment
   */
  public function setSubject($subject) {
    return $this->setField('subject', $subject);
  }

  /**
   * Get the nid of the node that the comment is about.
   *
   * @return int
   */
  public function nid() {
    return (int) $this->field('nid');
  }

  /**
   * Set the nid of the node that the comment is about.
   *
   * @param int
   * @return Comment
   */
  public function setNid($nid) {
    return $this->setField('nid', $nid);
  }

  /**
   * Get the node that the comment is about.
   *
   * @return Node
   */
  public function node() {
    $nid = $this->nid();
    if ($nid) {
      if (!isset($this->node) || $this->node->nid != $nid) {
        $this->node = Node::create($nid);
      }
    }
    else {
      $this->node = NULL;
    }
    return $this->node;
  }

  /**
   * Set the node that the comment is about.
   *
   * @param Node $node
   * @return Comment
   */
  public function setNode(Node $node) {
    $this->node = $node;
    return $this->setNid($node->nid());
  }

  /**
   * Get the uid of the user who created the comment.
   *
   * @return int
   */
  public function uid() {
    return (int) $this->field('uid');
  }

  /**
   * Set the uid of the user who created the comment.
   *
   * @param int
   * @return Comment
   */
  public function setUid($uid) {
    return $this->setField('uid', $uid);
  }

  /**
   * Get the comment's creator.
   *
   * @return User
   */
  public function user() {
    $uid = $this->uid();
    if ($uid) {
      if (!isset($this->user) || $this->user->uid != $uid) {
        $this->user = User::create($uid);
      }
    }
    else {
      $this->user = NULL;
    }
    return $this->user;
  }

  /**
   * Set the comment's creator.
   *
   * @param User
   * @return Comment
   */
  public function setUser(User $user) {
    $this->user = $user;
    return $this->setUid($user->uid());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Rendering.

  /**
   * Get the HTML for a comment.
   *
   * @param $comment
   * @return mixed|string
   */
  public function render() {
    // Make sure the comment is loaded:
    $this->load();

    // Get the node that the comment is about:
    $node = $this->node()->node();

    // Build the content. This sets the content property.
    comment_build_content($this->entity, $node);

    // Theme the comment:
    return theme('comment',
      array(
        'elements' => array(
          '#comment' => $this->entity,
          '#node'    => $node,
        ),
        'content'  => $this->entity->content
      )
    );
  }

}
