<?php

namespace Drupal\ocha_security\Asset;

use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssetsInterface;

/**
 * An asset resolver that allows to add per-request nonces.
 */
class OchaSecurityAssetResolver implements AssetResolverInterface {

  /**
   * Original service object.
   *
   * @var \Drupal\core\Asset\AssetResolverInterface
   */
  protected $assetResolver;

  /**
   * Constructs a new AssetResolver instance.
   *
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   The original asset resolver service.
   */
  public function __construct(AssetResolverInterface $asset_resolver) {
    $this->assetResolver = $asset_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function getCssAssets(AttachedAssetsInterface $assets, $optimize) {
    // Don't do anything on CSS assets.
    return $this->assetResolver->getCssAssets($assets, $optimize);
  }

  /**
   * {@inheritdoc}
   */
  public function getJsAssets(AttachedAssetsInterface $assets, $optimize) {
    [$js_assets_header, $js_assets_footer] = $this->assetResolver->getJsAssets($assets, $optimize);
    if (ocha_security_sends_csp_header()) {
      if (ocha_security_can_use_nonce()) {
        $this->addNonce($js_assets_header);
        $this->addNonce($js_assets_footer);
      }
      else {
        $this->addHash($js_assets_header);
        $this->addHash($js_assets_footer);
      }
    }
    return [
      $js_assets_header,
      $js_assets_footer,
    ];
  }

  /**
   * Add a nonce attribute to every file asset.
   *
   * @param array $assets
   *   An array with the assets to process.
   */
  private function addNonce(array &$assets) {
    if (empty($assets)) {
      return;
    }
    foreach ($assets as &$asset) {
      if (!in_array($asset['type'], ['file', 'external'])) {
        continue;
      }
      $attributes = array_key_exists('attributes', $asset) ? $asset['attributes'] : [];
      $attributes['nonce'] = ocha_security_get_nonce();
      $attributes['wherefrom'] = 'AssetResolver';
      $asset['attributes'] = $attributes;
    }
  }

  /**
   * Add a hash for every file asset.
   *
   * @param array $assets
   *   An array with the assets to process.
   */
  private function addHash(array &$assets) {
    if (empty($assets)) {
      return;
    }
    foreach ($assets as &$asset) {
      if (!in_array($asset['type'], ['file'])) {
        continue;
      }
      if (array_key_exists('data', $asset)) {
        $hash = ocha_security_get_hash_from_url($asset['data']);
      }
      if (!empty($hash)) {
        ocha_security_hash_store($hash);
      }
    }
  }

}
