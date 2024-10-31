(function( $ ) {
  'use strict';

  var keymap, $menu;

  /**
   * Determines if the user pressed the shortcut keys to navigate to the
   * scheduled posts.
   *
   * @param  keymap  The array of keys that have been pressed.
   *
   * @return boolean True if the shortcut keys have been pressed.
   */
  var userPressedShortcut = function(keymap) {
    return -1 !== $.inArray(16, keymap) &&
           ( -1 !== $.inArray(91, keymap) || -1 !== $.inArray(17, keymap)) &&
           -1 !== $.inArray(83, keymap) &&
           -1 !== $.inArray(39, keymap);

  };

  /**
   * Determines if the specified keycode is a valid key for the plugin's
   * shortcut.
   *
   * - Shift:    16
   * - Command:  91 (OS X)
   * - Control:  17 (Windows)
   * - S:        83
   * - Right:    39
   *
   * @param  keycode  The number corresponding to the key being pressed.
   *
   * @return boolean True if the number specified is part of the shortcut.
   */
  var isValidShortcutKey = function(keycode) {
    return 16 === keycode ||
          ( 91 === keycode || 17 === keycode ) ||
          83 === keycode ||
          39 === keycode;
  };

  $(function() {

    keymap = [];
    $menu  = $('#scheduled-post-count').parent();

    $(document).on( 'keydown', function(evt) {

      if (isValidShortcutKey(evt.keyCode)) {
        if (-1 === $.inArray(evt.keyCode, keymap)) {
          keymap.push(evt.keyCode);
        }
      }

    }).on('keyup', function() {
      if (userPressedShortcut(keymap)) {
        window.location.href = $menu.attr('href');
        keymap = [];
      }
    });
  });
})(jQuery);
