/**
 * Created by shaun on 21-May-2014.
 */

var $j = jQuery.noConflict();

/**
 * Remove chemical subscripts from a page.
 *
 * @param page
 */
function remove_chemical_subscripts(pageElement) {
  var $page = $j(pageElement),
      abbr,
      strippedAbbr,
      origHtml = $page.html(),
      html = origHtml;

  // Remove subscript tags from all chemical formulae.
  for (abbr in Drupal.settings.chemicals) {
    strippedAbbr = abbr.replace(/<\/?sub>/g, '');
    if (strippedAbbr != abbr) {
      console.log("Replacing " + abbr + " with " + strippedAbbr);
      html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), strippedAbbr);
      if (html != origHtml) {
        console.log('changed');
        console.log(html);
        //break;
      }
    }
  }

  if (html != origHtml) {
    $page.html(html);
  }
}

function markup_abbreviations(el) {
  if (el.nodeType == 3) {
    var abbr, abbrTag, i;
    el = $j(el);

    // Get the HTML.
    var text = el.text();
    var html;

    if (text) {
      html = text;

      // Add <abbr> tags to chemical elements and compounds:
      for (abbr in Drupal.settings.chemicals) {
        // Skip 'CO' when it means Colorado:
        if (abbr == 'CO' && location.pathname == '/references') {
          continue;
        }

        // Add <abbr> tags:
        abbrTag = "<abbr title='" + Drupal.settings.chemicals[abbr][0] + "'>" + abbr + "</abbr>";
        console.log(abbrTag);
        html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), abbrTag);
      }

      // Add <abbr> tags to acronyms:
      for (abbr in Drupal.settings.acronyms) {
        abbrTag = "<abbr title='" + Drupal.settings.acronyms[abbr][0] + "'>" + abbr + "</abbr>";
        html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), abbrTag);
      }

      if (html != text) {
        el.replaceWith(html);
      }
    }
  }

  // Recurse into children:
  $j(el).contents().each(function(i, el) {
    markup_abbreviations(el);
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
    var page = bookPages.get(0);
    remove_chemical_subscripts(page);
    //markup_abbreviations(page);
  }

  // Highlight the desired reference:
  if (location.hash) {
    $j(location.hash).css('background-color', 'yellow');
  }
});
