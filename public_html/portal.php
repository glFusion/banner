<?php
/**
 * Portal page that tracks banner clicks and redirects to the target.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2022 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

/** Import core glFusion libraries */
require_once '../lib-common.php';

// Make sure the banner is installed & enabled
if (!in_array('banner', $_PLUGINS)) {
    echo COM_refresh($_CONF['site_url'] . '/index.php');
    exit;
}

$url = '';

COM_setArgNames(array('id'));
$bid = COM_getArgument('id');

if (!empty($bid)) {
    $B = new Banner\Banner($bid);
    $url = $B->getOpt('url');
    if (!empty($url)) {
        $B->updateHits();
    } else {
        $url = $_CONF['site_url'];  // just to go somewhere
    }
}

echo COM_refresh($url);

