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
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, window) {
  function TableResponsive(table) {
    this.table = table;
    this.$table = $(table);
    this.showText = Drupal.t('Show all columns');
    this.hideText = Drupal.t('Hide lower priority columns');
    this.$headers = this.$table.find('th');
    this.$link = $('<button type="button" class="link tableresponsive-toggle"></button>').attr('title', Drupal.t('Show table cells that were hidden to make the table fit within a small screen.')).on('click', $.proxy(this, 'eventhandlerToggleColumns'));
    this.$table.before($('<div class="tableresponsive-toggle-columns"></div>').append(this.$link));
    $(window).on('resize.tableresponsive', $.proxy(this, 'eventhandlerEvaluateColumnVisibility')).trigger('resize.tableresponsive');
  }

  Drupal.behaviors.tableResponsive = {
    attach: function attach(context, settings) {
      once('tableresponsive', 'table.responsive-enabled', context).forEach(function (table) {
        TableResponsive.tables.push(new TableResponsive(table));
      });
    }
  };
  $.extend(TableResponsive, {
    tables: []
  });
  $.extend(TableResponsive.prototype, {
    eventhandlerEvaluateColumnVisibility: function eventhandlerEvaluateColumnVisibility(e) {
      var pegged = parseInt(this.$link.data('pegged'), 10);
      var hiddenLength = this.$headers.filter('.priority-medium:hidden, .priority-low:hidden').length;

      if (hiddenLength > 0) {
        this.$link.show().text(this.showText);
      }

      if (!pegged && hiddenLength === 0) {
        this.$link.hide().text(this.hideText);
      }
    },
    eventhandlerToggleColumns: function eventhandlerToggleColumns(e) {
      e.preventDefault();
      var self = this;
      var $hiddenHeaders = this.$headers.filter('.priority-medium:hidden, .priority-low:hidden');
      this.$revealedCells = this.$revealedCells || $();

      if ($hiddenHeaders.length > 0) {
        $hiddenHeaders.each(function (index, element) {
          var $header = $(this);
          var position = $header.prevAll('th').length;
          self.$table.find('tbody tr').each(function () {
            var $cells = $(this).find('td').eq(position);
            $cells.show();
            self.$revealedCells = $().add(self.$revealedCells).add($cells);
          });
          $header.show();
          self.$revealedCells = $().add(self.$revealedCells).add($header);
        });
        this.$link.text(this.hideText).data('pegged', 1);
      } else {
        this.$revealedCells.hide();
        this.$revealedCells.each(function (index, element) {
          var $cell = $(this);
          var properties = $cell.attr('style').split(';');
          var newProps = [];
          var match = /^display\s*:\s*none$/;

          for (var i = 0; i < properties.length; i++) {
            var prop = properties[i];
            prop.trim();
            var isDisplayNone = match.exec(prop);

            if (isDisplayNone) {
              continue;
            }

            newProps.push(prop);
          }

          $cell.attr('style', newProps.join(';'));
        });
        this.$link.text(this.showText).data('pegged', 0);
        $(window).trigger('resize.tableresponsive');
      }
    }
  });
  Drupal.TableResponsive = TableResponsive;
})(jQuery, Drupal, window);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

Drupal.debounce = function (func, wait, immediate) {
  var timeout;
  var result;
  return function () {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    var context = this;

    var later = function later() {
      timeout = null;

      if (!immediate) {
        result = func.apply(context, args);
      }
    };

    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);

    if (callNow) {
      result = func.apply(context, args);
    }

    return result;
  };
};;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Drupal, debounce) {
  var liveElement;
  var announcements = [];
  Drupal.behaviors.drupalAnnounce = {
    attach: function attach(context) {
      if (!liveElement) {
        liveElement = document.createElement('div');
        liveElement.id = 'drupal-live-announce';
        liveElement.className = 'visually-hidden';
        liveElement.setAttribute('aria-live', 'polite');
        liveElement.setAttribute('aria-busy', 'false');
        document.body.appendChild(liveElement);
      }
    }
  };

  function announce() {
    var text = [];
    var priority = 'polite';
    var announcement;
    var il = announcements.length;

    for (var i = 0; i < il; i++) {
      announcement = announcements.pop();
      text.unshift(announcement.text);

      if (announcement.priority === 'assertive') {
        priority = 'assertive';
      }
    }

    if (text.length) {
      liveElement.innerHTML = '';
      liveElement.setAttribute('aria-busy', 'true');
      liveElement.setAttribute('aria-live', priority);
      liveElement.innerHTML = text.join('\n');
      liveElement.setAttribute('aria-busy', 'false');
    }
  }

  Drupal.announce = function (text, priority) {
    announcements.push({
      text: text,
      priority: priority
    });
    return debounce(announce, 200)();
  };
})(Drupal, Drupal.debounce);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

