/**
 * @file
 * Layout Components behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  var ajax = Drupal.ajax,
    behaviors = Drupal.behaviors;

  behaviors.editBlockInline = {
    attach: function (context) {
      var selector = '.js-layout-builder-block';
      Array.prototype.forEach.call(context.querySelectorAll(selector), function (block) {
        $(block).on('dblclick', function (e) {
          var li = $(this).find('li.layout-builder-block-update > a');
          if (li) {
            li.click();
          }
        })
      })
    }
  }

  behaviors.controlForm = {
    attach: function () {
      $('div[data-drupal-selector="edit-layout-builder-layout-wrapper"], div[data-drupal-selector="edit-settings-block-form"]').removeClass('col-auto');

    }
  };

  /**
   * hiddeFormElements
   *
   * Function to hidde form elements.
   */
  behaviors.hiddeFormElements = {
    attach: function () {
      $('.layoutcomponents-element-hidden').each(function (i) {
        $(this).closest('.draggable').addClass('element-hidden');
      });

      // Hide alteral nav.
      $('ul.inline-block-list li a').on('click', function () {
        $('.ui-dialog-titlebar-close').click();

      });

      // Handle
      $('#layout-builder').on('replaceWith', function (event) {
        alert('closed');
      });
    }
  };

  /**
   * Check if the string is empty.
   *
   * @type {Drupal}
   *
   * @return boolean
   *   True or false.
   */
  Drupal.isEmpty = function (data) {
    if (data == null || data.length < 1 || data === '') {
      return true;
    }
    return false;
  }

  /**
   * Alter the element.
   *
   * @type {Drupal~behavior}
   *
   */
  behaviors.inlineChangeElement = function (settings) {
    $.each(settings, function (i) {
      let data = settings[i];
      let item = data.item;
      switch (data.type) {
        case "text": {

          // If append element.
          if (data.append === true) {
            item.html(data.value);
          }
          else {
            item.text(data.value);
          }

          // Change text element.
          (data.value.length > 0 ? item.show() : item.hide());

          break;

        }
        case "element": {

          item.changeElementType(data.value);

          break;

        }
        case "class": {

          if (!Drupal.isEmpty(data.remove)) {
            // Remove old class.
            item.alterClass(data.remove);
          }

          // Add new class.
          item.addClass(data.value);

          break;

        }
        case "style": {

          if (!Drupal.isEmpty(data.style)) {
            // Remove styles.
            if (!Drupal.isEmpty(data.remove)) {
              item.css({
                [`${data.remove}`]: '',
              });
            }

            // Add new style.
            item.css(data.style, data.value);

            // Post process.
            if (data.style === 'background') {
              // Alter background style.
              item.css({
                'background-position': '50% 50%',
                'background-size': 'cover'
              })
            }

            if (data.style === 'font-size') {
              // Alter font-size style.
              if (data.value === '0px') {
                item.css(data.style, '');
              }
            }

          }
          break;

        }
        case "attribute": {
          // Alter attribute.
          item.attr(data.attribute, data.value);
          break;

        }
      }
    })
  }

  /**
   * Process the type of element.
   *
   * @type {Drupal~behaviors}
   *
   * @return data
   *   The element with all data processed.
   */
  behaviors.inlineElement = function (input) {
    let $input = $(input);

    // Get input info.
    let $info = $.parseJSON($input.attr('lc'));

    // Get element to attach.
    let $item = behaviors.inlineGetItem($input);

    // Define custom data.
    let $data = {};

    // Check if we change a group.
    if ('group' in $info) {
      return behaviors.handleGroupOptions();
    }

    // Input type text.
    if ($info.type === "text") {
      $data = behaviors.inlineTypeText($input, $item);
    }

    // Input type element.
    if ($info.type === "element") {
      $data = behaviors.inlineTypeElement($input, $item);
    }

    // Input type class.
    if ($info.type === "class") {
      $data = behaviors.inlineTypeClass($input, $item);
    }

    // Input type style.
    if ($info.type === "style") {
      $data = behaviors.inlineTypeStyle($input, $item);
    }

    // Input type attribute.
    if ($info.type === "attribute") {
      $data = behaviors.inlineTypeAttribute($input, $item);
    }

    // Send new data to live preview.
    return behaviors.inlineChangeElement($data);
  }

  /**
   * Get the container of element.
   *
   * @type {Drupal~behaviors}
   *
   * @return $item
   *   The container of element.
   */
  behaviors.inlineGetItem = function (input) {
    let $input = $(input);
    let $info = $.parseJSON($input.attr('lc'));
    let $edit = $input.attr('edit');
    let $form = '';
    let $block = '';
    let $section = '';
    let $selector = '';
    let $item = '';

    // Component (not used).
    if ($edit === "layout-builder-configure-block") {
      $form = $input.parents('form.' + $edit);
      $block = $form.attr('data-layout-builder-target-highlight-id');
      $selector = '*[data-layout-block-uuid="' + $block + '"]';
    }
    // Sections / columns.
    else {
      $form = $input.parents('form.layout-builder-configure-section');
      $section = $form.attr('data-layout-builder-target-highlight-id');
      $selector = '*[data-layout-builder-highlight-id="section-update-' + $section.replace('section-update-', '') + '"]';
    }

    $item = $($selector).find('.' + $info.id + '-edit');

    if (Drupal.isEmpty($item)) {
      $item = $($selector);
    }

    return $item;
  };

  /**
   * Process the text.
   *
   * @type {Drupal~behaviors}
   *
   * @return data
   *   The data of element.
   */
  behaviors.inlineTypeText = function (input, item) {
    let $input = $(input);

    let $info = $.parseJSON($input.attr('lc'));
    let $data = [];
    let $value;
    let $type = $info.type;
    let $append;
    let $item = item;

    // Normal text.
    if ($info.input === "text") {
      $value = $input.val();
    }

    // CKEditor text.
    if ($info.input === "ckeditor") {
      $value = $input.val();
      $append = true;
    }

    $data.push({
      value: $value,
      type: $type,
      append: $append,
      item: item,
    });

    return $data;
  };

  /**
   * Process the element.
   *
   * @type {Drupal~behaviors}
   *
   * @return data
   *   The data of element.
   */
  behaviors.inlineTypeElement = function (input, item) {
    let $input = $(input);
    let $info = $.parseJSON($input.attr('lc'));
    let $data = [];

    $data.push({
      value: $input.val(),
      type: $info.type,
      item: item,
    });

    return $data;
  }

  // Preprocess Class type.
  behaviors.inlineTypeClass = function (input, item) {
    let $input = $(input);
    let $info = $.parseJSON($input.attr('lc'));
    let $value = $input.val();
    let $style = $info.style;
    let $remove = $info.class_remove;
    let $data = [];

    // Ratio style.
    if ($info.style === 'ratio') {
      $data.push({
        value: 'embed-responsive-' + $value,
        type: $info.type,
        style: $style,
        remove: $remove,
        item: item,
      })
    }

    // Align style.
    if ($info.style === 'align') {
      $data.push({
        value: $value,
        type: $info.type,
        style: $style,
        remove: $remove,
        item: item,
      })
    }

    // Checkbox style.
    if ($info.style === 'checkbox') {
      let $active = $info.class_checkbox_active;
      let $disable = $info.class_checkbox_disable;

      if ($input.is(':checked')) {
        $remove = $disable;
        $value = $active;
      } else {
        $remove = $active;
        $value = $disable;
      }

      $data.push({
        value: $value,
        type: $info.type,
        style: $style,
        remove: $remove,
        item: item,
      })
    }

    if ($info.style === 'column_size') {
      let $sizes = $value.split("/");
      let $columns = item.children();

      // Alter each column.
      $.each($columns, function (i) {
        let $column = $(this);
        $value = 'col-md-' + $sizes[i];
        item = $column;

        $data.push({
          value: $value,
          type: $info.type,
          style: $style,
          remove: $info.class_remove,
          item: item,
        })
      })
    }

    if ($info.style === 'extra_class') {
      let $classes = $value.split(",");
      $.each($classes, function (i) {
        $data.push({
          value: $classes[i],
          type: $info.type,
          remove: $input.attr('lc-prev-value'),
          item: item,
        })
      })
    }

    return $data;
  }

  // Preprocess Style type.
  behaviors.inlineTypeStyle = function (input, item) {
    input = $(input);
    let info = $.parseJSON(input.attr('lc'));
    let input_type = info.input;
    let value = input.val();
    let style = info.style;
    let depend = info.depend;
    let data = [];

    let borderType = function (value) {
      var style = 'border';
      if (value !== 'all' && (value !== 'none' || value !== '_none') && style != 'opacity') {
        style += '-' + value;
      }

      return style;
    };

    if (style === 'font-size') {
      if (value !== '') {
        value += 'px';
      }

      data.push({
        value: value,
        type: info.type,
        style: style,
        item: item,
      });
    }

    //Color text.
    if (style === "color") {
      let opacity = $('.' + info.class + '-opacity').val();
      data.push({
        value: Drupal.hexToRgbA(value, opacity),
        type: info.type,
        style: style,
        item: item,
      });
    }

    //Color opacity.
    if (style === "opacity") {
      if (!Drupal.isEmpty(depend)) {
        let background = $('.' + info.depend.background).val();
        let type = $('.' + info.depend.type).val();
        let size = $('.' + info.depend.size).val();
        let color = $('.' + info.depend.color);
        let colorInfo = $.parseJSON(color.attr('lc'));

        // Get the style of color.
        let opacity_style = colorInfo.style;
        let processedcolor = Drupal.hexToRgbA(color.val(), value);

        let values = '';

        if (!Drupal.isEmpty(type) && !Drupal.isEmpty(size)) {
          values = Drupal.builBorder(type, size, processedcolor);
        } else if (!Drupal.isEmpty(background)) {
          background = Drupal.buildBackground(background, color.val(), value, item);
          values = {
            value: background,
            style: 'background',
          }
        } else {
          values = {
            value: processedcolor,
            style: opacity_style,
          }
        }

        values.item = item;
        values.type = info.type;
        data.push(values);
      }
    }

    // Border process.
    if (style === 'border') {
      item.css('border', '');
      if (!Drupal.isEmpty(depend)) {
        let size = $('.' + info.depend.size).val();
        let color = $('input.' + info.depend.color).val();
        let opacity = $('input.' + info.depend.opacity).val();

        color = Drupal.hexToRgbA(color, opacity);

        let values = Drupal.builBorder(value, size, color);

        values.item = item;
        values.type = info.type;
        data.push(values);
      }
    }

    if (style === 'border-size') {
      item.css('border', '');
      if (!Drupal.isEmpty(depend)) {
        let opacity = $('.' + info.depend.opacity).val();
        let type = $('.' + info.depend.type).val();
        let color = $('.' + info.depend.color).val();

        color = Drupal.hexToRgbA(color, opacity);

        let values = Drupal.builBorder(type, value, color);
        values.item = item;
        values.type = info.type;
        data.push(values);
      }
    }

    if (style === 'border-color') {
      item.css('border', '');
      if (!Drupal.isEmpty(depend)) {

        let opacity = $('.' + info.class + '-opacity').val();
        let type = $('.' + info.depend.type).val();
        let size = $('.' + info.depend.size).val();

        value = Drupal.hexToRgbA(value, opacity);

        let values = Drupal.builBorder(type, size, value);

        values.item = item;
        values.type = info.type;
        data.push(values);
      }
    }

    if (style === 'border-radius') {
      data.push({
        value: value + "px",
        type: info.type,
        style: style,
        item: item,
      });
    }

    if (style === 'background') {
      let background_style = info.style;
      let background_remove = '';
      let color = '';
      let opacity = '';

      if (!Drupal.isEmpty(info.depend)) {
        color = $('.' + info.depend.color).val();
        opacity = $('.' + info.depend.opacity).val();
      }

      if (!Drupal.isEmpty(value)) {
        // Get background depend.
        value = Drupal.buildBackground(value, color, opacity, item);
      } else {
        background_style = 'background-color';
        background_remove = 'background';
        value = Drupal.hexToRgbA(color, opacity);
      }

      data.push({
        value: value,
        type: info.type,
        style: background_style,
        item: item,
        remove: background_remove,
      });

    }

    if (style === 'background-color') {
      let opacity = $('.' + info.class + '-opacity').val();
      let background = $('.' + info.depend.background).val();
      // Get background depend.
      if (!Drupal.isEmpty(depend)) {
        if (!Drupal.isEmpty(background)) {
          style = 'background';
          // Set values.
          value = Drupal.buildBackground(background, value, opacity, item);
        } else {
          value = Drupal.hexToRgbA(value, opacity);
        }

      } else {
        value = Drupal.hexToRgbA(value, opacity);
      }

      data.push({
        value: value,
        type: info.type,
        style: style,
        item: item,
      });
    }

    if (style === 'background-opacity') {
      // Get background depend.
      if (!Drupal.isEmpty(depend)) {
        let values = depend.split(" ");
        let background = $('input.' + values[0]).val();
        let color = $('input.' + values[1]).val();

        style = 'background-color';
        if (!Drupal.isEmpty(background)) {
          style = 'background';
          // Set values.
          value = Drupal.buildBackground(background, color, value.replace("px", ''), item);
        } else {
          value = Drupal.hexToRgbA(color, value.replace("px", ''));
        }
      }
      data.push({
        value: value,
        type: info.type,
        style: style,
        item: item,
      });
    }

    if (style === 'margin-top' ||
      style === 'margin-right' ||
      style === 'margin-bottom' ||
      style === 'margin-left'
    ) {
      value = value > 0 ? value + "px" : "0";
      data.push({
        value: value,
        type: info.type,
        style: style,
        item: item,
      });
    }

    if (
      style === 'padding-top' ||
      style === 'padding-right' ||
      style === 'padding-bottom' ||
      style === 'padding-left'
    ) {
      value = value > 0 ? value + "px" : "";
      data.push({
        value: value,
        type: info.type,
        style: style,
        item: item,
      });
    }

    if (style === "width") {
      value = value > 0 ? value + "%" : (Drupal.isEmpty(input.attr('lc-default')) ? "" : input.attr('lc-default'));
      data.push({
        value: value,
        type: info.type,
        style: style,
        item: item,
      });
    }

    if (style === "height_normal") {
      value = value > 0 ? value + "px" : "auto";
      data.push({
        value: value,
        type: info.type,
        style: "height",
        item: item,
      });
    }

    if (style === 'height') {
      if (!Drupal.isEmpty(depend)) {
        let size = $('.' + info.depend.size).val();
        value = Drupal.setHeight(value, size);
        value.type = info.type;
        value.style = style;
        value.item = item;
      }
      data.push(value);
    }

    if (style === 'height-size') {
      if (!Drupal.isEmpty(depend)) {
        let type = $('.' + info.depend.type).val();
        value = Drupal.setHeight(type, value);
        value.type = info.type;
        value.style = 'height';
        value.item = item;
      }
      else {
        let value = {
          value: value + 'px',
          type: info.type,
          style: 'height',
          item: item,
        }
      }
      data.push(value);
    }

    //Color opacity.
    if (style === "border-top-left-radius" ||
      style === "border-top-right-radius" ||
      style === "border-bottom-left-radius" ||
      style === "border-bottom-right-radius") {
      data.push({
        value: value + '%',
        type: info.type,
        style: style,
        item: item,
      });
    }

    return data;
  }

  behaviors.inlineTypeAttribute = function (input, item) {
    input = $(input);
    let input_type = input.attr('lc-input');
    let value = input.val();
    let info = $.parseJSON(input.attr('lc'));
    let style = info.style;
    let data = [];

    if (style === 'extra_attributes') {
      let attributes = value.split(" ");
      $.each(attributes, function (i) {
        let news = attributes[i].split("|");
        if (!Drupal.isEmpty(news)) {
          let removes = input.attr('lc-prev-value').split(" ");
          data.push({
            attribute: news[0],
            value: news[1],
            type: info.type,
            remove: input.attr('lc-prev-value'),
            item: item,
          });
        }
      })
    }

    if (style === "image") {
      if (!item.is('img')) {
        let element = info.id + "-edit";
        item.find(".content").first().append("<img class='" + element + "' style='max-width: 100%'>");
        item = item.find("." + element);
      }

      let media = Drupal.getMedia(value);
      // Set new media.
      data.push({
        attribute: 'src',
        value: media,
        type: info.type,
        item: item,
      });

      // If is empty then remove alt.
      if (Drupal.isEmpty(media)) {
        data.push({
          attribute: 'alt',
          value: '',
          type: info.type,
          item: item,
        });
      }
    }

    if (style === 'link') {
      data.push({
        attribute: 'href',
        value: value,
        type: info.type,
        item: item,
      });
    }

    return data;
  }

  Drupal.setHeight = function (type, size) {
    let nvalue = '';
    let nsize = 0;

    if (type === 'auto') {
      nvalue = 'auto';
    }
    else if (type === 'manual') {
      if (!Drupal.isEmpty(size)) {
        nsize = size;
      }
      nvalue = nsize + 'px';

      if (nvalue === '0px') {
        nvalue = 'auto';
      }
    }
    else {
      nvalue = type;
    }

    return {
      value: nvalue,
    }
  }

  Drupal.builBorder = function (type, size, color) {
    let value = '';

    let ntype = 'border';

    if (!Drupal.isEmpty(type)) {
      if (type === 'none' || type === '_none') {
        ntype = '';
      }

      if (type !== 'all') {
        ntype += '-' + type;
      }
    }

    if (!Drupal.isEmpty(size) && !Drupal.isEmpty(color)) {
      value += size + 'px solid ' + color
    }

    return {
      value: value,
      style: ntype,
    };
  }

  Drupal.getMedia = function (media) {

    if (Drupal.isEmpty(media)) {
      return '';
    }

    // Get media url from Drupal.
    let data = $.ajax({
      url: '/layoutcomponents/media/' + media,
      method: 'GET',
      async: false
    }).responseText;

    if (Drupal.isEmpty(data)) {
      throw new Error('Empty image');
    }

    return $.parseJSON(data).uri;
  }

  Drupal.buildBackground = function (media, color, opacity, item) {
    let res = '';

    // Get media url from Drupal.
    media = Drupal.getMedia(media);

    // Get color with opacity.
    color = Drupal.hexToRgbA(color, opacity);

    if (item.hasClass('parallax-window')) {
      // Add parallax with background color.
      res = Drupal.buildParallax(item, media, color);
      res = color;
    }
    else {
      // If item has not parallax return normal item.
      res = 'linear-gradient(' + color + ', ' + color + '), ' + 'url(' + media + ')' + '';
    }

    return res;
  }

  Drupal.buildParallax = function (item, src, color) {
    if (!Drupal.isEmpty(src)) {
      item.parallax({ imageSrc: src });
    }
  }

  Drupal.hexToRgbA = function (hex, opacity) {

    if (Drupal.isEmpty(opacity)) {
      opacity = 1;
    }

    if (Drupal.isEmpty(hex)) {
      return 'rgba(255,255,255,0)';
    }
    let c;
    if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
      c = hex.substring(1).split('');
      if (c.length === 3) {
        c = [c[0], c[0], c[1], c[1], c[2], c[2]];
      }
      c = '0x' + c.join('');
      return 'rgba(' + [(c >> 16) & 255, (c >> 8) & 255, c & 255].join(',') + ',' + opacity + ')';
    }
    throw new Error('Bad Hex');
  }

  Drupal.rgbToHex = function (rgb) {
    function rgb2hex(rgb) {
      rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
      return (rgb && rgb.length === 4) ? "#" +
        ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2) : '';
    }
  }

  behaviors.parallax = {
    attach: function (context) {
      $('.parallax-window').each(function () {
        let element = $(this);
        $(element).parallax({ imageSrc: element.attr('data-image-src') });
      });
    }
  };




})(jQuery, Drupal, drupalSettings);
;
/*!
 * parallax.js v1.5.0 (http://pixelcog.github.io/parallax.js/)
 * @copyright 2016 PixelCog, Inc.
 * @license MIT (https://github.com/pixelcog/parallax.js/blob/master/LICENSE)
 */
