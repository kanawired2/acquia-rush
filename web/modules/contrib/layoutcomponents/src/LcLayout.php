<?php

namespace Drupal\layoutcomponents;

use Drupal\Component\Utility\DiffArray;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Html;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Class LcLayout.
 */
class LcLayout {

  /**
   * The layout data.
   *
   * @var array
   */
  protected $data;

  /**
   * The original layout data, used to determine if layout data has changed.
   *
   * @var array
   */
  protected $original;


  /**
   * Random delta.
   *
   * @var int
   */
  protected $delta;

  /**
   * LcLayout constructor.
   *
   * @param string $id
   *   The layout identifier.
   * @param array $settings
   *   The layout settings.
   * @param array $content
   *   The new content.
   * @param array $default
   *   The default content.
   */
  public function __construct($id, array $settings, array $content, array $default) {
    $this->data = [
      'id' => $id,
      'settings' => $settings,
      'content' => $content,
      'default' => $default,
    ];
    $this->original = $this->data;
    $this->delta = mt_rand(1, 1000);
  }

  /**
   * Indicates whether or not the layout data has changed.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function hasChanged() {
    return !!DiffArray::diffAssocRecursive($this->original, $this->data);
  }

  /**
   * Retrieves the layout identifier.
   *
   * @return string
   *   Return the id.
   */
  public function getId() {
    return $this->data['id'];
  }

  /**
   * Retrieves the delta.
   *
   * @return int
   *   Return the delta.
   */
  public function getDelta() {
    return $this->delta;
  }

  /**
   * Retrieves the path to the layout, may not be set.
   *
   * @return string|null
   *   Return the path.
   */
  public function getPath() {
    return $this->data['path'];
  }

  /**
   * Retrieves a specific layout setting.
   *
   * @param string $name
   *   The layout setting name. Can be dot notation to indicate a deeper key in
   *   the settings array.
   * @param mixed $default_value
   *   The default value to use if layout setting does not exists.
   *
   * @return mixed
   *   The layout setting value or $default_value if it does not exist.
   */
  public function getSetting($name, $default_value = NULL) {
    $parts = explode('.', $name);
    if (count($parts) === 1) {
      return isset($this->data['settings'][$name]) ? $this->data['settings'][$name] : $default_value;
    }
    $value = NestedArray::getValue($this->data['settings'], $parts, $key_exists);
    return $key_exists ? $value : $default_value;
  }

  /**
   * Retrieves all defined layout settings.
   *
   * @return array
   *   An associative array of layout settings, keyed by their machine name.
   */
  public function getSettings() {
    return $this->data['settings'];
  }

  /**
   * Retrieves the region content.
   *
   * @param string $region
   *   The region.
   *
   * @return array
   *   An associative array of region settings.
   */
  public function getRegionContent($region) {
    if (!isset($this->data['content'][$region])) {
      return [];
    }
    return $this->data['content'][$region];
  }

  /**
   * Sets the layout identifier.
   *
   * @param string $id
   *   The layout identifier.
   *
   * @return string|null
   *   Return the object.
   */
  public function setId($id) {
    $this->data['id'] = $id;
    return $this;
  }

  /**
   * Sets the path to the layout.
   *
   * @param string $path
   *   The path to the layout.
   *
   * @return string|null
   *   Return the object.
   */
  public function setPath($path) {
    $this->data['path'] = $path;
    return $this;
  }

  /**
   * Sets a specific layout region.
   *
   * @param string $name
   *   The layout region name.
   * @param mixed $value
   *   The layout region value.
   *
   * @return \Drupal\layoutcomponents\LcLayout
   *   The current BootstrapLayout instance.
   */
  public function setRegion($name, $value = NULL) {
    $this->data['regions'][$name] = $value;
    return $this;
  }

