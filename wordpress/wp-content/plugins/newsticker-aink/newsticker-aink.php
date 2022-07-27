<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
Plugin Name: NewsTicker Aink
Plugin URI: http://www.classifindo.com/newsticker-aink/
Description: Inserts a sliding text (ticker).
Author: Dannie Herdyawan a.k.a k0z3y
Version: 4.0
Author URI: http://www.classifindo.com/
*/


/*
   _____                                                 ___  ___
  /\  __'\                           __                 /\  \/\  \
  \ \ \/\ \     __      ___     ___ /\_\     __         \ \  \_\  \
   \ \ \ \ \  /'__`\  /' _ `\ /` _ `\/\ \  /'__'\        \ \   __  \
    \ \ \_\ \/\ \L\.\_/\ \/\ \/\ \/\ \ \ \/\  __/    ___  \ \  \ \  \
     \ \____/\ \__/.\_\ \_\ \_\ \_\ \_\ \_\ \____\  /\__\  \ \__\/\__\
      \/___/  \/__/\/_/\/_/\/_/\/_/\/_/\/_/\/____/  \/__/   \/__/\/__/

*/

//////////////////////////////////////////////////////////////////////////////////////////////////////////////// 

global $NewsTickerAink_path;
$NewsTickerAink_path = get_settings('home').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));

