<?php

namespace Drupal\custom_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\VarDumper\VarDumper;
use Drush\Drush;

/**
 * Class SymfonyController.
 */
class SymfonyController extends ControllerBase {

  /**
   * Domcrawler.
   *
   * @return string
   *   Return Hello string.
   */
  public function domCrawler() {
    /** @var SiteAliasManager $alias_manager */
    $alias_manager = Drush::service('site.alias.manager');
    Drush::drush($alias_manager->getSelf(), 'cache-rebuild')->run();
    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
        <body>
            <p class="message">test content</p>
            <p>Hello Crawler!</p>
        </body>
    </html>
    HTML;

    //$html = quoted_printable_decode($html);
    $crawler = new Crawler($html);
    $elem = $crawler->filter('.message')->html();
    return [
      '#type' => 'markup',
      '#markup' => $this->t($elem)
    ];
  }

}