  /**
   * Sets a specific layout setting.
   *
   * @param string $name
   *   The layout setting name. Can be dot notation to indicate a deeper key in
   *   the settings array.
   * @param mixed $value
   *   The layout setting value.
   *
   * @return \Drupal\layoutcomponents\LcLayout
   *   The current BootstrapLayout instance.
   */
  public function setSetting($name, $value = NULL) {
    $parts = explode('.', $name);
    if (count($parts) === 1) {
      $this->data['settings'][$name] = $value;
    }
    else {
      NestedArray::setValue($this->data['settings'], $parts, $value);
    }
    return $this;
  }

  /**
   * Removes a layout region.
   *
   * @param string $name
   *   The layout region to remove.
   *
   * @return mixed
   *   The region that was removed.
   */
  public function unsetRegion($name) {
    $old = isset($this->data['regions'][$name]) ? $this->data['regions'][$name] : NULL;
    unset($this->data['regions'][$name]);
    return $old;
  }

  /**
   * Removes a layout setting.
   *
   * @param string $name
   *   The layout region to remove.
   *
   * @return mixed
   *   The setting that was removed.
   */
  public function unsetSetting($name) {
    $old = isset($this->data['settings'][$name]) ? $this->data['settings'][$name] : NULL;
    unset($this->data['settings'][$name]);
    return $old;
  }

  /**
   * Parses an attribute string saved in the UI.
   *
   * @param string $string
   *   The attribute string to parse.
   * @param array $tokens
   *   An associative array of token data to use.
   *
   * @return array
   *   A parsed attributes array.
   */
  public function parseAttributes($string = NULL, array $tokens = []) {
    static $token;
    if (!isset($token)) {
      /** @var \Drupal\Core\Utility\Token $token */
      $token = \Drupal::service('token');
    }
    $attributes = [];
    if (!empty($string)) {
      $parts = explode(',', $string);
      foreach ($parts as $attribute) {
        if (strpos($attribute, '|') !== FALSE) {
          [$key, $value] = explode('|', $token->replace($attribute, $tokens, ['clear' => TRUE]));
          $attributes[$key] = $value;
        }
      }
    }
    return $attributes;
  }

