=== Plugin Name ===
Contributors: InertiaInMotion
Tags: jquery, cdn, wordpress, content, delivery, network, library, google, microsoft
Requires at least: 3
Tested up to: 3.3
Stable tag: trunk

Activate Plugin and Select a jQuery CDN via the WP jQuery CDN options area

== Description ==

= Selectable options are currently: =
1. Google Ajax API jQuery CDN 
2. jQuery CDN 
3. Microsoft jQuery CDN 
4. Local jQuery (Inside this plugins js folder) 
5. Local jQuery (Wordpress)(Might be out of date) 
6. None (Don't load any jQuery, Or i wish to load it Myself)

== Installation ==

1. Upload to the '/wp-content/plugins/' directory
2. Activate through the 'Plugins' menu in WordPress
2. (Optional) Select a jQuery CDN via the WP jQuery CDN options area (Defaults to Google Ajax API jQuery CDN)
2. (Optional) Specify a Version number via the WP jQuery CDN options area (Defaults to 1.7.1)
3. Set it and forget it...

== Upgrade Notice ==

= 2.2 =
Corrected a HTML Request issue, Please update the plugin ASAP
= 2 =
Major Changes/Fixes, Please update the plugin ASAP

== Changelog ==

= 2.2 =
* Removed the fallback feature

= 2.1 =
* Updated "local-jquery.min.js" to jQuery 1.7.1
* Set the default jQuery version to (1.7.1)
* Celebrated the 3K Downloads of this plugin. Thank you!

= 2 =
* Set the default jQuery CDN to (Google Ajax API jQuery CDN)
* Set the default jQuery version to (1.6.4)
* Improved a fair bit of the Plugins code
* Improved the Fallback Feature (Now falls back on http error when a Remote CDN is selected to the "Local jQuery (Inside this plugins js folder)" option, Or if "Local jQuery (Inside this plugins js folder)" is the selected cdn then we fallback to "Google Ajax API jQuery CDN" on http error
* Implemented a Specify version text input in the WP jQuery CDN Options area
* Updated "local-jquery.min.js" to jQuery 1.6.4
* Removed the Auto Update feature (may re-include if there is the demand) 

= 1.9.5 001 =
* Changed wp-admin navigation from "admin.php?page=wp_jquery_cdn" to "admin.php?page=WP-jQuery-CDN.php"

= 1.9.5 =
* Fixed Auto Update version seeking for jQuery 1.6.3
* Updated "local-jquery.min.js" to jQuery 1.6.3
* Removed some Redundancies that were a result of the 1.9.4 Update
* Added Fallback Feature. IF the Selected CDN Cannot be reached, WP jQuery CDN will load the local jQuery (Inside this plugins js folder)

= 1.9.4 =
* Changed the way the Auto Update Feature works, This should fix the below issue (jQuery Version missing from URL)
* &lt;script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery//jquery.min.js'&gt;&lt;/script&gt;

= 1.9.3 =
* Fixed issue (HTTP request failed!)

= 1.92 =
* Fixed issue (Cannot modify header information - headers already sent by)
* Changed the way the Auto Update Feature handles things (See the error below)
* Fixed issue (stream does not support seeking)

= 1.91 = 
* Fixed some incorrect references

= 1.9 =
* Changed a few http calls to be just much more dynamic to your Wordpress install
* Added a Built In Auto Update feature
* Added more Description's (Other Features)

= 1.6.2.007 =
* Renamed the included jquery-1.6.2.min.js to local-jquery.min.js (for later features)
* Made it easier to manage jQuery updates across all CDN's
* Changed Option name for Local jQuery to Local jQuery (Inside this plugins js folder)

= 1.6.2.006 =
* Added a layout to the WP jQuery CDN options area

= 1.6.2.005 =
* Corrected spelling error

= 1.6.2.004 =
* Corrected Linking issue

= 1.6.2.003 =
* Corrected references of Jquery to jQuery

= 1.6.2.002 =
* Added Local jQuery (Wordpress) as a selectable jQuery CDN (Might be out of date)
* Added Local jQuery as a selectable jQuery CDN (See directly under this)
* Added jQuery 1.6.2 Library to WP jQuery CDN's Plugin Folder (Selectable in the WP jQuery CDN options area)
* Added jQuery CDN as a selectable jQuery CDN
* Added Microsoft jQuery CDN as a selectable jQuery CDN
* Added Google Ajax API jQuery CDN as a selectable jQuery CDN
* Added Options area for WP jQuery CDN

= 1.6.2 =
* Added jQuery 1.6.2 Library via Google Ajax API jQuery CDN