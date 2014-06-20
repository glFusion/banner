<?php
//  $Id: portal.php 84 2011-05-09 21:40:07Z root $
/**
*   Portal page that tracks banner clicks and redirects users to
*   the destination url.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*   GNU Public License v2 or later
*   @filesource
*/

/** Import core glFusion libraries */
require_once '../lib-common.php';

// Make sure the banner is installed & enabled
if (!in_array('banner', $_PLUGINS)) {
    echo COM_refresh($_CONF['site_url'] . '/index.php');
    exit;
}

USES_banner_class_banner();

$url = '';

COM_setArgNames(array('id'));
$bid = COM_getArgument('id');

if (!empty($bid)) {
    // Hack from Links plugin:
    // Due to PLG_afterSaveSwitch settings, we may get
    // an attached &msg - strip it off
    $i = explode('&', $bid);
    $bid = $i[0];
    $B = new Banner($bid);
    $url = $B->options['url'];
    if (!empty($url)) {
        $B->updateHits();
    } else {
        $url = $_CONF['site_url'];
    } 
}

header('HTTP/1.1 301 Moved');
header('Location: ' . $url);
header('Connection: close');

?>