(function ($, Drupal, debounce) {
  Drupal.behaviors.tableFilterByText = {
    attach: function attach(context, settings) {
      var _once = once('table-filter-text', 'input.table-filter-text'),
          _once2 = _slicedToArray(_once, 1),
          input = _once2[0];

      if (!input) {
        return;
      }

      var $table = $(input.getAttribute('data-table'));
      var $rowsAndDetails;
      var $rows;
      var $details;
      var searching = false;

      function hidePackageDetails(index, element) {
        var $packDetails = $(element);
        var $visibleRows = $packDetails.find('tbody tr:visible');
        $packDetails.toggle($visibleRows.length > 0);
      }

      function filterModuleList(e) {
        var query = $(e.target).val();
        var re = new RegExp("\\b".concat(query), 'i');

        function showModuleRow(index, row) {
          var $row = $(row);
          var $sources = $row.find('.table-filter-text-source, .module-name, .module-description');
          var textMatch = $sources.text().search(re) !== -1;
          $row.closest('tr').toggle(textMatch);
        }

        $rowsAndDetails.show();

        if (query.length >= 2) {
          searching = true;
          $rows.each(showModuleRow);
          $details.not('[open]').attr('data-drupal-system-state', 'forced-open');
          $details.attr('open', true).each(hidePackageDetails);
          Drupal.announce(Drupal.t('!modules modules are available in the modified list.', {
            '!modules': $rowsAndDetails.find('tbody tr:visible').length
          }));
        } else if (searching) {
          searching = false;
          $rowsAndDetails.show();
          $details.filter('[data-drupal-system-state="forced-open"]').removeAttr('data-drupal-system-state').attr('open', false);
        }
      }

      function preventEnterKey(event) {
        if (event.which === 13) {
          event.preventDefault();
          event.stopPropagation();
        }
      }

      if ($table.length) {
        $rowsAndDetails = $table.find('tr, details');
        $rows = $table.find('tbody tr');
        $details = $rowsAndDetails.filter('.package-listing');
        $(input).on({
          keyup: debounce(filterModuleList, 200),
          keydown: preventEnterKey
        });
      }
    }
  };
})(jQuery, Drupal, Drupal.debounce);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, debounce) {
  $.fn.drupalGetSummary = function () {
    var callback = this.data('summaryCallback');
    return this[0] && callback ? callback(this[0]).trim() : '';
  };

  $.fn.drupalSetSummary = function (callback) {
    var self = this;

    if (typeof callback !== 'function') {
      var val = callback;

      callback = function callback() {
        return val;
      };
    }

    return this.data('summaryCallback', callback).off('formUpdated.summary').on('formUpdated.summary', function () {
      self.trigger('summaryUpdated');
    }).trigger('summaryUpdated');
  };

  Drupal.behaviors.formSingleSubmit = {
    attach: function attach() {
      function onFormSubmit(e) {
        var $form = $(e.currentTarget);
        var formValues = $form.serialize();
        var previousValues = $form.attr('data-drupal-form-submit-last');

        if (previousValues === formValues) {
          e.preventDefault();
        } else {
          $form.attr('data-drupal-form-submit-last', formValues);
        }
      }

      $(once('form-single-submit', 'body')).on('submit.singleSubmit', 'form:not([method~="GET"])', onFormSubmit);
    }
  };

  function triggerFormUpdated(element) {
    $(element).trigger('formUpdated');
  }

  function fieldsList(form) {
    var $fieldList = $(form).find('[name]').map(function (index, element) {
      return element.getAttribute('id');
    });
    return $.makeArray($fieldList);
  }

  Drupal.behaviors.formUpdated = {
    attach: function attach(context) {
      var $context = $(context);
      var contextIsForm = $context.is('form');
      var $forms = $(once('form-updated', contextIsForm ? $context : $context.find('form')));
      var formFields;

      if ($forms.length) {
        $.makeArray($forms).forEach(function (form) {
          var events = 'change.formUpdated input.formUpdated ';
          var eventHandler = debounce(function (event) {
            triggerFormUpdated(event.target);
          }, 300);
          formFields = fieldsList(form).join(',');
          form.setAttribute('data-drupal-form-fields', formFields);
          $(form).on(events, eventHandler);
        });
      }

      if (contextIsForm) {
        formFields = fieldsList(context).join(',');
        var currentFields = $(context).attr('data-drupal-form-fields');

        if (formFields !== currentFields) {
          triggerFormUpdated(context);
        }
      }
    },
    detach: function detach(context, settings, trigger) {
      var $context = $(context);
      var contextIsForm = $context.is('form');

      if (trigger === 'unload') {
        once.remove('form-updated', contextIsForm ? $context : $context.find('form')).forEach(function (form) {
          form.removeAttribute('data-drupal-form-fields');
          $(form).off('.formUpdated');
        });
      }
    }
  };
  Drupal.behaviors.fillUserInfoFromBrowser = {
    attach: function attach(context, settings) {
      var userInfo = ['name', 'mail', 'homepage'];
      var $forms = $(once('user-info-from-browser', '[data-user-info-from-browser]'));

      if ($forms.length) {
        userInfo.forEach(function (info) {
          var $element = $forms.find("[name=".concat(info, "]"));
          var browserData = localStorage.getItem("Drupal.visitor.".concat(info));
          var emptyOrDefault = $element.val() === '' || $element.attr('data-drupal-default-value') === $element.val();

          if ($element.length && emptyOrDefault && browserData) {
            $element.val(browserData);
          }
        });
      }

      $forms.on('submit', function () {
        userInfo.forEach(function (info) {
          var $element = $forms.find("[name=".concat(info, "]"));

          if ($element.length) {
            localStorage.setItem("Drupal.visitor.".concat(info), $element.val());
          }
        });
      });
    }
  };

  var handleFragmentLinkClickOrHashChange = function handleFragmentLinkClickOrHashChange(e) {
    var url;

    if (e.type === 'click') {
      url = e.currentTarget.location ? e.currentTarget.location : e.currentTarget;
    } else {
      url = window.location;
    }

    var hash = url.hash.substr(1);

    if (hash) {
      var $target = $("#".concat(hash));
      $('body').trigger('formFragmentLinkClickOrHashChange', [$target]);
      setTimeout(function () {
        return $target.trigger('focus');
      }, 300);
    }
  };

  var debouncedHandleFragmentLinkClickOrHashChange = debounce(handleFragmentLinkClickOrHashChange, 300, true);
  $(window).on('hashchange.form-fragment', debouncedHandleFragmentLinkClickOrHashChange);
  $(document).on('click.form-fragment', 'a[href*="#"]', debouncedHandleFragmentLinkClickOrHashChange);
})(jQuery, Drupal, Drupal.debounce);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal) {
  function DetailsSummarizedContent(node) {
    this.$node = $(node);
    this.setupSummary();
  }

  $.extend(DetailsSummarizedContent, {
    instances: []
  });
  $.extend(DetailsSummarizedContent.prototype, {
    setupSummary: function setupSummary() {
      this.$detailsSummarizedContentWrapper = $(Drupal.theme('detailsSummarizedContentWrapper'));
      this.$node.on('summaryUpdated', $.proxy(this.onSummaryUpdated, this)).trigger('summaryUpdated').find('> summary').append(this.$detailsSummarizedContentWrapper);
    },
    onSummaryUpdated: function onSummaryUpdated() {
      var text = this.$node.drupalGetSummary();
      this.$detailsSummarizedContentWrapper.html(Drupal.theme('detailsSummarizedContentText', text));
    }
  });
  Drupal.behaviors.detailsSummary = {
    attach: function attach(context) {
      DetailsSummarizedContent.instances = DetailsSummarizedContent.instances.concat(once('details', 'details', context).map(function (details) {
        return new DetailsSummarizedContent(details);
      }));
    }
  };
  Drupal.DetailsSummarizedContent = DetailsSummarizedContent;

  Drupal.theme.detailsSummarizedContentWrapper = function () {
    return "<span class=\"summary\"></span>";
  };

  Drupal.theme.detailsSummarizedContentText = function (text) {
    return text ? " (".concat(text, ")") : '';
  };
})(jQuery, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal) {
  Drupal.behaviors.detailsAria = {
    attach: function attach() {
      $(once('detailsAria', 'body')).on('click.detailsAria', 'summary', function (event) {
        var $summary = $(event.currentTarget);
        var open = $(event.currentTarget.parentNode).attr('open') === 'open' ? 'false' : 'true';
        $summary.attr({
          'aria-expanded': open,
          'aria-pressed': open
        });
      });
    }
  };
})(jQuery, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Modernizr, Drupal) {
  function CollapsibleDetails(node) {
    this.$node = $(node);
    this.$node.data('details', this);
    var anchor = window.location.hash && window.location.hash !== '#' ? ", ".concat(window.location.hash) : '';

    if (this.$node.find(".error".concat(anchor)).length) {
      this.$node.attr('open', true);
    }

    this.setupSummaryPolyfill();
  }

  $.extend(CollapsibleDetails, {
    instances: []
  });
  $.extend(CollapsibleDetails.prototype, {
    setupSummaryPolyfill: function setupSummaryPolyfill() {
      var $summary = this.$node.find('> summary');
      $summary.attr('tabindex', '-1');
      $('<span class="details-summary-prefix visually-hidden"></span>').append(this.$node.attr('open') ? Drupal.t('Hide') : Drupal.t('Show')).prependTo($summary).after(document.createTextNode(' '));
      $('<a class="details-title"></a>').attr('href', "#".concat(this.$node.attr('id'))).prepend($summary.contents()).appendTo($summary);
      $summary.append(this.$summary).on('click', $.proxy(this.onSummaryClick, this));
    },
    onSummaryClick: function onSummaryClick(e) {
      this.toggle();
      e.preventDefault();
    },
    toggle: function toggle() {
      var _this = this;

      var isOpen = !!this.$node.attr('open');
      var $summaryPrefix = this.$node.find('> summary span.details-summary-prefix');

      if (isOpen) {
        $summaryPrefix.html(Drupal.t('Show'));
      } else {
        $summaryPrefix.html(Drupal.t('Hide'));
      }

      setTimeout(function () {
        _this.$node.attr('open', !isOpen);
      }, 0);
    }
  });
  Drupal.behaviors.collapse = {
    attach: function attach(context) {
      if (Modernizr.details) {
        return;
      }

      once('collapse', 'details', context).forEach(function (detail) {
        detail.classList.add('collapse-processed');
        CollapsibleDetails.instances.push(new CollapsibleDetails(detail));
      });
    }
  };

  var handleFragmentLinkClickOrHashChange = function handleFragmentLinkClickOrHashChange(e, $target) {
    $target.parents('details').not('[open]').find('> summary').trigger('click');
  };

  $('body').on('formFragmentLinkClickOrHashChange.details', handleFragmentLinkClickOrHashChange);
  Drupal.CollapsibleDetails = CollapsibleDetails;
})(jQuery, Modernizr, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, debounce) {
  var offsets = {
    top: 0,
    right: 0,
    bottom: 0,
    left: 0
  };

  function getRawOffset(el, edge) {
    var $el = $(el);
    var documentElement = document.documentElement;
    var displacement = 0;
    var horizontal = edge === 'left' || edge === 'right';
    var placement = $el.offset()[horizontal ? 'left' : 'top'];
    placement -= window["scroll".concat(horizontal ? 'X' : 'Y')] || document.documentElement["scroll".concat(horizontal ? 'Left' : 'Top')] || 0;

    switch (edge) {
      case 'top':
        displacement = placement + $el.outerHeight();
        break;

      case 'left':
        displacement = placement + $el.outerWidth();
        break;

      case 'bottom':
        displacement = documentElement.clientHeight - placement;
        break;

      case 'right':
        displacement = documentElement.clientWidth - placement;
        break;

      default:
        displacement = 0;
    }

    return displacement;
  }

  function calculateOffset(edge) {
    var edgeOffset = 0;
    var displacingElements = document.querySelectorAll("[data-offset-".concat(edge, "]"));
    var n = displacingElements.length;

    for (var i = 0; i < n; i++) {
      var el = displacingElements[i];

      if (el.style.display === 'none') {
        continue;
      }

      var displacement = parseInt(el.getAttribute("data-offset-".concat(edge)), 10);

      if (isNaN(displacement)) {
        displacement = getRawOffset(el, edge);
      }

      edgeOffset = Math.max(edgeOffset, displacement);
    }

    return edgeOffset;
  }

  function calculateOffsets() {
    return {
      top: calculateOffset('top'),
      right: calculateOffset('right'),
      bottom: calculateOffset('bottom'),
      left: calculateOffset('left')
    };
  }

  function displace(broadcast) {
    offsets = calculateOffsets();
    Drupal.displace.offsets = offsets;

    if (typeof broadcast === 'undefined' || broadcast) {
      $(document).trigger('drupalViewportOffsetChange', offsets);
    }

    return offsets;
  }

  Drupal.behaviors.drupalDisplace = {
    attach: function attach() {
      if (this.displaceProcessed) {
        return;
      }

      this.displaceProcessed = true;
      $(window).on('resize.drupalDisplace', debounce(displace, 200));
    }
  };
  Drupal.displace = displace;
  $.extend(Drupal.displace, {
    offsets: offsets,
    calculateOffset: calculateOffset
  });
})(jQuery, Drupal, Drupal.debounce);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings) {
  var activeItem = Drupal.url(drupalSettings.path.currentPath);

  $.fn.drupalToolbarMenu = function () {
    var ui = {
      handleOpen: Drupal.t('Extend'),
      handleClose: Drupal.t('Collapse')
    };

    function toggleList($item, switcher) {
      var $toggle = $item.children('.toolbar-box').children('.toolbar-handle');
      switcher = typeof switcher !== 'undefined' ? switcher : !$item.hasClass('open');
      $item.toggleClass('open', switcher);
      $toggle.toggleClass('open', switcher);
      $toggle.find('.action').text(switcher ? ui.handleClose : ui.handleOpen);
    }

    function toggleClickHandler(event) {
      var $toggle = $(event.target);
      var $item = $toggle.closest('li');
      toggleList($item);
      var $openItems = $item.siblings().filter('.open');
      toggleList($openItems, false);
    }

    function linkClickHandler(event) {
      if (!Drupal.toolbar.models.toolbarModel.get('isFixed')) {
        Drupal.toolbar.models.toolbarModel.set('activeTab', null);
      }

      event.stopPropagation();
    }

    function initItems($menu) {
      var options = {
        class: 'toolbar-icon toolbar-handle',
        action: ui.handleOpen,
        text: ''
      };
      $menu.find('li > a').wrap('<div class="toolbar-box">');
      $menu.find('li').each(function (index, element) {
        var $item = $(element);

        if ($item.children('ul.toolbar-menu').length) {
          var $box = $item.children('.toolbar-box');
          options.text = Drupal.t('@label', {
            '@label': $box.find('a').text()
          });
          $item.children('.toolbar-box').append(Drupal.theme('toolbarMenuItemToggle', options));
        }
      });
    }

    function markListLevels($lists, level) {
      level = !level ? 1 : level;
      var $lis = $lists.children('li').addClass("level-".concat(level));
      $lists = $lis.children('ul');

      if ($lists.length) {
        markListLevels($lists, level + 1);
      }
    }

    function openActiveItem($menu) {
      var pathItem = $menu.find("a[href=\"".concat(window.location.pathname, "\"]"));

      if (pathItem.length && !activeItem) {
        activeItem = window.location.pathname;
      }

      if (activeItem) {
        var $activeItem = $menu.find("a[href=\"".concat(activeItem, "\"]")).addClass('menu-item--active');
        var $activeTrail = $activeItem.parentsUntil('.root', 'li').addClass('menu-item--active-trail');
        toggleList($activeTrail, true);
      }
    }

    return this.each(function (selector) {
      var menu = once('toolbar-menu', this);

      if (menu.length) {
        var $menu = $(menu);
        $menu.on('click.toolbar', '.toolbar-box', toggleClickHandler).on('click.toolbar', '.toolbar-box a', linkClickHandler);
        $menu.addClass('root');
        initItems($menu);
        markListLevels($menu);
        openActiveItem($menu);
      }
    });
  };

  Drupal.theme.toolbarMenuItemToggle = function (options) {
    return "<button class=\"".concat(options.class, "\"><span class=\"action\">").concat(options.action, "</span> <span class=\"label\">").concat(options.text, "</span></button>");
  };
})(jQuery, Drupal, drupalSettings);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings) {
  var options = $.extend({
    breakpoints: {
      'toolbar.narrow': '',
      'toolbar.standard': '',
      'toolbar.wide': ''
    }
  }, drupalSettings.toolbar, {
    strings: {
      horizontal: Drupal.t('Horizontal orientation'),
      vertical: Drupal.t('Vertical orientation')
    }
  });
  Drupal.behaviors.toolbar = {
    attach: function attach(context) {
      if (!window.matchMedia('only screen').matches) {
        return;
      }

      once('toolbar', '#toolbar-administration', context).forEach(function (toolbar) {
        var model = new Drupal.toolbar.ToolbarModel({
          locked: JSON.parse(localStorage.getItem('Drupal.toolbar.trayVerticalLocked')),
          activeTab: document.getElementById(JSON.parse(localStorage.getItem('Drupal.toolbar.activeTabID'))),
          height: $('#toolbar-administration').outerHeight()
        });
        Drupal.toolbar.models.toolbarModel = model;
        Object.keys(options.breakpoints).forEach(function (label) {
          var mq = options.breakpoints[label];
          var mql = window.matchMedia(mq);
          Drupal.toolbar.mql[label] = mql;
          mql.addListener(Drupal.toolbar.mediaQueryChangeHandler.bind(null, model, label));
          Drupal.toolbar.mediaQueryChangeHandler.call(null, model, label, mql);
        });
        Drupal.toolbar.views.toolbarVisualView = new Drupal.toolbar.ToolbarVisualView({
          el: toolbar,
          model: model,
          strings: options.strings
        });
        Drupal.toolbar.views.toolbarAuralView = new Drupal.toolbar.ToolbarAuralView({
          el: toolbar,
          model: model,
          strings: options.strings
        });
        Drupal.toolbar.views.bodyVisualView = new Drupal.toolbar.BodyVisualView({
          el: toolbar,
          model: model
        });
        model.trigger('change:isFixed', model, model.get('isFixed'));
        model.trigger('change:activeTray', model, model.get('activeTray'));
        var menuModel = new Drupal.toolbar.MenuModel();
        Drupal.toolbar.models.menuModel = menuModel;
        Drupal.toolbar.views.menuVisualView = new Drupal.toolbar.MenuVisualView({
          el: $(toolbar).find('.toolbar-menu-administration').get(0),
          model: menuModel,
          strings: options.strings
        });
        Drupal.toolbar.setSubtrees.done(function (subtrees) {
          menuModel.set('subtrees', subtrees);
          var theme = drupalSettings.ajaxPageState.theme;
          localStorage.setItem("Drupal.toolbar.subtrees.".concat(theme), JSON.stringify(subtrees));
          model.set('areSubtreesLoaded', true);
        });
        Drupal.toolbar.views.toolbarVisualView.loadSubtrees();
        $(document).on('drupalViewportOffsetChange.toolbar', function (event, offsets) {
          model.set('offsets', offsets);
        });
        model.on('change:orientation', function (model, orientation) {
          $(document).trigger('drupalToolbarOrientationChange', orientation);
        }).on('change:activeTab', function (model, tab) {
          $(document).trigger('drupalToolbarTabChange', tab);
        }).on('change:activeTray', function (model, tray) {
          $(document).trigger('drupalToolbarTrayChange', tray);
        });

        if (Drupal.toolbar.models.toolbarModel.get('orientation') === 'horizontal' && Drupal.toolbar.models.toolbarModel.get('activeTab') === null) {
          Drupal.toolbar.models.toolbarModel.set({
            activeTab: $('.toolbar-bar .toolbar-tab:not(.home-toolbar-tab) a').get(0)
          });
        }

        $(window).on({
          'dialog:aftercreate': function dialogAftercreate(event, dialog, $element, settings) {
            var $toolbar = $('#toolbar-bar');
            $toolbar.css('margin-top', '0');

            if (settings.drupalOffCanvasPosition === 'top') {
              var height = Drupal.offCanvas.getContainer($element).outerHeight();
              $toolbar.css('margin-top', "".concat(height, "px"));
              $element.on('dialogContentResize.off-canvas', function () {
                var newHeight = Drupal.offCanvas.getContainer($element).outerHeight();
                $toolbar.css('margin-top', "".concat(newHeight, "px"));
              });
            }
          },
          'dialog:beforeclose': function dialogBeforeclose() {
            $('#toolbar-bar').css('margin-top', '0');
          }
        });
      });
    }
  };
  Drupal.toolbar = {
    views: {},
    models: {},
    mql: {},
    setSubtrees: new $.Deferred(),
    mediaQueryChangeHandler: function mediaQueryChangeHandler(model, label, mql) {
      switch (label) {
        case 'toolbar.narrow':
          model.set({
            isOriented: mql.matches,
            isTrayToggleVisible: false
          });

          if (!mql.matches || !model.get('orientation')) {
            model.set({
              orientation: 'vertical'
            }, {
              validate: true
            });
          }

          break;

        case 'toolbar.standard':
          model.set({
            isFixed: mql.matches
          });
          break;

        case 'toolbar.wide':
          model.set({
            orientation: mql.matches && !model.get('locked') ? 'horizontal' : 'vertical'
          }, {
            validate: true
          });
          model.set({
            isTrayToggleVisible: mql.matches
          });
          break;

        default:
          break;
      }
    }
  };

  Drupal.theme.toolbarOrientationToggle = function () {
    return '<div class="toolbar-toggle-orientation"><div class="toolbar-lining">' + '<button class="toolbar-icon" type="button"></button>' + '</div></div>';
  };

  Drupal.AjaxCommands.prototype.setToolbarSubtrees = function (ajax, response, status) {
    Drupal.toolbar.setSubtrees.resolve(response.subtrees);
  };
})(jQuery, Drupal, drupalSettings);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Backbone, Drupal) {
  Drupal.toolbar.MenuModel = Backbone.Model.extend({
    defaults: {
      subtrees: {}
    }
  });
})(Backbone, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Backbone, Drupal) {
  Drupal.toolbar.ToolbarModel = Backbone.Model.extend({
    defaults: {
      activeTab: null,
      activeTray: null,
      isOriented: false,
      isFixed: false,
      areSubtreesLoaded: false,
      isViewportOverflowConstrained: false,
      orientation: 'horizontal',
      locked: false,
      isTrayToggleVisible: true,
      height: null,
      offsets: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      }
    },
    validate: function validate(attributes, options) {
      if (attributes.orientation === 'horizontal' && this.get('locked') && !options.override) {
        return Drupal.t('The toolbar cannot be set to a horizontal orientation when it is locked.');
      }
    }
  });
})(Backbone, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, Backbone) {
  Drupal.toolbar.BodyVisualView = Backbone.View.extend({
    initialize: function initialize() {
      this.listenTo(this.model, 'change:activeTray ', this.render);
      this.listenTo(this.model, 'change:isFixed change:isViewportOverflowConstrained', this.isToolbarFixed);
    },
    isToolbarFixed: function isToolbarFixed() {
      var isViewportOverflowConstrained = this.model.get('isViewportOverflowConstrained');
      $('body').toggleClass('toolbar-fixed', isViewportOverflowConstrained || this.model.get('isFixed'));
    },
    render: function render() {
      $('body').toggleClass('toolbar-tray-open', !!this.model.get('activeTray'));
    }
  });
})(jQuery, Drupal, Backbone);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Backbone, Drupal) {
  Drupal.toolbar.MenuVisualView = Backbone.View.extend({
    initialize: function initialize() {
      this.listenTo(this.model, 'change:subtrees', this.render);
    },
    render: function render() {
      var _this = this;

      var subtrees = this.model.get('subtrees');
      Object.keys(subtrees || {}).forEach(function (id) {
        $(once('toolbar-subtrees', _this.$el.find("#toolbar-link-".concat(id)))).after(subtrees[id]);
      });

      if ('drupalToolbarMenu' in $.fn) {
        this.$el.children('.toolbar-menu').drupalToolbarMenu();
      }
    }
  });
})(jQuery, Backbone, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Backbone, Drupal) {
  Drupal.toolbar.ToolbarAuralView = Backbone.View.extend({
    initialize: function initialize(options) {
      this.strings = options.strings;
      this.listenTo(this.model, 'change:orientation', this.onOrientationChange);
      this.listenTo(this.model, 'change:activeTray', this.onActiveTrayChange);
    },
    onOrientationChange: function onOrientationChange(model, orientation) {
      Drupal.announce(Drupal.t('Tray orientation changed to @orientation.', {
        '@orientation': orientation
      }));
    },
    onActiveTrayChange: function onActiveTrayChange(model, tray) {
      var relevantTray = tray === null ? model.previous('activeTray') : tray;

      if (!relevantTray) {
        return;
      }

      var action = tray === null ? Drupal.t('closed') : Drupal.t('opened');
      var trayNameElement = relevantTray.querySelector('.toolbar-tray-name');
      var text;

      if (trayNameElement !== null) {
        text = Drupal.t('Tray "@tray" @action.', {
          '@tray': trayNameElement.textContent,
          '@action': action
        });
      } else {
        text = Drupal.t('Tray @action.', {
          '@action': action
        });
      }

      Drupal.announce(text);
    }
  });
})(Backbone, Drupal);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings, Backbone) {
  Drupal.toolbar.ToolbarVisualView = Backbone.View.extend({
    events: function events() {
      var touchEndToClick = function touchEndToClick(event) {
        event.preventDefault();
        event.target.click();
      };

      return {
        'click .toolbar-bar .toolbar-tab .trigger': 'onTabClick',
        'click .toolbar-toggle-orientation button': 'onOrientationToggleClick',
        'touchend .toolbar-bar .toolbar-tab .trigger': touchEndToClick,
        'touchend .toolbar-toggle-orientation button': touchEndToClick
      };
    },
    initialize: function initialize(options) {
      this.strings = options.strings;
      this.listenTo(this.model, 'change:activeTab change:orientation change:isOriented change:isTrayToggleVisible', this.render);
      this.listenTo(this.model, 'change:mqMatches', this.onMediaQueryChange);
      this.listenTo(this.model, 'change:offsets', this.adjustPlacement);
      this.listenTo(this.model, 'change:activeTab change:orientation change:isOriented', this.updateToolbarHeight);
      this.$el.find('.toolbar-tray .toolbar-lining').append(Drupal.theme('toolbarOrientationToggle'));
      this.model.trigger('change:activeTab');
    },
    updateToolbarHeight: function updateToolbarHeight() {
      var toolbarTabOuterHeight = $('#toolbar-bar').find('.toolbar-tab').outerHeight() || 0;
      var toolbarTrayHorizontalOuterHeight = $('.is-active.toolbar-tray-horizontal').outerHeight() || 0;
      this.model.set('height', toolbarTabOuterHeight + toolbarTrayHorizontalOuterHeight);
      $('body').css({
        'padding-top': this.model.get('height')
      });
      $('html').css({
        'scroll-padding-top': this.model.get('height')
      });
      this.triggerDisplace();
    },
    triggerDisplace: function triggerDisplace() {
      _.defer(function () {
        Drupal.displace(true);
      });
    },
    render: function render() {
      this.updateTabs();
      this.updateTrayOrientation();
      this.updateBarAttributes();
      $('body').removeClass('toolbar-loading');

      if (this.model.changed.orientation === 'vertical' || this.model.changed.activeTab) {
        this.loadSubtrees();
      }

      return this;
    },
    onTabClick: function onTabClick(event) {
      if (event.currentTarget.hasAttribute('data-toolbar-tray')) {
        var activeTab = this.model.get('activeTab');
        var clickedTab = event.currentTarget;
        this.model.set('activeTab', !activeTab || clickedTab !== activeTab ? clickedTab : null);
        event.preventDefault();
        event.stopPropagation();
      }
    },
    onOrientationToggleClick: function onOrientationToggleClick(event) {
      var orientation = this.model.get('orientation');
      var antiOrientation = orientation === 'vertical' ? 'horizontal' : 'vertical';
      var locked = antiOrientation === 'vertical';

      if (locked) {
        localStorage.setItem('Drupal.toolbar.trayVerticalLocked', 'true');
      } else {
        localStorage.removeItem('Drupal.toolbar.trayVerticalLocked');
      }

      this.model.set({
        locked: locked,
        orientation: antiOrientation
      }, {
        validate: true,
        override: true
      });
      event.preventDefault();
      event.stopPropagation();
    },
    updateTabs: function updateTabs() {
      var $tab = $(this.model.get('activeTab'));
      $(this.model.previous('activeTab')).removeClass('is-active').prop('aria-pressed', false);
      $(this.model.previous('activeTray')).removeClass('is-active');

      if ($tab.length > 0) {
        $tab.addClass('is-active').prop('aria-pressed', true);
        var name = $tab.attr('data-toolbar-tray');
        var id = $tab.get(0).id;

        if (id) {
          localStorage.setItem('Drupal.toolbar.activeTabID', JSON.stringify(id));
        }

        var $tray = this.$el.find("[data-toolbar-tray=\"".concat(name, "\"].toolbar-tray"));

        if ($tray.length) {
          $tray.addClass('is-active');
          this.model.set('activeTray', $tray.get(0));
        } else {
          this.model.set('activeTray', null);
        }
      } else {
        this.model.set('activeTray', null);
        localStorage.removeItem('Drupal.toolbar.activeTabID');
      }
    },
    updateBarAttributes: function updateBarAttributes() {
      var isOriented = this.model.get('isOriented');

      if (isOriented) {
        this.$el.find('.toolbar-bar').attr('data-offset-top', '');
      } else {
        this.$el.find('.toolbar-bar').removeAttr('data-offset-top');
      }

      this.$el.toggleClass('toolbar-oriented', isOriented);
    },
    updateTrayOrientation: function updateTrayOrientation() {
      var orientation = this.model.get('orientation');
      var antiOrientation = orientation === 'vertical' ? 'horizontal' : 'vertical';
      $('body').toggleClass('toolbar-vertical', orientation === 'vertical').toggleClass('toolbar-horizontal', orientation === 'horizontal');
      var removeClass = antiOrientation === 'horizontal' ? 'toolbar-tray-horizontal' : 'toolbar-tray-vertical';
      var $trays = this.$el.find('.toolbar-tray').removeClass(removeClass).addClass("toolbar-tray-".concat(orientation));
      var iconClass = "toolbar-icon-toggle-".concat(orientation);
      var iconAntiClass = "toolbar-icon-toggle-".concat(antiOrientation);
      var $orientationToggle = this.$el.find('.toolbar-toggle-orientation').toggle(this.model.get('isTrayToggleVisible'));
      $orientationToggle.find('button').val(antiOrientation).attr('title', this.strings[antiOrientation]).text(this.strings[antiOrientation]).removeClass(iconClass).addClass(iconAntiClass);
      var dir = document.documentElement.dir;
      var edge = dir === 'rtl' ? 'right' : 'left';
      $trays.removeAttr('data-offset-left data-offset-right data-offset-top');
      $trays.filter('.toolbar-tray-vertical.is-active').attr("data-offset-".concat(edge), '');
      $trays.filter('.toolbar-tray-horizontal.is-active').attr('data-offset-top', '');
    },
    adjustPlacement: function adjustPlacement() {
      var $trays = this.$el.find('.toolbar-tray');

      if (!this.model.get('isOriented')) {
        $trays.removeClass('toolbar-tray-horizontal').addClass('toolbar-tray-vertical');
      }
    },
    loadSubtrees: function loadSubtrees() {
      var $activeTab = $(this.model.get('activeTab'));
      var orientation = this.model.get('orientation');

      if (!this.model.get('areSubtreesLoaded') && typeof $activeTab.data('drupal-subtrees') !== 'undefined' && orientation === 'vertical') {
        var subtreesHash = drupalSettings.toolbar.subtreesHash;
        var theme = drupalSettings.ajaxPageState.theme;
        var endpoint = Drupal.url("toolbar/subtrees/".concat(subtreesHash));
        var cachedSubtreesHash = localStorage.getItem("Drupal.toolbar.subtreesHash.".concat(theme));
        var cachedSubtrees = JSON.parse(localStorage.getItem("Drupal.toolbar.subtrees.".concat(theme)));
        var isVertical = this.model.get('orientation') === 'vertical';

        if (isVertical && subtreesHash === cachedSubtreesHash && cachedSubtrees) {
          Drupal.toolbar.setSubtrees.resolve(cachedSubtrees);
        } else if (isVertical) {
          localStorage.removeItem("Drupal.toolbar.subtreesHash.".concat(theme));
          localStorage.removeItem("Drupal.toolbar.subtrees.".concat(theme));
          Drupal.ajax({
            url: endpoint
          }).execute();
          localStorage.setItem("Drupal.toolbar.subtreesHash.".concat(theme), subtreesHash);
        }
      }
    }
  });
})(jQuery, Drupal, drupalSettings, Backbone);;
/*! shepherd.js 8.3.1 */

