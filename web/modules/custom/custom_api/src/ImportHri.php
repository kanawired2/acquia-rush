<?php

namespace Drupal\custom_api;
use Drupal\custom_api\ReadExcel;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class ImportHri.
 */
class ImportHri {

  /**
   * Drupal\custom_api\ReadExcel definition.
   *
   * @var \Drupal\custom_api\ReadExcel
   */
  protected $customApiReadExcel;

  /**
   * Constructs a new ImportHri object.
   */
  public function __construct(ReadExcel $custom_api_read_excel) {
    $this->customApiReadExcel = $custom_api_read_excel;
  }

  

}
