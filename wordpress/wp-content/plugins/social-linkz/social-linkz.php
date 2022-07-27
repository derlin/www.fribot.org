<?php
/**
Plugin Name: Social Linkz
Plugin Tag: social, facebook, twitter, google, buttons
Description: <p>Add social links such as Twitter or Facebook in each post. </p><p>You can choose the buttons to be displayed such as : </p><ul><li>Twitter</li><li>FaceBook</li><li>LinkedIn</li><li>Viadeo</li><li>Google+</li><li>StumbleUpon</li><li>Pinterest</li><li>Print</li></ul><p>It is possible to manually insert the buttons in your post by adding the shortcode <code>[sociallinkz]</code> or <code>[sociallinkz url='http://domain.tld' buttons='facebook,google+' desc='Short description']</code> . </p><p>If you want to add the buttons in a very specific location, your may edit your theme and insert <code>$this->print_buttons($post, [$url], [$buttons]);</code> (be sure that <code>$post</code> refer to the current post). </p><p>It is also possible to add a widget to display buttons. </p><p>This plugin is under GPL licence. </p>
Version: 1.7.1
Author: SedLex
Author Email: sedlex@sedlex.fr
Framework Email: sedlex@sedlex.fr
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/plugins/social-linkz/
License: GPL3
*/

require_once('core.php') ; 
require_once('include/phpqrcode.php') ; 

class sociallinkz extends pluginSedLex {
	/** ====================================================================================================================================================
	* Initialisation du plugin
	* 
	* @return void
	*/
	static $instance = false;
	var $path = false;
	
	protected function _init() {
		global $wpdb ; 
		global $do_not_show_inSocialLinkz ; 
		// Configuration
		$this->pluginName = 'Social Linkz' ; 
		$this->tableSQL = "id mediumint(9) NOT NULL AUTO_INCREMENT, id_post mediumint(9) NOT NULL, counters MEDIUMTEXT, url MEDIUMTEXT, date_maj DATETIME, UNIQUE KEY id (id)" ; 
		$this->table_name = $wpdb->prefix . "pluginSL_" . get_class() ; 
		$this->path = __FILE__ ; 
		$this->pluginID = get_class() ; 
		
		//Init et des-init
		register_activation_hook(__FILE__, array($this,'install'));
		register_deactivation_hook(__FILE__, array($this,'deactivate'));
		register_uninstall_hook(__FILE__, array('sociallinkz','uninstall_removedata'));
		
		//Parametres supplementaires
		add_shortcode( 'sociallinkz', array( $this, 'display_button_shortcode' ) );
		add_shortcode( 'qrcode', array( $this, 'qrcode_shortcode' ) );
		
		//add_action( 'wp',  array( $this, 'output_print') );
		add_action('wp_enqueue_scripts', array( $this, 'output_print'), 10000001);

		add_filter( 'query_vars', array( $this, 'print_vars_callback'));

		add_action( 'wp_ajax_nopriv_forceUpdateSocialLinkz', array( $this, 'forceUpdateSocialLinkz'));
		add_action( 'wp_ajax_forceUpdateSocialLinkz', array( $this, 'forceUpdateSocialLinkz'));
		
		add_action( 'wp_ajax_emailSocialLinkz', array( $this, 'emailSocialLinkz'));
		
		wp_register_sidebar_widget('social_linkz', 'Social Linkz', array( $this, '_sidebar_widget'), array('description' => __('Display the social buttons as a widget.', $this->pluginID)));
		
		$do_not_show_inSocialLinkz = false ; 
		
	}
	/**
	 * Function to instantiate our class and make it a singleton
	 */
	public static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**====================================================================================================================================================
	* To display the widget.
	*
	* @return void
	*/
	
	public function _sidebar_widget() {
		$id = url_to_postid($_SERVER['REQUEST_URI']) ; 
		if ($id==0) {
			$post_art = new stdClass() ;
			$post_art->ID = 0 ; 
			echo $this->print_buttons($post_art) ; 
		} else {
			$post_art = get_post($id) ; 
			echo $this->print_buttons($post_art) ; 
		} 
	}
	
	/**====================================================================================================================================================
	* Function called when the plugin is activated
	* For instance, you can do stuff regarding the update of the format of the database if needed
	* If you do not need this function, you may delete it.
	*
	* @return void
	*/
	
	public function _update() {
		global $wpdb ; 
		
		SLFramework_Debug::log(get_class(), "Update the plugin." , 4) ; 
		
		// Delete the former counter ...
		$names = $this->get_name_params() ; 
		foreach($names as $n) {
			if (strpos($n, "counter_")===0) {
				$this->del_param($n) ; 
			}
		}
		
		// enable custom URL
		if ( !$wpdb->get_var("SHOW COLUMNS FROM ".$this->table_name." LIKE 'url'")  ) {
			$wpdb->query("ALTER TABLE ".$this->table_name." ADD url MEDIUMTEXT ") ; 
		}
	}	
	/** ====================================================================================================================================================
	* In order to uninstall the plugin, few things are to be done ... 
	* (do not modify this function)
	* 
	* @return void
	*/
	
	public function uninstall_removedata () {
		global $wpdb ;
		// DELETE OPTIONS
		delete_option('sociallinkz'.'_options') ;
		if (is_multisite()) {
			delete_site_option('sociallinkz'.'_options') ;
		}
		
		// DELETE SQL
		if (function_exists('is_multisite') && is_multisite()){
			$old_blog = $wpdb->blogid;
			$old_prefix = $wpdb->prefix ; 
			// Get all blog ids
			$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM ".$wpdb->blogs));
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				$wpdb->query("DROP TABLE ".str_replace($old_prefix, $wpdb->prefix, $wpdb->prefix . "pluginSL_" . 'sociallinkz')) ; 
			}
			switch_to_blog($old_blog);
		} else {
			$wpdb->query("DROP TABLE ".$wpdb->prefix . "pluginSL_" . 'sociallinkz' ) ; 
		}
	}

	/** ====================================================================================================================================================
	* Add a button in the TinyMCE Editor
	*
	* To add a new button, copy the commented lines a plurality of times (and uncomment them)
	* 
	* @return array of buttons
	*/
	
	function add_tinymce_buttons() {
		$buttons = array() ; 
		$buttons[] = array(__('Add SocialLinkz buttons', $this->pluginID), '[sociallinkz]', '', plugin_dir_url("/").'/'.str_replace(basename( __FILE__),"",plugin_basename( __FILE__)).'img/sociallinkz_button.png') ; 
		$buttons[] = array(__('Add a QR code', $this->pluginID), '[qrcode size="4" px_size="2" frame_size="5"]', '[/qrcode]', plugin_dir_url("/").'/'.str_replace(basename( __FILE__),"",plugin_basename( __FILE__)).'img/qr_button.png') ; 
		return $buttons ; 
	}
	
	/** ====================================================================================================================================================
	* Define the default option value of the plugin
	* 
	* @return variant of the option
	*/
	function get_default_option($option) {
		switch ($option) {
			case 'display_in_excerpt' 			: return false ; break ; 
			
			case 'display_top_in_post' 			: return false ; break ; 
			case 'display_bottom_in_post' 			: return true ; break ; 

			case 'display_top_in_page' 			: return false ; break ; 
			case 'display_bottom_in_page' 			: return true ; break ; 
			
			case 'twitter' 						: return true 	; break ; 
			case 'twitter_count' 						: return false 	; break ; 
			case 'twitter_hosted' 				: return false 	; break ; 
			case 'twitter_hosted_count' 		: return false 	; break ; 
			case 'twitter_string' 		: return "[Blog] %title% - %shorturl% (via %twitter_name%)" 	; break ; 
			case 'name_twitter'					: return "" 	; break ; 
			
			case 'pinterest_hosted' 				: return false 	; break ; 
			case 'pinterest_hosted_count' 		: return false 	; break ;
			case 'pinterest_hosted_defaultimage' 		: return "[file]/social-linkz/" 	; break ;
			case 'pinterest_string' 		: return "[Blog] %title% - %url%" 	; break ; 
			
			case 'linkedin' 					: return false 	; break ; 
			case 'linkedin_count' 					: return false 	; break ; 
			case 'linkedin_hosted' 				: return false 	; break ; 
			case 'linkedin_hosted_count' 		: return false 	; break ; 
			case 'linkedin_string' 		: return "[Blog] %title% - %shorturl%" 	; break ; 

			case 'viadeo' 					: return false 	; break ; 
			case 'viadeo_hosted' 					: return false 	; break ; 
			case 'viadeo_hosted_count' 		: return false 	; break ; 
			case 'viadeo_string' 		: return "[Blog] %title% - %shorturl%" 	; break ; 
						
			case 'googleplus_standard' 					: return false 	; break ; 
			case 'googleplus_standard_count' 					: return false 	; break ; 
			case 'googleplus' 					: return true 	; break ; 
			case 'googleplus_count' 			: return true 	; break ; 
			case 'googleplus_standard_key' 		: return '' ; break ; 

			case 'facebook' 					: return true 	; break ; 
			case 'facebook_id' 					: return "" 	; break ; 
			case 'facebook_count' 					: return false 	; break ; 
			case 'facebook_hosted' 				: return false 	; break ; 
			case 'facebook_hosted_share'			: return false 	; break ; 
			case 'facebook_string' 		: return "[Blog] %title% - %shorturl%" 	; break ; 
			
			case 'stumbleupon' 					: return false 	; break ; 
			case 'stumbleupon_count' 					: return false 	; break ; 
			case 'stumbleupon_hosted'				: return false 	; break ; 
			case 'stumbleupon_string' 		: return "[Blog] %title% - %shorturl%" 	; break ; 
			
			case 'print'	 					: return true 	; break ; 
			case 'print_newtab'	 					: return false 	; break ; 
			case 'print_qr'	 					: return false 	; break ; 
			case 'print_qr_end'	 					: return false 	; break ; 
			case 'print_newtab_hierarchy'	 					: return false 	; break ; 
			case 'print_newtab_hierarchy_admin'	 					: return true 	; break ; 
			case 'print_whitelist'			: return "" 	; break ; 
			case 'print_debug'			: return false 	; break ; 
			case 'print_shortcode'			: return true 	; break ; 
			case 'print_blacklist_shortcode'			: return "" 	; break ; 
			case 'print_load_external_css'	: return true 	; break ; 
			case 'print_watermark'	: return true 	; break ; 
			case 'print_css' 				: return "*div.container{
   width: auto;
   margin: 30px;
   text-align: justify;
   page-break-after:always; 
}
div.watermark {
   position: fixed;
   font-family:Helvetica,Geneva;
   text-align:center;
   width:100%;
   top: 30%;
   font-size:70px;
   opacity:0.2; 
    filter:alpha(opacity=50);
   -webkit-transform:rotate(-45deg);
    -moz-transform:rotate(-45deg);
    -o-transform:rotate(-45deg);
    transform:rotate(-45deg);
    color:#CCC;
    font-weight:bold;
    letter-spacing:5px;
}" ; break ; 
			
			case 'mail'	 					: return false 	; break ; 
			case 'mail_max'	 					: return 5 	; break ; 
			case 'mail_address'					: return  get_option('admin_email') ; break ; 
			case 'mail_name'						: return get_bloginfo('name'); break ; 
			case 'mail_string' 		: return "[Blog] %title%" 	; break ; 

			case 'refresh_time'						: return 10; break ; 

			case 'html'	 					: return "*<div class='social_linkz'>
   %buttons%
</div>" 	; break ; 
			case 'css'	 					: return "*.social_linkz { 
	padding: 5px 0 10px 0 ; 
	border-bottom-width: 0px;
	border-bottom-style: none;
}