'use strict';(function(G,ca){"object"===typeof exports&&"undefined"!==typeof module?module.exports=ca():"function"===typeof define&&define.amd?define(ca):(G="undefined"!==typeof globalThis?globalThis:G||self,G.Shepherd=ca())})(this,function(){function G(a,b){return!1!==b.clone&&b.isMergeableObject(a)?V(Array.isArray(a)?[]:{},a,b):a}function ca(a,b,c){return a.concat(b).map(function(a){return G(a,c)})}function tb(a){return Object.getOwnPropertySymbols?Object.getOwnPropertySymbols(a).filter(function(b){return a.propertyIsEnumerable(b)}):
[]}function Ia(a){return Object.keys(a).concat(tb(a))}function Ja(a,b){try{return b in a}catch(c){return!1}}function ub(a,b,c){var d={};c.isMergeableObject(a)&&Ia(a).forEach(function(b){d[b]=G(a[b],c)});Ia(b).forEach(function(e){if(!Ja(a,e)||Object.hasOwnProperty.call(a,e)&&Object.propertyIsEnumerable.call(a,e))if(Ja(a,e)&&c.isMergeableObject(b[e])){if(c.customMerge){var f=c.customMerge(e);f="function"===typeof f?f:V}else f=V;d[e]=f(a[e],b[e],c)}else d[e]=G(b[e],c)});return d}function V(a,b,c){c=
c||{};c.arrayMerge=c.arrayMerge||ca;c.isMergeableObject=c.isMergeableObject||vb;c.cloneUnlessOtherwiseSpecified=G;var d=Array.isArray(b),e=Array.isArray(a);return d!==e?G(b,c):d?c.arrayMerge(a,b,c):ub(a,b,c)}function W(a){return"function"===typeof a}function da(a){return"string"===typeof a}function Ka(a){let b=Object.getOwnPropertyNames(a.constructor.prototype);for(let c=0;c<b.length;c++){let d=b[c],e=a[d];"constructor"!==d&&"function"===typeof e&&(a[d]=e.bind(a))}return a}function wb(a,b){return c=>
{if(b.isOpen()){let d=b.el&&c.currentTarget===b.el;(void 0!==a&&c.currentTarget.matches(a)||d)&&b.tour.next()}}}function xb(a){let {event:b,selector:c}=a.options.advanceOn||{};if(b){let d=wb(c,a),e;try{e=document.querySelector(c)}catch(f){}if(void 0===c||e)e?(e.addEventListener(b,d),a.on("destroy",()=>e.removeEventListener(b,d))):(document.body.addEventListener(b,d,!0),a.on("destroy",()=>document.body.removeEventListener(b,d,!0)));else return console.error(`No element was found for the selector supplied to advanceOn: ${c}`)}else return console.error("advanceOn was defined, but no event name was passed.")}
function B(a){return a?(a.nodeName||"").toLowerCase():null}function z(a){return null==a?window:"[object Window]"!==a.toString()?(a=a.ownerDocument)?a.defaultView||window:window:a}function ea(a){var b=z(a).Element;return a instanceof b||a instanceof Element}function y(a){var b=z(a).HTMLElement;return a instanceof b||a instanceof HTMLElement}function La(a){if("undefined"===typeof ShadowRoot)return!1;var b=z(a).ShadowRoot;return a instanceof b||a instanceof ShadowRoot}function F(a){return a.split("-")[0]}
function X(a){a=a.getBoundingClientRect();return{width:a.width,height:a.height,top:a.top,right:a.right,bottom:a.bottom,left:a.left,x:a.left,y:a.top}}function ta(a){var b=X(a),c=a.offsetWidth,d=a.offsetHeight;1>=Math.abs(b.width-c)&&(c=b.width);1>=Math.abs(b.height-d)&&(d=b.height);return{x:a.offsetLeft,y:a.offsetTop,width:c,height:d}}function Ma(a,b){var c=b.getRootNode&&b.getRootNode();if(a.contains(b))return!0;if(c&&La(c)){do{if(b&&a.isSameNode(b))return!0;b=b.parentNode||b.host}while(b)}return!1}
function H(a){return z(a).getComputedStyle(a)}function L(a){return((ea(a)?a.ownerDocument:a.document)||window.document).documentElement}function la(a){return"html"===B(a)?a:a.assignedSlot||a.parentNode||(La(a)?a.host:null)||L(a)}function Na(a){return y(a)&&"fixed"!==H(a).position?a.offsetParent:null}function fa(a){for(var b=z(a),c=Na(a);c&&0<=["table","td","th"].indexOf(B(c))&&"static"===H(c).position;)c=Na(c);if(c&&("html"===B(c)||"body"===B(c)&&"static"===H(c).position))return b;if(!c)a:{c=-1!==
navigator.userAgent.toLowerCase().indexOf("firefox");if(-1===navigator.userAgent.indexOf("Trident")||!y(a)||"fixed"!==H(a).position)for(a=la(a);y(a)&&0>["html","body"].indexOf(B(a));){var d=H(a);if("none"!==d.transform||"none"!==d.perspective||"paint"===d.contain||-1!==["transform","perspective"].indexOf(d.willChange)||c&&"filter"===d.willChange||c&&d.filter&&"none"!==d.filter){c=a;break a}else a=a.parentNode}c=null}return c||b}function ua(a){return 0<=["top","bottom"].indexOf(a)?"x":"y"}function Oa(a){return Object.assign({},
{top:0,right:0,bottom:0,left:0},a)}function Pa(a,b){return b.reduce(function(b,d){b[d]=a;return b},{})}function Qa(a){var b,c=a.popper,d=a.popperRect,e=a.placement,f=a.offsets,h=a.position,k=a.gpuAcceleration,m=a.adaptive;a=a.roundOffsets;if(!0===a){a=f.y;var g=window.devicePixelRatio||1;a={x:ma(ma(f.x*g)/g)||0,y:ma(ma(a*g)/g)||0}}else a="function"===typeof a?a(f):f;g=a;a=g.x;a=void 0===a?0:a;g=g.y;g=void 0===g?0:g;var l=f.hasOwnProperty("x");f=f.hasOwnProperty("y");var p="left",t="top",A=window;
if(m){var C=fa(c),u="clientHeight",D="clientWidth";C===z(c)&&(C=L(c),"static"!==H(C).position&&(u="scrollHeight",D="scrollWidth"));"top"===e&&(t="bottom",g-=C[u]-d.height,g*=k?1:-1);"left"===e&&(p="right",a-=C[D]-d.width,a*=k?1:-1)}c=Object.assign({position:h},m&&yb);if(k){var v;return Object.assign({},c,(v={},v[t]=f?"0":"",v[p]=l?"0":"",v.transform=2>(A.devicePixelRatio||1)?"translate("+a+"px, "+g+"px)":"translate3d("+a+"px, "+g+"px, 0)",v))}return Object.assign({},c,(b={},b[t]=f?g+"px":"",b[p]=
l?a+"px":"",b.transform="",b))}function na(a){return a.replace(/left|right|bottom|top/g,function(a){return zb[a]})}function Ra(a){return a.replace(/start|end/g,function(a){return Ab[a]})}function va(a){a=z(a);return{scrollLeft:a.pageXOffset,scrollTop:a.pageYOffset}}function wa(a){return X(L(a)).left+va(a).scrollLeft}function xa(a){a=H(a);return/auto|scroll|overlay|hidden/.test(a.overflow+a.overflowY+a.overflowX)}function Sa(a){return 0<=["html","body","#document"].indexOf(B(a))?a.ownerDocument.body:
y(a)&&xa(a)?a:Sa(la(a))}function ha(a,b){var c;void 0===b&&(b=[]);var d=Sa(a);a=d===(null==(c=a.ownerDocument)?void 0:c.body);c=z(d);d=a?[c].concat(c.visualViewport||[],xa(d)?d:[]):d;b=b.concat(d);return a?b:b.concat(ha(la(d)))}function ya(a){return Object.assign({},a,{left:a.x,top:a.y,right:a.x+a.width,bottom:a.y+a.height})}function Ta(a,b){if("viewport"===b){b=z(a);var c=L(a);b=b.visualViewport;var d=c.clientWidth;c=c.clientHeight;var e=0,f=0;b&&(d=b.width,c=b.height,/^((?!chrome|android).)*safari/i.test(navigator.userAgent)||
(e=b.offsetLeft,f=b.offsetTop));a={width:d,height:c,x:e+wa(a),y:f};a=ya(a)}else y(b)?(a=X(b),a.top+=b.clientTop,a.left+=b.clientLeft,a.bottom=a.top+b.clientHeight,a.right=a.left+b.clientWidth,a.width=b.clientWidth,a.height=b.clientHeight,a.x=a.left,a.y=a.top):(f=L(a),a=L(f),d=va(f),b=null==(c=f.ownerDocument)?void 0:c.body,c=E(a.scrollWidth,a.clientWidth,b?b.scrollWidth:0,b?b.clientWidth:0),e=E(a.scrollHeight,a.clientHeight,b?b.scrollHeight:0,b?b.clientHeight:0),f=-d.scrollLeft+wa(f),d=-d.scrollTop,
"rtl"===H(b||a).direction&&(f+=E(a.clientWidth,b?b.clientWidth:0)-c),a=ya({width:c,height:e,x:f,y:d}));return a}function Bb(a){var b=ha(la(a)),c=0<=["absolute","fixed"].indexOf(H(a).position)&&y(a)?fa(a):a;return ea(c)?b.filter(function(a){return ea(a)&&Ma(a,c)&&"body"!==B(a)}):[]}function Cb(a,b,c){b="clippingParents"===b?Bb(a):[].concat(b);c=[].concat(b,[c]);c=c.reduce(function(b,c){c=Ta(a,c);b.top=E(c.top,b.top);b.right=M(c.right,b.right);b.bottom=M(c.bottom,b.bottom);b.left=E(c.left,b.left);return b},
Ta(a,c[0]));c.width=c.right-c.left;c.height=c.bottom-c.top;c.x=c.left;c.y=c.top;return c}function Ua(a){var b=a.reference,c=a.element,d=(a=a.placement)?F(a):null;a=a?a.split("-")[1]:null;var e=b.x+b.width/2-c.width/2,f=b.y+b.height/2-c.height/2;switch(d){case "top":e={x:e,y:b.y-c.height};break;case "bottom":e={x:e,y:b.y+b.height};break;case "right":e={x:b.x+b.width,y:f};break;case "left":e={x:b.x-c.width,y:f};break;default:e={x:b.x,y:b.y}}d=d?ua(d):null;if(null!=d)switch(f="y"===d?"height":"width",
a){case "start":e[d]-=b[f]/2-c[f]/2;break;case "end":e[d]+=b[f]/2-c[f]/2}return e}function ia(a,b){void 0===b&&(b={});var c=b;b=c.placement;b=void 0===b?a.placement:b;var d=c.boundary,e=void 0===d?"clippingParents":d;d=c.rootBoundary;var f=void 0===d?"viewport":d;d=c.elementContext;d=void 0===d?"popper":d;var h=c.altBoundary,k=void 0===h?!1:h;c=c.padding;c=void 0===c?0:c;c=Oa("number"!==typeof c?c:Pa(c,ja));var m=a.elements.reference;h=a.rects.popper;k=a.elements[k?"popper"===d?"reference":"popper":
d];e=Cb(ea(k)?k:k.contextElement||L(a.elements.popper),e,f);f=X(m);k=Ua({reference:f,element:h,strategy:"absolute",placement:b});h=ya(Object.assign({},h,k));f="popper"===d?h:f;var g={top:e.top-f.top+c.top,bottom:f.bottom-e.bottom+c.bottom,left:e.left-f.left+c.left,right:f.right-e.right+c.right};a=a.modifiersData.offset;if("popper"===d&&a){var l=a[b];Object.keys(g).forEach(function(a){var b=0<=["right","bottom"].indexOf(a)?1:-1,c=0<=["top","bottom"].indexOf(a)?"y":"x";g[a]+=l[c]*b})}return g}function Db(a,
b){void 0===b&&(b={});var c=b.boundary,d=b.rootBoundary,e=b.padding,f=b.flipVariations,h=b.allowedAutoPlacements,k=void 0===h?Va:h,m=b.placement.split("-")[1];b=m?f?Wa:Wa.filter(function(a){return a.split("-")[1]===m}):ja;f=b.filter(function(a){return 0<=k.indexOf(a)});0===f.length&&(f=b);var g=f.reduce(function(b,f){b[f]=ia(a,{placement:f,boundary:c,rootBoundary:d,padding:e})[F(f)];return b},{});return Object.keys(g).sort(function(a,b){return g[a]-g[b]})}function Eb(a){if("auto"===F(a))return[];
var b=na(a);return[Ra(a),b,Ra(b)]}function Xa(a,b,c){void 0===c&&(c={x:0,y:0});return{top:a.top-b.height-c.y,right:a.right-b.width+c.x,bottom:a.bottom-b.height+c.y,left:a.left-b.width-c.x}}function Ya(a){return["top","right","bottom","left"].some(function(b){return 0<=a[b]})}function Fb(a,b,c){void 0===c&&(c=!1);var d=L(b);a=X(a);var e=y(b),f={scrollLeft:0,scrollTop:0},h={x:0,y:0};if(e||!e&&!c){if("body"!==B(b)||xa(d))f=b!==z(b)&&y(b)?{scrollLeft:b.scrollLeft,scrollTop:b.scrollTop}:va(b);y(b)?(h=
X(b),h.x+=b.clientLeft,h.y+=b.clientTop):d&&(h.x=wa(d))}return{x:a.left+f.scrollLeft-h.x,y:a.top+f.scrollTop-h.y,width:a.width,height:a.height}}function Gb(a){function b(a){d.add(a.name);[].concat(a.requires||[],a.requiresIfExists||[]).forEach(function(a){d.has(a)||(a=c.get(a))&&b(a)});e.push(a)}var c=new Map,d=new Set,e=[];a.forEach(function(a){c.set(a.name,a)});a.forEach(function(a){d.has(a.name)||b(a)});return e}function Hb(a){var b=Gb(a);return Ib.reduce(function(a,d){return a.concat(b.filter(function(a){return a.phase===
d}))},[])}function Jb(a){var b;return function(){b||(b=new Promise(function(c){Promise.resolve().then(function(){b=void 0;c(a())})}));return b}}function Kb(a){var b=a.reduce(function(a,b){var c=a[b.name];a[b.name]=c?Object.assign({},c,b,{options:Object.assign({},c.options,b.options),data:Object.assign({},c.data,b.data)}):b;return a},{});return Object.keys(b).map(function(a){return b[a]})}function Za(){for(var a=arguments.length,b=Array(a),c=0;c<a;c++)b[c]=arguments[c];return!b.some(function(a){return!(a&&
"function"===typeof a.getBoundingClientRect)})}function za(){za=Object.assign||function(a){for(var b=1;b<arguments.length;b++){var c=arguments[b],d;for(d in c)Object.prototype.hasOwnProperty.call(c,d)&&(a[d]=c[d])}return a};return za.apply(this,arguments)}function Lb(){return[{name:"applyStyles",fn({state:a}){Object.keys(a.elements).forEach(b=>{if("popper"===b){var c=a.attributes[b]||{},d=a.elements[b];Object.assign(d.style,{position:"fixed",left:"50%",top:"50%",transform:"translate(-50%, -50%)"});
Object.keys(c).forEach(a=>{let b=c[a];!1===b?d.removeAttribute(a):d.setAttribute(a,!0===b?"":b)})}})}},{name:"computeStyles",options:{adaptive:!1}}]}function Mb(a){let b=Lb(),c={placement:"top",strategy:"fixed",modifiers:[{name:"focusAfterRender",enabled:!0,phase:"afterWrite",fn(){setTimeout(()=>{a.el&&a.el.focus()},300)}}]};return c=za({},c,{modifiers:Array.from(new Set([...c.modifiers,...b]))})}function $a(a){return da(a)&&""!==a?"-"!==a.charAt(a.length-1)?`${a}-`:a:""}function Aa(a){a=a.options.attachTo||
{};let b=Object.assign({},a);if(da(a.element)){try{b.element=document.querySelector(a.element)}catch(c){}b.element||console.error(`The element for this Shepherd step was not found ${a.element}`)}return b}function Ba(){let a=Date.now();return"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,b=>{let c=(a+16*Math.random())%16|0;a=Math.floor(a/16);return("x"==b?c:c&3|8).toString(16)})}function Nb(a,b){let c={modifiers:[{name:"preventOverflow",options:{altAxis:!0,tether:!1}},{name:"focusAfterRender",
enabled:!0,phase:"afterWrite",fn(){setTimeout(()=>{b.el&&b.el.focus()},300)}}],strategy:"absolute"};b.isCentered()?c=Mb(b):c.placement=a.on;(a=b.tour&&b.tour.options&&b.tour.options.defaultStepOptions)&&(c=ab(a,c));return c=ab(b.options,c)}function ab(a,b){if(a.popperOptions){let c=Object.assign({},b,a.popperOptions);if(a.popperOptions.modifiers&&0<a.popperOptions.modifiers.length){let d=a.popperOptions.modifiers.map(a=>a.name);b=b.modifiers.filter(a=>!d.includes(a.name));c.modifiers=Array.from(new Set([...b,
...a.popperOptions.modifiers]))}return c}return b}function x(){}function Ob(a,b){for(let c in b)a[c]=b[c];return a}function Y(a){return a()}function bb(a){return"function"===typeof a}function I(a,b){return a!=a?b==b:a!==b||a&&"object"===typeof a||"function"===typeof a}function w(a){a.parentNode.removeChild(a)}function cb(a){return document.createElementNS("http://www.w3.org/2000/svg",a)}function oa(a,b,c,d){a.addEventListener(b,c,d);return()=>a.removeEventListener(b,c,d)}function q(a,b,c){null==c?
a.removeAttribute(b):a.getAttribute(b)!==c&&a.setAttribute(b,c)}function db(a,b){let c=Object.getOwnPropertyDescriptors(a.__proto__);for(let d in b)null==b[d]?a.removeAttribute(d):"style"===d?a.style.cssText=b[d]:"__value"===d?a.value=a[d]=b[d]:c[d]&&c[d].set?a[d]=b[d]:q(a,d,b[d])}function Z(a,b,c){a.classList[c?"add":"remove"](b)}function pa(){if(!P)throw Error("Function called outside component initialization");return P}function Ca(a){qa.push(a)}function eb(){if(!Da){Da=!0;do{for(var a=0;a<ka.length;a+=
1){var b=ka[a];P=b;b=b.$$;if(null!==b.fragment){b.update();b.before_update.forEach(Y);let a=b.dirty;b.dirty=[-1];b.fragment&&b.fragment.p(b.ctx,a);b.after_update.forEach(Ca)}}P=null;for(ka.length=0;aa.length;)aa.pop()();for(a=0;a<qa.length;a+=1)b=qa[a],Ea.has(b)||(Ea.add(b),b());qa.length=0}while(ka.length);for(;fb.length;)fb.pop()();Da=Fa=!1;Ea.clear()}}function Q(){R={r:0,c:[],p:R}}function S(){R.r||R.c.forEach(Y);R=R.p}function n(a,b){a&&a.i&&(ra.delete(a),a.i(b))}function r(a,b,c,d){a&&a.o&&!ra.has(a)&&
(ra.add(a),R.c.push(()=>{ra.delete(a);d&&(c&&a.d(1),d())}),a.o(b))}function T(a){a&&a.c()}function N(a,b,c,d){let {fragment:e,on_mount:f,on_destroy:h,after_update:k}=a.$$;e&&e.m(b,c);d||Ca(()=>{let b=f.map(Y).filter(bb);h?h.push(...b):b.forEach(Y);a.$$.on_mount=[]});k.forEach(Ca)}function O(a,b){a=a.$$;null!==a.fragment&&(a.on_destroy.forEach(Y),a.fragment&&a.fragment.d(b),a.on_destroy=a.fragment=null,a.ctx=[])}function J(a,b,c,d,e,f,h=[-1]){let k=P;P=a;let m=a.$$={fragment:null,ctx:null,props:f,
update:x,not_equal:e,bound:Object.create(null),on_mount:[],on_destroy:[],on_disconnect:[],before_update:[],after_update:[],context:new Map(k?k.$$.context:b.context||[]),callbacks:Object.create(null),dirty:h,skip_bound:!1},g=!1;m.ctx=c?c(a,b.props||{},(b,c,...d)=>{d=d.length?d[0]:c;if(m.ctx&&e(m.ctx[b],m.ctx[b]=d)){if(!m.skip_bound&&m.bound[b])m.bound[b](d);g&&(-1===a.$$.dirty[0]&&(ka.push(a),Fa||(Fa=!0,Pb.then(eb)),a.$$.dirty.fill(0)),a.$$.dirty[b/31|0]|=1<<b%31)}return c}):[];m.update();g=!0;m.before_update.forEach(Y);
m.fragment=d?d(m.ctx):!1;b.target&&(b.hydrate?(c=Array.from(b.target.childNodes),m.fragment&&m.fragment.l(c),c.forEach(w)):m.fragment&&m.fragment.c(),b.intro&&n(a.$$.fragment),N(a,b.target,b.anchor,b.customElement),eb());P=k}function Qb(a){let b,c,d,e,f;return{c(){b=document.createElement("button");q(b,"aria-label",c=a[3]?a[3]:null);q(b,"class",d=`${a[1]||""} shepherd-button ${a[4]?"shepherd-button-secondary":""}`);b.disabled=a[2];q(b,"tabindex","0")},m(c,d){c.insertBefore(b,d||null);b.innerHTML=
a[5];e||(f=oa(b,"click",function(){bb(a[0])&&a[0].apply(this,arguments)}),e=!0)},p(e,[f]){a=e;f&32&&(b.innerHTML=a[5]);f&8&&c!==(c=a[3]?a[3]:null)&&q(b,"aria-label",c);f&18&&d!==(d=`${a[1]||""} shepherd-button ${a[4]?"shepherd-button-secondary":""}`)&&q(b,"class",d);f&4&&(b.disabled=a[2])},i:x,o:x,d(a){a&&w(b);e=!1;f()}}}function Rb(a,b,c){let {config:d}=b,{step:e}=b,f,h,k,m,g,l;a.$$set=a=>{"config"in a&&c(6,d=a.config);"step"in a&&c(7,e=a.step)};a.$$.update=()=>{if(a.$$.dirty&192){c(0,f=d.action?
d.action.bind(e.tour):null);c(1,h=d.classes);if(d.disabled){var b=d.disabled;b=W(b)?b.call(e):b}else b=!1;c(2,k=b);c(3,m=d.label);c(4,g=d.secondary);c(5,l=d.text)}};return[f,h,k,m,g,l,d,e]}function gb(a,b,c){a=a.slice();a[2]=b[c];return a}function hb(a){let b,c,d=a[1],e=[];for(let b=0;b<d.length;b+=1)e[b]=ib(gb(a,d,b));let f=a=>r(e[a],1,1,()=>{e[a]=null});return{c(){for(let a=0;a<e.length;a+=1)e[a].c();b=document.createTextNode("")},m(a,d){for(let b=0;b<e.length;b+=1)e[b].m(a,d);a.insertBefore(b,
d||null);c=!0},p(a,c){if(c&3){d=a[1];let h;for(h=0;h<d.length;h+=1){let f=gb(a,d,h);e[h]?(e[h].p(f,c),n(e[h],1)):(e[h]=ib(f),e[h].c(),n(e[h],1),e[h].m(b.parentNode,b))}Q();for(h=d.length;h<e.length;h+=1)f(h);S()}},i(a){if(!c){for(a=0;a<d.length;a+=1)n(e[a]);c=!0}},o(a){e=e.filter(Boolean);for(a=0;a<e.length;a+=1)r(e[a]);c=!1},d(a){var c=e;for(let b=0;b<c.length;b+=1)c[b]&&c[b].d(a);a&&w(b)}}}function ib(a){let b,c;b=new Sb({props:{config:a[2],step:a[0]}});return{c(){T(b.$$.fragment)},m(a,e){N(b,a,
e);c=!0},p(a,c){let d={};c&2&&(d.config=a[2]);c&1&&(d.step=a[0]);b.$set(d)},i(a){c||(n(b.$$.fragment,a),c=!0)},o(a){r(b.$$.fragment,a);c=!1},d(a){O(b,a)}}}function Tb(a){let b,c,d=a[1]&&hb(a);return{c(){b=document.createElement("footer");d&&d.c();q(b,"class","shepherd-footer")},m(a,f){a.insertBefore(b,f||null);d&&d.m(b,null);c=!0},p(a,[c]){a[1]?d?(d.p(a,c),c&2&&n(d,1)):(d=hb(a),d.c(),n(d,1),d.m(b,null)):d&&(Q(),r(d,1,1,()=>{d=null}),S())},i(a){c||(n(d),c=!0)},o(a){r(d);c=!1},d(a){a&&w(b);d&&d.d()}}}
function Ub(a,b,c){let d,{step:e}=b;a.$$set=a=>{"step"in a&&c(0,e=a.step)};a.$$.update=()=>{a.$$.dirty&1&&c(1,d=e.options.buttons)};return[e,d]}function Vb(a){let b,c,d,e,f;return{c(){b=document.createElement("button");c=document.createElement("span");c.textContent="\u00d7";q(c,"aria-hidden","true");q(b,"aria-label",d=a[0].label?a[0].label:"Close Tour");q(b,"class","shepherd-cancel-icon");q(b,"type","button")},m(d,k){d.insertBefore(b,k||null);b.appendChild(c);e||(f=oa(b,"click",a[1]),e=!0)},p(a,[c]){c&
1&&d!==(d=a[0].label?a[0].label:"Close Tour")&&q(b,"aria-label",d)},i:x,o:x,d(a){a&&w(b);e=!1;f()}}}function Wb(a,b,c){let {cancelIcon:d}=b,{step:e}=b;a.$$set=a=>{"cancelIcon"in a&&c(0,d=a.cancelIcon);"step"in a&&c(2,e=a.step)};return[d,a=>{a.preventDefault();e.cancel()},e]}function Xb(a){let b;return{c(){b=document.createElement("h3");q(b,"id",a[1]);q(b,"class","shepherd-title")},m(c,d){c.insertBefore(b,d||null);a[3](b)},p(a,[d]){d&2&&q(b,"id",a[1])},i:x,o:x,d(c){c&&w(b);a[3](null)}}}function Yb(a,
b,c){let {labelId:d}=b,{element:e}=b,{title:f}=b;pa().$$.after_update.push(()=>{W(f)&&c(2,f=f());c(0,e.innerHTML=f,e)});a.$$set=a=>{"labelId"in a&&c(1,d=a.labelId);"element"in a&&c(0,e=a.element);"title"in a&&c(2,f=a.title)};return[e,d,f,function(a){aa[a?"unshift":"push"](()=>{e=a;c(0,e)})}]}function jb(a){let b,c;b=new Zb({props:{labelId:a[0],title:a[2]}});return{c(){T(b.$$.fragment)},m(a,e){N(b,a,e);c=!0},p(a,c){let d={};c&1&&(d.labelId=a[0]);c&4&&(d.title=a[2]);b.$set(d)},i(a){c||(n(b.$$.fragment,
a),c=!0)},o(a){r(b.$$.fragment,a);c=!1},d(a){O(b,a)}}}function kb(a){let b,c;b=new $b({props:{cancelIcon:a[3],step:a[1]}});return{c(){T(b.$$.fragment)},m(a,e){N(b,a,e);c=!0},p(a,c){let d={};c&8&&(d.cancelIcon=a[3]);c&2&&(d.step=a[1]);b.$set(d)},i(a){c||(n(b.$$.fragment,a),c=!0)},o(a){r(b.$$.fragment,a);c=!1},d(a){O(b,a)}}}function ac(a){let b,c,d,e=a[2]&&jb(a),f=a[3]&&a[3].enabled&&kb(a);return{c(){b=document.createElement("header");e&&e.c();c=document.createTextNode(" ");f&&f.c();q(b,"class","shepherd-header")},
m(a,k){a.insertBefore(b,k||null);e&&e.m(b,null);b.appendChild(c);f&&f.m(b,null);d=!0},p(a,[d]){a[2]?e?(e.p(a,d),d&4&&n(e,1)):(e=jb(a),e.c(),n(e,1),e.m(b,c)):e&&(Q(),r(e,1,1,()=>{e=null}),S());a[3]&&a[3].enabled?f?(f.p(a,d),d&8&&n(f,1)):(f=kb(a),f.c(),n(f,1),f.m(b,null)):f&&(Q(),r(f,1,1,()=>{f=null}),S())},i(a){d||(n(e),n(f),d=!0)},o(a){r(e);r(f);d=!1},d(a){a&&w(b);e&&e.d();f&&f.d()}}}function bc(a,b,c){let {labelId:d}=b,{step:e}=b,f,h;a.$$set=a=>{"labelId"in a&&c(0,d=a.labelId);"step"in a&&c(1,e=
a.step)};a.$$.update=()=>{a.$$.dirty&2&&(c(2,f=e.options.title),c(3,h=e.options.cancelIcon))};return[d,e,f,h]}function cc(a){let b;return{c(){b=document.createElement("div");q(b,"class","shepherd-text");q(b,"id",a[1])},m(c,d){c.insertBefore(b,d||null);a[3](b)},p(a,[d]){d&2&&q(b,"id",a[1])},i:x,o:x,d(c){c&&w(b);a[3](null)}}}function dc(a,b,c){let {descriptionId:d}=b,{element:e}=b,{step:f}=b;pa().$$.after_update.push(()=>{let {text:a}=f.options;W(a)&&(a=a.call(f));a instanceof HTMLElement?e.appendChild(a):
c(0,e.innerHTML=a,e)});a.$$set=a=>{"descriptionId"in a&&c(1,d=a.descriptionId);"element"in a&&c(0,e=a.element);"step"in a&&c(2,f=a.step)};return[e,d,f,function(a){aa[a?"unshift":"push"](()=>{e=a;c(0,e)})}]}function lb(a){let b,c;b=new ec({props:{labelId:a[1],step:a[2]}});return{c(){T(b.$$.fragment)},m(a,e){N(b,a,e);c=!0},p(a,c){let d={};c&2&&(d.labelId=a[1]);c&4&&(d.step=a[2]);b.$set(d)},i(a){c||(n(b.$$.fragment,a),c=!0)},o(a){r(b.$$.fragment,a);c=!1},d(a){O(b,a)}}}function mb(a){let b,c;b=new fc({props:{descriptionId:a[0],
step:a[2]}});return{c(){T(b.$$.fragment)},m(a,e){N(b,a,e);c=!0},p(a,c){let d={};c&1&&(d.descriptionId=a[0]);c&4&&(d.step=a[2]);b.$set(d)},i(a){c||(n(b.$$.fragment,a),c=!0)},o(a){r(b.$$.fragment,a);c=!1},d(a){O(b,a)}}}function nb(a){let b,c;b=new gc({props:{step:a[2]}});return{c(){T(b.$$.fragment)},m(a,e){N(b,a,e);c=!0},p(a,c){let d={};c&4&&(d.step=a[2]);b.$set(d)},i(a){c||(n(b.$$.fragment,a),c=!0)},o(a){r(b.$$.fragment,a);c=!1},d(a){O(b,a)}}}function hc(a){let b,c=void 0!==a[2].options.title||a[2].options.cancelIcon&&
a[2].options.cancelIcon.enabled,d,e=void 0!==a[2].options.text,f,h=Array.isArray(a[2].options.buttons)&&a[2].options.buttons.length,k,m=c&&lb(a),g=e&&mb(a),l=h&&nb(a);return{c(){b=document.createElement("div");m&&m.c();d=document.createTextNode(" ");g&&g.c();f=document.createTextNode(" ");l&&l.c();q(b,"class","shepherd-content")},m(a,c){a.insertBefore(b,c||null);m&&m.m(b,null);b.appendChild(d);g&&g.m(b,null);b.appendChild(f);l&&l.m(b,null);k=!0},p(a,[k]){k&4&&(c=void 0!==a[2].options.title||a[2].options.cancelIcon&&
a[2].options.cancelIcon.enabled);c?m?(m.p(a,k),k&4&&n(m,1)):(m=lb(a),m.c(),n(m,1),m.m(b,d)):m&&(Q(),r(m,1,1,()=>{m=null}),S());k&4&&(e=void 0!==a[2].options.text);e?g?(g.p(a,k),k&4&&n(g,1)):(g=mb(a),g.c(),n(g,1),g.m(b,f)):g&&(Q(),r(g,1,1,()=>{g=null}),S());k&4&&(h=Array.isArray(a[2].options.buttons)&&a[2].options.buttons.length);h?l?(l.p(a,k),k&4&&n(l,1)):(l=nb(a),l.c(),n(l,1),l.m(b,null)):l&&(Q(),r(l,1,1,()=>{l=null}),S())},i(a){k||(n(m),n(g),n(l),k=!0)},o(a){r(m);r(g);r(l);k=!1},d(a){a&&w(b);m&&
m.d();g&&g.d();l&&l.d()}}}function ic(a,b,c){let {descriptionId:d}=b,{labelId:e}=b,{step:f}=b;a.$$set=a=>{"descriptionId"in a&&c(0,d=a.descriptionId);"labelId"in a&&c(1,e=a.labelId);"step"in a&&c(2,f=a.step)};return[d,e,f]}function ob(a){let b;return{c(){b=document.createElement("div");q(b,"class","shepherd-arrow");q(b,"data-popper-arrow","")},m(a,d){a.insertBefore(b,d||null)},d(a){a&&w(b)}}}function jc(a){let b,c,d,e,f,h,k,m,g=a[4].options.arrow&&a[4].options.attachTo&&a[4].options.attachTo.element&&
a[4].options.attachTo.on&&ob();d=new kc({props:{descriptionId:a[2],labelId:a[3],step:a[4]}});let l=[{"aria-describedby":e=void 0!==a[4].options.text?a[2]:null},{"aria-labelledby":f=a[4].options.title?a[3]:null},a[1],{role:"dialog"},{tabindex:"0"}],p={};for(let a=0;a<l.length;a+=1)p=Ob(p,l[a]);return{c(){b=document.createElement("div");g&&g.c();c=document.createTextNode(" ");T(d.$$.fragment);db(b,p);Z(b,"shepherd-has-cancel-icon",a[5]);Z(b,"shepherd-has-title",a[6]);Z(b,"shepherd-element",!0)},m(e,
f){e.insertBefore(b,f||null);g&&g.m(b,null);b.appendChild(c);N(d,b,null);a[13](b);h=!0;k||(m=oa(b,"keydown",a[7]),k=!0)},p(a,[k]){a[4].options.arrow&&a[4].options.attachTo&&a[4].options.attachTo.element&&a[4].options.attachTo.on?g||(g=ob(),g.c(),g.m(b,c)):g&&(g.d(1),g=null);var m={};k&4&&(m.descriptionId=a[2]);k&8&&(m.labelId=a[3]);k&16&&(m.step=a[4]);d.$set(m);m=b;{k=[(!h||k&20&&e!==(e=void 0!==a[4].options.text?a[2]:null))&&{"aria-describedby":e},(!h||k&24&&f!==(f=a[4].options.title?a[3]:null))&&
{"aria-labelledby":f},k&2&&a[1],{role:"dialog"},{tabindex:"0"}];let b={},c={},d={$$scope:1},g=l.length;for(;g--;){let a=l[g],e=k[g];if(e){for(u in a)u in e||(c[u]=1);for(let a in e)d[a]||(b[a]=e[a],d[a]=1);l[g]=e}else for(let b in a)d[b]=1}for(let a in c)a in b||(b[a]=void 0);var u=b}db(m,p=u);Z(b,"shepherd-has-cancel-icon",a[5]);Z(b,"shepherd-has-title",a[6]);Z(b,"shepherd-element",!0)},i(a){h||(n(d.$$.fragment,a),h=!0)},o(a){r(d.$$.fragment,a);h=!1},d(c){c&&w(b);g&&g.d();O(d);a[13](null);k=!1;m()}}}
function pb(a){return a.split(" ").filter(a=>!!a.length)}function lc(a,b,c){let {classPrefix:d}=b,{element:e}=b,{descriptionId:f}=b,{firstFocusableElement:h}=b,{focusableElements:k}=b,{labelId:m}=b,{lastFocusableElement:g}=b,{step:l}=b,{dataStepId:p}=b,t,A,C;pa().$$.on_mount.push(()=>{c(1,p={[`data-${d}shepherd-step-id`]:l.id});c(9,k=e.querySelectorAll('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex="0"]'));c(8,h=k[0]);
c(10,g=k[k.length-1])});pa().$$.after_update.push(()=>{if(C!==l.options.classes){var a=C;da(a)&&(a=pb(a),a.length&&e.classList.remove(...a));a=C=l.options.classes;da(a)&&(a=pb(a),a.length&&e.classList.add(...a))}});a.$$set=a=>{"classPrefix"in a&&c(11,d=a.classPrefix);"element"in a&&c(0,e=a.element);"descriptionId"in a&&c(2,f=a.descriptionId);"firstFocusableElement"in a&&c(8,h=a.firstFocusableElement);"focusableElements"in a&&c(9,k=a.focusableElements);"labelId"in a&&c(3,m=a.labelId);"lastFocusableElement"in
a&&c(10,g=a.lastFocusableElement);"step"in a&&c(4,l=a.step);"dataStepId"in a&&c(1,p=a.dataStepId)};a.$$.update=()=>{a.$$.dirty&16&&(c(5,t=l.options&&l.options.cancelIcon&&l.options.cancelIcon.enabled),c(6,A=l.options&&l.options.title))};return[e,p,f,m,l,t,A,a=>{const {tour:b}=l;switch(a.keyCode){case 9:if(0===k.length){a.preventDefault();break}if(a.shiftKey){if(document.activeElement===h||document.activeElement.classList.contains("shepherd-element"))a.preventDefault(),g.focus()}else document.activeElement===
g&&(a.preventDefault(),h.focus());break;case 27:b.options.exitOnEsc&&l.cancel();break;case 37:b.options.keyboardNavigation&&b.back();break;case 39:b.options.keyboardNavigation&&b.next()}},h,k,g,d,()=>e,function(a){aa[a?"unshift":"push"](()=>{e=a;c(0,e)})}]}function mc(a){a&&({steps:a}=a,a.forEach(a=>{a.options&&!1===a.options.canClickTarget&&a.options.attachTo&&a.target instanceof HTMLElement&&a.target.classList.remove("shepherd-target-click-disabled")}))}function nc({width:a,height:b,x:c=0,y:d=0,
r:e=0}){let {innerWidth:f,innerHeight:h}=window;return`M${f},${h}\
H0\
V0\
H${f}\
V${h}\
Z\
M${c+e},${d}\
a${e},${e},0,0,0-${e},${e}\
V${b+d-e}\
a${e},${e},0,0,0,${e},${e}\
H${a+c-e}\
a${e},${e},0,0,0,${e}-${e}\
V${d+e}\
a${e},${e},0,0,0-${e}-${e}\
Z`}function oc(a){let b,c,d,e,f;return{c(){b=cb("svg");c=cb("path");q(c,"d",a[2]);q(b,"class",d=`${a[1]?"shepherd-modal-is-visible":""} shepherd-modal-overlay-container`)},m(d,k){d.insertBefore(b,k||null);b.appendChild(c);a[11](b);e||(f=oa(b,"touchmove",a[3]),e=!0)},p(a,[e]){e&4&&q(c,"d",a[2]);e&2&&d!==(d=`${a[1]?"shepherd-modal-is-visible":""} shepherd-modal-overlay-container`)&&q(b,"class",d)},i:x,o:x,d(c){c&&w(b);a[11](null);e=!1;f()}}}function qb(a){if(!a)return null;let b=a instanceof HTMLElement&&
window.getComputedStyle(a).overflowY;return"hidden"!==b&&"visible"!==b&&a.scrollHeight>=a.clientHeight?a:qb(a.parentElement)}function pc(a,b,c){function d(){c(4,l={width:0,height:0,x:0,y:0,r:0})}function e(){c(1,p=!1);k()}function f(a=0,b=0,e,f){if(f){var g=f.getBoundingClientRect();var u=g.y||g.top;g=g.bottom||u+g.height;if(e){var h=e.getBoundingClientRect();e=h.y||h.top;h=h.bottom||e+h.height;u=Math.max(u,e);g=Math.min(g,h)}u={y:u,height:Math.max(g-u,0)};let {y:d,height:k}=u,{x:m,width:p,left:A}=
f.getBoundingClientRect();c(4,l={width:p+2*a,height:k+2*a,x:(m||A)-a,y:d-a,r:b})}else d()}function h(){c(1,p=!0)}function k(){t&&(cancelAnimationFrame(t),t=void 0);window.removeEventListener("touchmove",C,{passive:!1})}function m(a){let {modalOverlayOpeningPadding:b,modalOverlayOpeningRadius:c}=a.options,d=qb(a.target),e=()=>{t=void 0;f(b,c,d,a.target);t=requestAnimationFrame(e)};e();window.addEventListener("touchmove",C,{passive:!1})}let {element:g}=b,{openingProperties:l}=b;Ba();let p=!1,t=void 0,
A;d();let C=a=>{a.preventDefault()};a.$$set=a=>{"element"in a&&c(0,g=a.element);"openingProperties"in a&&c(4,l=a.openingProperties)};a.$$.update=()=>{a.$$.dirty&16&&c(2,A=nc(l))};return[g,p,A,a=>{a.stopPropagation()},l,()=>g,d,e,f,function(a){k();a.tour.options.useModalOverlay?(m(a),h()):e()},h,function(a){aa[a?"unshift":"push"](()=>{g=a;c(0,g)})}]}var vb=function(a){var b;if(b=!!a&&"object"===typeof a)b=Object.prototype.toString.call(a),b=!("[object RegExp]"===b||"[object Date]"===b||a.$$typeof===
qc);return b},qc="function"===typeof Symbol&&Symbol.for?Symbol.for("react.element"):60103;V.all=function(a,b){if(!Array.isArray(a))throw Error("first argument should be an array");return a.reduce(function(a,d){return V(a,d,b)},{})};var rc=V;class Ga{on(a,b,c,d=!1){void 0===this.bindings&&(this.bindings={});void 0===this.bindings[a]&&(this.bindings[a]=[]);this.bindings[a].push({handler:b,ctx:c,once:d});return this}once(a,b,c){return this.on(a,b,c,!0)}off(a,b){if(void 0===this.bindings||void 0===this.bindings[a])return this;
void 0===b?delete this.bindings[a]:this.bindings[a].forEach((c,d)=>{c.handler===b&&this.bindings[a].splice(d,1)});return this}trigger(a,...b){void 0!==this.bindings&&this.bindings[a]&&this.bindings[a].forEach((c,d)=>{let {ctx:e,handler:f,once:h}=c;f.apply(e||this,b);h&&this.bindings[a].splice(d,1)});return this}}var ja=["top","bottom","right","left"],Wa=ja.reduce(function(a,b){return a.concat([b+"-start",b+"-end"])},[]),Va=[].concat(ja,["auto"]).reduce(function(a,b){return a.concat([b,b+"-start",
b+"-end"])},[]),Ib="beforeRead read afterRead beforeMain main afterMain beforeWrite write afterWrite".split(" "),E=Math.max,M=Math.min,ma=Math.round,yb={top:"auto",right:"auto",bottom:"auto",left:"auto"},sa={passive:!0},zb={left:"right",right:"left",bottom:"top",top:"bottom"},Ab={start:"end",end:"start"},rb={placement:"bottom",modifiers:[],strategy:"absolute"},sc=function(a){void 0===a&&(a={});var b=a.defaultModifiers,c=void 0===b?[]:b;a=a.defaultOptions;var d=void 0===a?rb:a;return function(a,b,
h){function e(){g.orderedModifiers.forEach(function(a){var b=a.name,c=a.options;c=void 0===c?{}:c;a=a.effect;"function"===typeof a&&(b=a({state:g,name:b,instance:t,options:c}),l.push(b||function(){}))})}function f(){l.forEach(function(a){return a()});l=[]}void 0===h&&(h=d);var g={placement:"bottom",orderedModifiers:[],options:Object.assign({},rb,d),modifiersData:{},elements:{reference:a,popper:b},attributes:{},styles:{}},l=[],p=!1,t={state:g,setOptions:function(h){f();g.options=Object.assign({},d,
g.options,h);g.scrollParents={reference:ea(a)?ha(a):a.contextElement?ha(a.contextElement):[],popper:ha(b)};h=Hb(Kb([].concat(c,g.options.modifiers)));g.orderedModifiers=h.filter(function(a){return a.enabled});e();return t.update()},forceUpdate:function(){if(!p){var a=g.elements,b=a.reference;a=a.popper;if(Za(b,a))for(g.rects={reference:Fb(b,fa(a),"fixed"===g.options.strategy),popper:ta(a)},g.reset=!1,g.placement=g.options.placement,g.orderedModifiers.forEach(function(a){return g.modifiersData[a.name]=
Object.assign({},a.data)}),b=0;b<g.orderedModifiers.length;b++)if(!0===g.reset)g.reset=!1,b=-1;else{var c=g.orderedModifiers[b];a=c.fn;var d=c.options;d=void 0===d?{}:d;c=c.name;"function"===typeof a&&(g=a({state:g,options:d,name:c,instance:t})||g)}}},update:Jb(function(){return new Promise(function(a){t.forceUpdate();a(g)})}),destroy:function(){f();p=!0}};if(!Za(a,b))return t;t.setOptions(h).then(function(a){if(!p&&h.onFirstUpdate)h.onFirstUpdate(a)});return t}}({defaultModifiers:[{name:"eventListeners",
enabled:!0,phase:"write",fn:function(){},effect:function(a){var b=a.state,c=a.instance;a=a.options;var d=a.scroll,e=void 0===d?!0:d;a=a.resize;var f=void 0===a?!0:a,h=z(b.elements.popper),k=[].concat(b.scrollParents.reference,b.scrollParents.popper);e&&k.forEach(function(a){a.addEventListener("scroll",c.update,sa)});f&&h.addEventListener("resize",c.update,sa);return function(){e&&k.forEach(function(a){a.removeEventListener("scroll",c.update,sa)});f&&h.removeEventListener("resize",c.update,sa)}},data:{}},
{name:"popperOffsets",enabled:!0,phase:"read",fn:function(a){var b=a.state;b.modifiersData[a.name]=Ua({reference:b.rects.reference,element:b.rects.popper,strategy:"absolute",placement:b.placement})},data:{}},{name:"computeStyles",enabled:!0,phase:"beforeWrite",fn:function(a){var b=a.state,c=a.options;a=c.gpuAcceleration;a=void 0===a?!0:a;var d=c.adaptive;d=void 0===d?!0:d;c=c.roundOffsets;c=void 0===c?!0:c;a={placement:F(b.placement),popper:b.elements.popper,popperRect:b.rects.popper,gpuAcceleration:a};
null!=b.modifiersData.popperOffsets&&(b.styles.popper=Object.assign({},b.styles.popper,Qa(Object.assign({},a,{offsets:b.modifiersData.popperOffsets,position:b.options.strategy,adaptive:d,roundOffsets:c}))));null!=b.modifiersData.arrow&&(b.styles.arrow=Object.assign({},b.styles.arrow,Qa(Object.assign({},a,{offsets:b.modifiersData.arrow,position:"absolute",adaptive:!1,roundOffsets:c}))));b.attributes.popper=Object.assign({},b.attributes.popper,{"data-popper-placement":b.placement})},data:{}},{name:"applyStyles",
enabled:!0,phase:"write",fn:function(a){var b=a.state;Object.keys(b.elements).forEach(function(a){var c=b.styles[a]||{},e=b.attributes[a]||{},f=b.elements[a];y(f)&&B(f)&&(Object.assign(f.style,c),Object.keys(e).forEach(function(a){var b=e[a];!1===b?f.removeAttribute(a):f.setAttribute(a,!0===b?"":b)}))})},effect:function(a){var b=a.state,c={popper:{position:b.options.strategy,left:"0",top:"0",margin:"0"},arrow:{position:"absolute"},reference:{}};Object.assign(b.elements.popper.style,c.popper);b.styles=
c;b.elements.arrow&&Object.assign(b.elements.arrow.style,c.arrow);return function(){Object.keys(b.elements).forEach(function(a){var d=b.elements[a],f=b.attributes[a]||{};a=Object.keys(b.styles.hasOwnProperty(a)?b.styles[a]:c[a]).reduce(function(a,b){a[b]="";return a},{});y(d)&&B(d)&&(Object.assign(d.style,a),Object.keys(f).forEach(function(a){d.removeAttribute(a)}))})}},requires:["computeStyles"]},{name:"offset",enabled:!0,phase:"main",requires:["popperOffsets"],fn:function(a){var b=a.state,c=a.name;
a=a.options.offset;var d=void 0===a?[0,0]:a;a=Va.reduce(function(a,c){var e=b.rects;var f=F(c);var h=0<=["left","top"].indexOf(f)?-1:1,k="function"===typeof d?d(Object.assign({},e,{placement:c})):d;e=k[0];k=k[1];e=e||0;k=(k||0)*h;f=0<=["left","right"].indexOf(f)?{x:k,y:e}:{x:e,y:k};a[c]=f;return a},{});var e=a[b.placement],f=e.x;e=e.y;null!=b.modifiersData.popperOffsets&&(b.modifiersData.popperOffsets.x+=f,b.modifiersData.popperOffsets.y+=e);b.modifiersData[c]=a}},{name:"flip",enabled:!0,phase:"main",
fn:function(a){var b=a.state,c=a.options;a=a.name;if(!b.modifiersData[a]._skip){var d=c.mainAxis;d=void 0===d?!0:d;var e=c.altAxis;e=void 0===e?!0:e;var f=c.fallbackPlacements,h=c.padding,k=c.boundary,m=c.rootBoundary,g=c.altBoundary,l=c.flipVariations,p=void 0===l?!0:l,t=c.allowedAutoPlacements;c=b.options.placement;l=F(c);f=f||(l!==c&&p?Eb(c):[na(c)]);var A=[c].concat(f).reduce(function(a,c){return a.concat("auto"===F(c)?Db(b,{placement:c,boundary:k,rootBoundary:m,padding:h,flipVariations:p,allowedAutoPlacements:t}):
c)},[]);c=b.rects.reference;f=b.rects.popper;var n=new Map;l=!0;for(var u=A[0],D=0;D<A.length;D++){var v=A[D],q=F(v),r="start"===v.split("-")[1],U=0<=["top","bottom"].indexOf(q),x=U?"width":"height",w=ia(b,{placement:v,boundary:k,rootBoundary:m,altBoundary:g,padding:h});r=U?r?"right":"left":r?"bottom":"top";c[x]>f[x]&&(r=na(r));x=na(r);U=[];d&&U.push(0>=w[q]);e&&U.push(0>=w[r],0>=w[x]);if(U.every(function(a){return a})){u=v;l=!1;break}n.set(v,U)}if(l)for(d=function(a){var b=A.find(function(b){if(b=
n.get(b))return b.slice(0,a).every(function(a){return a})});if(b)return u=b,"break"},e=p?3:1;0<e&&"break"!==d(e);e--);b.placement!==u&&(b.modifiersData[a]._skip=!0,b.placement=u,b.reset=!0)}},requiresIfExists:["offset"],data:{_skip:!1}},{name:"preventOverflow",enabled:!0,phase:"main",fn:function(a){var b=a.state,c=a.options;a=a.name;var d=c.mainAxis,e=void 0===d?!0:d;d=c.altAxis;var f=void 0===d?!1:d;d=c.tether;d=void 0===d?!0:d;var h=c.tetherOffset,k=void 0===h?0:h,m=ia(b,{boundary:c.boundary,rootBoundary:c.rootBoundary,
padding:c.padding,altBoundary:c.altBoundary});c=F(b.placement);var g=b.placement.split("-")[1],l=!g,p=ua(c);c="x"===p?"y":"x";h=b.modifiersData.popperOffsets;var t=b.rects.reference,n=b.rects.popper,q="function"===typeof k?k(Object.assign({},b.rects,{placement:b.placement})):k;k={x:0,y:0};if(h){if(e||f){var u="y"===p?"top":"left",D="y"===p?"bottom":"right",v="y"===p?"height":"width",r=h[p],x=h[p]+m[u],w=h[p]-m[D],z=d?-n[v]/2:0,y="start"===g?t[v]:n[v];g="start"===g?-n[v]:-t[v];n=b.elements.arrow;n=
d&&n?ta(n):{width:0,height:0};var B=b.modifiersData["arrow#persistent"]?b.modifiersData["arrow#persistent"].padding:{top:0,right:0,bottom:0,left:0};u=B[u];D=B[D];n=E(0,M(t[v],n[v]));y=l?t[v]/2-z-n-u-q:y-n-u-q;t=l?-t[v]/2+z+n+D+q:g+n+D+q;l=b.elements.arrow&&fa(b.elements.arrow);q=b.modifiersData.offset?b.modifiersData.offset[b.placement][p]:0;l=h[p]+y-q-(l?"y"===p?l.clientTop||0:l.clientLeft||0:0);t=h[p]+t-q;e&&(e=d?M(x,l):x,w=d?E(w,t):w,e=E(e,M(r,w)),h[p]=e,k[p]=e-r);f&&(f=h[c],e=f+m["x"===p?"top":
"left"],m=f-m["x"===p?"bottom":"right"],e=d?M(e,l):e,d=d?E(m,t):m,d=E(e,M(f,d)),h[c]=d,k[c]=d-f)}b.modifiersData[a]=k}},requiresIfExists:["offset"]},{name:"arrow",enabled:!0,phase:"main",fn:function(a){var b,c=a.state,d=a.name,e=a.options,f=c.elements.arrow,h=c.modifiersData.popperOffsets,k=F(c.placement);a=ua(k);k=0<=["left","right"].indexOf(k)?"height":"width";if(f&&h){e=e.padding;e="function"===typeof e?e(Object.assign({},c.rects,{placement:c.placement})):e;e=Oa("number"!==typeof e?e:Pa(e,ja));
var m=ta(f),g="y"===a?"top":"left",l="y"===a?"bottom":"right",p=c.rects.reference[k]+c.rects.reference[a]-h[a]-c.rects.popper[k];h=h[a]-c.rects.reference[a];f=(f=fa(f))?"y"===a?f.clientHeight||0:f.clientWidth||0:0;h=f/2-m[k]/2+(p/2-h/2);k=E(e[g],M(h,f-m[k]-e[l]));c.modifiersData[d]=(b={},b[a]=k,b.centerOffset=k-h,b)}},effect:function(a){var b=a.state;a=a.options.element;a=void 0===a?"[data-popper-arrow]":a;if(null!=a){if("string"===typeof a&&(a=b.elements.popper.querySelector(a),!a))return;Ma(b.elements.popper,
a)&&(b.elements.arrow=a)}},requires:["popperOffsets"],requiresIfExists:["preventOverflow"]},{name:"hide",enabled:!0,phase:"main",requiresIfExists:["preventOverflow"],fn:function(a){var b=a.state;a=a.name;var c=b.rects.reference,d=b.rects.popper,e=b.modifiersData.preventOverflow,f=ia(b,{elementContext:"reference"}),h=ia(b,{altBoundary:!0});c=Xa(f,c);d=Xa(h,d,e);e=Ya(c);h=Ya(d);b.modifiersData[a]={referenceClippingOffsets:c,popperEscapeOffsets:d,isReferenceHidden:e,hasPopperEscaped:h};b.attributes.popper=
Object.assign({},b.attributes.popper,{"data-popper-reference-hidden":e,"data-popper-escaped":h})}}]});let P,ka=[],aa=[],qa=[],fb=[],Pb=Promise.resolve(),Fa=!1,Da=!1,Ea=new Set,ra=new Set,R;class K{$destroy(){O(this,1);this.$destroy=x}$on(a,b){let c=this.$$.callbacks[a]||(this.$$.callbacks[a]=[]);c.push(b);return()=>{let a=c.indexOf(b);-1!==a&&c.splice(a,1)}}$set(a){this.$$set&&0!==Object.keys(a).length&&(this.$$.skip_bound=!0,this.$$set(a),this.$$.skip_bound=!1)}}class Sb extends K{constructor(a){super();
J(this,a,Rb,Qb,I,{config:6,step:7})}}class gc extends K{constructor(a){super();J(this,a,Ub,Tb,I,{step:0})}}class $b extends K{constructor(a){super();J(this,a,Wb,Vb,I,{cancelIcon:0,step:2})}}class Zb extends K{constructor(a){super();J(this,a,Yb,Xb,I,{labelId:1,element:0,title:2})}}class ec extends K{constructor(a){super();J(this,a,bc,ac,I,{labelId:0,step:1})}}class fc extends K{constructor(a){super();J(this,a,dc,cc,I,{descriptionId:1,element:0,step:2})}}class kc extends K{constructor(a){super();J(this,
a,ic,hc,I,{descriptionId:0,labelId:1,step:2})}}class tc extends K{constructor(a){super();J(this,a,lc,jc,I,{classPrefix:11,element:0,descriptionId:2,firstFocusableElement:8,focusableElements:9,labelId:3,lastFocusableElement:10,step:4,dataStepId:1,getElement:12})}get getElement(){return this.$$.ctx[12]}}var sb=function(a,b){return b={exports:{}},a(b,b.exports),b.exports}(function(a,b){(function(){a.exports={polyfill:function(){function a(a,b){this.scrollLeft=a;this.scrollTop=b}function b(a){if(null===
a||"object"!==typeof a||void 0===a.behavior||"auto"===a.behavior||"instant"===a.behavior)return!0;if("object"===typeof a&&"smooth"===a.behavior)return!1;throw new TypeError("behavior member of ScrollOptions "+a.behavior+" is not a valid value for enumeration ScrollBehavior.");}function e(a,b){if("Y"===b)return a.clientHeight+r<a.scrollHeight;if("X"===b)return a.clientWidth+r<a.scrollWidth}function f(a,b){a=g.getComputedStyle(a,null)["overflow"+b];return"auto"===a||"scroll"===a}function h(a){var b=
e(a,"Y")&&f(a,"Y");a=e(a,"X")&&f(a,"X");return b||a}function k(a){var b=(q()-a.startTime)/468;var c=.5*(1-Math.cos(Math.PI*(1<b?1:b)));b=a.startX+(a.x-a.startX)*c;c=a.startY+(a.y-a.startY)*c;a.method.call(a.scrollable,b,c);b===a.x&&c===a.y||g.requestAnimationFrame(k.bind(g,a))}function m(b,c,d){var e=q();if(b===l.body){var f=g;var h=g.scrollX||g.pageXOffset;b=g.scrollY||g.pageYOffset;var u=n.scroll}else f=b,h=b.scrollLeft,b=b.scrollTop,u=a;k({scrollable:f,method:u,startTime:e,startX:h,startY:b,x:c,
y:d})}var g=window,l=document;if(!("scrollBehavior"in l.documentElement.style&&!0!==g.__forceSmoothScrollPolyfill__)){var p=g.HTMLElement||g.Element,n={scroll:g.scroll||g.scrollTo,scrollBy:g.scrollBy,elementScroll:p.prototype.scroll||a,scrollIntoView:p.prototype.scrollIntoView},q=g.performance&&g.performance.now?g.performance.now.bind(g.performance):Date.now,r=/MSIE |Trident\/|Edge\//.test(g.navigator.userAgent)?1:0;g.scroll=g.scrollTo=function(a,c){void 0!==a&&(!0===b(a)?n.scroll.call(g,void 0!==
a.left?a.left:"object"!==typeof a?a:g.scrollX||g.pageXOffset,void 0!==a.top?a.top:void 0!==c?c:g.scrollY||g.pageYOffset):m.call(g,l.body,void 0!==a.left?~~a.left:g.scrollX||g.pageXOffset,void 0!==a.top?~~a.top:g.scrollY||g.pageYOffset))};g.scrollBy=function(a,c){void 0!==a&&(b(a)?n.scrollBy.call(g,void 0!==a.left?a.left:"object"!==typeof a?a:0,void 0!==a.top?a.top:void 0!==c?c:0):m.call(g,l.body,~~a.left+(g.scrollX||g.pageXOffset),~~a.top+(g.scrollY||g.pageYOffset)))};p.prototype.scroll=p.prototype.scrollTo=
function(a,c){if(void 0!==a)if(!0===b(a)){if("number"===typeof a&&void 0===c)throw new SyntaxError("Value could not be converted");n.elementScroll.call(this,void 0!==a.left?~~a.left:"object"!==typeof a?~~a:this.scrollLeft,void 0!==a.top?~~a.top:void 0!==c?~~c:this.scrollTop)}else c=a.left,a=a.top,m.call(this,this,"undefined"===typeof c?this.scrollLeft:~~c,"undefined"===typeof a?this.scrollTop:~~a)};p.prototype.scrollBy=function(a,c){void 0!==a&&(!0===b(a)?n.elementScroll.call(this,void 0!==a.left?
~~a.left+this.scrollLeft:~~a+this.scrollLeft,void 0!==a.top?~~a.top+this.scrollTop:~~c+this.scrollTop):this.scroll({left:~~a.left+this.scrollLeft,top:~~a.top+this.scrollTop,behavior:a.behavior}))};p.prototype.scrollIntoView=function(a){if(!0===b(a))n.scrollIntoView.call(this,void 0===a?!0:a);else{for(a=this;a!==l.body&&!1===h(a);)a=a.parentNode||a.host;var c=a.getBoundingClientRect(),d=this.getBoundingClientRect();a!==l.body?(m.call(this,a,a.scrollLeft+d.left-c.left,a.scrollTop+d.top-c.top),"fixed"!==
g.getComputedStyle(a).position&&g.scrollBy({left:c.left,top:c.top,behavior:"smooth"})):g.scrollBy({left:d.left,top:d.top,behavior:"smooth"})}}}}}})()});sb.polyfill;sb.polyfill();class Ha extends Ga{constructor(a,b={}){super(a,b);this.tour=a;this.classPrefix=this.tour.options?$a(this.tour.options.classPrefix):"";this.styles=a.styles;Ka(this);this._setOptions(b);return this}cancel(){this.tour.cancel();this.trigger("cancel")}complete(){this.tour.complete();this.trigger("complete")}destroy(){this.tooltip&&
(this.tooltip.destroy(),this.tooltip=null);this.el instanceof HTMLElement&&this.el.parentNode&&(this.el.parentNode.removeChild(this.el),this.el=null);this._updateStepTargetOnHide();this.trigger("destroy")}getTour(){return this.tour}hide(){this.tour.modal.hide();this.trigger("before-hide");this.el&&(this.el.hidden=!0);this._updateStepTargetOnHide();this.trigger("hide")}isCentered(){let a=Aa(this);return!a.element||!a.on}isOpen(){return!(!this.el||this.el.hidden)}show(){if(W(this.options.beforeShowPromise)){let a=
this.options.beforeShowPromise();if(void 0!==a)return a.then(()=>this._show())}this._show()}updateStepOptions(a){Object.assign(this.options,a);this.shepherdElementComponent&&this.shepherdElementComponent.$set({step:this})}getElement(){return this.el}getTarget(){return this.target}_createTooltipContent(){this.shepherdElementComponent=new tc({target:this.tour.options.stepsContainer||document.body,props:{classPrefix:this.classPrefix,descriptionId:`${this.id}-description`,labelId:`${this.id}-label`,step:this,
styles:this.styles}});return this.shepherdElementComponent.getElement()}_scrollTo(a){let {element:b}=Aa(this);W(this.options.scrollToHandler)?this.options.scrollToHandler(b):b instanceof Element&&"function"===typeof b.scrollIntoView&&b.scrollIntoView(a)}_getClassOptions(a){var b=this.tour&&this.tour.options&&this.tour.options.defaultStepOptions;b=b&&b.classes?b.classes:"";a=[...(a.classes?a.classes:"").split(" "),...b.split(" ")];a=new Set(a);return Array.from(a).join(" ").trim()}_setOptions(a={}){let b=
this.tour&&this.tour.options&&this.tour.options.defaultStepOptions;b=rc({},b||{});this.options=Object.assign({arrow:!0},b,a);let {when:c}=this.options;this.options.classes=this._getClassOptions(a);this.destroy();this.id=this.options.id||`step-${Ba()}`;c&&Object.keys(c).forEach(a=>{this.on(a,c[a],this)})}_setupElements(){void 0!==this.el&&this.destroy();this.el=this._createTooltipContent();this.options.advanceOn&&xb(this);{this.tooltip&&this.tooltip.destroy();let a=Aa(this),b=a.element,c=Nb(a,this);
this.isCentered()&&(b=document.body,this.shepherdElementComponent.getElement().classList.add("shepherd-centered"));this.tooltip=sc(b,this.el,c);this.target=a.element}}_show(){this.trigger("before-show");this._setupElements();this.tour.modal||this.tour._setupModal();this.tour.modal.setupForStep(this);this._styleTargetElementForStep(this);this.el.hidden=!1;this.options.scrollTo&&setTimeout(()=>{this._scrollTo(this.options.scrollTo)});this.el.hidden=!1;let a=this.shepherdElementComponent.getElement(),
b=this.target||document.body;b.classList.add(`${this.classPrefix}shepherd-enabled`);b.classList.add(`${this.classPrefix}shepherd-target`);a.classList.add("shepherd-enabled");this.trigger("show")}_styleTargetElementForStep(a){let b=a.target;b&&(a.options.highlightClass&&b.classList.add(a.options.highlightClass),!1===a.options.canClickTarget&&b.classList.add("shepherd-target-click-disabled"))}_updateStepTargetOnHide(){let a=this.target||document.body;this.options.highlightClass&&a.classList.remove(this.options.highlightClass);
a.classList.remove("shepherd-target-click-disabled",`${this.classPrefix}shepherd-enabled`,`${this.classPrefix}shepherd-target`)}}class uc extends K{constructor(a){super();J(this,a,pc,oc,I,{element:0,openingProperties:4,getElement:5,closeModalOpening:6,hide:7,positionModal:8,setupForStep:9,show:10})}get getElement(){return this.$$.ctx[5]}get closeModalOpening(){return this.$$.ctx[6]}get hide(){return this.$$.ctx[7]}get positionModal(){return this.$$.ctx[8]}get setupForStep(){return this.$$.ctx[9]}get show(){return this.$$.ctx[10]}}
let ba=new Ga;class vc extends Ga{constructor(a={}){super(a);Ka(this);this.options=Object.assign({},{exitOnEsc:!0,keyboardNavigation:!0},a);this.classPrefix=$a(this.options.classPrefix);this.steps=[];this.addSteps(this.options.steps);"active cancel complete inactive show start".split(" ").map(a=>{(a=>{this.on(a,b=>{b=b||{};b.tour=this;ba.trigger(a,b)})})(a)});this._setTourID();return this}addStep(a,b){a instanceof Ha?a.tour=this:a=new Ha(this,a);void 0!==b?this.steps.splice(b,0,a):this.steps.push(a);
return a}addSteps(a){Array.isArray(a)&&a.forEach(a=>{this.addStep(a)});return this}back(){let a=this.steps.indexOf(this.currentStep);this.show(a-1,!1)}cancel(){this.options.confirmCancel?window.confirm(this.options.confirmCancelMessage||"Are you sure you want to stop the tour?")&&this._done("cancel"):this._done("cancel")}complete(){this._done("complete")}getById(a){return this.steps.find(b=>b.id===a)}getCurrentStep(){return this.currentStep}hide(){let a=this.getCurrentStep();if(a)return a.hide()}isActive(){return ba.activeTour===
this}next(){let a=this.steps.indexOf(this.currentStep);a===this.steps.length-1?this.complete():this.show(a+1,!0)}removeStep(a){let b=this.getCurrentStep();this.steps.some((b,d)=>{if(b.id===a)return b.isOpen()&&b.hide(),b.destroy(),this.steps.splice(d,1),!0});b&&b.id===a&&(this.currentStep=void 0,this.steps.length?this.show(0):this.cancel())}show(a=0,b=!0){if(a=da(a)?this.getById(a):this.steps[a])this._updateStateBeforeShow(),W(a.options.showOn)&&!a.options.showOn()?this._skipStep(a,b):(this.trigger("show",
{step:a,previous:this.currentStep}),this.currentStep=a,a.show())}start(){this.trigger("start");this.focusedElBeforeOpen=document.activeElement;this.currentStep=null;this._setupModal();this._setupActiveTour();this.next()}_done(a){let b=this.steps.indexOf(this.currentStep);Array.isArray(this.steps)&&this.steps.forEach(a=>a.destroy());mc(this);this.trigger(a,{index:b});ba.activeTour=null;this.trigger("inactive",{tour:this});this.modal&&this.modal.hide();"cancel"!==a&&"complete"!==a||!this.modal||(a=
document.querySelector(".shepherd-modal-overlay-container"))&&a.remove();this.focusedElBeforeOpen instanceof HTMLElement&&this.focusedElBeforeOpen.focus()}_setupActiveTour(){this.trigger("active",{tour:this});ba.activeTour=this}_setupModal(){this.modal=new uc({target:this.options.modalContainer||document.body,props:{classPrefix:this.classPrefix,styles:this.styles}})}_skipStep(a,b){a=this.steps.indexOf(a);this.show(b?a+1:a-1,b)}_updateStateBeforeShow(){this.currentStep&&this.currentStep.hide();this.isActive()||
this._setupActiveTour()}_setTourID(){this.id=`${this.options.tourName||"tour"}--${Ba()}`}}Object.assign(ba,{Tour:vc,Step:Ha});return ba})

