=== WP DS FAQ Plus ===
Contributors: kimipooh
Tags: faq, answer, question, documentation, pages, page, ajax
Requires at least: 3.0
Tested up to: 3.9
Stable tag: 1.2.0

WP DS FAQ Plus is the plugin which was improved based on WP DS FAQ 1.3.3. 
 
== Description ==

 WP DS FAQ Plus is the plugin which was improved based on WP DS FAQ 1.3.3.  This plugin includes the fixed some issues (Quotation and Security, such as SQL Injection and CSRF. ) , Japanese translation, improvement of interface, and SSL Admin setting.

== Installation ==

Download: <a href="http://kitaney.jp/~kitani/tools/wordpress/wp-ds-faq-plus_en.html">WP DS FAQ Plus</a>

1. Extract the source compress file and upload "wp-ds-faq-plus" folder to the plugin folder (wp-content/plugins/) in Wordpress.
2. If you use "WP DS FAQ" plugin, please disactivate the plugin.
3. Enable "WP DS FAQ Plus" plugin.

= Usage =

* Please ignore the error message if the 413 characters output error appears when you install it. The error is due to the Japanese comments in PHP code. 
* The data format is the same as "WP DS FAQ" plugin. Therefore, you can switch between "WP DS FAQ" plugin and "WP DS FAQ Plus" plugin.
* Please see the change log in the following web site about the compatibility of old version and "WP DS FAQ". 

About the detail information, please see the following site.

- English: http://kitaney.jp/~kitani/tools/wordpress/wp-ds-faq-plus_en.html
- Japanese: http://kitaney.jp/~kitani/tools/wordpress/wp-ds-faq-plus_ja.html

== Frequently Asked Questions ==

= Is it compatible between "WP DS FAQ Plus" and "WP DS FAQ"? =

 Yes, this plugin supports the compatibility with "WP DS FAQ" plugin.
When this plugin is deactivated and "WP DS FAQ" is activated, the special function of this plugin will be automatically ignored. And when this plugin is activated ("WP DS FAQ" is deactivated), the function will automatically work.

== Screenshots ==

1. View (with WP-PostRating plugin)
2. Editing Menu in Admin menu.
3. Setting Menu 1 (Permissions)
4. Setting Menu 2 (Safety Guard for a editor and Linkage from other plugins)
5. Edit Menu.

== Changelog ==

= 1.2.0 (April 8, 2014) =
- Tested up to WordPress 3.9

= 1.1.0 (December 12, 2013) =
- Tested up to WordPress 3.8

= 1.0.18 (December 11, 2013) =
- Tested up to WordPress 3.7.1.

= 1.0.17 (January 10, 2013) =
- (Fixed) In case of using PHP version 5.2, "Call to undefined method DateTime::setTimestamp()" error was appeared.

= 1.0.16 (January 10, 2013) =
- (Fixed) "Header and CSS Settings" in the"Admin Settings" cannot be saved or restored.

= 1.0.15 (December 11, 2012) =
- (Fixed) Copyright Information

= 1.0.14 (September 22, 2011) =
- (Fixed) In case of editing a FAQ data from front page, "Ajax error" was displayed when "Cancel" or "Save" button was pushed". Even if "Ajax error" was displayed, the data processing could be doing.  
- (Improvement) In case of using the table format in latest list function, the title of each item is displayed.

= 1.0.13 (September 14, 2011) =
- (Fixed) Some Security vulnerabilities, but you don't need to worry because this fix is 
          for multi protection.
- (Fixed) Design of FAQ category title
- (Fixed) Improvement of TimeZone processing. (Establishment of "convert_timezone_data" function. By this processing, WordPress 3.x does not support the manual offsets (ex. UTF+9), but the plugin was supported in not only PHP5.2 or above but also PHP5.1 or earlier version.
- (Added) New Function of latest FAQ List.

= 1.0.12-1 (August 30, 2011) =
- (Fixed) Cannot add new category.

= 1.0.12 (August 29, 2011) =
- (Fixed) Some Security vulnerabilities
- (Fixed) Support of Full compatibility of WP DS FAQ plugin.

= 1.0.11-1 (August 26, 2011) =
- (Fixed) When "Display Title" option in the admin menu check on, the setting could not be applied.

= 1.0.11 (August 26, 2011) =
- Movement of the settings for header and CSS.

Establishment of admin menu for FAQ in the Settings.

- General Settings (Display Title)
- Permission (Administrative and Editing Permissions)
- Safety Precaution (Disable Delete button and so on)
- Linkage from other plugins (WP-PostRating)

= 1.0.10 (August 22, 2011) =
- For new version of WP DS FAQ 1.3.3. 
- (Don't need to fix) Fix of escape processing.
- (Fixed) Fix and improvement of the check function for numeric characters.
- (Fixed) Removed "Delete" button in the editing area after push edit button and cancel button.
- By version [1.0.3] policy, you can only delete a FAQ article in the admin control page.
- (Fixed) Change the sort value in the FAQ sub menu of admin control page to "FAQ name".

= 1.0.9 (July 19, 2011) =
- Added "Sort" function ("Sort Key" and "Order by") in each FAQ category.
- (BUG) Now, if you continually select 2 or more checkboxes, I'm sorry that only last selected checkbox will be changed.
- (Unsolved) If you select "Descending" in "Order By", the "Custom" selection won't  normally work because the ajax of "WP DS FAQ" plugin only works in only case of "Ascending". I'd like to fix it, but this issue is not solved in this version.

= 1.0.8 (July 11, 2011) =
- "Under Construction" message changed to &lt;div class="dsfaq_plus_under_construction"&gt;Under Construction&lt;/div&gt;

= 1.0.7 (July 1, 2011) =
- If there is not data in the category, "Under Construction" will be displayed.

= 1.0.6 (April 23, 2011) =
- Added the submenu in main item. In the submenu, the FAQ categories are displayed.

= 1.0.5 (April 22, 2011) =
- Fixed the ajax problem for Admin SSL setting.
- (Issue) The setting for Admin SSL setting was forcibly enabled even if FORCE_SSL_ADMIN is false setting. 

= 1.0.4 (April 7, 2011) =
- Added CSRF (Cross Site Request Forgeries) security protection.

= 1.0.3 (April 1, 2011) =
- Removed "Delete" button in the editing area.
- Added the main item in the side menu (Multi-language).

= 1.0.2 (March 19, 2011) =
- Fixed the ajax problem for Admin SSL setting (FORCE_SSL_ADMIN).

= 1.0.1 (March 18, 2011) =
- Moved [Delete question] button to the right edge.
- Added [Last modified Date] in the right of [Edit] button.
- Added the sort the list of questions by last modified date.
- If there is the "()" in the head of question title, the view moves to the category item.
- Adjusted the size in [td] tag.

= 1.0.0 =
- Fixed SQL Injection problem.
- Fixed Escape function for quotation mark
- View of Source code with [<a href="http://wordpress.org/extend/plugins/wp-syntax/">WP Syntax</a>] plugin. 
- Japanese Translation
- Fixed [Error] message from Russian to English.

== Upgrade Notice ==

= 1.0.12 (August 29, 2011) =

This version cannot add the category. If you use this version, please upgrade to 1.0.12-1 or later