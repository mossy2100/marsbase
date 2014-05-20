var $j = jQuery.noConflict();

/**
 * Get the height of the admin menu.
 *
 * @returns int
 */
function admin_menu_fix_admin_menu_height() {
  if ($j('#admin-menu').size()) {
    return $j('#admin-menu').outerHeight();
  }
  return 0;
}

/**
 * Adjust the body top margin to fit the admin menu.
 */
function admin_menu_fix_adjust_admin_menu_margin() {
  if ($j('#admin-menu').size()) {
    $j('body').css('padding-top', admin_menu_fix_admin_menu_height() + 'px');
  }
}

/**
 * If there's a fragment, scroll the document if necessary to prevent the desired item from being blocked by the
 * admin menu.
 */
function admin_menu_fix_scroll_to_element() {
  // Check if there's a fragment in the URI:
  if (!location.hash) {
    return;
  }

  var el = $j(location.hash);

  var elementOffset = el.offset().top;
//  console.log("elementOffset: " + elementOffset);

  var documentScroll = $j(document).scrollTop();
//  console.log("documentScroll: " + documentScroll);

  var adminMenuHeight = admin_menu_fix_admin_menu_height();
//  console.log("adminMenuHeight: " + adminMenuHeight);

  if (elementOffset < (documentScroll + adminMenuHeight)) {
    var scrollAmt = elementOffset - documentScroll - adminMenuHeight;
//    console.log("scrollAmt: " + scrollAmt);
    window.scrollBy(0, scrollAmt);
  }
}

///**
// * Initialise the admin menu.
// */
//$j(function() {
//  // Adjust the body top margin to fit the admin menu now and on window resize:
//  admin_menu_fix_adjust_admin_menu_margin();
//  $j(window).resize(admin_menu_fix_adjust_admin_menu_margin);
//
//  // If there's a fragment in the URI, scroll to the specified element:
//  admin_menu_fix_scroll_to_element();
//});

Drupal.admin.behaviors.admin_menu_fix = function() {
  // Adjust the body top margin to fit the admin menu now and on window resize:
  admin_menu_fix_adjust_admin_menu_margin();
  $j(window).resize(admin_menu_fix_adjust_admin_menu_margin);

  // If there's a fragment in the URI, scroll to the specified element:
  admin_menu_fix_scroll_to_element();
};
