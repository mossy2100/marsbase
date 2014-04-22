(function($) {

  function markupAbbreviations(el) {
    if (el.nodeType == 3) {
      var abbr, abbrWithSubscripts;
      el = $(el);

      var text = el.text();
      var html;

      if (text) {
        html = text;

        // Replace any abbreviations:
        for (abbr in abbreviations) {
          html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + abbreviations[abbr] + "'>" + abbr + "</abbr>");
        }

        // Replace any chemical formulae:
        for (abbr in formulae) {
          // Wrap every sequence of digits in <sub> tags:
          abbrWithSubscripts = abbr.replace(/(\d+)/g, "<sub>$1</sub>");
          html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + formulae[abbr] + "'>" + abbrWithSubscripts + "</abbr>");
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
