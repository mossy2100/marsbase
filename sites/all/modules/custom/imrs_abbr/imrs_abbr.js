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

      // Replace any formulae for chemical elements:
      for (abbr in Drupal.settings.elements) {
        if (abbr.length > 1) {
          html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + Drupal.settings.elements[abbr] + "'>" + abbr + "</abbr>");
        }
      }

      // Replace any formulae for chemical compounds:
      for (abbr in Drupal.settings.compounds) {
        if (abbr == 'CO' && location.pathname == '/references') {
          continue;
        }

        // Wrap every sequence of digits in <sub> tags:
        abbrWithSubscripts = abbr.replace(/(\d+)/g, "<sub>$1</sub>");
        html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + Drupal.settings.compounds[abbr] + "'>" + abbrWithSubscripts + "</abbr>");
      }

      // Replace any abbreviations:
      for (abbr in Drupal.settings.acronyms) {
        html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + Drupal.settings.acronyms[abbr] + "'>" + abbr + "</abbr>");
      }

      // Replace any references:
      i = 1;
      for (abbr in Drupal.settings.references) {
        html = html.replace("[" + abbr + "]", "<sup title='" + Drupal.settings.references[abbr] + "'>[<a href='/references#ref" + i + "'>" + i + "</a>]</sup>");
        i++;
      }

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