;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Backbone, Drupal, settings, document, Shepherd) {
  var queryString = decodeURI(window.location.search);
  Drupal.behaviors.tour = {
    attach: function attach(context) {
      once('tour', 'body').forEach(function () {
        var model = new Drupal.tour.models.StateModel();
        new Drupal.tour.views.ToggleTourView({
          el: $(context).find('#toolbar-tab-tour'),
          model: model
        });
        model.on('change:isActive', function (tourModel, isActive) {
          $(document).trigger(isActive ? 'drupalTourStarted' : 'drupalTourStopped');
        });

        if (settings._tour_internal) {
          model.set('tour', settings._tour_internal);
        }

        if (/tour=?/i.test(queryString)) {
          model.set('isActive', true);
        }
      });
    }
  };
  Drupal.tour = Drupal.tour || {
    models: {},
    views: {}
  };
  Drupal.tour.models.StateModel = Backbone.Model.extend({
    defaults: {
      tour: [],
      isActive: false,
      activeTour: []
    }
  });
  Drupal.tour.views.ToggleTourView = Backbone.View.extend({
    events: {
      click: 'onClick'
    },
    initialize: function initialize() {
      this.listenTo(this.model, 'change:tour change:isActive', this.render);
      this.listenTo(this.model, 'change:isActive', this.toggleTour);
    },
    render: function render() {
      this.$el.toggleClass('hidden', this._getTour().length === 0);
      var isActive = this.model.get('isActive');
      this.$el.find('button').toggleClass('is-active', isActive).attr('aria-pressed', isActive);
      return this;
    },
    toggleTour: function toggleTour() {
      if (this.model.get('isActive')) {
        this._removeIrrelevantTourItems(this._getTour());

        var tourItems = this.model.get('tour');
        var that = this;

        if (tourItems.length) {
          settings.tourShepherdConfig.defaultStepOptions.popperOptions.modifiers.push({
            name: 'moveArrowJoyridePosition',
            enabled: true,
            phase: 'write',
            fn: function fn(_ref) {
              var state = _ref.state;
              var arrow = state.elements.arrow;
              var placement = state.placement;

              if (arrow && /^top|bottom/.test(placement) && /-start|-end$/.test(placement)) {
                var horizontalPosition = placement.split('-')[1];
                var offset = horizontalPosition === 'start' ? 28 : state.elements.popper.clientWidth - 56;
                arrow.style.transform = "translate3d(".concat(offset, "px, 0px, 0px)");
              }
            }
          });
          var shepherdTour = new Shepherd.Tour(settings.tourShepherdConfig);
          shepherdTour.on('cancel', function () {
            that.model.set('isActive', false);
          });
          shepherdTour.on('complete', function () {
            that.model.set('isActive', false);
          });
          tourItems.forEach(function (tourStepConfig, index) {
            var tourItemOptions = {
              title: tourStepConfig.title ? Drupal.checkPlain(tourStepConfig.title) : null,
              text: function text() {
                return Drupal.theme('tourItemContent', tourStepConfig);
              },
              attachTo: tourStepConfig.attachTo,
              buttons: [Drupal.tour.nextButton(shepherdTour, tourStepConfig)],
              classes: tourStepConfig.classes,
              index: index
            };
            tourItemOptions.when = {
              show: function show() {
                var nextButton = shepherdTour.currentStep.el.querySelector('footer button');
                nextButton.focus();

                if (Drupal.tour.hasOwnProperty('convertToJoyrideMarkup')) {
                  Drupal.tour.convertToJoyrideMarkup(shepherdTour);
                }
              }
            };
            shepherdTour.addStep(tourItemOptions);
          });
          shepherdTour.start();
          this.model.set({
            isActive: true,
            activeTour: shepherdTour
          });
        }
      } else {
        this.model.get('activeTour').cancel();
        this.model.set({
          isActive: false,
          activeTour: []
        });
      }
    },
    onClick: function onClick(event) {
      this.model.set('isActive', !this.model.get('isActive'));
      event.preventDefault();
      event.stopPropagation();
    },
    _getTour: function _getTour() {
      return this.model.get('tour');
    },
    _removeIrrelevantTourItems: function _removeIrrelevantTourItems(tourItems) {
      var tips = /tips=([^&]+)/.exec(queryString);
      var filteredTour = tourItems.filter(function (tourItem) {
        if (tips && tourItem.hasOwnProperty('classes') && tourItem.classes.indexOf(tips[1]) === -1) {
          return false;
        }

        return !(tourItem.selector && !document.querySelector(tourItem.selector));
      });

      if (tourItems.length !== filteredTour.length) {
        filteredTour.forEach(function (filteredTourItem, filteredTourItemId) {
          filteredTour[filteredTourItemId].counter = Drupal.t('!tour_item of !total', {
            '!tour_item': filteredTourItemId + 1,
            '!total': filteredTour.length
          });

          if (filteredTourItemId === filteredTour.length - 1) {
            filteredTour[filteredTourItemId].cancelText = Drupal.t('End tour');
          }
        });
        this.model.set('tour', filteredTour);
      }
    }
  });

  Drupal.tour.nextButton = function (shepherdTour, tourStepConfig) {
    return {
      classes: 'button button--primary',
      text: tourStepConfig.cancelText ? tourStepConfig.cancelText : Drupal.t('Next'),
      action: tourStepConfig.cancelText ? shepherdTour.cancel : shepherdTour.next
    };
  };

  Drupal.theme.tourItemContent = function (tourStepConfig) {
    return "".concat(tourStepConfig.body, "<div class=\"tour-progress\">").concat(tourStepConfig.counter, "</div>");
  };
})(jQuery, Backbone, Drupal, drupalSettings, document, window.Shepherd);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

