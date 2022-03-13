/*!
 * jQuery Once v2.2.3 - http://github.com/robloach/jquery-once
 * @license MIT, GPL-2.0
 *   http://opensource.org/licenses/MIT
 *   http://opensource.org/licenses/GPL-2.0
 */
(function(e){"use strict";if(typeof exports==="object"&&typeof exports.nodeName!=="string"){e(require("jquery"))}else if(typeof define==="function"&&define.amd){define(["jquery"],e)}else{e(jQuery)}})(function(t){"use strict";var r=function(e){e=e||"once";if(typeof e!=="string"){throw new TypeError("The jQuery Once id parameter must be a string")}return e};t.fn.once=function(e){var n="jquery-once-"+r(e);return this.filter(function(){return t(this).data(n)!==true}).data(n,true)};t.fn.removeOnce=function(e){return this.findOnce(e).removeData("jquery-once-"+r(e))};t.fn.findOnce=function(e){var n="jquery-once-"+r(e);return this.filter(function(){return t(this).data(n)===true})}});

/**
 * @file
 * JavaScript integration between Highcharts and Drupal.
 */
(function ($) {
  'use strict';

  Drupal.behaviors.chartsHighcharts = {
    attach: function (context, settings) {

      $('.charts-highchart').once().each(function () {
        if ($(this).attr('data-chart')) {
          var highcharts = $(this).attr('data-chart');
          var hc = JSON.parse(highcharts);
          if (hc.chart.type === 'pie') {
            delete hc.plotOptions.bar;
            hc.plotOptions.pie = {
              allowPointSelect: true,
              cursor: 'pointer',
              showInLegend: true,
              dataLabels: {
                enabled: hc.plotOptions.pie.dataLabels.enabled,
                format: '{point.y:,.0f}'
              },
              depth: hc.plotOptions.pie.depth
            };
            hc.legend.enabled = true;
            hc.legend.labelFormatter = function () {
              var legendIndex = this.index;
              return this.series.chart.axes[0].categories[legendIndex];
            };

            hc.tooltip.formatter = function () {
              var sliceIndex = this.point.index;
              var sliceName = this.series.chart.axes[0].categories[sliceIndex];
              var sliceSuffix = this.series.tooltipOptions.valueSuffix;
              return '' + sliceName +
                  ' : ' + this.y + sliceSuffix;
            };

          }
          // Allow Highcharts to use the default formatter for y-axis labels.
          var suffix = hc.yAxis[0].labels.suffix;
          var prefix = hc.yAxis[0].labels.prefix;
          hc.yAxis[0].labels = {
            formatter: function () {
              return prefix + this.axis.defaultLabelFormatter.call(this) + suffix;
            }
          };
          // If there's a secondary y-axis, format its label as well.
          if (hc.yAxis[1]) {
            var suffix1 = hc.yAxis[1].labels.suffix;
            var prefix1 = hc.yAxis[1].labels.prefix;
            hc.yAxis[1].labels = {
              formatter: function () {
                return prefix1 + this.axis.defaultLabelFormatter.call(this) + suffix1;
              }
            };
            if (hc.series[1]) {
              hc.series[1].yAxis = 1;
            }
          }

          $(this).highcharts(hc);
        }
      });
    }
  };
}(jQuery));
;
;
;
;
;
;
;