  /**
   * Render the new element.
   *
   * @return array
   *   The complete element.
   */
  public function render() {
    $output = [];

    $wrapper_style = [];
    $bg_wrapper = FALSE;

    // Add a layout wrapper and its attributes.
    $attributes = new Attribute($this->data['default']);
    $attributes->addClass(['lc-section', 'lc-section-' . $this->getDelta()]);
    $attributes->addClass(Html::cleanCssIdentifier($this->getId()));

    $component_container_attributes = new Attribute();
    $component_container_attributes->addClass('lc-inline_container-section-edit');
    $component_container_title_attributes = new Attribute();
    $component_container_title_attributes->addClass('lc-inline_container-title-edit');

    // Hidde the container if the title is empty.
    $title = $this->getSetting('title.general.title', '');
    if (empty($title)) {
      $component_container_title_attributes->addClass('hidden');
    }

    // Set wrapper full width.
    $full_width = $this->getSetting('section.styles.sizing.full_width', '');
    if (!empty($full_width) && $full_width == TRUE) {
      $attributes->addClass('container-fluid');
      // Set container for wrapper.
      $full_width_container = $this->getSetting('section.styles.sizing.full_width_container', '');
      if (!empty($full_width_container) && $full_width_container == TRUE) {
        $component_container_attributes->addClass('container');
      }
      // Set container for section title.
      $full_width_container_title = $this->getSetting('section.styles.sizing.full_width_container_title', '');
      if (!empty($full_width_container_title) && $full_width_container_title == TRUE) {
        $component_container_title_attributes->addClass('container');
      }
    }
    else {
      $attributes->addClass('container');
    }

    $this->setSetting('section.container', $component_container_attributes);
    $this->setSetting('section.title_container_attr', $component_container_title_attributes);

    // Add background color.
    $color = $this->getSetting('section.styles.background.background_color.settings.color', []);
    $opacity = $this->getSetting('section.styles.background.background_color.settings.opacity', []);
    if (!empty($color) && !empty($opacity)) {
      $background_color = $this->hexToRgba($color, $opacity);
      $bg_wrapper = TRUE;
    }

    // Set background image.
    $background_image_fid = $this->getSetting('section.styles.background.image', []);
    if (!empty($background_image_fid)) {
      $media = Media::load($background_image_fid);
      if (!empty($media)) {
        $background_image_fid = $media->getSource()->getSourceFieldValue($media);
        $file = File::load($background_image_fid);
        $url = Url::fromUri(file_create_url($file->getFileUri()))->getUri();
        if (!empty($url)) {
          $bg_image_ouput = 'background:';
          $attributes->setAttribute('data-image-src', $url);
          // Set parallax.
          $parallax = $this->getSetting('section.styles.misc.parallax', NULL);
          if (!empty($parallax)) {
            $attributes->addClass('parallax-window');
            $attributes->setAttribute('data-parallax', 'scroll');
            if (isset($background_color) && !empty($background_color)) {
              $bg_image_ouput .= "linear-gradient(" . $background_color . "," . $background_color . ")";
            }
            $wrapper_style[] = $bg_image_ouput;
            $bg_wrapper = FALSE;
          }
          // Set bg image.
          else {
            if (isset($background_color) && !empty($background_color)) {
              $bg_image_ouput .= "linear-gradient(" . $background_color . "," . $background_color . "), ";
            }
            $wrapper_style[] = $bg_image_ouput . 'url(' . $url . ')';
            $bg_wrapper = TRUE;
          }
        }
      }
    }
    elseif (isset($background_color) && !empty($background_color)) {
      $wrapper_style[] = 'background-color: ' . $background_color . ';';
    }

    if ($bg_wrapper == TRUE) {
      $wrapper_style[] = 'background-position: 50% 50%; background-size: cover';
    }

    // Set extra classes.
    $extra_classes = $this->getSetting('section.styles.misc.extra_class', '');
    if (!empty($extra_classes)) {
      foreach (explode(',', $extra_classes) as $class) {
        $class = str_replace(' ', '', $class);
        $attributes->addClass($class);
      }
    }

    // Set extra attributes.
    $extra_attributes = $this->getSetting('section.styles.misc.extra_attributes', '');
    if (!empty($extra_attributes)) {
      foreach (explode(' ', $extra_attributes) as $attr) {
        [$key, $value] = explode('|', $attr);
        $attributes->setAttribute($key, $value);
      }
    }

    // Set no top paddings.
    $px_padding_top = $this->getSetting('section.styles.spacing.top_padding', '');
    if ($px_padding_top) {
      $wrapper_style[] = 'padding-top: ' . $px_padding_top . 'px';
    }

    // Set no bottom paddings.
    $px_padding_bottom = $this->getSetting('section.styles.spacing.bottom_padding', '');
    if ($px_padding_bottom) {
      $wrapper_style[] = 'padding-bottom: ' . $px_padding_bottom . 'px !important';
    }

    // Set height.
    $height_type = $this->getSetting('section.styles.sizing.height', 'auto');

    if ($height_type == 'auto') {
      $wrapper_style[] = 'height: auto';
    }
    elseif ($height_type == 'manual') {
      $wrapper_style[] = 'height: ' . $this->getSetting('section.styles.sizing.height_size', 'auto') . 'px';
    }
    else {
      $wrapper_style[] = 'height: ' . $height_type;
    }

    // Set wrapper config.
    $attributes->addClass('lc-inline_section-edit');
    $attributes->setAttribute('style', implode(';', $wrapper_style));
    $this->setSetting('section.attributes', $attributes);

    // Section title.
    $this->setSectionTitle();

    $cont = 0;
    foreach ($this->getSetting('regions') as $name => $column) {

      // Set Column Title.
      $this->setColumnTitle($name);

      // Set Column config.
      $this->setColumn($name, $cont);

      $cont++;
    }

    // Carousel control.
    $section_carousel = $this->getSetting('section.general.structure.section_carousel');
    $section_carousel_slick = $this->getSetting('section.general.structure.section_carousel_slick');

    if (boolval($section_carousel) && $section_carousel_slick !== 'none') {
      /** @var \Drupal\slick\SlickManager $slick */
      $slick = \Drupal::service('slick.manager');

      $items = [];
      foreach ($this->getSetting('regions') as $name => $column) {
        $path = 'regions.' . $name;

        $item = $this->getSetting($path);
        unset($item['layout_builder-configuration']);
        $item['#title'] = $item['title'];
        $items[] = [
          'slide' => [
            '#type' => 'container',
            '#theme' => 'layout__layoutcomponents_slick_region',
            '#content' => $item,
            '#attributes' => [
              'class' => ['lc-slick-column-wrapper'],
            ],
          ],
        ];
      }

      if (!empty($items)) {
        $skin = \Drupal::entityTypeManager()->getStorage('slick')->load($section_carousel_slick);
        if (!empty($skin)) {
          $class = 'lc-slick-section-' . $this->getDelta();

          $build = [
            'items' => $items,
            'options' => $skin->getSettings(),
            'attributes' => [
              'class' => [$class, 'w-100'],
            ],
          ];

          // Get responsive options.
          $options = $skin->getResponsiveOptions();
          if (!empty($options)) {
            // Prepare the array for JS.
            $responsive_options = [
              'parent' => 'lc-section-' . $this->getDelta(),
              'options' => $options,
            ];
            // Normal array.
            foreach ($options as $option) {
              $build['options']['responsive'][] = $option;
            }
            // Store JS options.
            $this->setSetting('js.responsive.' . $class, $responsive_options);
          }
          $element = $slick->build($build);
          $this->setSetting('regions.slick', $element);
        }
      }
    }

    // Store data.
    $output['output'] = $this->getSettings();

    return $output;
  }

