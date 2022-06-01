<?php

namespace Drupal\ocha_security\Session;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\SessionConfigurationInterface;

/**
 * An session configuration decorator.
 *
 * Sets the cookie_samesite flag to "Lax".
 *
 * This is now default behavior on modern browsers:
 * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite#cookies_without_samesite_default_to_samesitelax
 */
class OchaSecuritySessionConfiguration implements SessionConfigurationInterface {

  /**
   * Original service object.
   *
   * @var \Drupal\core\Session\SessionConfigurationInterface
   */
  protected $sessionConfiguration;

  /**
   * Constructs a new Session Configuration instance.
   *
   * @param \Drupal\core\Session\SessionConfigurationInterface $session_configuration
   *   The original session configuration service.
   */
  public function __construct(SessionConfigurationInterface $session_configuration) {
    $this->sessionConfiguration = $session_configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function hasSession(Request $request) {
    return $this->sessionConfiguration->hasSession($request);
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(Request $request) {
    $options = $this->sessionConfiguration->getOptions($request);
    $options['cookie_samesite'] = 'Lax';
    return $options;
  }

}