(function ($, Drupal, _ref) {
  var tabbable = _ref.tabbable,
      isTabbable = _ref.isTabbable;

  function TabbingManager() {
    this.stack = [];
  }

  function TabbingContext(options) {
    $.extend(this, {
      level: null,
      $tabbableElements: $(),
      $disabledElements: $(),
      released: false,
      active: false,
      trapFocus: false
    }, options);
  }

  $.extend(TabbingManager.prototype, {
    constrain: function constrain(elements) {
      var _ref2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
          _ref2$trapFocus = _ref2.trapFocus,
          trapFocus = _ref2$trapFocus === void 0 ? false : _ref2$trapFocus;

      var il = this.stack.length;

      for (var i = 0; i < il; i++) {
        this.stack[i].deactivate();
      }

      var tabbableElements = [];
      $(elements).each(function (index, rootElement) {
        tabbableElements = [].concat(_toConsumableArray(tabbableElements), _toConsumableArray(tabbable(rootElement)));

        if (isTabbable(rootElement)) {
          tabbableElements = [].concat(_toConsumableArray(tabbableElements), [rootElement]);
        }
      });
      var tabbingContext = new TabbingContext({
        level: this.stack.length,
        $tabbableElements: $(tabbableElements),
        trapFocus: trapFocus
      });
      this.stack.push(tabbingContext);
      tabbingContext.activate();
      $(document).trigger('drupalTabbingConstrained', tabbingContext);
      return tabbingContext;
    },
    release: function release() {
      var toActivate = this.stack.length - 1;

      while (toActivate >= 0 && this.stack[toActivate].released) {
        toActivate--;
      }

      this.stack.splice(toActivate + 1);

      if (toActivate >= 0) {
        this.stack[toActivate].activate();
      }
    },
    activate: function activate(tabbingContext) {
      var $set = tabbingContext.$tabbableElements;
      var level = tabbingContext.level;
      var $disabledSet = $(tabbable(document.body)).not($set);
      tabbingContext.$disabledElements = $disabledSet;
      var il = $disabledSet.length;

      for (var i = 0; i < il; i++) {
        this.recordTabindex($disabledSet.eq(i), level);
      }

      $disabledSet.prop('tabindex', -1).prop('autofocus', false);
      var $hasFocus = $set.filter('[autofocus]').eq(-1);

      if ($hasFocus.length === 0) {
        $hasFocus = $set.eq(0);
      }

      $hasFocus.trigger('focus');

      if ($set.length && tabbingContext.trapFocus) {
        $set.last().on('keydown.focus-trap', function (event) {
          if (event.key === 'Tab' && !event.shiftKey) {
            event.preventDefault();
            $set.first().focus();
          }
        });
        $set.first().on('keydown.focus-trap', function (event) {
          if (event.key === 'Tab' && event.shiftKey) {
            event.preventDefault();
            $set.last().focus();
          }
        });
      }
    },
    deactivate: function deactivate(tabbingContext) {
      var $set = tabbingContext.$disabledElements;
      var level = tabbingContext.level;
      var il = $set.length;
      tabbingContext.$tabbableElements.first().off('keydown.focus-trap');
      tabbingContext.$tabbableElements.last().off('keydown.focus-trap');

      for (var i = 0; i < il; i++) {
        this.restoreTabindex($set.eq(i), level);
      }
    },
    recordTabindex: function recordTabindex($el, level) {
      var tabInfo = $el.data('drupalOriginalTabIndices') || {};
      tabInfo[level] = {
        tabindex: $el[0].getAttribute('tabindex'),
        autofocus: $el[0].hasAttribute('autofocus')
      };
      $el.data('drupalOriginalTabIndices', tabInfo);
    },
    restoreTabindex: function restoreTabindex($el, level) {
      var tabInfo = $el.data('drupalOriginalTabIndices');

      if (tabInfo && tabInfo[level]) {
        var data = tabInfo[level];

        if (data.tabindex) {
          $el[0].setAttribute('tabindex', data.tabindex);
        } else {
          $el[0].removeAttribute('tabindex');
        }

        if (data.autofocus) {
          $el[0].setAttribute('autofocus', 'autofocus');
        }

        if (level === 0) {
          $el.removeData('drupalOriginalTabIndices');
        } else {
          var levelToDelete = level;

          while (tabInfo.hasOwnProperty(levelToDelete)) {
            delete tabInfo[levelToDelete];
            levelToDelete++;
          }

          $el.data('drupalOriginalTabIndices', tabInfo);
        }
      }
    }
  });
  $.extend(TabbingContext.prototype, {
    release: function release() {
      if (!this.released) {
        this.deactivate();
        this.released = true;
        Drupal.tabbingManager.release(this);
        $(document).trigger('drupalTabbingContextReleased', this);
      }
    },
    activate: function activate() {
      if (!this.active && !this.released) {
        this.active = true;
        Drupal.tabbingManager.activate(this);
        $(document).trigger('drupalTabbingContextActivated', this);
      }
    },
    deactivate: function deactivate() {
      if (this.active) {
        this.active = false;
        Drupal.tabbingManager.deactivate(this);
        $(document).trigger('drupalTabbingContextDeactivated', this);
      }
    }
  });

  if (Drupal.tabbingManager) {
    return;
  }

  Drupal.tabbingManager = new TabbingManager();
})(jQuery, Drupal, window.tabbable);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, Backbone) {
  var strings = {
    tabbingReleased: Drupal.t('Tabbing is no longer constrained by the Contextual module.'),
    tabbingConstrained: Drupal.t('Tabbing is constrained to a set of @contextualsCount and the edit mode toggle.'),
    pressEsc: Drupal.t('Press the esc key to exit.')
  };

  function initContextualToolbar(context) {
    if (!Drupal.contextual || !Drupal.contextual.collection) {
      return;
    }

    var contextualToolbar = Drupal.contextualToolbar;
    contextualToolbar.model = new contextualToolbar.StateModel({
      isViewing: localStorage.getItem('Drupal.contextualToolbar.isViewing') !== 'false'
    }, {
      contextualCollection: Drupal.contextual.collection
    });
    var viewOptions = {
      el: $('.toolbar .toolbar-bar .contextual-toolbar-tab'),
      model: contextualToolbar.model,
      strings: strings
    };
    new contextualToolbar.VisualView(viewOptions);
    new contextualToolbar.AuralView(viewOptions);
  }

  Drupal.behaviors.contextualToolbar = {
    attach: function attach(context) {
      if (once('contextualToolbar-init', 'body').length) {
        initContextualToolbar(context);
      }
    }
  };
  Drupal.contextualToolbar = {
    model: null
  };
})(jQuery, Drupal, Backbone);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Drupal, Backbone) {
  Drupal.contextualToolbar.StateModel = Backbone.Model.extend({
    defaults: {
      isViewing: true,
      isVisible: false,
      contextualCount: 0,
      tabbingContext: null
    },
    initialize: function initialize(attrs, options) {
      this.listenTo(options.contextualCollection, 'reset remove add', this.countContextualLinks);
      this.listenTo(options.contextualCollection, 'add', this.lockNewContextualLinks);
      this.listenTo(this, 'change:contextualCount', this.updateVisibility);
      this.listenTo(this, 'change:isViewing', function (model, isViewing) {
        options.contextualCollection.each(function (contextualModel) {
          contextualModel.set('isLocked', !isViewing);
        });
      });
    },
    countContextualLinks: function countContextualLinks(contextualModel, contextualCollection) {
      this.set('contextualCount', contextualCollection.length);
    },
    lockNewContextualLinks: function lockNewContextualLinks(contextualModel, contextualCollection) {
      if (!this.get('isViewing')) {
        contextualModel.set('isLocked', true);
      }
    },
    updateVisibility: function updateVisibility() {
      this.set('isVisible', this.get('contextualCount') > 0);
    }
  });
})(Drupal, Backbone);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, Backbone, _) {
  Drupal.contextualToolbar.AuralView = Backbone.View.extend({
    announcedOnce: false,
    initialize: function initialize(options) {
      this.options = options;
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'change:isViewing', this.manageTabbing);
      $(document).on('keyup', _.bind(this.onKeypress, this));
      this.manageTabbing();
    },
    render: function render() {
      this.$el.find('button').attr('aria-pressed', !this.model.get('isViewing'));
      return this;
    },
    manageTabbing: function manageTabbing() {
      var tabbingContext = this.model.get('tabbingContext');

      if (tabbingContext) {
        if (tabbingContext.active) {
          Drupal.announce(this.options.strings.tabbingReleased);
        }

        tabbingContext.release();
      }

      if (!this.model.get('isViewing')) {
        tabbingContext = Drupal.tabbingManager.constrain($('.contextual-toolbar-tab, .contextual'));
        this.model.set('tabbingContext', tabbingContext);
        this.announceTabbingConstraint();
        this.announcedOnce = true;
      }
    },
    announceTabbingConstraint: function announceTabbingConstraint() {
      var strings = this.options.strings;
      Drupal.announce(Drupal.formatString(strings.tabbingConstrained, {
        '@contextualsCount': Drupal.formatPlural(Drupal.contextual.collection.length, '@count contextual link', '@count contextual links')
      }));
      Drupal.announce(strings.pressEsc);
    },
    onKeypress: function onKeypress(event) {
      if (!this.announcedOnce && event.keyCode === 9 && !this.model.get('isViewing')) {
        this.announceTabbingConstraint();
        this.announcedOnce = true;
      }

      if (event.keyCode === 27) {
        this.model.set('isViewing', true);
      }
    }
  });
})(jQuery, Drupal, Backbone, _);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Drupal, Backbone) {
  Drupal.contextualToolbar.VisualView = Backbone.View.extend({
    events: function events() {
      var touchEndToClick = function touchEndToClick(event) {
        event.preventDefault();
        event.target.click();
      };

      return {
        click: function click() {
          this.model.set('isViewing', !this.model.get('isViewing'));
        },
        touchend: touchEndToClick
      };
    },
    initialize: function initialize() {
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'change:isViewing', this.persist);
    },
    render: function render() {
      this.$el.toggleClass('hidden', !this.model.get('isVisible'));
      this.$el.find('button').toggleClass('is-active', !this.model.get('isViewing'));
      return this;
    },
    persist: function persist(model, isViewing) {
      if (!isViewing) {
        localStorage.setItem('Drupal.contextualToolbar.isViewing', 'false');
      } else {
        localStorage.removeItem('Drupal.contextualToolbar.isViewing');
      }
    }
  });
})(Drupal, Backbone);;
(function ($, Drupal) {
  Drupal.behaviors.adminToolbar = {
    attach: function (context, settings) {

      $('a.toolbar-icon', context).removeAttr('title');

      // Make the toolbar menu navigable with keyboard.
      $('ul.toolbar-menu li.menu-item--expanded a', context).on('focusin', function () {
        $('li.menu-item--expanded', context).removeClass('hover-intent');
        $(this).parents('li.menu-item--expanded').addClass('hover-intent');
      });

      $('ul.toolbar-menu li.menu-item a', context).keydown(function (e) {
        if ((e.shiftKey && (e.keyCode || e.which) == 9)) {
          if ($(this).parent('.menu-item').prev().hasClass('menu-item--expanded')) {
            $(this).parent('.menu-item').prev().addClass('hover-intent');
          }
        }
      });

      $('.toolbar-menu:first-child > .menu-item:not(.menu-item--expanded) a, .toolbar-tab > a', context).on('focusin', function () {
        $('.menu-item--expanded').removeClass('hover-intent');
      });

      $('.toolbar-menu:first-child > .menu-item', context).on('hover', function () {
        $(this, 'a').css("background: #fff;");
      });

      $('ul:not(.toolbar-menu)', context).on({
        mousemove: function () {
          $('li.menu-item--expanded').removeClass('hover-intent');
        },
        hover: function () {
          $('li.menu-item--expanded').removeClass('hover-intent');
        }
      });

      // Always hide the dropdown menu on mobile.
      if ($('body:not(.toolbar-fixed) #toolbar-item-administration-tray').hasClass('toolbar-tray-vertical')) {
        $('#toolbar-item-administration').removeClass('is-active');
        $('#toolbar-item-administration-tray').removeClass('is-active');
      };

    }
  };
})(jQuery, Drupal);
;
;/*!
 * hoverIntent v1.8.1 // 2014.08.11 // jQuery v1.9.1+
 * http://briancherne.github.io/jquery-hoverIntent/
 *
 * You may use hoverIntent under the terms of the MIT license. Basically that
 * means you are free to use hoverIntent as long as this header is left intact.
 * Copyright 2007, 2014 Brian Cherne
 */