  /**
   * Set the column attributes.
   *
   * @param string $name
   *   The name of column.
   * @param string $delta
   *   The delta.
   */
  public function setColumn($name, $delta) {
    $path = 'regions.' . $name;
    $column_size_sm = explode('/', $this->getSetting('section.general.structure.section_structure_sm', 1));
    $column_size = explode('/', $this->getSetting('section.general.structure.section_structure', 1));
    $column_size_lg = explode('/', $this->getSetting('section.general.structure.section_structure_lg', 1));
    $border = ($this->getSetting($path . '.styles.border.border', '') == 'all') ? '' : '-' . $this->getSetting($path . '.styles.border.border', '');
    $border_size = $this->getSetting($path . '.styles.border.size');
    $border_color = $this->getSetting($path . '.styles.border.color.settings.color');
    $border_radius_top_left = $this->getSetting($path . '.styles.border.radius_top_left');
    $border_radius_top_right = $this->getSetting($path . '.styles.border.radius_top_right');
    $border_radius_bottom_left = $this->getSetting($path . '.styles.border.radius_bottom_left');
    $border_radius_bottom_right = $this->getSetting($path . '.styles.border.radius_bottom_right');
    $background_color = $this->getSetting($path . '.styles.background.color.settings.color');
    $background_opacity = $this->getSetting($path . '.styles.background.color.settings.opacity');
    $remove_paddings = $this->getSetting($path . '.styles.spacing.paddings');
    $remove_padding_left = $this->getSetting($path . '.styles.spacing.paddings_left');
    $remove_padding_right = $this->getSetting($path . '.styles.spacing.paddings_right');
    $extra_classes = $this->getSetting($path . '.styles.misc.extra_class');

    $column_classes = new Attribute();
    $column_styles = [];

    // Column default classes.
    $column_classes->addClass('lc-inline_column_' . $name . '-edit');
    $column_classes->addClass('layoutcomponent-column');

    // Column size.
    if (is_numeric($delta)) {
      if (isset($column_size_sm[$delta]) && !empty($column_size_sm[$delta])) {
        $column_classes->addClass('col-sm-' . $column_size_sm[$delta]);
      }
      if (isset($column_size[$delta]) && !empty($column_size[$delta])) {
        $column_classes->addClass('col-md-' . $column_size[$delta]);
      }
      if (isset($column_size_lg[$delta]) && !empty($column_size_lg[$delta])) {
        $column_classes->addClass('col-lg-' . $column_size_lg[$delta]);
      }
    }

    // Column border.
    if ($border !== '-none') {
      if (!empty($border_size)) {
        $column_styles[] = 'border' . $border . ': ' . $border_size . 'px solid';
        if (!empty($border_color)) {
          $column_styles[] = 'border-color: ' . $border_color;
        }
        // Border radius.
        if (!empty($border_radius_top_left)) {
          $column_styles[] = 'border-top-left-radius: ' . $border_radius_top_left . '%';
        }
        if (!empty($border_radius_top_right)) {
          $column_styles[] = 'border-top-right-radius: ' . $border_radius_top_right . '%';
        }
        if (!empty($border_radius_bottom_left)) {
          $column_styles[] = 'border-bottom-left-radius: ' . $border_radius_bottom_left . '%';
        }
        if (!empty($border_radius_bottom_right)) {
          $column_styles[] = 'border-bottom-right-radius: ' . $border_radius_bottom_right . '%';
        }
      }
      else {
        $column_styles[] = 'border: none';
      }
    }

    // Column background.
    if (!empty($background_color) && !empty($background_opacity)) {
      $column_styles[] = 'background-color: ' . $this->hexToRgba($background_color, $background_opacity);
    }

    $column_styles[] = 'z-index: 1';

    // Column paddings.
    if (isset($remove_paddings) && $remove_paddings == TRUE) {
      $column_classes->addClass('p-0');
    }

    if (!empty($remove_padding_left) && $remove_padding_left == TRUE) {
      $column_classes->addClass('pl-0');
    }

    if (!empty($remove_padding_right) && $remove_padding_right == TRUE) {
      $column_classes->addClass('pr-0');
    }

    // Column extra class.
    if (!empty($extra_classes)) {
      foreach (explode(',', $extra_classes) as $class) {
        $class = str_replace(' ', '', $class);
        $column_classes->addClass(preg_replace('/[^A-Za-z0-9\-]/', '', $class));
      }
    }

    // Set column config.
    $column_classes->setAttribute('style', implode(';', $column_styles));
    $this->setSetting($path . '.styles.column_attr', $column_classes);

    // Column container type.
    $container_attributes = new Attribute();
    $container_attributes->addClass(['row']);
    $container_attributes->addClass('lc-container-cols');
    $container_attributes->addClass('lc-inline_row-edit');

    // Add container alignment.
    $align = $this->getSetting('section.general.basic.section_content_align', []);
    if (isset($align)) {
      $container_attributes->addClass($align);
    }

    $this->setSetting('section.row', $container_attributes);

    $content = $this->getRegionContent($name);
    $subregions = $this->getSetting('regions.' . $name . '.subcolumn');
    if (!empty($subregions['groups'])) {
      foreach ($subregions['data'] as $group => $uuids) {
        if (empty($group)) {
          continue;
        }
        $subregion_content = [];
        foreach ($uuids as $uuid) {
          if (!isset($content[$uuid])) {
            continue;
          }
          $classes = [];
          // Block attributes.
          if ($block_classes = explode(',', $subregions['structures'][$uuid])) {
            foreach ($block_classes as $block_class) {
              if (!empty($block_class)) {
                $classes[] = $block_class;
              }
            }
          }
          $classes[] = 'lc-inline_block_' . md5($uuid) . '-edit';
          $classes = array_unique($classes);
          if (!isset($content[$uuid]['#attributes'])) {
            $content[$uuid]['#attributes'] = new Attribute();
            $content[$uuid]['#attributes']->addClass($classes);
          }
          else {
            $content[$uuid]['#attributes']['class'] = array_merge($content[$uuid]['#attributes']['class'], $classes);
          }
          $subregion_content[] = $content[$uuid];
          unset($content[$uuid]);
        }

        // Subregion attributes.
        $subregion_classes = [
          'lc-inline_subcolumn_type_' . $group . '-edit',
          'js-layout-builder-column',
        ];
        if ($subregion_user_classes = explode(',', $subregions['classes'][$group])) {
          foreach ($subregion_user_classes as $subregion_class) {
            if (!empty($subregion_class)) {
              $subregion_classes[] = $subregion_class;
            }
          }
        }
        $subregion_classes = array_unique($subregion_classes);
        $subregion_attributes = new Attribute();
        $subregion_attributes->addClass($subregion_classes);
        $content[] = [
          '#theme' => 'layout__layoutcomponents_subregion',
          '#subregion' => [
            'type' => $subregions['types'][$group],
            'attributes' => $subregion_attributes,
          ],
          '#content' => $subregion_content,
          '#group' => $group,
        ];
      }
    }

    // Column content.
    $this->setSetting('regions.' . $name . '.content', $content);
    $this->setSetting('regions.' . $name . '.attributes', "lc-inline_column_$name-content-edit");

    // Store the region inside container width custom theme.
    $new_region = [
      '#theme' => 'layout__layoutcomponents_region',
      '#region' => $this->getSetting('regions.' . $name),
      '#key' => $name,
    ];

    $this->setSetting('regions.' . $name, $new_region);
  }