require_once ('newsticker-aink_fields.php');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* When plugin is activated */
register_activation_hook(__FILE__,'install_NewsTickerAink');
function install_NewsTickerAink()
{
	global $wpdb;

    $collate = '';
    if($wpdb->supports_collation()) {
        if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }

    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "newsticker_aink" ." (
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`title` varchar(250) NOT NULL,
		`content` LONGTEXT NOT NULL,
		`status` varchar(25) NOT NULL,
		`showfor` varchar(25) NOT NULL,
		`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY id (`id`)) $collate;";
    $wpdb->query($sql);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* When plugin is deactivation*/
register_deactivation_hook( __FILE__, 'hapus_newstickerAink' );
function hapus_NewsTickerAink()
{
	/* Deletes the database field */
	global $wpdb, $options;
	$options = get_option('NewsTickerAink_option');

    $sql = "DROP TABLE ". $wpdb->prefix . "newsticker_aink";
    $wpdb->query($sql);

	delete_option($options);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('admin_menu', 'NewsTickerAink_admin_menu');
function NewsTickerAink_admin_menu() {
	if((current_user_can('manage_options') || is_admin)) {
		global $NewsTickerAink_path;
		add_object_page('NewsTicker-Aink','NewsTicker',1,'NewsTicker-Aink','NewsTickerAink_page',$NewsTickerAink_path.'/images/favicon.png');
		add_submenu_page('NewsTicker-Aink','NewsTicker Aink Settings','Settings',1,'NewsTicker-Aink','NewsTickerAink_page');
		add_submenu_page('NewsTicker-Aink','Create New','Create New',1,'NewsTickerAink_new','NewsTickerAink_new');
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function NewsTickerAink_page() {
	if (isset($_POST['save'])) {
		$options['NewsTickerAink_height']		= trim($_POST['NewsTickerAink_height'],'{}');
		$options['NewsTickerAink_speed']		= trim($_POST['NewsTickerAink_speed'],'{}');
		$options['NewsTickerAink_content_align']= trim($_POST['NewsTickerAink_content_align'],'{}');
		$options['NewsTickerAink_link']			= trim($_POST['NewsTickerAink_link'],'{}');
		update_option('NewsTickerAink_option', $options);
		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __("Save Changes") . '</p></div>';
	} else {		
		$options = get_option('NewsTickerAink_option');
	}
	echo NewsTickerAinkSettings();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function NewsTickerAink_new()
{
	echo CreateNewNewsTickerAink();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function NewsTickerAink_init() {
	if ( !is_blog_installed() )
		return;
	register_widget('NewsTickerAink');
	do_action('widgets_init');
}

add_action('init', 'NewsTickerAink_init', 1);
add_action("wp_head", "NewsTickerAink_head");


////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function NewsTickerAink_head()
{
	global $options, $NewsTickerAink_path;
	$options = get_option('NewsTickerAink_option');
///////////////////////////////////////////////////////////////////////////////

	echo '<!-- NewsTicker Aink -->';
///////////////////////////////////////////////////////////////////////////////
	echo '<script type="text/javascript" language="javascript" src="'.$NewsTickerAink_path.'/js/jcarousellite.js"></script>';
	echo '<script type="text/javascript" language="javascript" src="'.$NewsTickerAink_path.'/js/easing.js"></script>';
	echo '
		<script type="text/javascript" language="javascript">
		jQuery(document).ready(function(){
			jQuery("div#NewsTicker").jCarouselLite({
				vertical: true,
				hoverPause: true,
				visible: 1,
				auto: 5000,
				speed: ' . $options[NewsTickerAink_speed] . ',
				easing: "easeOutSine"
			});
		});
		</script>';
////////////////////////////////////////////////////////////////////////////////
	echo '
		<style type="text/css">
			@font-face{
			font-family:Angelina;
			src:url("http://www.classifindo.com/fonts/Angelina.ttf");
			}
			div#NewsTicker {
				display:block;
			}
			div#NewsTicker ul {
				display:block;
			}
			div#NewsTicker ul li {
				height:' . $options[NewsTickerAink_height] . ';
				display:block;
			}
			div#NewsTicker ul li.news {
				display:block;
			}
			p.NewsTicker_title {
				text-align:' . $options[NewsTickerAink_title_align] . ';
				display:block;
				font-weight:bold;
				border-bottom:solid 1px #ddd;
				padding-bottom:2.5px;
			}
			p.NewsTicker_content {
				text-align:' . $options[NewsTickerAink_content_align] . ';
			}
		</style>';
///////////////////////////////////////////////////////////////////////////////
	echo '<!-- NewsTicker Aink -->';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* This Registers a Sidebar Widget.*/
class NewsTickerAink extends WP_Widget {

	function NewsTickerAink() {
		$widget_ops = array('description' => 'Show NewsTicker Aink.' );
		parent::WP_Widget(false, __('NewsTicker Aink', 'k0z3y'), $widget_ops);      
	}
	
	function widget( $args, $instance ) {
		extract($args);
		global $wpdb, $options;
		$options = get_option('NewsTickerAink_option');
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		} else {
			if($options[NewsTickerAink_link] == 'check'){
				echo $before_title . '<a href="http://www.classifindo.com/newsticker-aink/" target="_blank">NewsTicker Aink</a>' . $after_title;
			} else {
				echo $before_title . 'NewsTicker Aink' . $after_title;
			}
		}

		/* ---------------------------------------------------------------------------------------------------- */
		$AllUser = $wpdb->prepare("SELECT title, content "
		. "FROM ". $wpdb->prefix . "newsticker_aink "
		. "WHERE status='Active'"
		. "AND showfor='All User'"
		. "ORDER BY RAND()");

		$UserLogin = $wpdb->prepare("SELECT title, content "
		. "FROM ". $wpdb->prefix . "newsticker_aink "
		. "WHERE status='Active'"
		. "AND showfor='User Login'"
		. "ORDER BY RAND()");

		$UserNotLogin = $wpdb->prepare("SELECT title, content "
		. "FROM ". $wpdb->prefix . "newsticker_aink "
		. "WHERE status='Active'"
		. "AND showfor='User Not Login'"
		. "ORDER BY RAND()");
?>
	<div id="NewsTicker">
		<ul>
<?php /////////////////////////////////////////////////////////////////////////////// ?>
			<?php foreach ($wpdb->get_results($AllUser) as $All) { ?>
				<li class="news">
					<?php if($All->title != ''){ ?>
						<p class="NewsTicker_title"><?php echo $All->title; ?></p>
					<?php } ?>
					<p class="NewsTicker_content"><?php echo $All->content; ?></p>
				</li>
			<?php } ?>
<?php /////////////////////////////////////////////////////////////////////////////// ?>
			<?php if (is_user_logged_in()): ?>
				<?php foreach ($wpdb->get_results($UserLogin) as $Login) { ?>
					<li class="news">
						<?php if($Login->title != ''){ ?>
							<p class="NewsTicker_title"><?php echo $Login->title; ?></p>
						<?php } ?>
						<p class="NewsTicker_content"><?php echo $Login->content; ?></p>
					</li>
				<?php } ?>
			<?php endif; ?>
<?php /////////////////////////////////////////////////////////////////////////////// ?>
			<?php if (!is_user_logged_in()): ?>
				<?php foreach ($wpdb->get_results($UserNotLogin) as $NotLogin) { ?>
					<li class="news">
						<?php if($NotLogin->title != ''){ ?>
							<p class="NewsTicker_title"><?php echo $NotLogin->title; ?></p>
						<?php } ?>
						<p class="NewsTicker_content"><?php echo $NotLogin->content; ?></p>
					</li>
				<?php } ?>
			<?php endif; ?>
<?php /////////////////////////////////////////////////////////////////////////////// ?>
		</ul>
	</div>

<?php
///////////////////////////////////////////////////////////////////////////////
	if($options[NewsTickerAink_link] == 'check'){
		echo '<div style="text-align:right;">';
			echo '<a href="http://www.classifindo.com/newsticker-aink/" target="_blank" style="font-size:11px;">';
				echo '<font face="font-family:Angelina, Helvetica, sans-serif, "Trebuchet MS", sans-serif, Arial, Tahoma;">';
					echo 'NewsTicker Aink';
				echo '</font>';
			echo '</a>';
		echo '</div>';
	}
///////////////////////////////////////////////////////////////////////////////
		/* ---------------------------------------------------------------------------------------------------- */

		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = $instance['title'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

}

?>