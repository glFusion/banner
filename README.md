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

## Template Changes
To display a banner in the header you must add a template variable "banner_header" to your layout's header.thtml file.

For example, to display a banner in the upper right corner, replacing the default search box in the Nouveau theme, you can edit your header.html like so:
''''html
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
''''
You can add a similar variable named "banner_footer". For other display locations you can create a variable name of your choice and use that as the "type" in the category definition for banners.

## Credits
This plugin is based on the Banner Plugin for Geeklog:
* Auther: hiroshi sakuramoto    hiroron AT hiroron DOT com
* Presented by:Ivy komma AT ivywe DOT co DOT jp

... which, in turn, is based on the Geeklog Links plugin.
