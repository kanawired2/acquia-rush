<?php

namespace Drupal\layoutcomponents\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class LcClipboardController.
 *
 * Provide the LC clipboard data.
 */
class LcClipboardController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * LcClipboardController constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The PrivateTempStoreFactory object.
   */
  public  function __construct(PrivateTempStoreFactory $temp_store) {
    $this->tempStoreFactory = $temp_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * Return the element coied on clipboard.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JsonResponse object.
   */
  public function getElement() {
    $store = $this->tempStoreFactory->get('lc');
    $data = $store->get('lc_element');
    return new JsonResponse($data);
  }

}
