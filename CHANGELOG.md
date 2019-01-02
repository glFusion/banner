# Changelog - Banner plugin for glFusion

## Version 0.3.1
Release TBD
- Remove support for non-UIkit themes
- `E_ALL` fixes
- Implement glFusion caching

## Version 0.3.0
Release 2017-10-06
- Use `PLG_supportAdblock()` in glFusion 1.7.0 to get template names
- Add template configurations in categories to control ad display
- Submission queue is always required due to potential malicious code in script ads
- Add Autotag-type ad which will process autotags
- Target URL is optional for display-only banners
- Use class autoloader
- Change admin list AJAX to use Jquery
- Implement PHP namespace
- Use icon sets from Uikit or FontAwesome, depending on theme
- Implement `plugin_displayadblock_banner()`
- Disable banner caching in templateSetVars

## Version 0.2.0
Released 2017-01-21

Compatibile with glFusion 1.6.0 or higher.
- Remove PEAR dependency in banner validation
- Add uikit templates when a uikit-based theme is in use
- Simplify permissions.
  - Remove banner view permission
  - Change category to simple group view permission
  - Use full perms only for campaigns where users may submit ads
- Use LGLIB_ImageUrl() to size images to category settings
  - Global size limits should now be large sanity checks
  - Image is scaled to the smaller of the Category or Banner size limit when displayed.
  - Locally-hosted banner dimensions are reduced to a max of the image size during upload.

## glBanner - 0.1.2
Released 2010-03-17.

Fixes issue with new date fields in banner definition.
- 0000405: [Submission] Date fields don't work for banner entry (lee) - resolved.
- 0000399: [Administration] Banner option on admin menu leads to non-existant page (lee) - resolved.

## glBanner - 0.1.1
Released 2010-03-08
- 0000368: [Display] Banners and Campaigns should have topic limiters (lee) - resolved.
- 0000395: [Administration] Plugin crashes under PHP 4 (lee) - resolved.
- 0000382: [Campaigns] Campaign IDs with spaces cause problems in Campaign::getBanners() (lee) - resolved.
- 0000379: [Administration] Impressions and Max Impressions are not updated from banner form (lee) - resolved.
- 0000377: [Tracking] Add click tracking to HTML ads (lee) - resolved.
- 0000378: [Display] Add a "target" option for each ad (lee) - resolved.
- 0000366: [Display] Banners not displayed in stories (lee) - resolved.
- 0000365: [Administration] Centerblock not working (lee) - resolved.

## glBanner - 0.1.0
Released 2009-12-08
- 0000362: [Administration] Add a max_impression item to the banner table (lee) - resolved.
- 0000364: [Administration] Banner permissions aren't saved if owner permissions are empty (lee) - resolved.
- 0000361: [Submission] A failed image upload causes the banner creation to fail without warning (lee) - resolved.
- 0000360: [Administration] In moderation.php, category ID is displayed instead of a name (lee) - resolved.
- 0000359: [Submission] Allow users to specify ID values (lee) - resolved.
- 0000358: [Administration] Deleting banner from banner form doesn't work (lee) - resolved.
- 0000357: [Display] Add ability to display ads in more templates (lee) - resolved.
- 0000356: [Display] Alt tag for banner is not populated (lee) - resolved.
- 0000355: [Submission] Add max image dimensions to category to override global (lee) - resolved.
- 0000349: [Administration] Centerblock settings don't work properly (lee) - resolved.

## glBanner - 0.0.2
Released publicly 2009-11-12
- 0000303: [Display] Add option to not count banners displayed to Root users (lee) - resolved.
- 0000300: [Campaigns] Add hit ratio stats to banner records (lee) - resolved.
- 0000307: [Administration] Add glFusion auto-installation support (lee) - resolved.

## glBanner - 0.0.1
Initial redevelopment work. Released privately.
- 0000298: [Campaigns] Add support for advertising campaigns (lee) - resolved.
- 0000302: [Submission] Add datepicker for publish start & end dates (lee) - resolved.
- 0000308: [Display] Need an option to open link targets in a new window. (lee) - resolved.
- 0000306: [Display] Add option to suppress banner display from admin pages (lee) - resolved.
- 0000301: [Display] Add an option to disable banners (lee) - resolved.