  /**
   * Set the column title attributes.
   *
   * @param string $name
   *   The name of column.
   */
  public function setColumnTitle($name) {
    $path = 'regions.' . $name;
    $title = $this->getSetting($path . '.general.title', '');
    $title_type = $this->getSetting($path . '.styles.title.type', 'h2');
    $title_size = $this->getSetting($path . '.styles.title.size');
    $title_color = $this->getSetting($path . '.styles.title.color.settings.color');
    $title_opacity = $this->getSetting($path . '.styles.title.color.settings.opacity');
    $title_border = ($this->getSetting($path . '.styles.title.border', '') == 'all') ? '' : '-' . $this->getSetting($path . '.styles.title.border', '');
    $title_border_size = $this->getSetting($path . '.styles.title.border_size', 0);
    $title_border_color = $this->getSetting($path . '.styles.title.border_color.settings.color');

    // Container title.
    $container_classes = new Attribute();
    $container_styles = [];

    // Container inline.
    $container_classes->addClass('lc-inline_column_' . $name . '-container-title-edit');

    // Container Border.
    if ($title_border !== 'none') {
      $container_styles[] = 'border' . $title_border . ': ' . $title_border_size . 'px solid';
      if (!empty($title_border_color)) {
        $container_styles[] = 'border-color: ' . $title_border_color;
      }
    }

    $container_classes->setAttribute('style', implode(';', $container_styles));
    $this->setSetting($path . '.styles.title_wrap_attr', $container_classes);

    // Title.
    $title_classes = new Attribute();
    $title_styles = [];

    // Hide the title if empty.
    if (empty($title)) {
      $title_styles[] = 'display: none;';
    }

    // Title inline.
    $title_classes->addClass('lc-inline_column_' . $name . '-title-edit');

    // Title type.
    $this->setSetting($path . '.styles.title_wrapper', $title_type);

    // Title size.
    if (!empty($title_size)) {
      $title_styles[] = 'font-size:' . $title_size . 'px';
    }

    // Title align.
    $title_classes->addClass($this->getSetting($path . '.styles.title.align'));

    // Title Color.
    if (!empty($title_color)) {
      $title_styles[] = 'color:' . $this->hexToRgba($title_color, $title_opacity);
    }

    $title_classes->setAttribute('style', implode(';', $title_styles));
    $this->setSetting($path . '.styles.title_attr', $title_classes);
  }

