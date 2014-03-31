(function ($) {

  /**
   * Adjust the body top margin to fit the admin menu.
   */
  function ibis_misc_adjust_admin_menu_margin() {
    if ($('#admin-menu').size()) {
      $('body').style('margin-top', $('#admin-menu').outerHeight() + 'px', 'important');
    }
  }

  // Adjust the body top margin to fit the admin menu on window resize:
  $(window).resize(ibis_misc_adjust_admin_menu_margin);
  // Initialise the body top margin:
  ibis_misc_adjust_admin_menu_margin();

})(jQuery);
