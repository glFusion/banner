<?php
//  $Id: index.php 80 2011-05-09 18:50:23Z root $
/**
*   This is the main user-facing page for the Banner Plugin.
*   Provides a way for users to view and edit banners that they own.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2011 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.1.6
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

require_once '../lib-common.php';

USES_banner_functions();
USES_banner_class_banner();

// Nothing here for anonymous users.
if (!in_array('banner', $_PLUGINS) || COM_isAnonUser()) {
    COM_404();
}

// Clean $_POST and $_GET, in case magic_quotes_gpc is set
if (GVERSION < '1.3.0') {
    $_POST = BANR_stripslashes($_POST);
    $_GET = BANR_stripslashes($_GET);
}

// MAIN
$display = '';
$view = 'campaigns';
$expected = array('banners', 'campaigns', 'campaignDetail', 'report', 
        'edit', 'toggleEnabled', 'toggleEnabledCampaign',
        'action', 'view');
$action = 'view';
$actionval = 'campaigns';
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
        $actionval = $_GET[$provided];
        break;
    }
}

// Sanitize the banner ID
$bid = isset($_GET['bid']) ? COM_sanitizeId($_GET['bid']) : '';

// Override the action if deleting multiple banners
if (isset($_POST['delitem']) && is_array($_POST['delitem'])) {
    $action = 'delmulti';
}

$message = array();

switch ($action) {
case 'report':
    // Send a broken banner report to the admin
    if (!empty($bid)) {
        $result = DB_query("SELECT url, title 
                    FROM {$_TABLES['banner']} 
                    WHERE bid = '$bid'");
        list($url, $title) = DB_fetchArray($result);

        $editurl = $_CONF['site_admin_url']
                 . '/plugins/banner/index.php?mode=edit&bid=' . $bid;
        $msg = $LANG_BANNER[119] . LB . LB . "$title, <$url>". LB . LB
                 .  $LANG_BANNER[120] . LB . '<' . $editurl . '>' . LB . LB
                 .  $LANG_BANNER[121] . $_USER['username'] . ', IP: '
                 . $_SERVER['REMOTE_ADDR'];
        COM_mail($_CONF['site_mail'], $LANG_BANNER[118], $msg);
        $message = array($LANG_BANNER[123], $LANG_BANNER[122]);
    }
    break;
case 'toggleEnabled':
    USES_banner_class_banner();
    $B = new Banner($_REQUEST['bid']);
    $B->toggleEnabled($_REQUEST['newval']);
    $view = 'banners';
    break;

case 'toggleEnabledCampaign':
    USES_banner_class_campaign();
    $C = new Campaign($_REQUEST['camp_id']);
    $C->toggleEnabled($_REQUEST['newval']);
    $view = 'campaigns';
    break;

case 'delmulti':
    // Delete multiple banners.  Double-check that the user has access.
    USES_banner_class_banner();
    foreach ($_POST['delitem'] as $item) {
        $B = new Banner($item);
        if ($B->hasAccess(3)) {
            $B->Delete();
        } else {
            BANNER_auditLog("Tried to delete banner $item without access");
        }
    }
    $view = 'banners';
    break;

default:
    $view = $actionval;
    break;
}

switch ($view) {
case 'banners':
    $view_title = $LANG_BANNER[114];
    $camp_id = '';
    USES_banner_class_bannerlist();
    $L = new BannerList();
    if (isset($_REQUEST['camp_id']))
        $L->setCampID($_REQUEST['camp_id']);
    $content .= $L->ShowList();
    break;

case 'campaigns':
    USES_banner_class_campaignlist();
    $L = new CampaignList();
    $content .= $L->ShowList();
    break;

case 'campaignDetail':
    USES_banner_class_campaign();
    USES_banner_class_image();
    USES_lib_admin();

    $C = new Campaign($_REQUEST['camp_id']);
    $C->getBanners();

    $menu_arr = array(
        array('url' => BANR_URL . '/index.php?mode=banners',
              'text' => $LANG_BANNER['banners']),
        array('url' => BANR_URL . '/index.php?mode=campaigns',
                'text' => 'Campaigns'),
    );
    $content .= ADMIN_createMenu($menu_arr, $LANG_BANNER['banners'] . $validate_help, plugin_geticon_banner());

    $T = new Template($_CONF['path'] . 'plugins/banner/templates/');
    $T->set_file(array('camp_detail' => 'campaign.thtml',));
    $T->set_var('camp_id', $C->camp_id);
    $T->set_var('camp_descrip', $C->description);
    $T->set_var('camp_pubstart', $C->start);
    $T->set_var('camp_pubfinish', $C->finish);
    $T->set_block('camp_detail', 'BannerRow', 'brow');
    foreach ($C->Banners as $B) {
        $T->set_var('banner_id', $B->bid);
        $T->set_var('banner_pubstart', $B->publishstart);
        $T->set_var('banner_pubend', $B->publishend);
        list($width, $height) = Image::reDim($B->width, $B->height, 300);
        $T->set_var('banner_content', $B->BuildBanner('', $width, $height, false));
        $T->set_var('banner_hits', $B->hits.'/'.$B->max_hits);
        $T->parse('brow', 'BannerRow', true);
    }
    $T->parse ('output', 'camp_detail');
    $content .= $T->finish($T->get_var('output'));
    break;

case 'edit':
    if (SEC_hasRights('banner.edit')) {
        USES_banner_class_banner();
        $B = new Banner($_REQUEST['bid']);
        $B->setAdmin(false);
        $content .= $B->Edit('useredit');
    }
    break;

}

$display .= COM_siteHeader('menu', $view_title);
$display .= $content;
$display .= COM_siteFooter();
echo $display;

?>
