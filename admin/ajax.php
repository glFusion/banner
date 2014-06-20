<?php
/**
 *  $Id: ajax.php 64 2010-03-17 21:40:30Z root $
 *  Common AJAX functions
 *  @author     Lee Garner <lee@leegarner.com>
 *  @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
 *  @package    banner
 *  @version    0.1
 *  @license    http://opensource.org/licenses/gpl-2.0.php 
 *  GNU Public License v2 or later
 *  @filesource
 */

/**
 *  Include required glFusion common functions
 */
require_once '../../../lib-common.php';

$base_url = $_CONF['site_url'];
$status = false;

switch ($_GET['action']) {
    case 'toggleEnabled':
        $newval = $_REQUEST['newval'] == 1 ? 1 : 0;

        switch ($_GET['type']) {
            case 'banner':
                USES_banner_class_banner();
                $B = new Banner($_REQUEST['id']);
                $status = $B->toggleEnabled($newval);
                //Banner::toggleEnabled($_REQUEST['newval'], $_REQUEST['id']);
                break;

            case 'category':
                USES_banner_class_category();
                Category::toggleEnabled($_REQUEST['newval'], $_REQUEST['id']);
                $status = true;
                break;

            case 'campaign':
                USES_banner_class_campaign();
                Campaign::toggleEnabled($_REQUEST['newval'], $_REQUEST['id']);
                $status = true;
                break;

            case 'cat_cb':
                USES_banner_class_category();
                Category::toggleCenterblock($_REQUEST['newval'], $_REQUEST['id']);
                $status = true;
                break;

            default:
                exit;
        }

        if (!$status) {
            // If update failed, revert $newval back to original value
            $newval = $newval == 1 ? 0 : 1;
        }
        $img_url = $base_url . "/" . $_CONF_BANR['pi_name'] . "/images/";
        $img_url .= $newval == 1 ? 'on.png' : 'off.png';

        header('Content-Type: text/xml');
        header("Cache-Control: no-cache, must-revalidate");
        //A date in the past
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
    echo '<info>'. "\n";
    echo "<newval>$newval</newval>\n";
    echo "<id>{$_REQUEST['id']}</id>\n";
    echo "<type>{$_REQUEST['type']}</type>\n";
    echo "<imgurl>$img_url</imgurl>\n";
    echo "<baseurl>{$base_url}</baseurl>\n";
    echo "</info>\n";
    break;

case 'toggleBannerEnabled':
    $newval = $_REQUEST['newval'] == 1 ? 1 : 0;
    USES_banner_class_banner();
    Banner::toggleEnabled($_REQUEST['newval'], $_REQUEST['bid']);

    $img_url = $base_url . "/" . $_CONF_BANR['pi_name'] . "/images/";
    $img_url .= $newval == 1 ? 'on.png' : 'off.png';

    header('Content-Type: text/xml');
    header("Cache-Control: no-cache, must-revalidate");
    //A date in the past
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
    echo '<eninfo>'. "\n";
    echo "<newval>$newval</newval>\n";
    echo "<bid>{$_REQUEST['bid']}</bid>\n";
    echo "<imgurl>$img_url</imgurl>\n";
    echo "<baseurl>{$base_url}</baseurl>\n";
    echo "</eninfo>\n";
    break;
}

?>
