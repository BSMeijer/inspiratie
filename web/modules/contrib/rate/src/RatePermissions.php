<?php

namespace Drupal\rate;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\comment\CommentManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to return permissions based on entity type for rate module.
 *
 * @package Drupal\rate
 */
class RatePermissions implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * The config factory wrapper to fetch settings.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Comment manager service.
   *
   * @var \Drupal\comment\CommentManagerInterface
   */
  protected $commentManager;

  /**
   * Constructs Permissions object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\comment\CommentManagerInterface $comment_manager
   *   The comment manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, CommentManagerInterface $comment_manager) {
    $this->config = $config_factory->get('rate.settings');
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->commentManager = $comment_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('comment.manager')
    );
  }

  /**
   * Get permissions for Rate module.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    $permissions = [];
    $widgets = $this->entityTypeManager->getStorage('rate_widget')->loadMultiple();

    // No need to continue without widgets.
    if (empty($widgets)) {
      return $permissions;
    }

    $comment_module_enabled = $this->moduleHandler->moduleExists('comment');

    foreach ($widgets as $widget) {
      $entities = $widget->get('entity_types');
      if ($entities && count($entities) > 0) {
        foreach ($entities as $entity) {
          $parameter = explode('.', $entity);
          $entity_type_id = $parameter[0];
          $bundle = $parameter[1];
          if ($bundle == $entity_type_id) {
            $perm_index = 'cast rate vote on ' . $entity_type_id . ' of ' . $bundle;
            $permissions[$perm_index] = [
              'title' => $this->t('Can vote on :type', [':type' => $entity_type_id]),
            ];
          }
          else {
            $perm_index = 'cast rate vote on ' . $entity_type_id . ' of ' . $bundle;
            $permissions[$perm_index] = [
              'title' => $this->t('Can vote on :type type of :bundle', [':bundle' => $bundle, ':type' => $entity_type_id]),
            ];
          }
        }
      }

      $comments = $widget->get('comment_types');
      if ($comment_module_enabled && $comments && count($comments) > 0) {
        foreach ($comments as $comment) {
          $parameter = explode('.', $comment);
          $entity_type_id = $parameter[0];
          $bundle = $parameter[1];
          // Get the comment fields attached to the bundle.
          $fields = $this->commentManager->getFields($parameter[0]);
          foreach ($fields as $fid => $field) {
            if (in_array($bundle, $field['bundles'])) {
              if ($bundle == $entity_type_id) {
                $perm_index = 'cast rate vote on ' . $fid . ' on ' . $entity_type_id . ' of ' . $bundle;
                $permissions[$perm_index] = [
                  'title' => $this->t('Can vote on :comment on :type', [':comment' => $fid, ':type' => $entity_type_id]),
                ];
              }
              else {
                $perm_index = 'cast rate vote on ' . $fid . ' on ' . $entity_type_id . ' of ' . $bundle;
                $permissions[$perm_index] = [
                  'title' => $this->t('Can vote on :comment on :type type of :bundle', [
                    ':comment' => $fid,
                    ':type' => $entity_type_id,
                    ':bundle' => $bundle,
                  ]),
                ];
              }
            }
          }
        }
      }
    }

    return $permissions;
  }

}
