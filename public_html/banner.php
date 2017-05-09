<?php
/**
*   Display a specific banner image
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.2.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/
namespace Banner;

require_once '../lib-common.php';

/** Make sure the banner plugin is loaded */
if (!function_exists('USES_banner_class_banner')) {
    exit;
}

/** Import the needed Banner class */
USES_banner_class_banner();

/** Set up the options array */
$options = array(
    'limit' => '1',
    'campaign'] => isset($_GET['campaign']) ? trim($_GET['campaign']) : '',
);

$B = new Banner();
$bids = Banner::GetBanner($options);
if (is_array($bids) && !empty($bids)) {
    $B->Read($bids[0]);
    $banner = $B->BuildBanner();
    $B->updateImpressions();
    echo $banner;
}

?>
