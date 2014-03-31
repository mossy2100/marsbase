<?php
/**
 * Interface to encapsulate domain-specific methods.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2013-08-13 14:14
 */

namespace AstroMultimedia\Drupal;

interface InterfaceDomainSpecific {

  /**
   * Get ids of the domains this entity is published on.
   * If the relevant domain module is not enabled an exception will be thrown.
   *
   * @return array
   * @throws Exception
   */
  public function domainIds();

  /**
   * Get the domains this entity is published on.
   * If the relevant domain module is not enabled an exception will be thrown.
   *
   * @return array
   * @throws Exception
   */
  public function domains();

  /**
   * Set which domains this entity is published on.
   * If the relevant domain module is not enabled an exception will be thrown.
   * NOTE - if this is a new entity, this method will save it.
   *
   * @param array|int|Domain $domains
   * @return self
   * @throws Exception
   */
  public function setDomains($domains);

  /**
   * Check if an entity is published on all domains.
   * If the relevant domain module is not enabled an exception will be thrown.
   *
   * @return bool
   * @throws Exception
   */
  public function isPublishedOnAllDomains();

  /**
   * Set if the entity is published on all domains.
   * If the relevant domain module is not enabled an exception will be thrown.
   *
   * @param bool $publish
   * @return self
   * @throws Exception
   */
  public function publishOnAllDomains($publish = TRUE);

  /**
   * Check if an entity is published on a certain domain. If the domain is unspecified, defaults to current.
   * If the relevant domain module is not enabled an exception will be thrown.
   *
   * @param Domain $domain
   * @return bool
   * @throws Exception
   */
  public function isPublishedOnDomain($domain = NULL);

  /**
   * Add the entity to multiple domains.
   *
   * @param array $new_domains
   * @return self
   * @throws Exception
   */
  public function publishOnDomains(array $new_domains);

  /**
   * Add the entity to a domain. If the domain is unspecified, defaults to current.
   *
   * @param mixed $domain
   * @return self
   * @throws Exception
   */
  public function publishOnDomain($domain = NULL);

}
