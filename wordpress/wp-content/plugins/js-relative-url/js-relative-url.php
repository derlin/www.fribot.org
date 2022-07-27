<?php

/*
Plugin Name: Js Relative URLs
Description: add the domain to an href or src attribute if the containing tag has the class add-site-prefix.
Author: Lucy Linder
Version: 1.0
*/


function hello_dolly() {
	$chosen = hello_dolly_get_lyric();
	echo "<p id='dolly'>$chosen</p>";
}


add_action( 'wp_enqueue_scripts', 'js_rel_url' );

// add the script to the page
function js_rel_url() {
	wp_enqueue_script( 'js_rel_url', plugins_url('js-relative-url.js', __FILE__), array('jquery') );
}
	

?>