.social_linkz a { 
	text-decoration: none;		
	border-bottom-width: 0px;
	border-bottom-style: none;
}" 	; break ; 

			case 'qr_html'	 					: return "*<div class='qr_code'>
   <p>The QR code for this page is:</p>
   <div>%qr_image%</div>
</div>" 	; break ; 
			case 'qr_html_sc'	 					: return "*<div class='qr_code'>
   <div>%qr_image%</div>
</div>" 	; break ; 
			case 'qr_css'	 					: return "*.qr_code { 
	padding: 10px ; 
	margin: 10px ; 
	border: 1px solid #CCC ; 
    text-align:center;
}" 	; break ; 
			case 'exclude' : return "*" 		; break ; 

		}
		return null ;
	}

	/** ====================================================================================================================================================
	* Init javascript for the public side
	* If you want to load a script, please type :
	* 	<code>wp_enqueue_script( 'jsapi', 'https://www.google.com/jsapi');</code> or 
	*	<code>wp_enqueue_script('my_plugin_script', plugins_url('/script.js', __FILE__));</code>
	*	<code>$this->add_inline_js($js_text);</code>
	*	<code>$this->add_js($js_url_file);</code>
	*
	* @return void
	*/
	
	function _public_js_load() {	
		//Google API for the scripts
		wp_enqueue_script('google_plus', 'https://apis.google.com/js/plusone.js');
		//Facebook Insight tags
		if ($this->get_param('facebook_id')!="") {
			echo '<meta property="fb:admins" content="'.$this->get_param('facebook_id').'" />' ; 
		}
		if ($this->get_param('mail')) {
			// jquery
			wp_enqueue_script('jquery');   
		
			ob_start() ; 
			?>
				function sendEmailSocialLinkz(sha1, id) { 
					jQuery("#wait_mail"+sha1).show();
					jQuery("#emailSocialLinkz"+sha1).attr('disabled', 'disabled');
					
					listemail = jQuery("#emailSocialLinkz"+sha1).val();
					nom = jQuery("#nameSocialLinkz"+sha1).val();
					
					var arguments = {
						action: 'emailSocialLinkz', 
						id_article: id,
						name: nom, 
						list_emails: listemail
					} 
					var ajaxurl2 = "<?php echo admin_url()."admin-ajax.php"?>" ; 
					//POST the data and append the results to the results div
					jQuery.post(ajaxurl2, arguments, function(response) {
						jQuery("#innerdialog"+sha1).html(response);
					});    
				}
		
			<?php 
			
			$java = ob_get_clean() ; 
			$this->add_inline_js($java) ; 
		}
		
		ob_start() ; 
		
		
	}
	
	/** ====================================================================================================================================================
	* Init css for the public side
	* If you want to load a style sheet, please type :
	*	<code>$this->add_inline_css($css_text);</code>
	*	<code>$this->add_css($css_url_file);</code>
	*
	* @return void
	*/
	
	function _public_css_load() {	
		$this->add_inline_css($this->get_param('css')) ; 
		$this->add_inline_css($this->get_param('qr_css')) ; 
	}
	
	/** ====================================================================================================================================================
	* The configuration page
	* 
	* @return void
	*/
	function configuration_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->pluginID;
			
		?>
		<div class="plugin-titleSL">
			<h2><?php echo $this->pluginName ?></h2>
		</div>
		
		<div class="plugin-contentSL">		
			<?php echo $this->signature ; ?>

		<?php
		
			// On verifie que les droits sont corrects
			$this->check_folder_rights( array() ) ; 
			
			//==========================================================================================
			//
			// Mise en place du systeme d'onglet
			//		(bien mettre a jour les liens contenu dans les <li> qui suivent)
			//
			//==========================================================================================
			$tabs = new SLFramework_Tabs() ; 
			
			ob_start() ; 
				$params = new SLFramework_Parameters($this, 'tab-parameters') ; 
				$title = "Twitter&#8482;" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('twitter', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_twitter.png'/> ".sprintf(__('The %s button:',$this->pluginID), $title),"","",array('twitter_count')) ; 
				$params->add_comment(sprintf(__('To share the post on %s !',$this->pluginID), $title))  ; 
				$params->add_param('twitter_count', sprintf(__('Show the counter of this %s button:',$this->pluginID), $title))  ; 
				$params->add_param('twitter_hosted', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_twitter_hosted.png'/> ".sprintf(__('The official %s button:',$this->pluginID), $title),"","",array('twitter_hosted_count'))  ; 
				$params->add_comment(__('The SSL websites may not work properly with this official button... Moreover the rendering is not perfect !',$this->pluginID)) ; 
				$params->add_param('twitter_hosted_count', sprintf(__('Show the counter of this official %s button:',$this->pluginID), $title) ) ; 
				$params->add_param('name_twitter', sprintf(__('Your %s pseudo:',$this->pluginID), $title)) ; 
				$params->add_param('twitter_string', sprintf(__('The string used for a sharing with %s:',$this->pluginID), $title)) ; 
				$params->add_comment(sprintf(__('%s is for the short url of the post',$this->pluginID), "<code>%shorturl%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the url of the post',$this->pluginID), "<code>%url%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the title of the post',$this->pluginID), "<code>%title%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the pseudo used on Twitter',$this->pluginID), "<code>%twitter_name%</code>"))  ; 
				
				$title = "FaceBook&#8482;" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('facebook', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_facebook.png'/> ".sprintf(__('The %s button:',$this->pluginID), $title),"","",array('facebook_count')) ; 
				$params->add_comment(sprintf(__('To share the post on %s !',$this->pluginID), $title)) ; 
				$params->add_param('facebook_count', sprintf(__('Show the counter of this %s button:',$this->pluginID), $title)) ; 
				$params->add_param('facebook_hosted', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_facebook_hosted.png'/> ".sprintf(__('The official %s button:',$this->pluginID), "Like ".$title)) ; 
				$params->add_param('facebook_hosted_share', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_facebook_hosted_share.png'/> ".sprintf(__('The official %s button:',$this->pluginID), "Share ".$title)) ; 
				$params->add_comment(__('The SSL websites may not work properly with this official button... Moreover the rendering is not perfect !',$this->pluginID)) ; 
				$params->add_param('facebook_id', __('Your FaceBook ID to enable Insight:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('Insight provides metrics around your content. See %s for futher details. To identify your Facebook ID, please visit the previous link and then click on Statistic of my website.',$this->pluginID), "<a href='http://www.facebook.com/insights'>Facebook Insights</a>")) ; 
				$params->add_comment(__('You may use an user id, an app id or a page id.',$this->pluginID)) ; 
				$params->add_param('facebook_string', sprintf(__('The string used for a sharing with %s:',$this->pluginID), $title)) ; 
				$params->add_comment(sprintf(__('%s is for the short url of the post',$this->pluginID), "<code>%shorturl%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the url of the post',$this->pluginID), "<code>%url%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the title of the post',$this->pluginID), "<code>%title%</code>"))  ; 

				$title = "LinkedIn&#8482;" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('linkedin', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_linkedin.png'/> ".sprintf(__('The %s button:',$this->pluginID), $title),"","",array('linkedin_count')) ; 
				$params->add_comment(sprintf(__('To share the post on %s !',$this->pluginID), $title)) ; 
				$params->add_param('linkedin_count', sprintf(__('Show the counter of this %s button:',$this->pluginID), $title))  ; 
				$params->add_param('linkedin_hosted', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_linkedin_hosted.png'/> ".sprintf(__('The official %s button:',$this->pluginID), $title),"","",array('linkedin_hosted_count')) ; 
				$params->add_comment(__('The SSL websites may not work properly with this official button... Moreover the rendering is not perfect !',$this->pluginID)) ; 
				$params->add_param('linkedin_hosted_count', sprintf(__('Show the counter of this official %s button:',$this->pluginID), $title) ) ; 
				$params->add_param('linkedin_string', sprintf(__('The string used for a sharing with %s:',$this->pluginID), $title)) ; 
				$params->add_comment(sprintf(__('%s is for the short url of the post',$this->pluginID), "<code>%shorturl%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the url of the post',$this->pluginID), "<code>%url%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the title of the post',$this->pluginID), "<code>%title%</code>"))  ; 

				$title = "Viadeo&#8482;" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('viadeo', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_viadeo.png'/> ".sprintf(__('The %s button:',$this->pluginID), $title)) ; 
				$params->add_comment(sprintf(__('To share the post on %s !',$this->pluginID), $title)) ; 
				$params->add_param('viadeo_hosted', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_viadeo_hosted.png'/> ".sprintf(__('The official %s button:',$this->pluginID), $title),"","",array('viadeo_hosted_count')) ; 
				$params->add_param('viadeo_hosted_count', sprintf(__('Show the counter of this official %s button:',$this->pluginID), $title) ) ; 
				$params->add_param('viadeo_string', sprintf(__('The string used for a sharing with %s:',$this->pluginID), $title)) ; 
				$params->add_comment(sprintf(__('%s is for the short url of the post',$this->pluginID), "<code>%shorturl%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the url of the post',$this->pluginID), "<code>%url%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the title of the post',$this->pluginID), "<code>%title%</code>"))  ; 

				$title = "Google+&#8482;" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('googleplus_standard', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_googleplus.png'/> ".sprintf(__('The %s button:',$this->pluginID), $title),"","",array('googleplus_standard_count')) ; 
				$params->add_comment(sprintf(__('To share the post on %s !',$this->pluginID), $title)) ; 
				$params->add_param('googleplus_standard_count', sprintf(__('Show the counter of this %s button:',$this->pluginID), $title)) ; 
				$params->add_param('googleplus_standard_key', sprintf(__('Your API key to be able to retrieve the counts (visit %s):',$this->pluginID), "<a href='https://code.google.com/apis/console/'>Google console</a>")) ; 
				$params->add_param('googleplus', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_googleplus_hosted.png'/> ".sprintf(__('The official %s button:',$this->pluginID), $title),"","",array('googleplus_count')) ; 
				$params->add_comment(__('The SSL websites may not work properly with this official button... Moreover the rendering is not perfect !',$this->pluginID)) ; 
				$params->add_param('googleplus_count', sprintf(__('Show the counter of this official %s button:',$this->pluginID), $title)) ; 
				
				$title = "StumbleUpon&#8482;" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('stumbleupon', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_stumbleupon.png'/> ".sprintf(__('The %s button:',$this->pluginID), $title),"","",array('stumbleupon_count')) ; 
				$params->add_comment(sprintf(__('To share the post on %s !',$this->pluginID), $title)) ; 
				$params->add_param('stumbleupon_count', sprintf(__('Show the counter of this %s button:',$this->pluginID), $title)) ; 
				$params->add_param('stumbleupon_hosted', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_stumbleupon_hosted.png'/> ".sprintf(__('The official %s button:',$this->pluginID), $title)) ; 
				$params->add_param('stumbleupon_string', sprintf(__('The string used for a sharing with %s:',$this->pluginID), $title)) ; 
				$params->add_comment(sprintf(__('%s is for the short url of the post',$this->pluginID), "<code>%shorturl%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the url of the post',$this->pluginID), "<code>%url%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the title of the post',$this->pluginID), "<code>%title%</code>"))  ; 
				
				$title = "Pinterest&#8482;" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('pinterest_hosted', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_pinterest_hosted.jpg'/> ".sprintf(__('The %s button:',$this->pluginID), $title),"","",array('pinterest_hosted_count', 'pinterest_hosted_defaultimage')) ; 
				$params->add_comment(sprintf(__('To share the post on %s !',$this->pluginID), $title)) ; 
				$params->add_param('pinterest_hosted_count', sprintf(__('Show the counter of this %s button:',$this->pluginID), $title)) ; 
				$params->add_param('pinterest_hosted_defaultimage', __('Default image:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('%s requires that an image is pinned. By default, the plugin will take the first image in the post but if there is not any image, this image will be used.',$this->pluginID), $title)) ; 
				$params->add_param('pinterest_string', sprintf(__('The string used for a sharing with %s:',$this->pluginID), $title)) ; 
				$params->add_comment(sprintf(__('%s is for the short url of the post',$this->pluginID), "<code>%shorturl%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the url of the post',$this->pluginID), "<code>%url%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the title of the post',$this->pluginID), "<code>%title%</code>"))  ; 

				$title = "Print" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('print', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_print.png'/> ".sprintf(__('The standard %s button:',$this->pluginID), $title)) ; 
				$params->add_param('print_newtab', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_print_newtab.png'/> ".sprintf(__('The %s button that open a new tab for a pretty printing:',$this->pluginID), $title),"","",array('print_whitelist','print_blacklist_shortcode','print_debug', 'print_shortcode', 'print_css', 'print_load_external_css', 'print_newtab_hierarchy', 'print_newtab_hierarchy_admin', 'print_watermark')) ; 
				$params->add_param('print_whitelist', __('Separated-coma list of filters that are allowed to modify the display of the printed pages:',$this->pluginID)) ; 
				$params->add_comment(__('With this option, you may allow the execution of specific plugins that modify the text to be printed. This options should be a list of filters separeted by coma, without blanks.',$this->pluginID)) ; 
				$params->add_comment(__('In order to dertermine the list of the filter available, you may tick the debug option below and then display a printed page: The list of avaliable filters will be displayed at the top of the page.',$this->pluginID)) ; 
				$params->add_param('print_qr', sprintf(__('Add a QR code %s with the URL of the article/post just below the title when the article is printed:',$this->pluginID), "<img src='".plugin_dir_url("/").'/'.str_replace(basename( __FILE__),"",plugin_basename( __FILE__)).'img/qr_button.png'."'/>")) ; 
				$params->add_param('print_qr_end', sprintf(__('Add a QR code %s with the URL of the article/post at the end of the article when the article is printed:',$this->pluginID), "<img src='".plugin_dir_url("/").'/'.str_replace(basename( __FILE__),"",plugin_basename( __FILE__)).'img/qr_button.png'."'/>")) ; 
				$params->add_param('print_debug', __('Debug mode to display available filters (displayed only if the current user is logged in):',$this->pluginID)) ; 
				$params->add_param('print_shortcode', __('Replace shortcodes in the page:',$this->pluginID),"","",array('print_blacklist_shortcode')) ; 
				$params->add_param('print_blacklist_shortcode', __('Separated-coma list of shortcode that are not to be replaced:',$this->pluginID)) ; 
				$params->add_param('print_load_external_css', __('Load the CSS of the website for the printed pages:',$this->pluginID)) ; 
				$params->add_param('print_css', __('Add the following CSS for the printed pages:',$this->pluginID)) ; 
				$params->add_param('print_watermark', __('Print the name of your URL of the website on printed pages (watermarking):',$this->pluginID)) ; 
				$params->add_param('print_newtab_hierarchy', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_print_newtab_hiera.png'/> ".sprintf(__('The %s button that open a new tab for a pretty printing (all pages under the current page in the hierarchy is printed in a single click):',$this->pluginID), $title),"","",array('print_newtab_hierarchy_admin')) ; 
				$params->add_param('print_newtab_hierarchy_admin',__('Allow the pretty printing with hierarchy only for logged users',$this->pluginID)) ; 

				$title = "Mail" ; 
				$params->add_title(sprintf(__('Display %s button?',$this->pluginID), $title)) ; 
				$params->add_param('mail', "<img src='".plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/lnk_mail.png'/> ".sprintf(__('The %s button:',$this->pluginID), $title),"","",array('mail_max', 'mail_address', 'mail_name')) ; 
				$params->add_param('mail_max', __('The maximum number of emails for each mailing:',$this->pluginID)) ; 
				$params->add_param('mail_name', __('The name used to send the email:',$this->pluginID)) ; 
				$params->add_param('mail_address', __('The mail address used to send the email:',$this->pluginID)) ; 
				$address = explode("/", home_url('/')) ; 
				$params->add_comment(sprintf(__('You may use the admin email %s, a noreply address such as %s or any other email',$this->pluginID), "<code>".get_option('admin_email')."</code>","<code>noreply@".str_replace("www.", "", $address[2])."</code>")) ; 
				$params->add_param('mail_string', __('The string used for the subject of the mail:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('%s is for the short url of the post',$this->pluginID), "<code>%shorturl%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the url of the post',$this->pluginID), "<code>%url%</code>"))  ; 
				$params->add_comment(sprintf(__('%s is for the title of the post',$this->pluginID), "<code>%title%</code>"))  ; 

				
				$params->add_title(__('Display all these buttons in the excerpt?',$this->pluginID)) ; 
				$params->add_param('display_in_excerpt', __('These buttons should be displayed in excerpt:',$this->pluginID)) ; 

				$params->add_title(__('Where do you want to display the buttons in post?',$this->pluginID)) ; 
				$params->add_param('display_top_in_post', "".__('At the Top:',$this->pluginID)) ; 
				$params->add_param('display_bottom_in_post', "".__('At the Bottom:',$this->pluginID)) ; 

				$params->add_title(__('Where do you want to display the buttons in page?',$this->pluginID)) ; 
				$params->add_param('display_top_in_page', "".sprintf(__('At the Top:',$this->pluginID), $title)) ; 
				$params->add_param('display_bottom_in_page', "".sprintf(__('At the Bottom:',$this->pluginID), $title)) ; 
				
				$params->add_title(__('Advanced options',$this->pluginID)) ; 
				$params->add_param('refresh_time', __('Number of minutes between two refreshes:',$this->pluginID)) ; 
				$params->add_param('html', __('HTML:',$this->pluginID)) ; 
				$default = str_replace("*", "", str_replace(" ", "&nbsp;", str_replace("\n", "<br>", str_replace(">", "&gt;", str_replace("<", "&lt;", $this->get_default_option('html'))))))."<br/>" ; 
				$params->add_comment(sprintf(__('Default HTML is : %s with %s the displayed buttons',$this->pluginID), "<br/>"."<code>".$default."</code>", "<code>%buttons%</code>")) ; 
				$params->add_param('css', __('CSS:',$this->pluginID)) ; 
				$default = str_replace("*", "", str_replace(" ", "&nbsp;", str_replace("\n", "<br>", str_replace(">", "&gt;", str_replace("<", "&lt;", $this->get_default_option('css'))))))."<br/>" ; 
				$params->add_comment(sprintf(__('Default CSS is : %s',$this->pluginID), "<br/>"."<code>".$default."</code>")) ; 
				$params->add_param('exclude', __('Page to be excluded:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__("Please enter one entry per line. If the page %s is to be excluded, you may enter %s.",  $this->pluginID), "<code>http://yourdomain.tld/contact/</code>","<code>contact</code>")) ; 

				$params->add_title(__('Advanced options for QR code',$this->pluginID)) ; 
				$params->add_param('qr_html', __('The HTML to display the QR code (all but the shortcode):',$this->pluginID)) ; 
				$params->add_comment(__('The default value is:',$this->pluginID)) ; 
				$params->add_comment_default_value('qr_html') ;  
				$params->add_param('qr_html_sc', __('The HTML to display the QR code for the shortcode:',$this->pluginID)) ; 
				$params->add_comment(__('The default value is:',$this->pluginID)) ; 
				$params->add_comment_default_value('qr_html_sc') ; 
				$params->add_param('qr_css', __('The CSS to display the QR code:',$this->pluginID)) ; 
				$params->add_comment(__('The default value is:',$this->pluginID)) ; 
				$params->add_comment_default_value('qr_css') ; 
				
				$params->flush() ; 
			$tabs->add_tab(__('Parameters',  $this->pluginID), ob_get_clean() , plugin_dir_url("/").'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_param.png") ; 	
			
			// HOW To
			ob_start() ;
				echo "<p>".__('This plugin enables the sharing of your posts/pages on the social networks by adding different social buttons.', $this->pluginID)."</p>" ;
			$howto1 = new SLFramework_Box (__("Purpose of that plugin", $this->pluginID), ob_get_clean()) ; 
			ob_start() ;
				echo "<p>".__('You can configure the place of the social buttons in the configuration tab.', $this->pluginID)."</p>" ;
				echo "<p>".sprintf(__('It is also possible to manually insert the social buttons in your post by adding the shortcode %s or %s', $this->pluginID), "<code>[sociallinkz]</code>", "<code>[sociallinkz url='http://domain.tld' buttons='facebook,google+' desc='Short description']</code>")."</p>" ; 
				echo "<p>".__('The name of the different buttons for these shortcodes are:', $this->pluginID)."</p>" ;
				echo "<ul style='list-style-type: disc;padding-left:40px;'>" ; 
					echo "<li>facebook</li>" ;
					echo "<li>facebook_hosted</li>" ; 
					echo "<li>twitter</li>" ; 
					echo "<li>twitter_hosted</li>" ; 
					echo "<li>googleplus_standard</li>" ; 
					echo "<li>googleplus</li>" ; 
					echo "<li>linkedin</li>" ; 
					echo "<li>linkedin_hosted</li>" ; 
					echo "<li>viadeo</li>" ; 
					echo "<li>viadeo_hosted</li>" ; 
					echo "<li>stumbleupon</li>" ; 
					echo "<li>stumbleupon_hosted</li>" ; 
					echo "<li>pinterest</li>" ; 
					echo "<li>pinterest_hosted</li>" ; 
					echo "<li>print</li>" ; 
					echo "<li>print_newtab</li>" ; 
					echo "<li>print_newtab_hierarchy</li>" ; 
					echo "<li>mail</li>" ; 
				echo "</ul>" ; 
				echo "<p>".__('Please note that there is also a widget available to display the buttons.', $this->pluginID)."</p>" ;
				echo "<p>".sprintf(__('If your are a theme developer, you also may add %s in your theme to display the buttons (with %s the post to use or if the post should be the blog frontpage, you may use %s).', $this->pluginID), "<code>".'$sociallinkz->print_buttons($post)'."</code>", '<code>$post</code>', '<code>$post = new stdClass ; $post->ID = 0 ;</code>')."</p>" ; 
			$howto2 = new SLFramework_Box (__("How to display the social buttons?", $this->pluginID), ob_get_clean()) ; 
			ob_start() ;
				echo "<p>".sprintf(__('In addition, you may add any QR code you want by adding the shortcode %s. If you do not set the text, it will be replaced with the URL to the article', $this->pluginID), '<code>[qrcode size="4" px_size="2" frame_size="5"]Your text to be encoded[/qrcode]</code>')."</p>" ; 
				echo "<p>".__('A button is inserted in the post editor to ease the insertion of QR code.', $this->pluginID)."</p>" ;
			$howto3 = new SLFramework_Box (__("Custom QR code", $this->pluginID), ob_get_clean()) ; 
			ob_start() ;
				 echo $howto1->flush() ; 
				 echo $howto2->flush() ; 
				 echo $howto3->flush() ; 
			$tabs->add_tab(__('How To',  $this->pluginID), ob_get_clean() , plugin_dir_url("/").'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_how.png") ; 				

			ob_start() ; 
				$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
				$trans = new SLFramework_Translation($this->pluginID, $plugin) ; 
				$trans->enable_translation() ; 
			$tabs->add_tab(__('Manage translations',  $this->pluginID), ob_get_clean() , plugin_dir_url("/").'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_trad.png") ; 	

			ob_start() ; 
				$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
				$trans = new SLFramework_Feedback($plugin, $this->pluginID) ; 
				$trans->enable_feedback() ; 
			$tabs->add_tab(__('Give feedback',  $this->pluginID), ob_get_clean() , plugin_dir_url("/").'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_mail.png") ; 	
			
			ob_start() ; 
				$trans = new SLFramework_OtherPlugins("sedLex", array('wp-pirates-search')) ; 
				$trans->list_plugins() ; 
			$tabs->add_tab(__('Other plugins',  $this->pluginID), ob_get_clean() , plugin_dir_url("/").'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_plug.png") ; 	
			
			echo $tabs->flush() ; 					
			
			echo $this->signature ; ?>
		</div>
		<?php
	}
	
	/** ====================================================================================================================================================
	* Called when the content is displayed
	*
	* @param string $content the content which will be displayed
	* @param string $type the type of the article (e.g. post, page, custom_type1, etc.)
	* @param boolean $excerpt if the display is performed during the loop
	* @return string the new content
	*/
	
	function _modify_content($content, $type, $excerpt) {	
		global $post ; 
		
		// We check whether there is an exclusion
		$exclu = $this->get_param('exclude') ;
		$exclu = explode("\n", $exclu) ;
		foreach ($exclu as $e) {
			$e = trim(str_replace("\r", "", $e)) ; 
			if ($e!="") {
				$e = "#".$e."#i"; 
				if (preg_match($e, get_permalink($post->ID))) {
					return $content ; 
				}
				if (preg_match($e, $_SERVER['REQUEST_URI'])) {
					return $content ; 
				}				
			}
		}
		
		// If it is the loop and an the_except is called, we leave
		if ($excerpt) {
			// Excerpt
			if ($this->get_param('display_in_excerpt')) {
				return $content.$this->print_buttons($post) ; 
			}
		} else {
			// Page
			if ($type=="page") {
				$return =  $content ; 
				if ($this->get_param('display_bottom_in_page')) {
					$return =  $return.$this->print_buttons($post) ;  
				}
				if ($this->get_param('display_top_in_page')) {
					$return =  $this->print_buttons($post).$return ; 
				}
				return $return; 				
			}
			// Post
			if ($type=="post") {
				$return =  $content ; 
				if ($this->get_param('display_bottom_in_post')) {
					$return =  $return.$this->print_buttons($post) ;  
				}
				if ($this->get_param('display_top_in_post')) {
					$return =  $this->print_buttons($post).$return ; 
				}
				return $return; 				
			}
		}
		
		return $content ; 
	}
		
	/** ====================================================================================================================================================
	* Shortcode to Print the buttons
	* 
	* @return void
	*/

	function display_button_shortcode( $_atts, $text ) {
		global $post ; 
		
		// We check whether there is an exclusion
		$exclu = $this->get_param('exclude') ;
		$exclu = explode("\n", $exclu) ;
		foreach ($exclu as $e) {
			$e = trim(str_replace("\r", "", $e)) ; 
			if ($e!="") {
				$e = "#".$e."#i"; 
				if (preg_match($e, get_permalink($post->ID))) {
					return "" ; 
				}
				if (preg_match($e, $_SERVER['REQUEST_URI'])) {
					return "" ; 
				}				
			}
		}
				
		extract( shortcode_atts( array(
			'url' => '',
			'button' => '',
			'desc' => ''
		), $_atts ) );
		return $this->print_buttons($post, $url, $button, $desc) ; 
	}
	
	/** ====================================================================================================================================================
	* Print the buttons
	* 
	* @return void
	*/
	
	function print_buttons($post, $forceURL="", $forceButton="",$forceDesc="") {
		global $do_not_show_inSocialLinkz ; 
		
		if ($forceButton!="") {
			$forceButton = ",".$forceButton."," ; 
			$forceButton = str_replace(" ", "", $forceButton) ; 
		}
		
		$rand = rand(0,1000000000) ; 
		?>
		<script>
			function forceUpdateSocialLinkz_<?php echo $rand ; ?>() {	
				<?php
				if ($forceURL=="") {
				?>
				var arguments = {
					action: 'forceUpdateSocialLinkz', 
					id:<?php echo $post->ID ;  ?>
				} 
				<?php 
				} else {
				?>
				var arguments = {
					action: 'forceUpdateSocialLinkz', 
					id:-1, 
					url:"<?php echo str_replace('"', "", $forceURL) ; ?>", 
				} 				
				<?php
				}
				?>
				//POST the data and append the results to the results div
				var ajaxurl2 = "<?php echo admin_url()."admin-ajax.php"?>" ; 
				jQuery.post(ajaxurl2, arguments, function(response) {
					// nothing
				});
			}
			
			// We launch the callback
			if (window.attachEvent) {window.attachEvent('onload', forceUpdateSocialLinkz_<?php echo $rand ; ?>);}
			else if (window.addEventListener) {window.addEventListener('load', forceUpdateSocialLinkz_<?php echo $rand ; ?>, false);}
			else {document.addEventListener('load', forceUpdateSocialLinkz_<?php echo $rand ; ?>, false);} 
		</script>

		<?php
		if ($forceURL!="") {
			$url = $forceURL ; 
			$long_url = $forceURL ; 
			$titre = $forceDesc ; 	
			$postID = -1 ; 		
		} else if ($post->ID==0) {
			$url = home_url("/") ; 
			$long_url = home_url("/") ; 
			$postID = $post->ID ; 	
			if ($forceDesc!="")
				$titre = $forceDesc ; 	
			else
				$titre = get_bloginfo('name') ." - ".get_bloginfo('description') ; 		
		} else {
			$url = wp_get_shortlink($post->ID) ; 
			$long_url = get_permalink($post->ID) ; 
			$postID = $post->ID ; 	
			if ($forceDesc!="")
				$titre = $forceDesc ; 	
			else
				$titre = $post->post_title ; 
		}
		
		if ($do_not_show_inSocialLinkz) {
			return ; 
		}
		ob_start() ; 
		?>
			<?php
			
			
			if ((($this->get_param('facebook'))&&($forceButton==""))||((strpos($forceButton, ',facebook,')!==false)&&($forceButton!=""))) {
				
				$facebook_title = str_replace("%url%", $long_url, $this->get_param('facebook_string')) ; 
				$facebook_title = str_replace("%shorturl%", $url, $facebook_title) ;
				$facebook_title = str_replace("%title%", $titre, $facebook_title) ; 
				?>
				<a rel="nofollow" target="_blank" href="http://www.facebook.com/sharer.php?u=<?php echo urlencode($long_url) ; ?>&amp;t=<?php echo urlencode($facebook_title) ; ?>" title="<?php echo sprintf(__("Share -%s- on %s", $this->pluginID), htmlentities($titre, ENT_QUOTES, 'UTF-8'), "Facebook") ; ?>">
					<img class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ; ?>/img/lnk_facebook.png" alt="Facebook" height="24" width="24"/></a>
				<?php
				if ((($this->get_param('facebook_count'))&&($forceButton==""))||((strpos($forceButton, ',facebook_count,')!==false)&&($forceButton!=""))) {
					if ($postID!=-1)
						$this->display_bubble($this->get_counter("facebook", $postID)) ; 
					else 
						$this->display_bubble($this->get_counter("facebook", -1, $url)) ; 
				}
			}
			
			if ((($this->get_param('facebook_hosted'))&&($forceButton==""))||((strpos($forceButton, ',facebook_hosted,')!==false)&&($forceButton!=""))) {
				?>
				<span id="fb-root"></span><script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like href="<?php echo $long_url ; ?>" send="false" layout="button_count" width="35" show_faces="false" action="like" font=""></fb:like>
				<?php
			}
			
			if ((($this->get_param('facebook_hosted_share'))&&($forceButton==""))||((strpos($forceButton, ',facebook_hosted_share,')!==false)&&($forceButton!=""))) {
				?>
				<a name="fb_share" type="button_count" share_url="<?php echo $long_url ?>" href="http://www.facebook.com/sharer.php">Share</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
				<?php
			}
			
			if ((($this->get_param('twitter'))&&($forceButton==""))||((strpos($forceButton, ',twitter,')!==false)&&($forceButton!=""))) {
				$via = "" ; 
				if ($this->get_param('name_twitter')!="") {
					$via = $this->get_param('name_twitter') ; 
					if ((strlen($via)!=0)&&(substr($via, 0,1) != "@")) {
						$via = "@".$via ; 
					}
				}
				$twitter_title = str_replace("%url%", $long_url, $this->get_param('twitter_string')) ; 
				$twitter_title = str_replace("%shorturl%", $url, $twitter_title) ;
				$twitter_title = str_replace("%title%", $titre, $twitter_title) ; 
				$twitter_title = str_replace("%twitter_name%", $via, $twitter_title) ; 
				
				?>
				<a rel="nofollow" target="_blank" href="http://twitter.com/?status=<?php echo str_replace('+','%20',urlencode($twitter_title)) ; ?>" title="<?php echo sprintf(__("Share -%s- on %s", $this->pluginID), htmlentities($titre, ENT_QUOTES, 'UTF-8'), "Twitter") ; ?>">
					<img class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ;  ?>/img/lnk_twitter.png" alt="Twitter" height="24" width="24"/></a>
				<?php
				if ((($this->get_param('twitter_count'))&&($forceButton==""))||((strpos($forceButton, ',twitter_count,')!==false)&&($forceButton!=""))) {
					if ($postID!=-1)
						$this->display_bubble($this->get_counter("twitter", $postID)) ; 
					else 
						$this->display_bubble($this->get_counter("twitter", -1, $url)) ; 
				}
			}
			
			if ((($this->get_param('twitter_hosted'))&&($forceButton==""))||((strpos($forceButton, ',twitter_hosted,')!==false)&&($forceButton!=""))) {
				$via = "" ; 
				if ($this->get_param('name_twitter')!="") {
					$via = 'data-via="'.$this->get_param('name_twitter').'"' ; 
				}
				$coun = "none" ; 
				if ((($this->get_param('twitter_hosted_count'))&&($forceButton==""))||((strpos($forceButton, ',twitter_hosted_count,')!==false)&&($forceButton!=""))) {
					$coun = 'horizontal' ; 
				}
				
				$via2 = "" ; 
				if ($this->get_param('name_twitter')!="") {
					$via2 = $this->get_param('name_twitter') ; 
					if ((strlen($via2)!=0)&&(substr($via2, 0,1) != "@")) {
						$via2 = "@".$via2 ; 
					}
				}
				$twitter_title = str_replace("%url%", $long_url, $this->get_param('twitter_string')) ; 
				$twitter_title = str_replace("%shorturl%", $url, $twitter_title) ;
				$twitter_title = str_replace("%title%", $titre, $twitter_title) ; 
				$twitter_title = str_replace("%twitter_name%", $via2, $twitter_title) ; 

				?>
				<a href="http://twitter.com/share" class="twitter-share-button" data-text="<?php echo $twitter_title ; ?>" data-url="<?php echo urlencode($url) ; ?>" data-count="<?php echo $coun ; ?>" <?php echo $via ; ?> ><?php echo __('Tweet', $this->pluginID) ; ?></a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
				<?php
			}

			if ((($this->get_param('googleplus_standard'))&&($forceButton==""))||((strpos($forceButton, ',googleplus_standard,')!==false)&&($forceButton!=""))) {
				?>
				<a rel="nofollow" target="_blank" href="https://plus.google.com/share?url=<?php echo $long_url ; ?>" title="<?php echo sprintf(__("Share -%s- on %s", $this->pluginID), htmlentities($titre, ENT_QUOTES, 'UTF-8'), "Google+") ; ?>">
					<img class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ; ?>/img/lnk_googleplus.png" alt="Google+" height="24" width="24"/></a>
				<?php
				if ((($this->get_param('googleplus_standard_count'))&&($forceButton==""))||((strpos($forceButton, ',googleplus_standard_count,')!==false)&&($forceButton!=""))) {
					if ($postID!=-1)
						$this->display_bubble($this->get_counter("google+", $postID)) ; 
					else 
						$this->display_bubble($this->get_counter("google+", -1, $url)) ; 
				}
			}
			
			if ((($this->get_param('googleplus'))&&($forceButton==""))||((strpos($forceButton, ',googleplus,')!==false)&&($forceButton!=""))) {
				$count = "false" ; 
				if ((($this->get_param('googleplus_count'))&&($forceButton==""))||((strpos($forceButton, ',googleplus_count,')!==false)&&($forceButton!=""))) {
					$count = "true" ; 
				}
				?>
				<g:plusone size="standard" count="<?php echo $count; ?>"></g:plusone>
				<?php
			}
			
			
			if ((($this->get_param('linkedin'))&&($forceButton==""))||((strpos($forceButton, ',linkedin,')!==false)&&($forceButton!=""))) {
				$linkedin_title = str_replace("%url%", $long_url, $this->get_param('linkedin_string')) ; 
				$linkedin_title = str_replace("%shorturl%", $url, $linkedin_title) ;
				$linkedin_title = str_replace("%title%", $titre, $linkedin_title) ; 
	
				?>
				<a rel="nofollow" target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo urlencode($long_url) ; ?>&amp;title=<?php echo str_replace('+','%20',urlencode($linkedin_title)) ; ?>&amp;source=<?php echo urlencode(get_bloginfo('name')) ; ?>" title="<?php echo sprintf(__("Share -%s- on %s", $this->pluginID), htmlentities($titre, ENT_QUOTES, 'UTF-8'), "LinkedIn") ; ?>">
					<img class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ;  ?>/img/lnk_linkedin.png" alt="LinkedIn" height="24" width="24"/></a>
				<?php
				if ((($this->get_param('linkedin_count'))&&($forceButton==""))||((strpos($forceButton, ',linkedin_count,')!==false)&&($forceButton!=""))) {
					if ($postID!=-1)
						$this->display_bubble($this->get_counter("linkedin", $postID)) ; 
					else 
						$this->display_bubble($this->get_counter("linkedin", -1, $url)) ; 
				}
			}
			
			if ((($this->get_param('linkedin_hosted'))&&($forceButton==""))||((strpos($forceButton, ',linkedin_hosted,')!==false)&&($forceButton!=""))) {
				$coun = "" ; 
				if ((($this->get_param('linkedin_hosted_count'))&&($forceButton==""))||((strpos($forceButton, ',linkedin_hosted_count,')!==false)&&($forceButton!=""))) {
					$coun = 'data-counter="right"' ; 
				}
				?>
				<script src="http://platform.linkedin.com/in.js" type="text/javascript"></script><script type="IN/Share" <?php echo $coun ; ?>></script>
				<?php
			}
			
			if ((($this->get_param('viadeo'))&&($forceButton==""))||((strpos($forceButton, ',viadeo,')!==false)&&($forceButton!=""))) {
				$viadeo_title = str_replace("%url%", $long_url, $this->get_param('viadeo_string')) ; 
				$viadeo_title = str_replace("%shorturl%", $url, $viadeo_title) ;
				$viadeo_title = str_replace("%title%", $titre, $viadeo_title) ; 

				?>
				<a rel="nofollow" target="_blank" href="http://www.viadeo.com/shareit/share/?url=<?php echo urlencode($long_url) ; ?>&amp;title=<?php echo str_replace('+','%20',urlencode($viadeo_title)) ; ?>&amp;overview=<?php echo str_replace('+','%20',urlencode($viadeo_title)) ; ?>" title="<?php echo sprintf(__("Share -%s- on %s", $this->pluginID), htmlentities($titre, ENT_QUOTES, 'UTF-8'), "Viadeo") ; ?>">
					<img class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ;  ?>/img/lnk_viadeo.png" alt="Viadeo" height="24" width="24"/></a>
				<?php
			}
			
			if ((($this->get_param('viadeo_hosted'))&&($forceButton==""))||((strpos($forceButton, ',viadeo_hosted,')!==false)&&($forceButton!=""))) {
				$coun = "" ; 
				if ((($this->get_param('linkedin_hosted_count'))&&($forceButton==""))||((strpos($forceButton, ',linkedin_hosted_count,')!==false)&&($forceButton!=""))) {
					$coun = 'data-count="right"' ; 
				}
				$viadeoUrl = 'data-url="'.$url.'"' ; 
				?>
				
				<script type="text/javascript">window.viadeoWidgetsJsUrl = document.location.protocol+"//widgets.viadeo.com";(function(){var e = document.createElement('script'); e.type='text/javascript'; e.async = true;e.src = viadeoWidgetsJsUrl+'/js/viadeowidgets.js';var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(e, s);})();</script><div class="viadeo-share" <?php echo $viadeoUrl ?> data-display="btnlight" <?php echo $coun ;?> ></div>
				<?php
			}
			
			if ((($this->get_param('stumbleupon'))&&($forceButton==""))||((strpos($forceButton, ',stumbleupon,')!==false)&&($forceButton!=""))) {
				$stumbleupon_title = str_replace("%url%", $long_url, $this->get_param('stumbleupon_string')) ; 
				$stumbleupon_title = str_replace("%shorturl%", $url, $stumbleupon_title) ;
				$stumbleupon_title = str_replace("%title%", $titre, $stumbleupon_title) ; 

				?>
				<a rel="nofollow" target="_blank" href="http://www.stumbleupon.com/submit?url=<?php echo urlencode($long_url) ; ?>&amp;title=<?php echo str_replace('+','%20',urlencode($stumbleupon_title)) ; ?>" title="<?php echo sprintf(__("Share -%s- on %s", $this->pluginID), htmlentities($titre, ENT_QUOTES, 'UTF-8'), "StumbleUpon") ; ?>">
					<img class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ;  ?>/img/lnk_stumbleupon.png" alt="StumbleUpon" height="24" width="24"/></a>
				<?php
				if ((($this->get_param('stumbleupon_count'))&&($forceButton==""))||((strpos($forceButton, ',stumbleupon_count,')!==false)&&($forceButton!=""))) {
					if ($postID!=-1)
						$this->display_bubble($this->get_counter("stumbleupon", $postID)) ; 
					else 
						$this->display_bubble($this->get_counter("stumbleupon", -1, $url)) ; 
				}
			}
			
			if ((($this->get_param('stumbleupon_hosted'))&&($forceButton==""))||((strpos($forceButton, ',stumbleupon_hosted,')!==false)&&($forceButton!=""))) {
				?>
				<script src="http://www.stumbleupon.com/hostedbadge.php?s=1&amp;r=<?php echo urlencode($long_url) ?>"></script>
				<?php
			}
			
			
			if ((($this->get_param('pinterest_hosted'))&&($forceButton==""))||((strpos($forceButton, ',pinterest_hosted,')!==false)&&($forceButton!=""))) {
				// Get all image of the post
				$img = $this->get_first_image(get_the_ID()) ; 
				if ($img == "") {
					if ($this->get_param('pinterest_hosted_defaultimage')!=$this->get_default_option('pinterest_hosted_defaultimage')) {
						$upload = wp_upload_dir() ;
						$img = $upload['baseurl']."/".$this->get_param('pinterest_hosted_defaultimage') ; 
					} else {
						$img = plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__))."/img/no_image.png" ; 
					}
				} 
				
				$coun = "none" ; 
				if ((($this->get_param('pinterest_hosted_count'))&&($forceButton==""))||((strpos($forceButton, ',pinterest_hosted_count,')!==false)&&($forceButton!=""))) {
					$coun = 'horizontal' ; 
				}
				$pinterest_title = str_replace("%url%", $long_url, $this->get_param('pinterest_string')) ; 
				$pinterest_title = str_replace("%shorturl%", $url, $pinterest_title) ;
				$pinterest_title = str_replace("%title%", $titre, $pinterest_title) ; 
				?>
				<a href="http://pinterest.com/pin/create/button/?url=<?php echo urlencode($url) ; ?>&amp;media=<?php echo urlencode($img) ; ?>&amp;description=<?php echo str_replace('+','%20',urlencode($pinterest_title)) ; ?>" class="pin-it-button" count-layout="<?php echo $coun ; ?>"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a><script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
				<?php
			}
			
			if ($postID != -1) {
			
				if ((($this->get_param('print'))&&($forceButton==""))||((strpos($forceButton, ',print,')!==false)&&($forceButton!=""))) {
					?>
					<a rel="nofollow" target="_blank" href="#" title="<?php echo __("Print this page", $this->pluginID) ;?>">
						<img onclick="window.print();return false;" class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ; ?>/img/lnk_print.png" alt="Print" height="24" width="24"/></a>
					<?php
				}
				
				if ((($this->get_param('print_newtab'))&&($forceButton==""))||((strpos($forceButton, ',print_newtab,')!==false)&&($forceButton!=""))) {
					?>
					<a rel="nofollow" target="_blank" href="?print=socialz_page" title="<?php echo __("Pretty print this page", $this->pluginID) ;?>">
						<img class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ; ?>/img/lnk_print_newtab.png" alt="Pretty Print" height="24" width="24"/></a>
					<?php
				}
				
				if ((is_user_logged_in())||(!$this->get_param('print_newtab_hierarchy_admin'))) {
					if ((($this->get_param('print_newtab_hierarchy'))&&($forceButton==""))||((strpos($forceButton, ',print_newtab_hierarchy,')!==false)&&($forceButton!=""))) {
						?>
						<a rel="nofollow" target="_blank" href="?print=socialz_hiera" title="<?php echo __("Pretty print this page and all pages under", $this->pluginID) ;?>">
							<img class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ; ?>/img/lnk_print_newtab_hiera.png" alt="Hierarchical Pretty Print " height="24" width="24"/></a>
						<?php
					}
				}
				
				if ((($this->get_param('mail'))&&($forceButton==""))||((strpos($forceButton, ',mail,')!==false)&&($forceButton!=""))) {
					$randsha1 = sha1($long_url.rand(1,10000)) ; 
					?>
					<a rel="nofollow" target="_blank" href="#" title="<?php echo __("Mail", $this->pluginID) ;?>">
						<img onclick="openEmailSocialLinkz('<?php echo $randsha1 ?>');return false;" class="lnk_social_linkz" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ; ?>/img/lnk_mail.png" alt="Mail" height="24" width="24"/></a>
					<div id="mask<?php echo $randsha1 ?>" class="social_mask"></div>
					<div id="dialog<?php echo $randsha1 ?>" class="social_window">
						<div id="innerdialog<?php echo $randsha1 ?>">
							<h3><?php echo __("Send this article by email", $this->pluginID) ;?></h3>
							<p class='textEmailSocialLinkz'><?php echo __("What is your name?", $this->pluginID) ;?></p>
							<p><input name="nameSocialLinkz<?php echo $randsha1 ?>" id="nameSocialLinkz<?php echo $randsha1 ?>" /></p>
							<p class='textEmailSocialLinkz'><?php echo sprintf(__("Please indicate below the emails to which you want to send this article: %s", $this->pluginID), "<b>".$titre."</b>") ;?></p>
							<p><textarea name="emailSocialLinkz<?php echo $randsha1 ?>" id="emailSocialLinkz<?php echo $randsha1 ?>" rows="5"></textarea></p>
							<p class='closeEmailSocialLinkz'><?php echo sprintf(__("Enter one email per line. No more than %s emails.", $this->pluginID), $this->get_param('mail_max')) ;?></p>
							<p class='sendEmailSocialLinkz'><a href="#" title="<?php echo __("Close", $this->pluginID) ;?>" onclick="sendEmailSocialLinkz('<?php echo $randsha1 ?>', <?php echo $post->ID ?>);return false;"><span class='sendEmailSocialLinkz'><?php echo __("Send", $this->pluginID) ;?></span></a></p>
						</div>
						<p class='closeEmailSocialLinkz'><a href="#" title="<?php echo __("Close", $this->pluginID) ;?>" onclick="closeEmailSocialLinkz('<?php echo $randsha1 ?>');return false;"><span class='closeEmailSocialLinkz'><?php echo __("Close", $this->pluginID) ;?></span></a></p>
					</div>
					<?php
				}
			}
			?>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return trim(str_replace("\r", "", str_replace("\n", "", str_replace('%buttons%', $content, $this->get_param('html'))))) ; 
	}
	
	
	/** ====================================================================================================================================================
	* Get counter
	* 
	* @return void
	*/

	function get_counter($social, $id, $url="") {
		global $wpdb ; 
		if (($id!=-1) && (isset($this->cache[$id]->{$social}))) {
			return $this->cache[$id]->{$social} ; 
		} else if (($id==-1) && (isset($this->cache[$url]->{$social}))) {
			return $this->cache[$url]->{$social} ; 
		} else {
			if ($id!=-1) {
				$select = "SELECT counters FROM ".$this->table_name." WHERE id_post='".$id."'" ;
				$result = $wpdb->get_var($select) ; 
				if (($result==null)||($result==false)||($result=="")) {
					return 0 ; 
				} else {
					$result = @json_decode($result) ;
					if ($result==NULL) {
						return 0 ; 
					} else {
						// Cache the result to avoid plurality of Mysql Request
						$this->cache[$id] = $result ; 
						// Return the result
						if (isset($result->{$social})) {
							return $result->{$social} ; 
						} else {
							return 0 ; 
						}
					}
				}
			} else {
				$select = "SELECT counters FROM ".$this->table_name." WHERE url='".addslashes($url)."'" ;
				$result = $wpdb->get_var($select) ; 
				if (($result==null)||($result==false)||($result=="")) {
					return 0 ; 
				} else {
					$result = @json_decode($result) ;
					if ($result==NULL) {
						return 0 ; 
					} else {
						// Cache the result to avoid plurality of Mysql Request
						$this->cache[$url] = $result ; 
						// Return the result
						if (isset($result->{$social})) {
							return $result->{$social} ; 
						} else {
							return 0 ; 
						}
					}
				}
			}
		}
	}
	
	/** ====================================================================================================================================================
	* Set counter
	* 
	* @return void
	*/

	function set_counter($socials, $id, $url='') {
		global $wpdb ; 
		$new_counters = array() ; 
		
		if ($id==0) {
			$url = home_url("/") ; 
		} else if ($id==-1) {
			//void
		} else {
			$url = get_permalink($id) ; 
		}
		
		foreach ($socials as $s) {
			$old_counter = $this->get_counter($s, $id, $url) ; 
			
			$nb = $old_counter ; 
			
			// TWITTER
			if ($s=="twitter") {
				$result = wp_remote_get('http://urls.api.twitter.com/1/urls/count.json?url=' .  $url ); 
				if ( is_wp_error($result) ) {
					//trigger_error("SOCIAL LINKZ PLUGIN : Twitter API could not be retrieved to count hits") ; 
				} else {
					$res = @json_decode($result['body'], true);
					if (isset($res['count'])) {
						if (intval($res['count'])>$old_counter)
							$nb =  intval($res['count']);
					} else {
						trigger_error("SOCIAL LINKZ PLUGIN : Twitter API responded but no count can be retrieved for $url") ; 
					}
				}	
			}
			
			// FACEBOOK
			if ($s=="facebook") {
				$result = wp_remote_get("http://graph.facebook.com/fql?q=SELECT%20url,%20normalized_url,%20share_count,%20like_count,%20comment_count,%20total_count,%20commentsbox_count,%20comments_fbid,%20click_count%20FROM%20link_stat%20WHERE%20url='".urlencode($url)."'"); 
				if ( is_wp_error($result) ) {
					//trigger_error("SOCIAL LINKZ PLUGIN : Facebook API could not be retrieved to count hits") ; 
				} else {
					$result2 = wp_remote_get("http://graph.facebook.com/?ids=".urlencode($url)); 
					if ( is_wp_error($result2) ) {
						//trigger_error("SOCIAL LINKZ PLUGIN : Facebook API could not be retrieved to count hits") ; 
					} else {
						$res = @json_decode($result['body'], true);
						$res2 = @json_decode($result2['body'], true);
						if ((isset($res['data'][0]['total_count']))&&(isset($res2[$url]['likes']))) {
							if (intval($res['data'][0]['total_count'])+intval($res2[$url]['likes'])>$old_counter)
								$nb =  intval($res['data'][0]['total_count'])+intval($res2[$url]['likes']);
						} else if (isset($res['data'][0]['total_count'])) {
							if (intval($res['data'][0]['total_count'])>$old_counter)
								$nb =  intval($res['data'][0]['total_count']);
							trigger_error("SOCIAL LINKZ PLUGIN : Only FQL Facebook API responded with a  count for $url. <br>") ; 
						} else if (isset($res2[$url]['likes'])) {
							if (intval($res2[$url]['likes'])>$old_counter)
								$nb =  intval($res2[$url]['likes']);
							trigger_error("SOCIAL LINKZ PLUGIN : Only IDS Facebook API responded with a  count for $url. <br>") ; 
						} else {
							ob_start() ; 
								print_r($res) ; 
							$more = ob_get_clean() ; 
							trigger_error("SOCIAL LINKZ PLUGIN : Both Facebook API responded but no count can be retrieved for $url. <br>$more") ; 
						}
					}
				}
			}		
			
			// LINKEDIN
			if ($s=="linkedin") {
				$result = wp_remote_get('http://www.linkedin.com/countserv/count/share?url=' .  $url ); 
				if ( is_wp_error($result) ) {
					//trigger_error("SOCIAL LINKZ PLUGIN : Linkedin API could not be retrieved to count hits") ; 
				} else {
					if (!(preg_match('/IN.Tags.Share.handleCount\({"count":(\d+),/i',$result['body'],$tmp))) {
						trigger_error("SOCIAL LINKZ PLUGIN : Linkedin API responded but no count can be retrieved for $url") ; 
					} else {
						if (intval($tmp[1])>$old_counter)
							$nb = intval($tmp[1]) ;
					}
				}
			}			
			
			// GOOGLE +
			if ($s=="google+") {
				$post_data = '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' .  $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]' ; 
				$result = wp_remote_post("https://clients6.google.com/rpc?key=".$this->get_param('googleplus_standard_key'), array( 'headers' => array('content-type' => 'application/json'), 'body' => $post_data ) );
				if ( is_wp_error($result) ) {
					//trigger_error("SOCIAL LINKZ PLUGIN : Google+ API could not be retrieved to count hits") ; 
				} else {
					$res = @json_decode($result['body'], true);
					if (isset($res[0]['result']['metadata']['globalCounts']['count'])) {
						if (intval($res[0]['result']['metadata']['globalCounts']['count']) >$old_counter)
							$nb = intval($res[0]['result']['metadata']['globalCounts']['count']) ;
					} else {
						trigger_error("SOCIAL LINKZ PLUGIN : Google+ API responded but no count can be retrieved for $url") ; 
					}
				}
			}
			
			// STUMBLEUPON
			if ($s=="stumbleupon") {
				$result = wp_remote_get('http://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$url ); 
				if ( is_wp_error($result) ) {
					//trigger_error("SOCIAL LINKZ PLUGIN : StumbleUpon API could not be retrieved to count hits") ; 
				} else {
					$res = @json_decode($result['body'], true);
					if (isset($res['result']['views'])) {
						if (intval($res['result']['views'])>intval($old_counter))
							$nb = intval($res['result']['views']) ;	
					} else {
						if ((isset($res['result']['in_index'])) && ($res['result']['in_index']==false)){
							//  nothing
						} else {
							trigger_error("SOCIAL LINKZ PLUGIN : StumbleUpon API responded but no count can be retrieved for $url") ;
						}
					}
				}
			}
			
			$new_counters[$s] = $nb ; 	
		}
		
		// FINALIZATION
		if ($id!=-1) {
			$select = "SELECT COUNT(*) FROM ".$this->table_name." WHERE id_post='".$id."'" ;
			$result = $wpdb->get_var($select) ; 
			if ($result==0) {
				$query = "INSERT INTO ".$this->table_name." (id_post, counters, date_maj) VALUES ('".$id."', '".@json_encode($new_counters)."', '".date_i18n("Y-m-d H:i:s")."')" ; 
			} else {
				$query = "UPDATE ".$this->table_name." SET counters='".@json_encode($new_counters)."', date_maj='".date_i18n("Y-m-d H:i:s")."' WHERE id_post='".$id."'" ; 
			}
			$wpdb->query($query) ; 
		} else {
			$select = "SELECT COUNT(*) FROM ".$this->table_name." WHERE url='".addslashes($url)."'" ;
			$result = $wpdb->get_var($select) ; 
			if ($result==0) {
				$query = "INSERT INTO ".$this->table_name." (id_post, counters, date_maj, url) VALUES ('-1', '".@json_encode($new_counters)."', '".date_i18n("Y-m-d H:i:s")."', '".addslashes($url)."')" ; 
			} else {
				$query = "UPDATE ".$this->table_name." SET counters='".@json_encode($new_counters)."', date_maj='".date_i18n("Y-m-d H:i:s")."' WHERE url='".addslashes($url)."'" ; 
			}
			$wpdb->query($query) ; 
		}
	}
	
	/** ====================================================================================================================================================
	* Callback for updating the counters
	* 
	* @return void
	*/
	
	function forceUpdateSocialLinkz() {
		$id = $_POST['id'] ; 
		$url = "" ; 
		if (isset($_POST['url'])) {
			$url = $_POST['url'] ; 
		}
		global $wpdb ; 
		if (!is_numeric($id)) {
			echo "no_numeric" ; 
			die() ; 
		}
		
		if ($id!="-1") {
			$select = "SELECT date_maj FROM ".$this->table_name." WHERE id_post='".$id."'" ;
		} else {
			$select = "SELECT date_maj FROM ".$this->table_name." WHERE url='".addslashes($url)."'" ;
		}
		$date = $wpdb->get_var($select) ; 
		$now = strtotime(date_i18n("Y-m-d H:i:s")) ; 
		$shouldbeupdate = false ; 
		
		if (($date==null)||($date==false)||($date=="")) {
			$shouldbeupdate = true ; 
		} else {
			$date = strtotime($date) ; 
			if ($now-$date>$this->get_param('refresh_time')*60) {
				$shouldbeupdate = true ; 
			}
		}
		
		if ($shouldbeupdate) {
			if ($id!="-1") {
				$this->set_counter(array("twitter", "facebook", "google+", "stumbleupon", "linkedin"), $id) ; 
			} else {
				$this->set_counter(array("twitter", "facebook", "google+", "stumbleupon", "linkedin"), -1, $url) ; 
			}
			echo "refreshed" ; 
		} else {
			echo "nothing" ; 
		}
		
		die() ; 
	}
	
	/** ====================================================================================================================================================
	* Display bubble 
	* 
	* @return void
	*/
	function display_bubble($nb) {
		?>
		<span class="social_bubble">
			<img class="arrow" src="<?php echo plugin_dir_url("/")."/".plugin_basename(dirname(__FILE__)) ; ?>/img/arrow.png"/>
			<em><?php echo $nb  ; ?></em>
		</span>
		<?php
	}
	
	/** ====================================================================================================================================================
	* Add query vars
	*
	* @return void
	*/
	
	function print_vars_callback( $query_vars ) {
		$query_vars[]	=	'print';
		return $query_vars;
	}
	
	/** ====================================================================================================================================================
	* Output a page to be printed
	*
	* @return void
	*/

	function output_print( $query ) {
		global $post, $wp_filter;
		
		if (!$this->get_param('print_newtab')) {
			return ; 
		}
		
		$print = get_query_var( 'print' ) ; 
		
		$white_list = ",wptexturize,convert_smilies,convert_chars,wpautop,prepend_attachment,shortcode_unautop," ; 
		
		if ( $this->get_param('print_shortcode') ) {
			$white_list .= "do_shortcode," ; 
		}
		
		$white_list .= $this->get_param('print_whitelist')."," ; 
		
		if ($print!="") {
			
			$filters = $wp_filter["the_content"] ;
			
			if ( $this->get_param('print_debug') && is_user_logged_in()) {
			
				echo "<h1>".__("List of the filters that is normally called during the display of the page", $this->pluginID)."</h1>" ; 
				echo "<p>".__("This list is displayed as you have set the debug mode.", $this->pluginID)."</p>" ; 
				echo "<p>".__("If you want to allow the execution of a plugin, you may identify its call in the following list (filters that are not already in the allowed filters) and add it to the allowed filters in the param page:", $this->pluginID)."</p>" ; 

				// We display the filter in debug mode to help the user to identify the filter to allow
				foreach ( $filters as $priority => $filter ) {
					foreach ( $filter as $identifier => $function ) {
						if ( is_array( $function['function']) ) {
							if (strpos($white_list,",".get_class($function['function'][0])."/".$function['function'][1].",")===false) { 
								echo "<p><i>".get_class($function['function'][0])."/".$function['function'][1]."</i></p>" ; 
							}
						} else {
							if (strpos($white_list,",".$function['function'].",")===false) { 
								echo "<p><i>".$function['function']."</i></p>" ; 
							}
						}
					}
				}
				echo "<h1>".__("End of the list", $this->pluginID)."</h1>" ; 

			}
			
			// We remove filter that are not on the whitelist
			foreach ( $filters as $priority => $filter ) {
				foreach ( $filter as $identifier => $function ) {
					if ( is_array( $function['function']) ) {
						// if this is not in the whitelist
						if (strpos($white_list,",".get_class($function['function'][0])."/".$function['function'][1].",")===false) { 
							$result = remove_filter( "the_content", array ( $function['function'][0], $function['function'][1] ), $priority);
						}
					} else {
						// if this is not in the whitelist
						if (strpos($white_list,",".$function['function'].",")===false) { 
							$result = remove_filter( "the_content", $function['function'], $priority);
						}
					}
				}
			}
			
						
			switch ( $print ) {
				case 'socialz_page': /* Content for printing from post */
					echo $this->print_header(true);
					$this->print_page($post, false) ; 
					echo $this->print_footer();
					die();
					break;
				case 'socialz_hiera': 
					if (!$this->get_param('print_newtab_hierarchy')) {
						die() ; 
					}
					if ((!is_user_logged_in())&&$this->get_param('print_newtab_hierarchy_admin')) {
						die() ; 
					}
					echo $this->print_header(true);
					$this->print_page($post, true) ; 
					echo $this->print_footer();
					die();
					break;
			}
		}
	}
	
	/** ====================================================================================================================================================
	* Display the page (and eventually all pages under)
	*
	* @return void
	*/
	
	function print_page($post_obj, $recurse=false) {
		global $post ; 
		$html = $post_obj->post_content ; 
		
		$old_post = $post ; 
		$post = $post_obj ; 
	
		//Remove forbid shortcode pattern
		$patternToRemove  = "\[(\[?)(".str_replace(' ','',str_replace(',','|',$this->get_param('print_blacklist_shortcode'))).")(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)" ; 
		if (( $this->get_param('print_shortcode') ) && ( $this->get_param('print_blacklist_shortcode')!="")) {
			$html = preg_replace('/'. $patternToRemove .'/s', "", $html) ; 
		}
		
		$qr_code = "" ; 
		if ($this->get_param('print_qr')) {
			$qr_code = $this->display_qr(get_permalink($post->ID), QR_ECLEVEL_M, 3, 3, false) ; 
		}
		$qr_code_end = "" ; 
		if ($this->get_param('print_qr_end')) {
			$qr_code_end = $this->display_qr(get_permalink($post->ID), QR_ECLEVEL_M, 3, 3, false) ; 
		}
		$html = '<div class="container">
					<div class="title"><h1>' . $post_obj->post_title . '</h1></div><br/>'.$qr_code.'
					<div class="content">'. apply_filters( 'the_content', $html ) . '</div>'.$qr_code_end.'
				</div>';
		
		//Remove unused shortcode pattern
		if ( !$this->get_param('print_shortcode') ) {
			$html = preg_replace('/'. get_shortcode_regex() .'/s', "", $html) ; 
		}
		
		echo $html ; 
		
		$post = $old_post ; 
				
		if (($recurse)&&($post_obj->post_type=='page')) {
			
			$args = array(
				'sort_order' => 'ASC',
				'sort_column' => 'menu_order,post_title',
				'parent' => $post_obj->ID,
				'child_of' => $post_obj->ID,
				'offset' => 0,
				'post_type' => 'page',
				'post_status' => 'publish'
			);

			$child = get_pages($args) ; 
		
			foreach ($child as $c) {
				$this->print_page($c, true) ; 
			}
		}
	}
	
	/** ====================================================================================================================================================
	* Output an header of a page to be printed
	*
	* @return void
	*/

	function print_header($isprint = false ) {
		ob_start(); /* Starting output buffering */
		?>
			<html>
				<head>
					<style>
					<?php
						echo $this->get_param('print_css') ; 
					?>
					</style>
					
					<?php 
					if ($this->get_param('print_load_external_css')) {
						global $wp_styles;
						foreach ( $wp_styles->queue as $handle ) {
							echo "<link rel='stylesheet' href='".$wp_styles->registered[$handle]->src."'/>\r\n" ; 
						}
					}
					
					if ( $isprint ) { ?>
						<script type="text/javascript"> window.onload = function() { window.print(); }</script>
					<?php } ?>
				</head>
				<body>
				
		<?php
		if ($this->get_param('print_watermark')) {
			echo '<div class="watermark">'.site_url().'</div>' ; 
		}
		
		$html = ob_get_contents(); /* Getting output buffering */
		ob_end_clean(); /* Closing output buffering */
		return $html; /* Now we done with template */
	}
	/** ====================================================================================================================================================
	* Output an header of a page to be printed
	*
	* @return void
	*/

	function print_footer() {
		ob_start(); /* Starting output buffering */
		?>
				</body>
			</html>
		<?php
		$html = ob_get_contents(); /* Getting output buffering */
		ob_end_clean(); /* Closing output buffering */
		return $html; /* Now we done with template */
	}
	
	/** ====================================================================================================================================================
	* Get the URL of the first image of the post
	* 
	* @return string the URL of the image (empty, if there is none)
	*/
	function get_first_image ($postID) {					
		$args = array(
		'numberposts' => 1,
		'order'=> 'ASC',
		'post_mime_type' => 'image',
		'post_parent' => $postID,
		'post_status' => null,
		'post_type' => 'attachment'
		);
		
		$attachments = get_children( $args );
				
		if ($attachments) {
			foreach($attachments as $attachment) {
				return wp_get_attachment_url( $attachment->ID , 'full');
			}
		} else {
			return "" ; 
		}
	}
	
	/** ====================================================================================================================================================
	* Send an article by email
	* 
	* @return void
	*/

	function emailSocialLinkz() {
		global $post ; 
		global $do_not_show_inSocialLinkz; 
		if (!$this->get_param('mail')) {
			echo "ERROR: Sending has been disabled" ; 
			die() ; 
		}
		
		$id = preg_replace("/[^0-9]/", "", $_POST['id_article']) ;
		
		$name = trim(preg_replace("[:.,;()]", " ", strip_tags($_POST['name'])));
		$emails = explode("\n", $_POST['list_emails']) ; 
		echo "<h2>".__('Sending Report', $this->pluginID)."</h2>" ; 
		$nb = 0 ; 
		
		if ($name=="") {
			echo "<p>".sprintf(__('Sorry, but you have not provided a name. Please refresh the current page and then retry.', $this->pluginID), $email)."</p>" ; 
		}
		
		$content = "<p>".sprintf(__('%s has recommended this article to you: %s', $this->pluginID), "<b>$name</b>", '"<i>'.get_the_title($id).'</i>"')."</p>" ; 
		$content .= "<p>".__('Here is an extract of the article:', $this->pluginID)."</p>" ; 
		$post = get_post($id) ; 
		setup_postdata($post);
		$do_not_show_inSocialLinkz = true ; 
		$content .= "<p style='font-style:italic;border:1px #AAAAAA solid;margin:10px; left-margin:40px;padding:10px;background-color:#DDDDDD;'>".get_the_excerpt()."</p>" ; 
		$do_not_show_inSocialLinkz = false ; 
		
		$subject = html_entity_decode(sprintf(__('%s has recommended this article to you: %s', $this->pluginID), $name, '"'.get_the_title($id).'"')); 
		$subject = preg_replace_callback("/(&#[0-9]+;)/", array($this,'transformHTMLEntitiesWithDash'), $subject); 
		
		foreach ($emails as $email) {
			if ($nb>=$this->get_param('mail_max'))
				die() ; 
			$email = trim($email) ; 
			$email = filter_var($email, FILTER_VALIDATE_EMAIL) ; 
			if ($email !== FALSE) {
				$nb++ ; 
				
				$headers= 	"MIME-Version: 1.0\n" .
						"From: ".$this->get_param('mail_name')." <".$this->get_param('mail_address').">\n" .
						"Content-Type: text/html; charset=\"". get_option('blog_charset') . "\"\n";
					
				$result = wp_mail($email, $subject , $content, $headers);
				
				if ($result) {
					echo "<p>".sprintf(__('Email successfully sent to %s', $this->pluginID), $email)."</p>" ; 
				} else {
					echo "<p>".sprintf(__('Wordpress is unable to send an email to %s', $this->pluginID), $email)."</p>" ; 
					die() ; 
				}
			} 
		}
		die() ; 
	}
	
	function transformHTMLEntitiesWithDash($m) { 
		return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); 
	}
	
	/** ====================================================================================================================================================
	* QR code shortcode
	* 
	* @return void
	*/
	
	function qrcode_shortcode($_atts, $text) {
		global $post ;
		$atts = shortcode_atts( array(
			'size' => 3, 
			'px_size' => 3,
			'frame_size' => 3
		), $_atts );
		
		if ($text=="") {
			$text = get_permalink($post->ID) ; 
		}
		
		// CCR
		$size = $atts['size'] ; 
		if (!is_numeric($size)) {
			$size = 3 ; 
		} else {
			$size = floor(intval($size)) ; 
		}
		if ($size == 1) {
			$ccr_param = QR_ECLEVEL_L ; 
		} else if ($size == 2) {
			$ccr_param = QR_ECLEVEL_M ; 
		} else if ($size == 3) {
			$ccr_param = QR_ECLEVEL_Q ; 
		} else if ($size == 4) {
			$ccr_param = QR_ECLEVEL_H ; 
		} else {
			$ccr_param = QR_ECLEVEL_M ; 
		}
		
		// PIXEL SIZE
		$px_size = $atts['px_size'] ; 
		if (!is_numeric($px_size)) {
			$pixel_param = 3 ; 
		} else {
			$pixel_param = floor(intval($px_size)) ; 
		}
		
		// FRAME SIZE
		$frame_size = $atts['frame_size'] ; 
		if (!is_numeric($frame_size)) {
			$frame_param = 3 ; 
		} else {
			$frame_param = floor(intval($frame_size)) ; 
		}
			
		return $this->display_qr($text, $ccr_param, $pixel_param, $frame_param, true)  ; 
	}
	
	/** ====================================================================================================================================================
	* print QR code
	* 
	* @return void
	*/
	
	function display_qr($text, $ccr_param, $pixel_param, $frame_param, $shortcode=false) {
		
		if (!is_dir(WP_CONTENT_DIR."/sedlex/social_linkz/qr/")) {
			@mkdir(WP_CONTENT_DIR."/sedlex/social_linkz/qr/", 0777, true) ; 
		}
		
		$name_f = sha1($text)."_".$ccr_param."_".$pixel_param."_".$frame_param.".png" ; 
		$name_file = WP_CONTENT_DIR."/sedlex/social_linkz/qr/".$name_f ;
		$name_url = WP_CONTENT_URL."/sedlex/social_linkz/qr/".$name_f ;
		if (!file_exists($name_file)) {
        	QRcode::png($text, $name_file, $ccr_param, $pixel_param, $frame_param);
    	}
		
		if ($shortcode) {
			return str_replace("%qr_image%",'<img src="'.$name_url.'" />',$this->get_param('qr_html_sc')); 
		} else {
			return str_replace("%qr_image%",'<img src="'.$name_url.'" />',$this->get_param('qr_html')); 
		}
	}


}

$sociallinkz = sociallinkz::getInstance();

?>