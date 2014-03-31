<?php
/**
 * Encapsulates a content (node) type.
 * 
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 19-Apr-2013 15:24
 */

namespace AstroMultimedia\Drupal;

class ContentType extends Bundle {

  /**
   * Create a new ContentType object.
   *
   * @todo improve this so it uses lazy loading and caching, like entities.
   *
   * @param null|int|string $machine_name
   * @return Vocabulary
   */
  public static function create($machine_name = NULL) {
    if (is_null($machine_name)) {
      $content_type = NULL;
      $content_type_obj = new ContentType();
      $content_type_obj->loaded = TRUE;
    }
    elseif (is_string($machine_name)) {
      $content_type = node_type_load($machine_name);
    }

    if ($content_type) {
      $content_type_obj = new ContentType();
      $content_type_obj->entity = $content_type;
      $content_type_obj->loaded = TRUE;
    }

    return $content_type_obj;
  }

  /**
   * Get the content type machine name.
   *
   * @override Bundle::machineName()
   * @return string
   */
  public function machineName() {
    return $this->entity->type;
  }

  /**
   * Set the content type machine name.
   *
   * @override Bundle::setMachineName()
   * @param string $machine_name
   * @return Vocabulary
   */
  public function setMachineName($machine_name) {
    $this->entity->type = (string) $machine_name;
    return parent::setMachineName($this->entity->type);
  }

  /**
   * Get the nodes for a given content type that are permitted on the current site/domain as options for a select list.
   *
   * @param string $order_by
   * @return array
   */
  public function options($order_by = 'title') {
    $q = db_select('node', 'n')
      ->fields('n', array('nid', 'title'))
      ->condition('n.status', 1)
      ->condition('n.type', $this->machineName());

    // Check if the domain module is enabled:
    $current_domain = module_exists('domain') ? Domain::current() : FALSE;
    if ($current_domain) {
      $q->leftJoin('domain_access', 'da', "n.nid = da.nid");
      // Get nodes published on all domains, or on the current domain:
      $q->condition(
        db_or()
          ->condition(
            db_and()
              ->condition('da.gid', $current_domain->id())
              ->condition('realm', 'domain_id'))
          ->condition(
            db_and()
              ->condition('da.gid', 0)
              ->condition('realm', 'domain_site')));
    }

    $q->orderBy($order_by, 'ASC');

    $rs = $q->execute();

    // Collect valid terms:
    $options = array();
    foreach ($rs as $rec) {
      $options[$rec->nid] = $rec->title;
    }

    return $options;
  }

}
