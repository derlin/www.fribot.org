<?php

define('CHILD_DIR', dirname(get_stylesheet_uri()) );



if (!is_admin()) add_action("wp_enqueue_scripts", "booba_child_enqueue_scripts", 11);

function booba_child_enqueue_scripts() {
    /* remove the main.js from the parent theme */
    wp_deregister_style('main-style');
    /* add the jquery script */
    wp_deregister_script('jquery');
    wp_register_script('jquery', CHILD_DIR . "/jquery-1.11.1.min.js", false, null);
    wp_enqueue_script('jquery');
    /* add custom js script */
    wp_enqueue_script('booba-script', CHILD_DIR . '/functions.js', false, null);
}


/* add a login button in the main menu */
add_filter( 'wp_nav_menu_items','wpsites_loginout_menu_link' );

function wpsites_loginout_menu_link( $menu ) { 
    $loginout = wp_loginout($_SERVER['REQUEST_URI'], false );
    
    // extract href + text from the generated <a> tag
    preg_match('/href="(.*)".?>(.*)</', $loginout, $matches);

    $href = $matches[1];
    $title = $matches[2];

    // replace the text by an icon (cf. font-awesome)
    $link = '<a href="' . $href . '" title="' . $title . '"><i class="fa fa-lock"></i></a>';

    $menu .= '<li>' . $link . '</li>

';
    return $menu;
}

?>
