<?php
/**
 * Some handy functions that Drupal should have but doesn't.
 */


////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful functions related to URL arguments.

/**
 * Return the number of arguments in the system path. Supplements Drupal's arg() and args() functions.
 *
 * @return int
 */
function arg_count() {
  $n = 0;
  while (arg($n) !== NULL) {
    $n++;
  }
  return $n;
}

/**
 * Return the last argument in the system path. Supplements Drupal's arg() and args() functions.
 *
 * @return string
 */
function last_arg() {
  return arg(arg_count() - 1);
}

/**
 * Get the requested path (i.e. what appears in the URL bar). This may be a system path or an alias.
 * Supports clean and unclean URLs.
 *
 * @return string
 */
function request() {
  if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
    // Non-clean URLs: get the requested path from the query string.
    parse_str($_SERVER['QUERY_STRING'], $query);
    $request_uri = $query['q'];
  }
  else {
    // Clean URLs: get the requested path directly:
    $request_uri = $_SERVER['REQUEST_URI'];
  }
  return trim(urldecode($request_uri), '/');
}

/**
 * Get the parts of the requested path. Mirrors Drupal's args() function.
 *
 * @return array
 */
function request_args() {
  return array_filter(explode('/', request()));
}

/**
 * Get a specific part of the requested path. Mirrors Drupal's arg() function.
 *
 * @return string
 */
function request_arg($i) {
  $request_args = request_args();
  return array_key_exists($i, $request_args) ? $request_args[$i] : NULL;
}

/**
 * Get the number of parts in the requested path.
 *
 * @return int
 */
function request_arg_count() {
  return count(request_args());
}

/**
 * Return the last argument in the requested path.
 *
 * @return string
 */
function last_request_arg() {
  $request_args = request_args();
  return $request_args[count($request_args) - 1];
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful database functions.

/**
 * Same as db_query, but if debug mode is on that it will show the actual query being executed.
 * I copied db_query to avoid hacking core.
 */
function debug_query($query) {
  $args = func_get_args();
  array_shift($args);
  $query = db_prefix_tables($query);
  if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
    $args = $args[0];
  }
  _db_query_callback($args, TRUE);
  $query = preg_replace_callback(DB_QUERY_REGEXP, '_db_query_callback', $query);
  // determine whether to display the query:
  $debug = function_exists('debugMode') ? debugMode() : TRUE;
  if ($debug) {
    $x = debug_backtrace();
    $n = $x[0]['file'] ? 0 : 1;
    if ($x[$n + 1]['function'] == 'do_query') {
      $n++;
    }
    $msg = "<p>Query called from {$x[$n]['file']}, line {$x[$n]['line']}";
    if (isset($x[$n + 1])) {
      $msg .= ", function {$x[$n + 1]['function']}()";
    }
    $msg .= "</p>";
    echo $msg;
  }
  return _db_query($query, $debug);
}

/**
 * Helper function for db_query().
 */
function show_query($query) {
  global $active_db, $queries, $user;

  $args = func_get_args();
  array_shift($args);
  $query = db_prefix_tables($query);
  if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
    $args = $args[0];
  }
  _db_query_callback($args, TRUE);
  $query = preg_replace_callback(DB_QUERY_REGEXP, '_db_query_callback', $query);

  if (variable_get('dev_query', 0)) {
    list($usec, $sec) = explode(' ', microtime());
    $timer = (float)$usec + (float)$sec;
    // If devel.module query logging is enabled, prepend a comment with the username and calling function
    // to the SQL string. This is useful when running mysql's SHOW PROCESSLIST to learn what exact
    // code is issueing the slow query.
    $bt = debug_backtrace();
    // t() may not be available yet so we don't wrap 'Anonymous'
    $name = $user->uid ? $user->name : variable_get('anonymous', 'Anonymous');
    // str_replace() to prevent SQL injection via username or anonymous name.
    $name = str_replace(array('*', '/'), '', $name);
    $query = '/* '. $name .' : '. $bt[2]['function'] .' */ '. $query;
  }
  print '<p>query: ' . $query . '</p>';
}

function do_query($really_do_it, $query) {
  $fn = $really_do_it ? 'debug_query' : 'show_query';
  $args = func_get_args();
  array_shift($args);
  return call_user_func_array($fn, $args);
}


////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful node-related functions.

/**
 * Just get the HTML for a node.
 * @param object $node
 * @return string
 */
function theme_node($node) {
  node_build_content($node, FALSE, FALSE);
  return drupal_render($node->content);
}

