(function ($) {

  function resizeIframe() {
    // Set the iframe width to 100%.
    $('iframe').width('100%');
    // Get the iframe width.
    var width = $('iframe').width();
    // Calculate the height.
    var height = width * 121/215;
    // Set the height.
    $('iframe').height(height);
  }

  $(function() {
    resizeIframe();
    $(window).resize(resizeIframe);
  });

})(jQuery);
