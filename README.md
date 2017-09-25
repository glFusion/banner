# Banner plugin for glFusion

The Banner plugin for glFusion allows site administrators to display
banner ads in the header, footer, blocks or within other content on their site.

## Requirements
* glFusion 1.6.0 or higher
* lgLib plugin 1.0.5 or higher

## Definitions
* Banner: An image or html/javascript snippet to display an ad on the site,
including a link to the advertiser's site.
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

## Credits
This plugin is based on the Banner Plugin for Geeklog:
* Auther: hiroshi sakuramoto    hiroron AT hiroron DOT com
* Presented by:Ivy komma AT ivywe DOT co DOT jp

... which, in turn, is based on the Geeklog Links plugin.
