# Banner plugin for glFusion

The Banner plugin for glFusion allows site administrators to display
banner ads in the header, footer, blocks or within other content on their site.

## Requirements
* glFusion 1.6.0 or higher
* lgLib plugin 1.0.5 or higher

## Definitions
* Banner: An image or html/javascript snippet to display an ad on the site,
including a link to the advertiser's site. Banners can be one of several types:
  * Uploaded Image: Upload an image to your site.
  * Remotely-Hosted Image: Just enter the URL to an image on another website. The banner will be displayed inline from that URL.
  * HTML or Javascript: Enter the complete ad code, e.g. from Adsense. This banner type does not take a target URL value. You have complete control over all elements of the ad.
  * Autotag: This banner may include autotags, or any other text or HTML. A target URL must be provided for the link.
* Category: A way to group banners arbitrarily by type. Also determines the
ad placement based on the "type" field in the category definition.
* Campaign: An advertising campaign is used to control the number of banners
for an advertiser, and the dates when ads will be displayed.

## Placing Banners on Your Site
Banners can be placed on your site by updating your layout templates, or by using a standard autotag.

### Template Changes
There are two methods of getting banner ads into your templates, both using autotags: `banner_<topic>}` and `{adblock}`.
Which you use depends on your own environment.

#### Banner Template Variable
Add an autotag to your template where you'd like the banner to appear. The autotag must be formatted as `banner_<templatename>`
where templatname is the name of the template that gets passed to `PLG_setTemplateVars()`. Examples are "header", "footer",
"story", and "staticpage".

Next, associate one or more banner with a category that has a type matching the template name. Categories for headers, footers and
blocks are provided by default.

To display a banner in the header you can add a template variable `banner_header` to your layout's header.thtml file. Banners associated
with a category of type "header" will be displayed in that area.

The template name should be `banner_` + the template type, such as `header`, `footer`, `article`, etc. There must also be one or more
banner categories of the same type containing the banners to display.

For example, to display a banner in the upper right corner, replacing the default search box in the Nouveau theme, you can edit your header.html like so:
```
{!if banner_header}
{banner_header}
{!else}
<form method="get" action="{site_url}/search.php">
  <div>
    <input id="header-textbox" type="text" name="query" maxlength="255" value="{$LANG09[10]}" title="{$LANG09[10]}" onfocus="if (this.value=='{$LANG09[10]}')this.value='';" onblur="if(this.value=='')this.value='{$LANG09[10]}';"/>
    <input type="hidden" name="type" value="all" />
    <input type="hidden" name="mode" value="search" />
    <input type="hidden" name="results" value="{num_search_results}" />
  </div>
</form>
{!endif}
```
You can add a similar variable named `banner_footer`. For other display locations you can create a variable name of your choice and use that as the "type" in the category definition for banners.

#### Adblock Template Variable
glFusion 1.6.0 and above includes a function that is called from lib-story.php to display an ad within story content.
To use this, edit your storytext.thtml file and place `{adblock}` where you'd like the ad to appear.

The AdBlock function recieves the plugin name and a counter of items displayed on the page. For the full story view, the counter is zero.
You can use this to have as appear only under the first, second, third, etc. article on the index page.

Create categories with the type set to `story_1`, `story_2`, `story_3`, or whichever ones you like. Ads in `story_1` will appear
under the first article, `story_2` under the second, etc.

For the full story view, an ad will be selected from among all the `story*` categories.

### Standard Autotag
The third method of ad placement is to use a standard autotag in an article, staticpage, or any other content. The options are:
    * `[banner:banner_id]` to display a specific banner
    * `[randombanner:category_type]` to select a single random banner from all categories of the given type
    * `[bannercategory:category_id]` to select all banners within a specific category. Banners will be formatted and displayed by the `bannercategory.thtml` template which you may wish to customize.

This method should be used to create ad blocks.

## Configuration Options
### General
#### Display in Templates?
Select "Yes" to have the banner plugin set template variables based on the category name. See "Template Variables" above. You should ensure that the appropriate variables are created in your templates if this feature is enabled.
E.g. If you have a category named "story" then you should have a template variable named `{banner_story}` in all your templates.
Otherwise the plugin will increment the impression count for ads that are never actually displayed.

**Note for glFusion 1.7.0 and higher: There is a flexible template-to-category mapping option for each category to control ad placement. This is the recommended method and this option should be set to "No".**

#### Submissions from Site Members?
Select "Yes" to allow site members to submit their own banners. Banners from non-admin users go into the submission queue for moderation.

#### Send Notification Email?
Select "Yes" to have an email sent to the site administrator when a non-admin user submits a banner.

#### Delete Banner with Owner?
Select "Yes" to have banners deleted when the owner's account is deleted.

#### Show Target Links in New Window?
Select "Yes" to have the target links open in a new window when clicked.

#### Maximum Image Width and Height
Enter the maximum image dimensions that will be accepted. Each category also has maximum dimension settings; this is a global sanity check.

#### Default Weight
Enter the default weight for banners. Each banner can have a higher or lower weight to increase or decrease the likelyhood of being displayed.

#### Max Ads to show in Blocks
Enter the maximum number of ads to be shown in sideblocks for "block"-type ads.

### Display Control
#### Show Ads in Admin Pages?
Select "Yes" to have banners shown in admin pages (those under the /admin/ URL). Normally this should be set to "No".

#### IP Addresses and User-Agents to not be shown ads
To keep from inflating your impression counts you can enter multiple IP addresses and User-Agent strings here.
Page requests from any of these addresses or containing any of these user-agent strings will not be shown ads.

#### Show Ads to the Ad Owner or Ad Administrators?
Select "Yes" if the ad owner or administrator should be shown their own ads.

#### Count Clicks Made by the Ad Owner or Administrator?
These should almost certainly be set to "No" except for initial testing.

#### Count Impressions for the Ad Owner or Administrator?
These should almost certainly be set to "No" except for initial testing.

#### Centerblock Enabled?
Select "Yes" to globally enable the centerblock function. In addition, there must be at least one category with the "Centerblock" flag set.

#### Centerblock Position
Select the position of the centerblock ad. This has no effect if the previous setting is "No".

#### Centerblock Replaces the Home Page
Select "Yes" to have a single banner ad displayed on the home page. This may be useful for a splash screen.
No effect if the Centerblock Enabled setting is "No".

#### Default Permissions
These are the default permissions applied to Campaigns, and indicate who can view ads under the campaign and who can submit ads to it.
Each Banner and Category also has a "group view" permission indicating what glFusion group may view ads.

## Credits
This plugin is based on the Banner Plugin for Geeklog:
* Auther: hiroshi sakuramoto    hiroron AT hiroron DOT com
* Presented by:Ivy komma AT ivywe DOT co DOT jp

... which, in turn, is based on the Geeklog Links plugin.