/* hoverIntent is similar to jQuery's built-in "hover" method except that
 * instead of firing the handlerIn function immediately, hoverIntent checks
 * to see if the user's mouse has slowed down (beneath the sensitivity
 * threshold) before firing the event. The handlerOut function is only
 * called after a matching handlerIn.
 *
 * // basic usage ... just like .hover()
 * .hoverIntent( handlerIn, handlerOut )
 * .hoverIntent( handlerInOut )
 *
 * // basic usage ... with event delegation!
 * .hoverIntent( handlerIn, handlerOut, selector )
 * .hoverIntent( handlerInOut, selector )
 *
 * // using a basic configuration object
 * .hoverIntent( config )
 *
 * @param  handlerIn   function OR configuration object
 * @param  handlerOut  function OR selector for delegation OR undefined
 * @param  selector    selector OR undefined
 * @author Brian Cherne <brian(at)cherne(dot)net>
 */(function (factory) {
  'use strict';
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else if (jQuery && !jQuery.fn.hoverIntent) {
    factory(jQuery);
  }
})(function ($) {
  'use strict';

  // default configuration values
  var _cfg = {
    interval: 100,
    sensitivity: 6,
    timeout: 0
  };

  // counter used to generate an ID for each instance
  var INSTANCE_COUNT = 0;

  // current X and Y position of mouse, updated during mousemove tracking (shared across instances)
  var cX, cY;

  // saves the current pointer position coordinates based on the given mousemove event
  var track = function (ev) {
    cX = ev.pageX;
    cY = ev.pageY;
  };

  // compares current and previous mouse positions
  var compare = function (ev,$el,s,cfg) {
    // compare mouse positions to see if pointer has slowed enough to trigger `over` function
    if ( Math.sqrt( (s.pX - cX) * (s.pX - cX) + (s.pY - cY) * (s.pY - cY) ) < cfg.sensitivity ) {
      $el.off(s.event,track);
      delete s.timeoutId;
      // set hoverIntent state as active for this element (permits `out` handler to trigger)
      s.isActive = true;
      // overwrite old mouseenter event coordinates with most recent pointer position
      ev.pageX = cX; ev.pageY = cY;
      // clear coordinate data from state object
      delete s.pX; delete s.pY;
      return cfg.over.apply($el[0],[ev]);
    } else {
      // set previous coordinates for next comparison
      s.pX = cX; s.pY = cY;
      // use self-calling timeout, guarantees intervals are spaced out properly (avoids JavaScript timer bugs)
      s.timeoutId = setTimeout( function () {compare(ev, $el, s, cfg);} , cfg.interval );
    }
  };

  // triggers given `out` function at configured `timeout` after a mouseleave and clears state
  var delay = function (ev,$el,s,out) {
    delete $el.data('hoverIntent')[s.id];
    return out.apply($el[0],[ev]);
  };

  $.fn.hoverIntent = function (handlerIn,handlerOut,selector) {
    // instance ID, used as a key to store and retrieve state information on an element
    var instanceId = INSTANCE_COUNT++;

    // extend the default configuration and parse parameters
    var cfg = $.extend({}, _cfg);
    if ( $.isPlainObject(handlerIn) ) {
      cfg = $.extend(cfg, handlerIn);
      if ( !$.isFunction(cfg.out) ) {
        cfg.out = cfg.over;
      }
    } else if ( $.isFunction(handlerOut) ) {
      cfg = $.extend(cfg, { over: handlerIn, out: handlerOut, selector: selector } );
    } else {
      cfg = $.extend(cfg, { over: handlerIn, out: handlerIn, selector: handlerOut } );
    }

    // A private function for handling mouse 'hovering'
    var handleHover = function (e) {
      // cloned event to pass to handlers (copy required for event object to be passed in IE)
      var ev = $.extend({},e);

      // the current target of the mouse event, wrapped in a jQuery object
      var $el = $(this);

      // read hoverIntent data from element (or initialize if not present)
      var hoverIntentData = $el.data('hoverIntent');
      if (!hoverIntentData) { $el.data('hoverIntent', (hoverIntentData = {})); }

      // read per-instance state from element (or initialize if not present)
      var state = hoverIntentData[instanceId];
      if (!state) { hoverIntentData[instanceId] = state = { id: instanceId }; }

      // state properties:
      // id = instance ID, used to clean up data
      // timeoutId = timeout ID, reused for tracking mouse position and delaying "out" handler
      // isActive = plugin state, true after `over` is called just until `out` is called
      // pX, pY = previously-measured pointer coordinates, updated at each polling interval
      // event = string representing the namespaced event used for mouse tracking

      // clear any existing timeout
      if (state.timeoutId) { state.timeoutId = clearTimeout(state.timeoutId); }

      // namespaced event used to register and unregister mousemove tracking
      var mousemove = state.event = 'mousemove.hoverIntent.hoverIntent' + instanceId;

      // handle the event, based on its type
      if (e.type === 'mouseenter') {
        // do nothing if already active
        if (state.isActive) { return; }
        // set "previous" X and Y position based on initial entry point
        state.pX = ev.pageX; state.pY = ev.pageY;
        // update "current" X and Y position based on mousemove
        $el.off(mousemove,track).on(mousemove,track);
        // start polling interval (self-calling timeout) to compare mouse coordinates over time
        state.timeoutId = setTimeout( function () {compare(ev,$el,state,cfg);} , cfg.interval );
      } else { // "mouseleave"
        // do nothing if not already active
        if (!state.isActive) { return; }
        // unbind expensive mousemove event
        $el.off(mousemove,track);
        // if hoverIntent state is true, then call the mouseOut function after the specified delay
        state.timeoutId = setTimeout( function () {delay(ev,$el,state,cfg.out);} , cfg.timeout );
      }
    };

    // listen for mouseenter and mouseleave
    return this.on({'mouseenter.hoverIntent':handleHover,'mouseleave.hoverIntent':handleHover}, cfg.selector);
  };
});
;
(function ($) {
  $(document).ready(function () {
    $('.toolbar-tray-horizontal li.menu-item--expanded, .toolbar-tray-horizontal ul li.menu-item--expanded .menu-item').hoverIntent({
      over: function () {
        // At the current depth, we should delete all "hover-intent" classes.
        // Other wise we get unwanted behaviour where menu items are expanded while already in hovering other ones.
        $(this).parent().find('li').removeClass('hover-intent');
        $(this).addClass('hover-intent');
      },
      out: function () {
        $(this).removeClass('hover-intent');
      },
      timeout: 250
    });
  });
})(jQuery);
;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, drupalSettings) {
  var pathInfo = drupalSettings.path;
  var escapeAdminPath = sessionStorage.getItem('escapeAdminPath');
  var windowLocation = window.location;

  if (!pathInfo.currentPathIsAdmin && !/destination=/.test(windowLocation.search)) {
    sessionStorage.setItem('escapeAdminPath', windowLocation);
  }

  Drupal.behaviors.escapeAdmin = {
    attach: function attach() {
      var toolbarEscape = once('escapeAdmin', '[data-toolbar-escape-admin]');

      if (toolbarEscape.length && pathInfo.currentPathIsAdmin) {
        var $toolbarEscape = $(toolbarEscape);

        if (escapeAdminPath !== null) {
          $toolbarEscape.attr('href', escapeAdminPath);
        } else {
          $toolbarEscape.text(Drupal.t('Home'));
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings);;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal) {
  function init(tab) {
    var $tab = $(tab);
    var $target = $tab.find('[data-drupal-nav-tabs-target]');
    var isCollapsible = $tab.hasClass('is-collapsible');

    function openMenu(e) {
      $target.toggleClass('is-open');
    }

    function handleResize(e) {
      $tab.addClass('is-horizontal');
      var $tabs = $tab.find('.tabs');
      var isHorizontal = $tabs.outerHeight() <= $tabs.find('.tabs__tab').outerHeight();
      $tab.toggleClass('is-horizontal', isHorizontal);

      if (isCollapsible) {
        $tab.toggleClass('is-collapse-enabled', !isHorizontal);
      }

      if (isHorizontal) {
        $target.removeClass('is-open');
      }
    }

    $tab.addClass('position-container is-horizontal-enabled');
    $tab.on('click.tabs', '[data-drupal-nav-tabs-trigger]', openMenu);
    $(window).on('resize.tabs', Drupal.debounce(handleResize, 150)).trigger('resize.tabs');
  }

  Drupal.behaviors.navTabs = {
    attach: function attach(context, settings) {
      var notSmartPhone = window.matchMedia('(min-width: 300px)');

      if (notSmartPhone.matches) {
        once('nav-tabs', '[data-drupal-nav-tabs]', context).forEach(init);
      }
    }
  };
})(jQuery, Drupal);;
