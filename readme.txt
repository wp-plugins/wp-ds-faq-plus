Plugin Name: WP DS FAQ Plus
Plugin URI: http://kitaney.jp/~kitani/tools/wordpress/wp-ds-faq-plus_en.html
Description: WP DS FAQ Plus is the expand of WP DS FAQ  plugin. The plugin bases on WP DS FAQ 1.3.3. This plugin includes the fixed some issues (Quotation and Security, such as SQL Injection and CSRF. ) , Japanese translation, improvement of interface, and SSL Admin setting.
Version: 1.0.14 (September 22, 2011)
Author: Kimiya Kitani
Author URI: http://kitaney.jp/~kitani/

* Change Log

[1.0.0]
- Fixed SQL Injection problem.
- Fixed Escape function for quotation mark 
-- Fixed trouble: When ' or " include in the content, these characters are automatically escaped  in infinitum.
   ([' --&gt; \'] &gt;&gt;&gt; [\' --&gt; \\'] &gt;&gt;&gt; [\\' --&gt; \\\'])
-- The escape should carry out only once  ([' --&gt; \'] &gt;&gt;&gt; [\' --&gt; \'] &gt;&gt;&gt; [\' --&gt; \']). 
- View of Source code
-- Comfirmed [<a href="http://wordpress.org/extend/plugins/wp-syntax/">WP Syntax</a>] plugin. 
- Japanese Translation
-- Fixed [Error] message from Russian to English.

[1.0.1]: March 18, 2011
- Moved [Delete question] button to the right edge.
- Added [Last modified Date] in the right of [Edit] button.
- Added the sort the list of questions by last modified date.
- If there is the "()" in the head of question title, the view moves to the category item.
-- If there is [-] (hyphen) in the category data, it is converted to &lt;br&gt;.
- Adjusted the size in [td] tag.

[1.0.2]: March 19, 2011
- Fixed the ajax problem for Admin SSL setting (FORCE_SSL_ADMIN).

[1.0.3]: April 1, 2011
- Removed "Delete" button in the editing area.
- Added the main item in the side menu (Multi-language).

[1.0.4]: April 7, 2011
- Added CSRF (Cross Site Request Forgeries) security protection.

[1.0.5]: April 22, 2011
- Fixed the ajax problem for Admin SSL setting.
-- (Issue) The setting for Admin SSL setting was forcibly enabled even if FORCE_SSL_ADMIN is false setting. 

[1.0.6]: April 23, 2011
- Added the submenu in main item. In the submenu, the FAQ categories are displayed.

[1.0.7]: July 1, 2011
- If there is not data in the category, "Under Construction" will be displayed.

[1.0.8]: July 11, 2011
- "Under Construction" message changed to &lt;div class="dsfaq_plus_under_construction"&gt;Under Construction&lt;/div&gt;
-- By this setting, the design and layout for "Under Construction" message can be controlled.

[1.0.9]: July 19, 2011
- Added "Sort" function ("Sort Key" and "Order by") in each FAQ category.
 You can freely select the sort of each category.
 Sort Key: [Custome] (Default in "WP DS FAQ" plugin and older version), [Last Modified], [Answer Name]
 Order by: [Ascending], [Descending]
 
 -- (BUG) Now, if you continually select 2 or more checkboxes, I'm sorry that
          only last selected checkbox will be changed.
       * Please reload the web browser and check the settings after you change one setting.
 -- (Unsolved) If you select "Descending" in "Order By", the "Custom" selection won't  normally 
 work because the ajax of "WP DS FAQ" plugin only works in only case of "Ascending". 
 I'd like to fix it, but this issue is not solved in this version.
 * If you want to use "Custom" in case of "Descending", please change "Ascending" in "Order By" setting and change the custom setting. And then, please change "Descending",
 * By updating new version, the data does not be lost. 
 * If you change this plugin to older version or "WP DS FAQ" Plugin, please confirm and check on "Display" mode setting again.

[1.0.10]: August 22, 2011.
- For new version of WP DS FAQ 1.3.3. 
-- (Don't need to fix) Fix of escape processing.
-- (Fixed) Fix and improvement of the check function for numeric characters.
- (Fixed) Removed "Delete" button in the editing area after push edit button and cancel button.
-- By version [1.0.3] policy, you can only delete a FAQ article in the admin control page.
- (Fixed) Change the sort value in the FAQ sub menu of admin control page to "FAQ name".

[1.0.11]: August 26, 2011
- Establishment of admin menu for FAQ in the Settings.
-- General Settings (Display Title)
-- Permission (Administrative and Editing Permissions)
-- Safety Precaution (Disable Delete button and so on)
-- Linkage from other plugins (WP-PostRating)
- Movement of the settings for header and CSS.

[1.0.11-1]: August 26, 2011
- (Fixed) When "Display Title" option in the admin menu check on, the setting could not be applied.

[1.0.12]: August 29, 2011
- (Fixed) Some Security vulnerabilities
- (Fixed) Support of Full compatibility of WP DS FAQ plugin.
-- Since version 1.0.9, a part of compatibility was lost because the sort function was established.
-- In case of version between 1.0.9 and 1.0.11-1, if you deactivate WP DS FAQ Plus and activate
   WP DS FAQ, all FAQ datas are disappeared because the display mode setting is unrecognized by WP
   DS FAQ. Of course, you re-check the mode setting, the data will be appeared.
-- In this time, new database item called "custom_mode" in dsfaq_name table was created.
   the special settings for WP DS FAQ Plus are read/written to this item.
   Then, the display mode setting is written to mode and custom_mode.
   therefore, you can use WP DS FAQ and WP DS FAQ Plus in same database.
-- Of course, please don't activate both of  WP DS FAQ and WP DS FAQ plus.

[1.0.12-1]: August 30, 2011
- (Fixed) Cannot add new category.

[1.0.13]: September 14, 2011 (Added New function of latest FAQ list)
- (Fixed) Some Security vulnerabilities, but you don't need to worry because this fix is 
          for multi protection.
- (Fixed) Design of FAQ category title
-- Please check "Text before and Text after the FAQ book name in FAQ setting menu.
- (Fixed) Improvement of TimeZone processing. (Establishment of "convert_timezone_data" function)
-- About 1.0.12-1 or earlier version (from 1.0.1), the timezone was tentatively held on
   "Asia/Tokyo". I'm sorry that I forgot... But you don't need to worry because there are not any
   influences except the display of last modified date in admin menu. 
   In this version, the date information is very important because of adding "latest FAQ list".
   Therefore, I drastically coped with about TimeZone processing by creating new date convert function. 
-- About the timezone setting, please check "TIMEZONE" in Wordpress setting menu.
-- Especially, this version follows a timezone of manual offset (UTF+9). Unfortunately, "date_i18n"
   function (Wordpress 3.x) does not support manual offset.
     * My original "convert_timezone_data" function is the following conversions.
       - In case of using PHP5.2 or above version, original conversion is carried out.
       - In case of using PHP5.1 or earlier version:
        -- If "date_i18n" function is found out, the function will be used.
        -- Else, the conversion won't be carried out.
-- (Improvement) Added TIMEZONE information to date in FAQ input menu.
-- (Fixed) First category does not display in the FAQ sub menu.
- (Added) New Function of latest FAQ List.
-- Added [dsfaq latest="10" latest_format="li/dl/table"] option to shortcode.
-- The design and layout of FAQ display data including latest FAQ list can be changed
   in "wp-ds-faq-plus.css" file.
-- * About the detail information, please see "FAQ setting menu" in Wordpress "Settings" menu.

[1.0.14]: September 22, 2011
- (Fixed) In case of editing a FAQ data from front page, "Ajax error" was displayed when "Cancel" 
          or "Save" button was pushed". Even if "Ajax error" was displayed, the data processing could
          be doing.  
- (Improvement) In case of using the table format in latest list function, the title of each item is displayed.

* Issues or Plan
Sorry, I don't have Javascript technical skill in the high level, so if you can fix the issues, please contact me.

1. Import and Export menu for settings.

2. Bug of Editing button.
-- You click "Edit" button in document A and click "Edit" button in document B.
   Then, if you edit document A and save it, the data will be overwritten by  document B.

3. There is not the delete confirmation function...
  Now, when the Delete button is clicked, the data will be lost without conformation.
  This is very afraid....
  By upgrading 1.0.11, you can tentatively avoid this issue. 

4. Multi-language for Question.