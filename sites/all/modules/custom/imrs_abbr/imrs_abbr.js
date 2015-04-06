/**
 * Created by shaun on 21-May-2014.
 */

var $j = jQuery.noConflict();

function imrs_abbr_markup_abbreviations(el) {
  if (el.nodeType == 3) {
    var abbr, abbrWithSubscripts, i;
    el = $j(el);

    var text = el.text();
    var html;

    if (text) {
      html = text;

      //// Add <abbr> tags to chemical elements:
      //for (abbr in Drupal.settings.elements) {
      //  // Skip those with only one letter for now, 'C' in particular has multiple uses.
      //  if (abbr.length > 1) {
      //    html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + Drupal.settings.elements[abbr][0] + "'>" + abbr + "</abbr>");
      //  }
      //}
      //
      //// Add <abbr> and <sub> tags to chemical compounds:
      //for (abbr in Drupal.settings.compounds) {
      //  // Skip 'CO' when it means Colorado:
      //  if (abbr == 'CO' && location.pathname == '/references') {
      //    continue;
      //  }
      //
      //  // Wrap every sequence of digits in <sub> tags:
      //  abbrWithSubscripts = abbr.replace(/(\d+)/g, "<sub>$1</sub>");
      //
      //  // Add <abbr> tags:
      //  abbrWithSubscripts = "<abbr title='" + Drupal.settings.compounds[abbr][0] + "'>" + abbrWithSubscripts + "</abbr>";
      //
      //  html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), abbrWithSubscripts);
      //}

      // Add <abbr> tags to acronyms:
      for (abbr in Drupal.settings.acronyms) {
        html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + Drupal.settings.acronyms[abbr][0] + "'>" + abbr + "</abbr>");
      }

      // Replace any references:
      i = 1;
      for (abbr in Drupal.settings.references) {
        html = html.replace("[" + abbr + "]", "<sup title='" + Drupal.settings.references[abbr].ref + "'>[<a href='/references#ref" + i + "'>" + i + "</a>]</sup>");
        i++;
      }

      // Remove any '~~' strings, which is used to break acronyms in acronyms to prevent double-encoding:
      html = html.replace('~~', '');

      if (html != text) {
        el.replaceWith(html);
      }
    }
  }

  // Recurse into children:
  $j(el).contents().each(function(i, el) {
    imrs_abbr_markup_abbreviations(el);
  });
}

// On document load:
$j(function() {
  // Add tooltips to <abbr> and <sup> elements:
  $j('.node--book--full').tooltip({
    items: "abbr, sup",
    position: {
      my: "left-5 bottom-3",
      at: "left top"
    },
    show: false
  });

  var bookPages = $j('.node--book--full');
  if (bookPages.length) {
    imrs_abbr_markup_abbreviations(bookPages.get(0));
  }

  // Highlight the desired reference:
  if (location.hash) {
    $j(location.hash).css('background-color', 'yellow');
  }
});
