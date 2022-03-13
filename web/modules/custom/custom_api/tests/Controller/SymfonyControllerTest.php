<?php

namespace Drupal\custom_api\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the custom_api module.
 */
class SymfonyControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "custom_api SymfonyController's controller functionality",
      'description' => 'Test Unit for module custom_api and controller SymfonyController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests custom_api functionality.
   */
  public function testSymfonyController() {
    // Check that the basic functions of module custom_api.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
