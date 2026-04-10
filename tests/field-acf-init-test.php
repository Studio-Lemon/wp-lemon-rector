<?php

namespace WP_Lemon\Child\Models;

add_action('acf/init', 'my_acf_add_local_field_groups');
add_action('acf/init', function () {
   // register fields
});

add_action(
   'acf/init',
   function () {
      // register fields
   }
);


add_action(
   'acf/init',
   function (): void {
      // register fields
   }
);

// This one should NOT be changed (different hook)
add_action('acf/save_post', 'my_callback');