  /**
   * Set the section title attributes.
   */
  public function setSectionTitle() {
    // Data.
    $title = $this->getSetting('title.general.title', '');
    $description = $this->getSetting('title.general.description', '');
    $title_color = $this->getSetting('title.styles.design.title_color.settings.color', []);
    $title_opacity = $this->getSetting('title.styles.design.title_color.settings.opacity', []);
    $title_size = $this->getSetting('title.styles.sizing.title_size', []);
    $title_border = ($this->getSetting('title.styles.border.title_border') == 'all') ? '' : '-' . $this->getSetting('title.styles.border.title_border');
    $title_border_size = $this->getSetting('title.styles.border.title_border_size');
    $title_border_color = $this->getSetting('title.styles.border.title_border_color.settings.color');
    $title_margin_top = ($this->getSetting('title.styles.spacing.title_margin_top') == 0) ? '' : $this->getSetting('title.styles.spacing.title_margin_top') . 'px';
    $title_margin_bottom = ($this->getSetting('title.styles.spacing.title_margin_bottom') == 0) ? '' : $this->getSetting('title.styles.spacing.title_margin_bottom') . 'px';
    $title_extra_class = $this->getSetting('title.styles.misc.title_extra_class', '');
    $description_extra_class = $this->getSetting('title.styles.misc.description_extra_class', '');

    // Title container classes.
    $container_classes = new Attribute();
    $container_classes->addClass('lc-inline_title-container-edit');

    // Set container classes.
    $this->setSetting('title.styles.attr_class.container', $container_classes);

    // Title container styles.
    $container_styles = [];

    // Padding top (Margin in menu).
    if (!empty($title_margin_top)) {
      $container_styles[] = 'padding-top: ' . $title_margin_top;
    }

    // Margin bottom.
    if (!empty($title_margin_bottom)) {
      $container_styles[] = 'margin-bottom: ' . $title_margin_bottom;
    }

    // Set container styles.
    $styles = new Attribute();
    $styles->setAttribute('style', implode(';', $container_styles));
    $this->setSetting('title.styles.attr_styles.container', $styles);

    // Title classes.
    $title_classes = new Attribute();
    $title_classes->addClass('lc-inline_title-edit');
    $title_classes->addClass('border-type' . $title_border);
    $title_classes->addClass($this->getSetting('title.styles.design.title_align'));

    // Title extra class.
    if (!empty($title_extra_class)) {
      foreach (explode(',', $title_extra_class) as $class) {
        $class = str_replace(' ', '', $class);
        $title_classes->addClass(preg_replace('/[^A-Za-z0-9\-]/', '', $class));
      }
    }

    $this->setSetting('title.styles.attr_class.title', $title_classes);

    // Description classes.
    $description_classes = new Attribute();
    $description_classes->addClass('lc-inline_description-edit');

    // Description extra class.
    if (!empty($description_extra_class)) {
      foreach (explode(',', $description_extra_class) as $class) {
        $class = str_replace(' ', '', $class);
        $description_classes->addClass(preg_replace('/[^A-Za-z0-9\-]/', '', $class));
      }
    }

    $this->setSetting('title.styles.attr_class.description', $description_classes);

    // Title styles.
    $title_styles = [];

    if (empty($title)) {
      $section_title_wrap_style[] = 'display: none;';
    }

    // Size.
    if (!empty($title_size)) {
      $title_styles[] = 'font-size:' . $title_size . 'px';
    }

    // Color.
    if (!empty($title_color)) {
      $title_styles[] = 'color:' . $this->hexToRgba($title_color, $title_opacity);
    }

    // Border.
    if ($title_border !== 'none') {
      if ($title_border_size > 0) {
        $title_styles[] = 'border' . $title_border . ': ' . $title_border_size . 'px solid';
        $title_styles[] = 'border-color: ' . $title_border_color;
      }
    }

    // Set title styles.
    $styles = new Attribute();
    $styles->setAttribute('style', implode(';', $title_styles));
    $this->setSetting('title.styles.attr_styles.title', $styles);
  }

  /**
   * Get the JS settings.
   *
   * @return array
   *   The JS settings.
   */
  public function getJsSettings() {
    return $this->getSetting('js', []);
  }

  /**
   * Convert hex color to rgba.
   *
   * @param string $hex
   *   The color as hex.
   * @param string $opacity
   *   The opacity.
   *
   * @return string
   *   The color converted to rgb|rgba.
   */
  public function hexToRgba($hex, $opacity = NULL) {
    if (isset($hex) && isset($opacity)) {
      [$r, $g, $b] = sscanf($hex, "#%02x%02x%02x");
      $background_color = 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $opacity . ')';
    }
    else {
      [$r, $g, $b] = sscanf($hex, "#%02x%02x%02x");
      $background_color = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
    }
    return $background_color;
  }

}
