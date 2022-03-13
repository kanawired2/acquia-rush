<?php

namespace Drupal\custom_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Drupal\Core\StreamWrapper\PublicStream;

/**
 * Class ExcelReaderController.
 */
class ExcelReaderController extends ControllerBase {

  /**
   * Readexcel.
   *
   * @return string
   *   Return Hello string.
   */
  public function readExcel() {
$filePath =  PublicStream::basePath() . '/file.xlsx';
$reader = ReaderEntityFactory::createReaderFromFile($filePath);

$reader->open($filePath);

foreach ($reader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {
        // do stuff with the row
        $cells = $row->getCells();
        foreach ($cells as $key => $value) {
          print_r($value->getValue());
        }
    }
}

$reader->close();
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: readExcel')
    ];
  }

}
