<?php

namespace Drupal\video_embed_spotify\Plugin\video_embed_field\Provider;

use Drupal\video_embed_field\ProviderPluginBase;
use GuzzleHttp\ClientInterface;

/**
 * Provides Spotify plugin.
 *
 * @VideoEmbedProvider(
 *   id = "spotify",
 *   title = @Translation("Spotify")
 * )
 */
class Spotify extends ProviderPluginBase {

  const URLREGEXP = "/^https:\/\/(open\.spotify\.com\/)(embed-podcast\/|embed\/)?(?<type>album|artist|episode|playlist|show|track)\/(?<id>[0-9A-Za-z_-]*)(\?.*)?$/i";

  /**
   * The embed url to use for this video.
   *
   * @var string
   */
  protected $embedUrl;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $http_client);
    $this->embedUrl = $this->buildEmbedUrl($configuration['input']);
  }

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay, $responsive = FALSE) {
    $embed_code = [
      '#type' => 'video_embed_iframe',
      '#provider' => 'spotify',
      '#url' => $this->getEmbedUrl(),
      '#attributes' => [
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
        'allowtransparency' => 'true',
        'allow' => 'encrypted-media',
      ],
    ];
    if ($responsive) {
      unset($embed_code['#attributes']['width'], $embed_code['#attributes']['height']);
    }
    return $embed_code;
  }

  /**
   * Get the Spotify oembed data.
   *
   * @return mixed
   *   An array of data from the oembed endpoint.
   */
  protected function oEmbedData() {
    return json_decode(file_get_contents('https://open.spotify.com/oembed?url=' . $this->getInput()));
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    try {
      return $this->oEmbedData()->thumbnail_url;
    }
    catch (\Exception $e) {
    }
    return "";
  }

  /**
   * {@inheritdoc}
   */
  public function getLocalThumbnailUri() {
    return $this->thumbsDirectory . '/' . str_replace('/', '', $this->getVideoId()) . '.jpg';
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    preg_match(static::URLREGEXP, $input, $matches);
    return isset($matches['id']) ? $matches['id'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEmbedUrl($value) {
    preg_match(static::URLREGEXP, $value, $matches);

    if (isset($matches['id']) && isset($matches['type'])) {
      $path = 'https://open.spotify.com/embed';
      $path .= ((in_array($matches['type'], ['episode', 'show'])) ? ('-podcast') : (''));
      $path .= '/' . $matches['type'] . '/' . $matches['id'];
      return $path;
    }
    return "";
  }

  /**
   * {@inheritdoc}
   */
  public function getEmbedUrl() {
    return $this->embedUrl;
  }

}
