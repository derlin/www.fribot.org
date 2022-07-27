<?php ob_start(); ?>
<?php
/*
Plugin Name: WP jQuery CDN
Description: Activate Plugin and Select a jQuery CDN via the WP jQuery CDN options area
Author: InertiaInMotion
Version: 2.2
Author URI: http://inertiainmotion.com.au/
Plugin URI: http://wordpress.org/extend/plugins/wp-jquery-cdn/
*/
/*  Copyright 2011 - 2012 InertiaInMotion

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
	function wp_jquery_cdn_options_init(){
    	register_setting( 'wp_jquery_cdn_options', 'wp_jquery_cdn' );
	}
	add_action('admin_init', 'wp_jquery_cdn_options_init' );

    function wp_jquery_cdn_menu(){

        add_menu_page('WP jQuery CDN', 'WP jQuery CDN', 'manage_options', 'WP-jQuery-CDN.php', 'wp_jquery_cdn');

	}
	add_action('admin_menu', 'wp_jquery_cdn_menu');

	function wp_jquery_cdn(){
        if(!current_user_can('manage_options')){
	        wp_die( __('You do not have sufficient permissions to access this page.') );
	    } ?>
	    <link rel="stylesheet" href="<?php echo plugins_url(); ?>/wp-jquery-cdn/options.css" type="text/css" />
        <form id="options-form" method="post" action="options.php">
            <em>Set it and forget it...</em>
            <h1>WP jQuery CDN</h1>
        	<?php settings_fields('wp_jquery_cdn_options'); ?>
            <?php $options = get_option('wp_jquery_cdn'); ?>
			<select type="select" name="wp_jquery_cdn[jquery_cdn]">
            	<option value="1" <?php if($options['jquery_cdn'] == "1"){ echo "selected='selected'"; } ?>>
            		Google Ajax API jQuery CDN
            	</option>
            	<option value="2" <?php if($options['jquery_cdn'] == "2"){ echo "selected='selected'"; } ?>>
                	jQuery CDN
                </option>
            	<option value="3" <?php if($options['jquery_cdn'] == "3"){ echo "selected='selected'"; } ?>>
            		Microsoft jQuery CDN
            	</option>
                <option value="4" <?php if($options['jquery_cdn'] == "4"){ echo "selected='selected'"; } ?>>
                	Local jQuery (Inside this plugins js folder)
                </option>
                <option value="5" <?php if($options['jquery_cdn'] == "5"){ echo "selected='selected'"; } ?>>
                	Local jQuery (Wordpress) (Might be out of date)
                </option>
                <option value="6" <?php if($options['jquery_cdn'] == "6"){ echo "selected='selected'"; } ?>>
					None (Dont load any jQuery, Or i wish to load it myself)
			    </option>
           	</select>
           	<?php if($options['jquery_version']){ $jquery_version = $options['jquery_version']; } ?>
           	<?php if(!$options['jquery_version']){ $jquery_version = "1.7.1"; } ?>
           	
           	<p>Specify Version: <input type="text" name="wp_jquery_cdn[jquery_version]" value="<?php echo $jquery_version; ?>" />
            <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
            <p class="shameless-plug">Created by: <a href="http://inertiainmotion.com.au">inertiainmotion.com.au</a></p>
         </form><?php

	}

    $options = get_option('wp_jquery_cdn');
    $jquery = $options['jquery_version'];
    
    if($options['jquery_cdn'] == "0" || !$options['jquery_cdn']){ $options['jquery_cdn'] = "1"; }
    
    if($options['jquery_cdn'] != "5"){
    	if($options['jquery_cdn'] == "1"){
			$jquery = 'http://ajax.googleapis.com/ajax/libs/jquery/' . $jquery . '/jquery.min.js';
    	}

    	if($options['jquery_cdn'] == "2"){
			$jquery = 'http://code.jquery.com/jquery-' . $jquery . '.min.js';
    	}

    	if($options['jquery_cdn'] == "3"){
    		$jquery = 'http://ajax.aspnetcdn.com/ajax/jQuery/jquery-' . $jquery . '.min.js';
    	}

    	if($options['jquery_cdn'] == "4"){
			$jquery = plugins_url() . '/wp-jquery-cdn/js/local-jquery.min.js';
    	}
    }
    		
    if($options['jquery_cdn'] != "5"){
    	if($options['jquery_cdn'] == "1" || $options['jquery_cdn'] == "2"
    	   || $options['jquery_cdn'] == "3" || $options['jquery_cdn'] == "4"){
			function wp_jquery_cdn_init(){
    			if (!is_admin()){
    				global $jquery;
        			wp_deregister_script('jquery');
        			wp_register_script('jquery', $jquery);
        			wp_enqueue_script('jquery');
        		}
        	}
			add_action('init', 'wp_jquery_cdn_init');
    	}
    	
    	if($options['jquery_cdn'] == "6"){
			function wp_jquery_cdn_init(){
    			if (!is_admin()){
        			wp_deregister_script('jquery');
        		}
        	}
			add_action('init', 'wp_jquery_cdn_init');
    	}
    }

?>