!function(t,i,e,s){function o(i,e){var h=this;"object"==typeof e&&(delete e.refresh,delete e.render,t.extend(this,e)),this.$element=t(i),!this.imageSrc&&this.$element.is("img")&&(this.imageSrc=this.$element.attr("src"));var r=(this.position+"").toLowerCase().match(/\S+/g)||[];if(r.length<1&&r.push("center"),1==r.length&&r.push(r[0]),"top"!=r[0]&&"bottom"!=r[0]&&"left"!=r[1]&&"right"!=r[1]||(r=[r[1],r[0]]),this.positionX!==s&&(r[0]=this.positionX.toLowerCase()),this.positionY!==s&&(r[1]=this.positionY.toLowerCase()),h.positionX=r[0],h.positionY=r[1],"left"!=this.positionX&&"right"!=this.positionX&&(isNaN(parseInt(this.positionX))?this.positionX="center":this.positionX=parseInt(this.positionX)),"top"!=this.positionY&&"bottom"!=this.positionY&&(isNaN(parseInt(this.positionY))?this.positionY="center":this.positionY=parseInt(this.positionY)),this.position=this.positionX+(isNaN(this.positionX)?"":"px")+" "+this.positionY+(isNaN(this.positionY)?"":"px"),navigator.userAgent.match(/(iPod|iPhone|iPad)/))return this.imageSrc&&this.iosFix&&!this.$element.is("img")&&this.$element.css({backgroundImage:"url("+this.imageSrc+")",backgroundSize:"cover",backgroundPosition:this.position}),this;if(navigator.userAgent.match(/(Android)/))return this.imageSrc&&this.androidFix&&!this.$element.is("img")&&this.$element.css({backgroundImage:"url("+this.imageSrc+")",backgroundSize:"cover",backgroundPosition:this.position}),this;this.$mirror=t("<div />").prependTo(this.mirrorContainer);var a=this.$element.find(">.parallax-slider"),n=!1;0==a.length?this.$slider=t("<img />").prependTo(this.$mirror):(this.$slider=a.prependTo(this.$mirror),n=!0),this.$mirror.addClass("parallax-mirror").css({visibility:"hidden",zIndex:this.zIndex,position:"fixed",top:0,left:0,overflow:"hidden"}),this.$slider.addClass("parallax-slider").one("load",function(){h.naturalHeight&&h.naturalWidth||(h.naturalHeight=this.naturalHeight||this.height||1,h.naturalWidth=this.naturalWidth||this.width||1),h.aspectRatio=h.naturalWidth/h.naturalHeight,o.isSetup||o.setup(),o.sliders.push(h),o.isFresh=!1,o.requestRender()}),n||(this.$slider[0].src=this.imageSrc),(this.naturalHeight&&this.naturalWidth||this.$slider[0].complete||a.length>0)&&this.$slider.trigger("load")}!function(){for(var t=0,e=["ms","moz","webkit","o"],s=0;s<e.length&&!i.requestAnimationFrame;++s)i.requestAnimationFrame=i[e[s]+"RequestAnimationFrame"],i.cancelAnimationFrame=i[e[s]+"CancelAnimationFrame"]||i[e[s]+"CancelRequestAnimationFrame"];i.requestAnimationFrame||(i.requestAnimationFrame=function(e){var s=(new Date).getTime(),o=Math.max(0,16-(s-t)),h=i.setTimeout(function(){e(s+o)},o);return t=s+o,h}),i.cancelAnimationFrame||(i.cancelAnimationFrame=function(t){clearTimeout(t)})}(),t.extend(o.prototype,{speed:.2,bleed:0,zIndex:-100,iosFix:!0,androidFix:!0,position:"center",overScrollFix:!1,mirrorContainer:"body",refresh:function(){this.boxWidth=this.$element.outerWidth(),this.boxHeight=this.$element.outerHeight()+2*this.bleed,this.boxOffsetTop=this.$element.offset().top-this.bleed,this.boxOffsetLeft=this.$element.offset().left,this.boxOffsetBottom=this.boxOffsetTop+this.boxHeight;var t,i=o.winHeight,e=o.docHeight,s=Math.min(this.boxOffsetTop,e-i),h=Math.max(this.boxOffsetTop+this.boxHeight-i,0),r=this.boxHeight+(s-h)*(1-this.speed)|0,a=(this.boxOffsetTop-s)*(1-this.speed)|0;r*this.aspectRatio>=this.boxWidth?(this.imageWidth=r*this.aspectRatio|0,this.imageHeight=r,this.offsetBaseTop=a,t=this.imageWidth-this.boxWidth,"left"==this.positionX?this.offsetLeft=0:"right"==this.positionX?this.offsetLeft=-t:isNaN(this.positionX)?this.offsetLeft=-t/2|0:this.offsetLeft=Math.max(this.positionX,-t)):(this.imageWidth=this.boxWidth,this.imageHeight=this.boxWidth/this.aspectRatio|0,this.offsetLeft=0,t=this.imageHeight-r,"top"==this.positionY?this.offsetBaseTop=a:"bottom"==this.positionY?this.offsetBaseTop=a-t:isNaN(this.positionY)?this.offsetBaseTop=a-t/2|0:this.offsetBaseTop=a+Math.max(this.positionY,-t))},render:function(){var t=o.scrollTop,i=o.scrollLeft,e=this.overScrollFix?o.overScroll:0,s=t+o.winHeight;this.boxOffsetBottom>t&&this.boxOffsetTop<=s?(this.visibility="visible",this.mirrorTop=this.boxOffsetTop-t,this.mirrorLeft=this.boxOffsetLeft-i,this.offsetTop=this.offsetBaseTop-this.mirrorTop*(1-this.speed)):this.visibility="hidden",this.$mirror.css({transform:"translate3d("+this.mirrorLeft+"px, "+(this.mirrorTop-e)+"px, 0px)",visibility:this.visibility,height:this.boxHeight,width:this.boxWidth}),this.$slider.css({transform:"translate3d("+this.offsetLeft+"px, "+this.offsetTop+"px, 0px)",position:"absolute",height:this.imageHeight,width:this.imageWidth,maxWidth:"none"})}}),t.extend(o,{scrollTop:0,scrollLeft:0,winHeight:0,winWidth:0,docHeight:1<<30,docWidth:1<<30,sliders:[],isReady:!1,isFresh:!1,isBusy:!1,setup:function(){function s(){if(p==i.pageYOffset)return i.requestAnimationFrame(s),!1;p=i.pageYOffset,h.render(),i.requestAnimationFrame(s)}if(!this.isReady){var h=this,r=t(e),a=t(i),n=function(){o.winHeight=a.height(),o.winWidth=a.width(),o.docHeight=r.height(),o.docWidth=r.width()},l=function(){var t=a.scrollTop(),i=o.docHeight-o.winHeight,e=o.docWidth-o.winWidth;o.scrollTop=Math.max(0,Math.min(i,t)),o.scrollLeft=Math.max(0,Math.min(e,a.scrollLeft())),o.overScroll=Math.max(t-i,Math.min(t,0))};a.on("resize.px.parallax load.px.parallax",function(){n(),h.refresh(),o.isFresh=!1,o.requestRender()}).on("scroll.px.parallax load.px.parallax",function(){l(),o.requestRender()}),n(),l(),this.isReady=!0;var p=-1;s()}},configure:function(i){"object"==typeof i&&(delete i.refresh,delete i.render,t.extend(this.prototype,i))},refresh:function(){t.each(this.sliders,function(){this.refresh()}),this.isFresh=!0},render:function(){this.isFresh||this.refresh(),t.each(this.sliders,function(){this.render()})},requestRender:function(){var t=this;t.render(),t.isBusy=!1},destroy:function(e){var s,h=t(e).data("px.parallax");for(h.$mirror.remove(),s=0;s<this.sliders.length;s+=1)this.sliders[s]==h&&this.sliders.splice(s,1);t(e).data("px.parallax",!1),0===this.sliders.length&&(t(i).off("scroll.px.parallax resize.px.parallax load.px.parallax"),this.isReady=!1,o.isSetup=!1)}});var h=t.fn.parallax;t.fn.parallax=function(s){return this.each(function(){var h=t(this),r="object"==typeof s&&s;this==i||this==e||h.is("body")?o.configure(r):h.data("px.parallax")?"object"==typeof s&&t.extend(h.data("px.parallax"),r):(r=t.extend({},h.data(),r),h.data("px.parallax",new o(this,r))),"string"==typeof s&&("destroy"==s?o.destroy(this):o[s]())})},t.fn.parallax.Constructor=o,t.fn.parallax.noConflict=function(){return t.fn.parallax=h,this},t(function(){t('[data-parallax="scroll"]').parallax()})}(jQuery,window,document);;
/**
 * @file
 * Layout Components behaviors.
 */

