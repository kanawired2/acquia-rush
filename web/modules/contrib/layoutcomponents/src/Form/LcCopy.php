<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layoutcomponents\LcLayoutsManager;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * Provides a form to copy a block.
 */
class LcCopy extends FormBase {

  use AjaxFormHelperTrait;
  use LayoutRebuildTrait;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * The Lc manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $layoutManager;

  /**
   * The field delta.
   *
   * @var int
   */
  protected $delta;

  /**
   * The current region.
   *
   * @var string
   */
  protected $region;

  /**
   * The UUID of the component.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The Type of the element.
   *
   * @var string
   */
  protected $type;


  /**
   * The section storage.
   *
   * @var \Drupal\layout_builder\SectionStorageInterface
   */
  protected $sectionStorage;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Is a default section.
   *
   * @var bool
   */
  protected $isDefault;

  /**
   * Constructs a new copy block form.
   *
   * @param \Drupal\layoutcomponents\LcLayoutsManager $layout_manager
   *   The layout manager object.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The UUID generator.
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The PrivateTempStoreFactory object.
   */
  public function __construct(LcLayoutsManager $layout_manager, UuidInterface $uuid, LayoutTempstoreRepositoryInterface $layout_tempstore_repository, PrivateTempStoreFactory $temp_store) {
    $this->layoutManager = $layout_manager;
    $this->uuidGenerator = $uuid;
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->tempStoreFactory = $temp_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.layoutcomponents_layouts'),
      $container->get('uuid'),
      $container->get('layout_builder.tempstore_repository'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutcomponents_copy';
  }

  /**
   * Get the type of section.
   *
   * @return bool
   *   If the section is default or not.
   */
  protected function getDefault() {
    return $this->isDefault;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Check section type.
    $section_overwrite = $this->sectionStorage->getSection($this->delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
    $this->isDefault = (boolval($section_overwrite) && !$this->sectionStorage instanceof DefaultsSectionStorage) ? TRUE : FALSE;

    if ($this->isDefault) {
      $message = 'This element cannot be deleted because is configurated as default in your display settings';
      $form = $this->layoutManager->getDefaultCancel($message);
    }
    else {
      $form['markup'] = [
        '#type' => 'markup',
        '#markup' => '<div class="layout_builder__add-section-confirm">' . $this->t('Do you want to copy this element?') . '</div>',
      ];

      $form['info'] = [
        '#type' => 'markup',
        '#markup' => '<div class="layout_builder__add-section-confirm">' . $this->t('Once the element is copied to the clipboard you will paste it clicking on the icon below') . ' </div><span class="lc-copy"></span>',
        '#weight' => 0,
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->submitLabel(),
        '#button_type' => 'primary',
        '#ajax' => [
          'callback' => '::ajaxSubmit',
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  protected function submitLabel() {
    return $this->t('Copy to clipboard');
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $response = $this->rebuildAndClose($this->sectionStorage);
    if ($this->isDefault) {
      return $response;
    }
    $response->addCommand(new InvokeCommand('a[class*="lc-clipboard"]', 'removeClass', ['hidden']));
    return $response;
  }

}
