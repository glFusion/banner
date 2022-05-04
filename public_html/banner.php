<?php
/**
 * Display a specific banner image.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v0.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

require_once '../lib-common.php';

/** Make sure the banner plugin is loaded */
if (!in_array('banner', $_PLUGINS)) {
    exit;
}

/** Set up the options array */
$options = array(
    'limit'     => '1',
    'campaign'  => isset($_GET['campaign']) ? trim($_GET['campaign']) : '',
);

$Banners = Banner\Banner::getBanners($options);
if (!empty($Banners)) {
    echo $Banners[0]->updateImpressions()->buildBanner();
}

