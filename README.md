banner
======

The Banner plugin for glFusion allows site administrators to display
banner ads in the header, footer, or blocks on their site.

*********************************************************
This plugin is based on the Banner Plugin for Geeklog:

Auther: hiroshi sakuramoto    hiroron AT hiroron DOT com
Presented by:Ivy komma AT ivywe DOT co DOT jp

... which, in turn, is based on the Geeklog Links plugin.

The original README_jp.txt file has been removed since it is no longer
accurate.  If someone would like to submit a translation of this file 
to Japanese, that would be welcome.
*********************************************************

This is an early test release, and is not intended for use on production
sites.  The documentation is a work in progress and can currently be seen
at http://www.leegarner.com/dokuwiki/doku.php/glfusion:glbanner:start.

Suggesions are welcome.

Template Changes
================
To display a banner in the header you must add a template variable "banner_header" to your layout's header.thtml file.

For example, to display a banner in the upper right corner, replacing the default search box in the Nouveau theme, you can edit your header.html like so, adding the left-justified lines below:
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
