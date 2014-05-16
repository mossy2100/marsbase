(function($) {

  function markupAbbreviations(el) {
    if (el.nodeType == 3) {
      var abbr, abbrWithSubscripts;
      el = $(el);

      var text = el.text();
      var html;

      if (text) {
        html = text;

        // Replace any formulae for chemical elements:
        for (abbr in Drupal.settings.elements) {
          html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + Drupal.settings.elements[abbr] + "'>" + abbr + "</abbr>");
        }

        // Replace any formulae for chemical compounds:
        for (abbr in Drupal.settings.compounds) {
          // Wrap every sequence of digits in <sub> tags:
          abbrWithSubscripts = abbr.replace(/(\d+)/g, "<sub>$1</sub>");
          html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + Drupal.settings.compounds[abbr] + "'>" + abbrWithSubscripts + "</abbr>");
        }

        // Replace any abbreviations:
        for (abbr in Drupal.settings.acronyms) {
          html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + Drupal.settings.acronyms[abbr] + "'>" + abbr + "</abbr>");
        }

        if (html != text) {
          el.replaceWith(html);
        }
      }
    }

    // Recurse into children:
    $(el).contents().each(function(i, el) {
      markupAbbreviations(el);
    });
  }

  function init() {
    $('.node--book--full').tooltip({
      items: "abbr",
      position: {
        my: "left-5 bottom-3",
        at: "left top"
      },
      show: false
    });

    var bookPages = $('article.node--book--full');
    if (bookPages.length) {
      markupAbbreviations(bookPages.get(0));
    }
  }

  $(init);

})(jQuery);
