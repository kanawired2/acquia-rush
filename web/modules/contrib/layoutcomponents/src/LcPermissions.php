<?php

namespace Drupal\layoutcomponents;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for Layout Components.
 */
class LcPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * LcPermissions constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   The bundle info service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_info) {
    $this->entityTypeManager = $entity_type_manager;
    $this->bundleInfo = $bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * The LC permissions for entities.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  public function getPermissions() {
    $permissions = [];

    /** @var \Drupal\layout_builder\Entity\LayoutEntityDisplayInterface[] $entity_displays */
    $entity_displays = $this->entityTypeManager->getStorage('entity_view_display')->loadByProperties(['third_party_settings.layout_builder.allow_custom' => TRUE]);
    foreach ($entity_displays as $entity_display) {
      $entity_type_id = $entity_display->getTargetEntityTypeId();
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $bundle = $entity_display->getTargetBundle();
      $args = [
        '%entity_type' => $entity_type->getCollectionLabel(),
        '@entity_type_singular' => $entity_type->getSingularLabel(),
        '@entity_type_plural' => $entity_type->getPluralLabel(),
        '%bundle' => $this->bundleInfo->getBundleInfo($entity_type_id)[$bundle]['label'],
      ];

      // Create sections.
      $permissions["create $bundle $entity_type_id sections"] = [
        'title' => $this->t('%entity_type - %bundle: Create LC sections', $args),
      ];

      // Move sections.
      $permissions["move all $bundle $entity_type_id sections"] = [
        'title' => $this->t('%entity_type - %bundle: Move all LC sections', $args),
      ];

      // Remove sections.
      $permissions["remove all $bundle $entity_type_id sections"] = [
        'title' => $this->t('%entity_type - %bundle: Remove all LC sections', $args),
      ];

      // Configure sections.
      $permissions["configure all $bundle $entity_type_id sections"] = [
        'title' => $this->t('%entity_type - %bundle: Configure all LC sections', $args),
      ];

      // Change layout sections.
      $permissions["change all $bundle $entity_type_id layout sections"] = [
        'title' => $this->t('%entity_type - %bundle: Change all LC layout sections', $args),
      ];

      // Copy sections.
      $permissions["copy all $bundle $entity_type_id sections"] = [
        'title' => $this->t('%entity_type - %bundle: Copy all LC sections', $args),
      ];

      // Configure columns.
      $permissions["configure all $bundle $entity_type_id columns"] = [
        'title' => $this->t('%entity_type - %bundle: Configure all LC columns', $args),
      ];

      // Copy columns.
      $permissions["copy all $bundle $entity_type_id columns"] = [
        'title' => $this->t('%entity_type - %bundle: Copy all LC columns', $args),
      ];

      // Add block.
      $permissions["add $bundle $entity_type_id blocks"] = [
        'title' => $this->t('%entity_type - %bundle: Add LC blocks', $args),
      ];

      // Move block.
      $permissions["move $bundle $entity_type_id blocks"] = [
        'title' => $this->t('%entity_type - %bundle: Move LC blocks', $args),
      ];

      // Remove block.
      $permissions["remove $bundle $entity_type_id blocks"] = [
        'title' => $this->t('%entity_type - %bundle: Remove LC blocks', $args),
      ];

      // Configure block.
      $permissions["configure $bundle $entity_type_id blocks"] = [
        'title' => $this->t('%entity_type - %bundle: Configure LC blocks', $args),
      ];

      // Copy block.
      $permissions["copy $bundle $entity_type_id blocks"] = [
        'title' => $this->t('%entity_type - %bundle: Copy LC blocks', $args),
      ];

    }
    return $permissions;
  }
}
