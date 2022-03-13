<?php

namespace Drupal\layoutcomponents;

use Drupal\media\Entity\Media;

/**
 * Extend twig with additional functions used in layoutcomponents.
 */
class LayoutComponentsTwigExtension extends \Twig_Extension {

  /**
   * Get additional functions in twig.
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('mediaUrl', [$this, 'mediaUrl']),
      new \Twig_SimpleFunction('getMediaStyle', [$this, 'getMediaStyle']),
    ];
  }

  /**
   * Get media image url according to the selected language in page.
   */
  public function mediaUrl($id) {
    if (!empty($id)) {
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if (isset($language)) {
        $media = Media::load($id);
        if (isset($media)) {
          if (!$media->hasTranslation($language)) {
            $language = \Drupal::languageManager()->getDefaultLanguage()->getId();
          }
          $translation = $media->getTranslation($language);
          if (isset($translation)) {
            return $translation->field_media_image->entity->getFileUri();
          }
        }
      }
    }
    return '';
  }

  /**
   * Get image media style.
   */
  public function getMediaStyle($viewmode) {
    if (isset($viewmode)) {
      $settings = \Drupal::service('entity_type.manager')
        ->getStorage('entity_view_display')
        ->load('media.image.' . $viewmode)->get('content');
      if (isset($settings)) {
        return $settings['field_media_image']['settings']['image_style'];
      }
    }
    return '';
  }

}
