<?php
/**
 Plugin Name: Subscribe2 widget
 Plugin URI: http://www.mijnpress.nl
 Description: Widget show subscribe2 bar in any sidebar.
 Version: 1.1
 Author: Ramon Fincken
 Author URI: http://www.mijnpress.nl
 Based on: http://www.mijnpress.nl/blog/plugin-framework/
 */
if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

if(!class_exists('mijnpress_plugin_framework'))
{
	include('mijnpress_plugin_framework.php');
}

class subscribe2_widget extends mijnpress_plugin_framework
{
	function __construct()
	{
		$this->showcredits = true;
		$this->showcredits_fordevelopers = true;
		$this->plugin_title = 'Subscribe2 widget';
		$this->plugin_class = 'subscribe2_widget';
		$this->plugin_filename = 'subscribe2-widget/subscribe2-widget.php';
		$this->plugin_config_url = 'widgets.php'; // If you do not have an admin page: NULL
	}

	function subscribe2_widget()
	{
		$args= func_get_args();
		call_user_func_array
		(
		array(&$this, '__construct'),
		$args
		);
	}

	function frontend_widget_init()
	{
		$options = get_option('plugin__subscribe2_widget');
		
		$html = '<div>'.stripslashes($options['title']).'<div>[subscribe2]</div></div>';
		echo do_shortcode($html);
	}

	function admin_widget_init()
	{
		// check for the required API functions
		if (!function_exists('register_sidebar_widget') || !function_exists('register_widget_control'))
		return;

		// Tell Dynamic Sidebar about our new widget and its control
		register_sidebar_widget(array (
	      'Subscribe 2 widget',
	      'widgets'
	      ), array('subscribe2_widget','frontend_widget_init'));

	      // this registers the widget control form
	      register_widget_control(array (
	      'Subscribe 2 widget',
	      'widgets'
	      ), array('subscribe2_widget','admin_widget_controls'), 335, 700);
	}

	// widget options
	function admin_widget_controls() {
		// get our options and see if we're handling a form submission.
		$options = get_option('plugin__subscribe2_widget');

		// default values
		if (!is_array($options) || empty($options['title']))
		{ 
		$options = array (
            'title' => '<h4>Subscribe to receive new blogposts</h4>'
            );
		}
            if ($_POST['plugin__subscribe2_widget-submit']) {
            	$nonce = $_REQUEST['plugin__subscribe2_widget-nonce'];
            	if (!wp_verify_nonce($nonce, 'plugin__subscribe2_widget-nonce'))
            	die('Security check failed. Please use the back button and try resubmitting the information.');

            	$options['title'] = $_POST['title'];
            	update_option('plugin__subscribe2_widget', $options);
            }
            ?>
<p><label for="title">Title:</label><br />
<textarea style="width: 350px;" id="title" name="title"><?php echo stripslashes($options['title']); ?></textarea>

<input type="hidden" name="plugin__subscribe2_widget-nonce"
	value="<?php echo wp_create_nonce('plugin__subscribe2_widget-nonce'); ?>" />
<input type="hidden" id="vn_plugin_add_link_submit"
	name="plugin__subscribe2_widget-submit" value="yes" /> <?php

	}

	function addPluginSubMenu()
	{
		$plugin = new subscribe2_widget();
		parent::addPluginSubMenu($plugin->plugin_title,array($plugin->plugin_class, 'admin_menu'),__FILE__);
	}

	/**
	 * Additional links on the plugin page
	 */
	function addPluginContent($links, $file) {
		$plugin = new subscribe2_widget();
		$links = parent::addPluginContent($plugin->plugin_filename,$links,$file,$plugin->plugin_config_url);
		return $links;
	}

	/**
	 * Shows the admin plugin page
	 */
	public function admin_menu()
	{
		$plugin = new subscribe2_widget();
		$plugin->content_start();

		// TODO: Include your admin page or call a function here

		$plugin->content_end();
	}
}

// Admin only
if(mijnpress_plugin_framework::is_admin())
{
	// add_action('admin_menu',  array('subscribe2_widget', 'addPluginSubMenu'));
	add_filter('plugin_row_meta',array('subscribe2_widget', 'addPluginContent'), 10, 2);
}
add_action('widgets_init', array('subscribe2_widget', 'admin_widget_init'));
?>