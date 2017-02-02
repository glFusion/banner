<?php
/**
*
*  Common AJAX functions
*
*  @author     Lee Garner <lee@leegarner.com>
*  @copyright  Copyright (c) 2009-2014 Lee Garner <lee@leegarner.com>
*  @package    banner
*  @version    0.1.7
*  @license    http://opensource.org/licenses/gpl-2.0.php 
*  GNU Public License v2 or later
*  @filesource
*/

/**
*  Include required glFusion common functions
*/
require_once '../../../lib-common.php';

switch ($_GET['action']) {
case 'toggleEnabled':
    $oldval = $_REQUEST['oldval'] == 1 ? 1 : 0;

    switch ($_GET['type']) {
    case 'banner':
        USES_banner_class_banner();
        $B = new Banner($_REQUEST['id']);
        $newval = $B->toggleEnabled($oldval);
        break;

    case 'category':
        USES_banner_class_category();
        $newval = banrCategory::toggleEnabled($oldval, $_REQUEST['id']);
        $status = true;
        break;

    case 'campaign':
        USES_banner_class_campaign();
        $newval = banrCampaign::toggleEnabled($oldval, $_REQUEST['id']);
        break;

    case 'cat_cb':
        USES_banner_class_category();
        $newval = banrCategory::toggleCenterblock($oldval, $_REQUEST['id']);
        $status = true;
        break;

    default:
        exit;
    }
    $result = array(
        'newval' => $newval,
        'id' => $_GET['id'],
        'type' => $_GET['type'],
        'baseurl' => $_CONF['site_url'],
    );
    $result = json_encode($result);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    //A date in the past
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

    echo $result;
    break;
}

?>
