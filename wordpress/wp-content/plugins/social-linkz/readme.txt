=== Social Linkz ===

Author: SedLex
Contributors: SedLex
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/plugins/social-linkz/
Tags: social, facebook, twitter, google, buttons
Requires at least: 3.0
Tested up to: 3.9.1
Stable tag: trunk

Add social links such as Twitter or Facebook in each post. 

== Description ==

Add social links such as Twitter or Facebook in each post. 

You can choose the buttons to be displayed such as : 

* Twitter
* FaceBook
* LinkedIn
* Viadeo
* Google+
* StumbleUpon
* Pinterest
* Print

It is possible to manually insert the buttons in your post by adding the shortcode [sociallinkz] or [sociallinkz url='http://domain.tld' buttons='facebook,google+' desc='Short description'] . 

If you want to add the buttons in a very specific location, your may edit your theme and insert $this-&gt;print_buttons($post, [$url], [$buttons]); (be sure that $post refer to the current post). 

It is also possible to add a widget to display buttons. 

This plugin is under GPL licence. 

= Multisite - Wordpress MU =

This plugin works with multisite installation.

= Localization =

* German (Germany) translation provided by Olly, FarChris, Susann
* English (United States), default language
* Spanish (Argentina) translation provided by GianFrancoAlarcn, Sunombre
* Spanish (Chile) translation provided by Xaloc
* Spanish (Spain) translation provided by sesi, AlexSancho, fco, JavierLaChica, Verto
* Spanish (Guatemala) translation provided by EnriqueBran
* Finnish (Finland) translation provided by ProDexorite
* French (France) translation provided by SedLex, JP, ChristopheReverd
* Galician (Spain) translation provided by prios
* Croatian (Croatia) translation provided by nikola
* Hungarian (Hungary) translation provided by LaszloPinter
* Italian (Italy) translation provided by BRENDON-75, BRENDON-75, StefanoBontempi
* Japanese (Japan) translation provided by Toshi
* Norwegian (Bokmal) (Norway) translation provided by Hkon, Hakon, MohamedBoyeJalloJamboria
* Norwegian (Bokmal) (Norway) translation provided by Hakon, Hkon
* Dutch (Netherlands) translation provided by HermanTimmermans, Jens
* Polish (Poland) translation provided by Kajaczek
* Portuguese (Brazil) translation provided by AndrVasconcellos, MarceloSrougi
* Swedish (Sweden) translation provided by 
* Turkish (Turkey) translation provided by OsmanERDOAN, Hseyinzkan, BlentDnmez

= Features of the framework =

This plugin uses the SL framework. This framework eases the creation of new plugins by providing tools and frames (see dev-toolbox plugin for more info).

You may easily translate the text of the plugin and submit it to the developer, send a feedback, or choose the location of the plugin in the admin panel.

Have fun !

== Installation ==

1. Upload this folder social-linkz to your plugin directory (for instance '/wp-content/plugins/')
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the 'SL plugins' box
4. All plugins developed with the SL core will be listed in this box
5. Enjoy !

== Screenshots ==

1. The configuration page of the plugin
2. The buttons

== Changelog ==

= 1.7.1 =
* NEW: How to
* NEW: You may customize the sharing string 

= 1.7.0 =
* NEW : Support for QR code / shortcode
* NEW: Add Banners to improve the look

= 1.6.0 -&gt; 1.6.2 =
* BUG : Some plugin do not work correctly during the printing as the $post was not set correctly
* NEW : The hierarchy button may be limited to logged users
* BUG : Avoid recursive call listing the translation folder
* NEW : Add an option to customize the printed pages (custom CSS, selection of plugins that are to be executed...)

= 1.5.0 -&gt; 1.5.5 =
* Update of the core
* Issue with the update process
* Filter issue with feed
* SSL issue with facebook and some installation
* improve the Facebook counter
* Issue with the excerpt
* Change the method to store the counters
* Improve the detection
* Add the hosted viadeo buttons

= 1.4.0 -&gt; 1.4.4 =
* New url for Google +
* New API for LinkedIn
* English correction
* New icon for twitter
* Correct a bug with some installation and the button
* Add a tinyMCE button and a shortcode to put buttons wherever you want
* Add a mail button 

=1.3.0 -&gt; 1.3.7 = 
* CSS and HTML may be customized
* The buttons may be managed for pages independently from posts 
* Pinterest bug corrected
* The PinteRest API has changed and the bug is then corrected
* Bug with the excerpt corrected 
* Issues with UTF8  text corrected
* Add translations
* Add Pinterest button
* Major release of the framework

= 1.2.0 -&gt; 1.2.6 =
* Improve English text thanks to Rene
* Polish translation (by Kajaczek)
* Add Insight for Facebook
* Correction of a bug with Firefox8
* Release of a Google+ button to post article directly (replace the ugly mobile post method)
* Espagnol/Guatemala translation (by EnriqueBran)
* Bug Correction for the official tweet button
* Finnish translation (by ProDexorite)
* SVN support

= 1.1.0 -&gt; 1.1.6 =
* The button may be added at the top/bottom of the post
* New Croatian translation by nikola
* Core update
* Bug correction (when the excerpt is not an excerpt but a get_the_content) thanks to florian  
* The buttons may be correctly displayed in the excerpt
* ZipArchive class has been suppressed and pclzip is used instead
* New translation for Espagnol (Argentina) by GianFrancoAlarcn
* New translation for Turk by Hseyinzkan
* Big thanks to them!
* Ensure that folders and files permissions are correct for an adequate behavior
* Major release
* Adding hosted/non hosted button
* Support added for StumbleUpon (on the request of REX)
* Support added for LinkedIn
* Counters have been added

= 1.0.0 -&gt; 1.0.4 =
* Update the framework (3.0)
* Add the google+ features
* Remove all french sentences hard coded in the code and preparation for the i8n
* First release in the wild web (enjoy)
* Basic support for facebook, twitter

== Frequently Asked Questions ==

* Where can I read more?

Visit http://www.sedlex.fr/cote_geek/

 
InfoVersion:95dae9f89a4d38b17a006308e3fb7519169310f7