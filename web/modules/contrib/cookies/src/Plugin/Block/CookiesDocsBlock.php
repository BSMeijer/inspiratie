<?php

namespace Drupal\cookies\Plugin\Block;

use Drupal\cookies\Services\CookiesConfigService;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Cookies Documentation' block.
 *
 * @Block(
 *  id = "cookies_docs_block",
 *  admin_label = @Translation("Cookies Documentation"),
 * )
 */
class CookiesDocsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The COOKiES config service.
   *
   * @var \Drupal\cookies\Services\CookiesConfigService
   */
  protected $configService;

  /**
   * Constructor for COOKiES Documentation block.
   *
   * @param array $configuration
   *   Block config.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   Block plugun definition.
   * @param \Drupal\cookies\Services\CookiesConfigService $cookies_config_service
   *   The config serve providing the drupalSettings (JS).
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CookiesConfigService $cookies_config_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configService = $cookies_config_service;
  }

  /**
   * Static creator for dependencies injection in blocks.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container delivers the services.
   * @param array $configuration
   *   Block config.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   Block plugun definition.
   *
   * @return static
   *   Static object instance.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): CookiesDocsBlock {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cookies.config')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function build(): array {
    return $this->configService->getRenderedCookiesDocs();
  }

}
