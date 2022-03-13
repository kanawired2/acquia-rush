<?php

namespace Drupal\layoutcomponents\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class LcInlineMedia.
 *
 * Provide the LC media data.
 */
class LcInlineMedia extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ChooseBlockController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public  function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * ChooseBlockController constructor.
   *
   * @param string $id
   *   The media id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JsonResponse object.
   */
  public function getMedia($id) {
    /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $media_field */
    $media_field = $this->entityTypeManager->getStorage('media')->load($id)->get('field_media_image')->getValue();

    /** @var \Drupal\file\Entity\file $file */
    $file = $this->entityTypeManager->getStorage('file')->load($media_field[0]['target_id']);
    if (!isset($file)) {
      return new JsonResponse(['uri' => '']);
    }

    $data['uri'] = $file->createFileUrl();

    return new JsonResponse($data);
  }

}