/**
 * Create a new node of a certain type, with default settings.
 * The node is not saved to the DB by this function.
 *
 * Remember that the uid is set to the current user by default, so you may want to override this.
 *
 * @param string $type
 * @param string|null $title
 * @param bool $published
 * @param string|null $path
 * @param string $lang
 * @return stdClass
 */
function node_create($type, $title = NULL, $published = TRUE, $path = NULL, $lang = LANGUAGE_NONE) {
  $node = new stdClass();
  $node->type = $type;
  node_object_prepare($node);

  // Set the title if specified:
  if ($title !== NULL) {
    $node->title = $title;
  }

  // Set the status:
  $node->status = (int) $published;

  // Set the language:
  $node->language = $lang;

  // Set the path if specified:
  if ($path !== NULL) {
    $node->path = array('alias' => $path);
  }

  return $node;
}

/**
 * Load a node from the database, bypassing the static node cache in node_load()
 *
 * @param int $nid
 * @return object
 */
function node_load_bypass_cache($nid) {
  $vid = (int) db_result(db_query("SELECT vid FROM {node} WHERE nid = %d", $nid));
  // Remove cached CCK data:
  if (function_exists('content_cache_tablename')) {
    db_query("DELETE FROM " . content_cache_tablename() . " WHERE cid = 'content:$nid:$vid'");
  }
  // load the node:
  return node_load($nid, $vid);
}

/**
 * Delete all nodes of a given type.
 * @param string $type
 */
function node_delete_by_type($type) {
  // Delete all functions:
  $sql = "SELECT * FROM node WHERE type='$type'";
  $rs = db_query($sql);
  while ($rec = db_fetch_array($rs)) {
//    debug($rec);
    node_delete($rec['nid']);
  }
}

/**
 * Get a node's title, given its nid.
 *
 * @param int $nid
 * @return string
 */
function node_get_title($nid) {
  if (!$nid) {
    return FALSE;
  }

  $rs = db_select('node', 'n')
    ->fields('n', array('title'))
    ->condition('nid', $nid)
    ->execute();

  if (!$rs->rowCount()) {
    return FALSE;
  }

  $rec = $rs->fetch();
  return $rec->title;
}

/**
 * Get a node's type, given its nid.
 *
 * @param int $nid
 * @return string
 */
function node_get_type($nid) {
  if (!$nid) {
    return FALSE;
  }

  $rs = db_select('node', 'n')
    ->fields('n', array('type'))
    ->condition('nid', $nid)
    ->execute();

  if (!$rs->rowCount()) {
    return FALSE;
  }

  $rec = $rs->fetch();
  return $rec->type;
}

/**
 * Return a link to a node, given a nid.
 * @param int $nid
 * @return string
 */
