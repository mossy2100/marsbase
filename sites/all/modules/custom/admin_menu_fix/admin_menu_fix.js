var $j = jQuery.noConflict();

/**
 * Get the height of the admin menu.
 *
 * @returns int
 */
function adminMenuFixHeight() {
  if ($j('#admin-menu').size()) {
    return $j('#admin-menu').outerHeight();
  }
  return 0;
}

/**
 * Adjust the body top margin to fit the admin menu.
 */
function adminMenuFixAdjustMargin() {
  if ($j('#admin-menu').size()) {
    $j('body').css('padding-top', adminMenuFixHeight() + 'px');
  }
}

/**
 * If there's a fragment, scroll the document if necessary to prevent the desired item from being blocked by the
 * admin menu.
 */
function adminMenuFixScrollToElement() {
  // Check if there's a fragment in the URI:
  if (!location.hash) {
    return;
  }

  var el = $j(location.hash);
//  console.log( el.offset());

  var elementOffset = el.offset().top;
//  console.log("elementOffset: " + elementOffset);

  var documentScroll = $j(document).scrollTop();
//  console.log("documentScroll: " + documentScroll);

  var adminMenuHeight = adminMenuFixHeight();
//  console.log("adminMenuHeight: " + adminMenuHeight);

  if (elementOffset < (documentScroll + adminMenuHeight)) {
    var scrollAmt = elementOffset - documentScroll - adminMenuHeight;
//    console.log("scrollAmt: " + scrollAmt);
    window.scrollBy(0, scrollAmt);
  }
}

if (Drupal && Drupal.admin && Drupal.admin.behaviors) {
  Drupal.admin.behaviors.admin_menu_fix = function() {
    // Adjust the body top margin to fit the admin menu now and on window resize:
    adminMenuFixAdjustMargin();
    $j(window).resize(adminMenuFixAdjustMargin);

    // If there's a fragment in the URI, scroll to the specified element:
    setTimeout(function() {
      adminMenuFixScrollToElement();
    }, 100);
  }
};
