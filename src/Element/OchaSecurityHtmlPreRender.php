<?php

namespace Drupal\ocha_security\Element;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Provides a trusted callback to alter elements.
 *
 * @see ocha_security_element_info_alter().
 */
class OchaSecurityHtmlPreRender implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

  /**
   * Sets pre_render callback.
   */
  public static function preRender($build) {
    if (ocha_security_can_use_nonce()) {
      ocha_security_set_nonce($build);
    }
    else {
      ocha_security_add_hash($build);
    }
    return $build;
  }

}