function node_get_link($nid) {
  return $nid ? l(node_get_title($nid), "node/$nid") : '';
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful user functions.

/**
 * Make a username from a first and last name.
 *
 * @param string $first_name
 * @param string $last_name
 * @return string
 */
function user_get_base_username($first_name, $last_name = NULL) {
  // Remove any punctuation and whitespace characters from the names:
  $first_name = stripNonAlnum($first_name);
  $last_name = stripNonAlnum($last_name);

  if (!$first_name && !$last_name) {
    // No names provided!  Should never happen.
    return FALSE;
  }
  elseif (!$first_name) {
    $username = $last_name;
  }
  elseif (!$last_name) {
    $username = $first_name;
  }
  else {
    // Build a base name from the first and last name, containing only letters and digits:
    $username = $first_name . $last_name[0];
    // Check if the username is too short:
    if (strlen($username) < 6) {
      // See if we can make a longer one by using the first initial and last name:
      $alt_name = $first_name[0] . $last_name;
      if (strlen($alt_name) > strlen($username)) {
        $username = $alt_name;
      }
      // Check if the username is still too short:
      if (strlen($username) < 6) {
        // See if we can make a longer one by using both first and last names:
        $alt_name = $first_name . $last_name;
        if (strlen($alt_name) > strlen($username)) {
          $username = $alt_name;
        }
      }
    }
  }
  return $username;
}

/**
 * Generate a new, unique username for a new user.
 * @param $first_name
 * @param $last_name
 * @return string
 */
function user_get_unique_name($first_name, $last_name = NULL) {
  // Get the base username:
  $base_name = user_get_base_username($first_name, $last_name);

  // Try out the base_name with appended digits until we find an unused username:
  $i = 0;
  do {
    $name = $base_name . ($i ? $i : '');
    $sql = "SELECT uid FROM {users} WHERE name = '%s'";
    $rec = db_fetch_array(db_query($sql, $name));
    $i++;
  } while ($rec);
  return $name;
}

/**
 * Create a new user with unique name generated from provided names.
 * @param string $first_name
 * @param string $last_name
 * @param int $status = 0
 * @return object
 */
function user_create($first_name, $last_name = NULL, $status = 0) {
  $name = user_get_unique_name($first_name, $last_name);
  return user_save(NULL, array('name' => $name, 'status' => $status));
}

/**
 * Get a user's name from a uid.
 *
 * This is faster than loading the user object - but here's the catch - only if the user object isn't already loaded
 * and in the cache. But there's no way to check that.
 *
 * @param int $uid
 * @return string
 */
function user_get_name($uid) {
  return db_select('users', 'u')
    ->fields('u', array('name'))
    ->condition('uid', $uid)
    ->execute()
    ->fetch()
    ->uid;
}

/**
 * Return a link to a node, given a nid.
 * @param int $nid
 * @return string
 * @author shaunm
 */
function user_get_link($uid) {
  return $uid ? l(user_get_name($uid), "user/$uid") : '';
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful role-related functions.

/**
 * Given a role name or a rid, find the role.
 *
 * @param mixed $role
 * @return array
 */
function role_load($role) {
  if (is_numeric($role)) {
    // Assume $role == rid
    return db_select('role', 'r')
      ->fields('r')
      ->condition('rid', $role)
      ->execute()
      ->fetchObject();
  }

  // Assume $role is role name:
  return db_select('role', 'r')
    ->fields('r')
    ->condition('name', $role)
    ->execute()
    ->fetchObject();
}

/**
 * Add a given role to a user.
 * @param mixed $user
 * @param mixed $role
 */
function user_add_role(&$user, $role) {
  // get the uid:
  if (is_object($user)) {
    $uid = $user->uid;
  }
  else {
    // assume the first param is a $uid:
    $uid = (int)$user;
  }
  // get the role:
  $rec = role_load($role);
  if ($rec) {
    // see if this user already has this role:
    $sql2 = "SELECT * FROM {users_roles} WHERE uid = %d AND rid = %d";
    $rec2 = db_fetch_array(db_query($sql2, $uid, $rec['rid']));
    if (!$rec2) {
      // add the role:
      $sql3 = "INSERT INTO {users_roles} (uid, rid) VALUES (%d, %d)";
      db_query($sql3, $uid, $rec['rid']);
      // update the user object:
      if (is_object($user)) {
        $user->roles[$rec['rid']] = $rec['name'];
      }
    }
    return $rec;
  }
  return FALSE;
}

/**
 * Remove a given role from a user.
 * This function does not use Drupal functions but direct db access for speed.
 * @param mixed $user
 * @param mixed $role
 */
function user_remove_role(&$user, $role) {
  // Get the uid:
  if (is_object($user)) {
    $uid = $user->uid;
  }
  else {
    // Assume the first param is a $uid:
    $uid = (int) $user;
  }
  // Get the role:
  $rec = role_load($role);
  if ($rec) {
    // Remove the role for this user:
    $sql2 = "DELETE FROM {users_roles} WHERE uid = %d AND rid = %d";
    db_query($sql2, $uid, $rec['rid']);
  }
}

/**
 * Returns TRUE if the user has the given role.
 * If $user == NULL, defaults to the logged-in user.
 * If the user is not logged in, returns FALSE.
 *
 * @param mixed $role
 * @param object $user
 * @return bool
 */
function user_has_role($role, $user = NULL) {
  // Default to global user:
  if (!$user) {
    $user = $GLOBALS['user'];
  }

  // Allow for $user being the uid:
  if (is_uint($user)) {
    $user = user_load($user);
  }

  // Check we have a user:
  if (!$user || !$user->uid) {
    return FALSE;
  }

  // Check if the role (which may be a rid or a role name) is either a key or a value in the user's roles array:
  return in_array($role, $user->roles) || array_key_exists($role, $user->roles);
}

/**
 * Get all roles for a user as an array of rid => role_name.
 * Does not load user object, optimised for speed.
 * @param int $uid
 * @return array
 */
function user_get_roles($uid) {
  if (!$uid) {
    return FALSE;
  }
  $sql = "
    SELECT *
    FROM {users_roles} ur LEFT JOIN {role} USING (rid)
    WHERE uid = %d";
  $rs = db_query($sql, $uid);
  $roles = array();
  while ($rec = db_fetch_array($rs)) {
    $roles[(int) $rec['rid']] = $rec['name'];
  }
  return $roles;
}

/**
 * Checks if the logged-in user is an administrator.
 *
 * @return bool
 */
function user_is_admin() {
  return user_has_role('administrator');
}

/**
 * Checks if the logged-in user is the superuser.
 *
 * @return bool
 */
function user_is_superuser() {
  return $GLOBALS['user']->uid == 1;
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful form-related functions.

/**
 * Remove any elements from an array with a key equal to $key, regardlesss of depth.
 * @param string $key
 * @param array $array
 * @param string $parent
 */
function remove_key_from_array($key, &$array, $parent = '') {
  if (is_array($array) && !empty($array)) {
    foreach ($array as $key2 => $value) {
      if ($key2 === $key) {
        // debug("Removing \$array{$parent}[$key2].");
        unset($array[$key2]);
      }
      else if (is_array($value) && !empty($value)) {
        remove_key_from_array($key, $array[$key2], $parent . "[$key2]");
      }
    }
  }
}

/**
 * Remove a field or a group from a form.
 * @param string $field
 * @param array $form
 * @param array $form_state
 */
function drupal_form_remove_field($field, &$form, &$form_state = NULL) {
  remove_key_from_array($field, $form);
  if ($form_state) {
    remove_key_from_array($field, $form_state);
  }
}

/**
 * Remove a group from a form including all fields inside it.
 * @param string $group
 * @param array $form
 * @param array $form_state
 */
function drupal_form_remove_group($group, &$form, &$form_state = NULL) {
  // remove the fields in the group:
  if (is_array($form[$group])) {
    foreach ($form[$group] as $key => $value) {
      // don't remove attributes, which start with '#':
      if ($key[0] != '#') {
        // debug("Removing $key");
        remove_key_from_array($key, $form);
        if ($form_state) {
          remove_key_from_array($key, $form_state);
        }
      }
    }
  }
  // remove the group:
  remove_key_from_array($group, $form);
  if ($form_state) {
    remove_key_from_array($group, $form_state);
  }
}

/**
 * Move a field from one fieldset (field group) to another.
 *
 * @param $form
 * @param $field_name
 * @param $old_group
 * @param $new_group
 */
function drupal_form_move_field(&$form, $field_name, $old_group, $new_group) {
  $form['#groups'][$old_group]->children = array_diff($form['#groups'][$old_group]->children, array($field_name));
  $form['#groups'][$new_group]->children[] = 'field_moon_or_mars';
  $form['#group_children'][$field_name] = $new_group;
}

/**
 * Get the true path to the root of the Drupal site.
 * Better than using DOCUMENT_ROOT and base_path().
 *
 * NOTE - this code is replicated in classes_base_dir()
 * Copied here because this is a more logical place for it.
 * Kept in the classes module so that this file is not a dependency.
 * @todo Real solution is to make the Star library a module.
 */
function absolute_path_to_drupal() {
  static $absolute_path_to_drupal = NULL;

  if ($absolute_path_to_drupal === NULL) {
    // Get the absolute path to this file:
    $dir = rtrim(str_replace('\\', '/', dirname(__FILE__)), '/');
    $parts = explode('/', $dir);

    // Iterate up the directory hierarchy until we find the website root:
    $done = FALSE;
    do {
      // Check a couple of obvious things:
      $done = is_dir("$dir/sites") && is_dir("$dir/includes") && is_file("$dir/index.php");
      if (!$done) {
        // If there's no more path to examine, we didn't find the site root:
        if (empty($parts)) {
          $absolute_path_to_drupal = FALSE;
          break;
        }
        // Go up one level and look again:
        array_pop($parts);
        $dir = implode('/', $parts);
      }
    } while (!$done);
    
    $absolute_path_to_drupal = $dir;
  }
  
  return $absolute_path_to_drupal;
}


/**
 * Given a node object or a nid, get the node object.
 *
 * @param mixed $node
 * @return stdClass|bool
 */
function param_node($node) {
  if (is_uint($node)) {
    return node_load($node);
  }
  elseif (is_object($node)) {
    return $node;
  }
  return FALSE;
}

/**
 * Given a node object or a nid, get the nid.
 *
 * @param mixed $node
 * @return int|bool
 */
function param_nid($node) {
  if (is_uint($node)) {
    return $node;
  }
  elseif (is_object($node)) {
    return $node->nid;
  }
  return FALSE;
}

/**
 * Given a user object or a uid, get the user object.
 *
 * @param mixed $user
 * @return stdClass|bool
 */
function param_user($user) {
  if (is_null($user)) {
    global $user;
    return $user;
  }
  elseif (is_uint($user)) {
    return user_load($user);
  }
  elseif (is_object($user)) {
    return $user;
  }
  elseif (is_string($user)) {
    $user = user_load_by_name($user);
    if ($user) {
      return $user;
    }
    // Try to find the matching path alias:
    $alias = "users/$user";
    $path = drupal_get_normal_path($alias);
    $path_parts = explode('/', $path);
    if (is_uint($path_parts[1])) {
      return user_load($path_parts[1]);
    }
  }
  return FALSE;
}

/**
 * Given a user object or a uid, get the uid.
 *
 * @param mixed $user
 * @return int|bool
 */
function param_uid($user) {
  if (is_null($user)) {
    global $user;
    return $user->uid;
  }
  elseif (is_uint($user)) {
    return $user;
  }
  elseif (is_object($user)) {
    return $user->uid;
  }
  elseif (is_string($user)) {
    return db_select('users', 'u')
      ->fields('u', 'uid')
      ->condition('name', $user)
      ->execute()
      ->fetch()
      ->uid;
  }
  return FALSE;
}

/**
 * Given a node or user object, or a nid or uid, get the entity id.
 *
 * @param string $entity_type
 * @param int|stdClass $entity
 * @return int|bool
 */
function param_entity_id($entity_type, $entity) {
  switch ($entity_type) {
    case 'node':
      return param_nid($entity);

    case 'user':
      return param_uid($entity);
  }
  return FALSE;
}

/**
 * Given a node or user object, or a nid or uid, get the entity id.
 *
 * @param string $entity_type
 * @param int|stdClass $entity
 * @return int|bool
 */
function param_entity($entity_type, $entity) {
  switch ($entity_type) {
    case 'node':
      return param_node($entity);

    case 'user':
      return param_user($entity);
  }
  return FALSE;
}

/**
 * Get the HTML for a node.
 *
 * @param stdClass|int $node
 * @return mixed|string
 */
function render_node($node, $view_mode = 'full') {
  $node = param_node($node);
  return theme('node',
    array(
      'elements' => array(
        '#node'      => $node,
        '#view_mode' => $view_mode,
      ),
    )
  );
}

/**
 * Get the HTML for a comment.
 *
 * @param $comment
 * @return mixed|string
 */
function render_comment($comment) {
  $node = node_load($comment->nid);
  return theme('comment',
    array(
         'elements' => array(
           '#comment' => $comment,
           '#node'    => $node,
         ),
         'content'  => $comment->content
    )
  );
}

/**
 * Get the type of the node that a comment is for.
 *
 * @param $cid
 * @return int
 */
function comment_get_node_type($cid) {
  if (!$cid) {
    return FALSE;
  }

  $q = db_select('comment', 'c');
  $q->join('node', 'n', "c.nid = n.nid");
  $rs = $q->fields('n', array('type'))
    ->condition('cid', $cid)
    ->execute();

  if (!$rs->rowCount()) {
    return FALSE;
  }

  $rec = $rs->fetch();
  return $rec->type;
}

/**
 * Get all terms from a vocabulary.
 *
 * @param int|string $vocab
 *   Either the vocabulary id or machine name.
 * @return stdClass
 */
function taxonomy_term_load_vocabulary($vocab) {
  if (is_pint($vocab)) {
    $vid = $vocab;
  }
  else {
    $vocab = taxonomy_vocabulary_machine_name_load($vocab);
    $vid = $vocab->vid;
  }
  return taxonomy_term_load_multiple(NULL, array('vid' => $vid));
}

/**
 * Format a node title (or a name for a taxonomy term or a user), or for another field as required.
 * Removes any HTML tags and character entities from the title, and trims (with ellipsis) to maximum length.
 *
 * @param string $title
 * @param int $max_length
 * @return string
 */
function drupal_format_title($title, $max_length = 255) {
  // Convert from whatever it is to UTF-8:
  $title = convert_to_utf8($title);

  // Remove any HTML tags:
  $title = strip_tags($title);

  // Convert non-breaking spaces to normal spaces:
  $title = str_replace('&nbsp;', ' ', $title);

  // Decode any other HTML character entities to UTF-8:
  $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');

  // Trim whitespace from the ends:
  $title = trim($title);

  // Shorten to required length:
  return ellipsis_trim($title, $max_length);
}