(function ($, Drupal) {

  'use strict';

  var ajax = Drupal.ajax,
      behaviors = Drupal.behaviors;

  behaviors.lateralParallax = {
    attach: function (context) {
      function control() {
        $(window).trigger('resize.px.parallax');
      }
      setInterval(control, 500);
    }
  }

  behaviors.lateralAccordion = {
    attach: function (context) {
      let $selector = Array(
        'details[data-drupal-selector="edit-layout-settings-container-title-container-styles"]',
        'details[data-drupal-selector="edit-layout-settings-container-section-container-styles"]',
        'details[data-drupal-selector="edit-layout-settings-container-section-container-general"]',
        'details[data-drupal-selector^="edit-layout-settings-container-regions-"]',
        'details[data-drupal-selector^="edit-group-styles-"]',
      );
      let $container = $($selector);
      $container.each(function () {
        let $items = $(this).find('details.js-form-wrapper.form-wrapper');
        $items.on('click', function () {
          closeAll($items, this);
        })
      });

      function closeAll(elements, element) {
        $.each(elements, function () {
          if (this !== element) {
            $(this).removeAttr('open');
          }
        })
      }
    }
  }

})(jQuery, Drupal);
;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Drupal, drupalSettings) {
  Drupal.behaviors.activeLinks = {
    attach: function attach(context) {
      var path = drupalSettings.path;
      var queryString = JSON.stringify(path.currentQuery);
      var querySelector = path.currentQuery ? "[data-drupal-link-query='".concat(queryString, "']") : ':not([data-drupal-link-query])';
      var originalSelectors = ["[data-drupal-link-system-path=\"".concat(path.currentPath, "\"]")];
      var selectors;

      if (path.isFront) {
        originalSelectors.push('[data-drupal-link-system-path="<front>"]');
      }

      selectors = [].concat(originalSelectors.map(function (selector) {
        return "".concat(selector, ":not([hreflang])");
      }), originalSelectors.map(function (selector) {
        return "".concat(selector, "[hreflang=\"").concat(path.currentLanguage, "\"]");
      }));
      selectors = selectors.map(function (current) {
        return current + querySelector;
      });
      var activeLinks = context.querySelectorAll(selectors.join(','));
      var il = activeLinks.length;

      for (var i = 0; i < il; i++) {
        activeLinks[i].classList.add('is-active');
      }
    },
    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        var activeLinks = context.querySelectorAll('[data-drupal-link-system-path].is-active');
        var il = activeLinks.length;

        for (var i = 0; i < il; i++) {
          activeLinks[i].classList.remove('is-active');
        }
      }
    }
  };
})(Drupal, drupalSettings);;
