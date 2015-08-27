/**
 * Created by shaun on 21-May-2014.
 */

var $j = jQuery.noConflict();

/**
 * Strip HTML tags from a string.
 *
 * @param {string} str
 * @returns {string}
 */
function stripTags(str) {
  return str.replace(/<\/?[^>]+>/g, '');
}

/**
 * Remove chemical subscripts from a page.
 *
 * @param page
 */
function removeChemicalSubscripts(pageElement) {
  var $page = $j(pageElement),
      abbr,
      strippedAbbr,
      origHtml = $page.html(),
      html = origHtml;

  // Remove subscript tags from all chemical formulae.
  for (abbr in Drupal.settings.chemicals) {
    strippedAbbr = stripTags(abbr);
    if (strippedAbbr != abbr) {
      html = html.replace(new RegExp(abbr, 'g'), strippedAbbr);
    }
  }

  if (html != origHtml) {
    $page.html(html);
  }
}

function markupAbbrev(el) {
  if (el.nodeType == 3) {
    var abbr, abbrTag, strippedAbbr;
    el = $j(el);

    // Get the HTML.
    var text = el.text();
    var html;

    if (text) {
      html = text;

      // Add <abbr> tags to chemical elements and compounds:
      for (abbr in Drupal.settings.chemicals) {
        // Skip 1-letter chemical symbols.
        if (abbr.length == 1) {
          continue;
        }

        // Skip 'CO' when it means Colorado:
        if (abbr == 'CO' && location.pathname == '/references') {
          continue;
        }

        // Add <abbr> tags:
        abbrTag = "<abbr title='" + Drupal.settings.chemicals[abbr] + "'>" + abbr + "</abbr>";
        strippedAbbr = stripTags(abbr);
        html = html.replace(new RegExp("\\b" + strippedAbbr + "\\b", 'g'), abbrTag);
      }

      // Add <abbr> tags to acronyms.
      for (abbr in Drupal.settings.acronyms) {
        // Use placeholder titles to avoid nested <abbr> tags.
        abbrTag = "<abbr title='title" + abbr + "'>" + abbr + "</abbr>";
        html = html.replace(new RegExp("(^|[^A-Za-z0-9-])" + abbr + "(e?s)?($|[^A-Za-z0-9-])", 'g'), '$1' + abbrTag + '$2$3');
      }

      // Replace the <abbr> tag titles.
      for (abbr in Drupal.settings.acronyms) {
        html = html.replace(new RegExp('title' + abbr, 'g'), Drupal.settings.acronyms[abbr]);
      }

      if (html != text) {
        html = html.replace('xxx', '');
        el.replaceWith(html);
      }
    }
  }

  // Recurse into children:
  $j(el).contents().each(function(i, el) {
    markupAbbrev(el);
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

  var bookPages = $j('.field--name-field-html-editor');
  if (bookPages.length) {
    var page = bookPages.get(0);
    removeChemicalSubscripts(page);
    markupAbbrev(page);
  }
});
