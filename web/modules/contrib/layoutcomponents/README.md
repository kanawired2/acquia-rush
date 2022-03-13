# LAYOUT COMPONENTS


This module has been based on "Layout Builder" and provides a complete package of components integrated with that system,
functionality that is available on Drupal 8 and is already fully supported by Drupal 9.

With this module we want to extend the user experience by creating an improved interface, much more customizable and with
aspects that are essential for us, such as the live preview of the changes while editing the content.

Layout components provides the editor with a series of components already created and available to be used out of the box.

For the experts, the module provides an API where you can add your own fields and get the benefits from all the
customization that the module adds to the platform.

### PRE-INSTALATION

- Required patches:
  - Since the module is based on the Layout builder and there are some known bugs that still needs to be fixed you need
  to install the composer patches and enabled them on the project. ``composer require cweagans/composer-patches`` after
  adding this sentence you will need to add under the extras section of your composer json file the next line
  ``"enable-patching": true``

  Note: If you have installed the module before enabling the patch installation you should run a composer update in
  order to apply the necessary patches.

- This version works with **Bootstrapt 4** and it is mandatory to have the custom themes of the project based on this
theme. You can enable the theme under "Appearance" and set it as default.

- Configure Bootstrap 4 settings: (Optional)
  - Website container type: fluid

- Required modules:
  - layout_builder
  - layout_discovery
  - field_group
  - video_embed_field
  - entity_reference_revisions
  - media
  - media_library
  - linked_field
  - color_field
  - media_library_form_element
  - inline_entity_form
  - block_form_alter
  - jquery_ui_slider
  - jquery_ui
  - jquery_ui_tooltip
  - sliderwidget - This module is not Drupal 9 ready and it has been temporaly moved to have this functionality for the
   release of this module.

Module Installation
===================

The layout components manages its dependencies via composer. To install the module on your project:

```
cd $DRUPAL
composer require drupal/layoutcomponents
```

1. Enable the module 'layoutcomponents' in /admin/modules or through the drush console with ``drush en layoutcomponents``

2. Enable the modules/components of layoutcomponents that you want to use in
  /admin/modules. Each component will create a block type after enabling.

3. Enable layout builder to an entity.

4. Configure the layout builder to add a section with a new "custom block".

### FEATURES

This module provides 6 types of templates based on bootstrap 4, this templates have the possibility of use from 1 to 6
columns with different distributions that can be easily changed.


With layoutcomponents you can perform a lot of things like:

- Add a background color or background image with parallax option.
- Configure the paddings top/bottom of the section.
- Add multiples classes and attributes to the section.
- Add a wrapper to the section (div, span, section, article...)
- Align the content of the section.
- Create and customize different components.
- Add title to the section and configure styles, text... of it.
- Editors can change the template to another one.
- Editors can set the sections as full width of the screen.
- and much more...

There are a lot of options that allow editors to create a page as they want to be, the limit is on their side.

### COMPONENTS

List of components included:

1. Accordion
2. Button
3. Countdown
4. Iframe
5. Image
6. Tabs
7. Text
8. Title
9. Video

NOTE: More information about them in the README.md of each component.

### DEMO - VIDEOTUTORIAL

In progress

### INLINE API

In progress



Initial configuration
---------------------

Current configuration available right now:

Settings url: /admin/layoutcomponents/settings

- Width: Width of the "Layout components" panel
- Colors: List of the colors that the editor will have available while using the color type.
- Theme: Light, Dark, Grey Light, Grey Dark.

Layout components features
==========================

Currently we continue to develop the module with a lot of passion and we are proud to present all the functionalities that we already have available for its launch.

- Section settings:
  - Title
    - Styles
      - Text
        - Color
          - Opacity
        - Title type: H1, H2, H3, ...
        - Title align: Left, center, right
      - Sizing
        - Font Size: 0-100 px
      - Border
        - Type
        - Color
          - Opacity
      - Spacing
        - Margin top: 0-500px
        - Margin bottom: 0-500px
  - Section
    - General
      - Basic
        - Type: Div, span, section, article,...
      - Structure
        - Type: Display distribution (Example: 4 Column - 25/25/25/25)
    - Styles
      - Background
        - Image : Media image
        - Color
          - Opacity
      - Sizing
        - Full width
        - Height Type
        - Height Size
      - Spacing
        - Top padding: 0-500px
        - Bottom padding: 0-500px
      - Misc
        - Additional classes
        - Additional attributes
        - Parallax

- Column settings:
  - General
    - Title
  - Styles
    - Text
      - Title type
      - Title color
      - Title size
      - Title align
      - Title border
      - Title border size
      - Title border color
    - Border
      - Type
      - Size
      - Color
      - Radius (Top-left, Top-right, Bottom-left, Bottom-right)
    - Background
      - Color
    - Spacing
      - No paddings
      - No left padding
      - No right padding
    - Misc
      - Extra class
- Components settings
  - Accordion: Component that provides the accordion functionality toggling the visibility of content.
  - Button: Component that provides a simple customizable button.
  - Countdown: Component that provides a simple countdown with specific end date.
  - Iframe: Component that provides the use of Iframes on the page.
  - Image: Component that provides the use of an Image
  - Tabs: Complex component that gives the ability of using tabs. Each tab will have its own page with the
  Layout components builder
  - Text: Simple component that gives a Simple text with CKEditor.
  - Title: Component that gives a simple text component plus more fields.
  - Video: Component that provides the ability to include videos through the Media upload system or external video URL.
