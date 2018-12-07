<?php
/**
 * This is the main user-facing page for the Banner Plugin.
 * Provides a way for users to view and edit banners that they own.
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
USES_lib_admin();

// Nothing here for anonymous users.
if (!in_array('banner', $_PLUGINS) || COM_isAnonUser()) {
    COM_404();
}

// MAIN
$display = '';
$view = 'campaigns';
$expected = array('banners', 'campaigns', 'campaignDetail', 'report', 
        'edit', 'toggleEnabled', 'toggleEnabledCampaign',
        'deleteBanner', 'save',
        'action', 'view');
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
case 'deleteBanner':
    $B = new Banner\Banner($_GET['bid']);
    if ($B->isNew || $B->owner_id != $_USER['uid']) {
        COM_404();
    }
    $B->Delete();
    echo COM_refresh(BANR_URL);
    exit;
    break;

case 'report':
    // Send a broken banner report to the admin
    if (!empty($bid)) {
        $result = DB_query("SELECT url, title 
                    FROM {$_TABLES['banner']} 
                    WHERE bid = '$bid'");
        list($url, $title) = DB_fetchArray($result);

        $editurl = $_CONF['site_admin_url']
                 . '/plugins/banner/index.php?edit=x&bid=' . $bid;
        $msg = $LANG_BANNER['broken_txt1'] . LB . LB . "$title, <$url>". LB . LB
                 .  $LANG_BANNER['click_to_edit'] . LB . '<' . $editurl . '>' . LB . LB
                 .  $LANG_BANNER['broken_report_by'] . $_USER['username'] . ', IP: '
                 . $_SERVER['REMOTE_ADDR'];
        COM_mail($_CONF['site_mail'], $LANG_BANNER['broken_report'], $msg);
        $message = array($LANG_BANNER['thanks'], $LANG_BANNER['thanks_for_report']);
    }
    break;
case 'toggleEnabled':
    $B = new Banner\Banner($_REQUEST['bid']);
    $B->toggleEnabled($_REQUEST['newval']);
    $view = 'banners';
    break;

case 'toggleEnabledCampaign':
    $C = new Banner\Campaign($_REQUEST['camp_id']);
    $C->toggleEnabled($_REQUEST['newval']);
    $view = 'campaigns';
    break;

case 'delmulti':
    // Delete multiple banners.  Double-check that the user has access.
    foreach ($_POST['delitem'] as $item) {
        $B = new Banner\Banner($item);
        if ($B->hasAccess(3)) {
            $B->Delete();
        } else {
            BANNER_auditLog("Tried to delete banner $item without access");
        }
    }
    $view = 'banners';
    break;

default:
    $view = $action;
    break;
}

switch ($view) {
case 'banners':
default:
    $view_title = $LANG_BANNER[['pi_name']];
    $camp_id = '';
    $L = new Banner\BannerList();
    if (isset($_REQUEST['camp_id']))
        $L->setCampID($_REQUEST['camp_id']);
    $content .= $L->ShowList();
    break;

case 'campaigns':
    $L = new Banner\CampaignList();
    $content .= $L->ShowList();
    break;

case 'campaignDetail':
    $C = new Banner\Campaign($_REQUEST['camp_id']);
    $C->getBanners();

    $menu_arr = array(
        array('url' => BANR_URL . '/index.php?banners=x',
              'text' => $LANG_BANNER['banners']),
        array('url' => BANR_URL . '/index.php?campaigns=x',
                'text' => 'Campaigns'),
    );
    $content .= ADMIN_createMenu($menu_arr, $LANG_BANNER['banners'] . $validate_help, plugin_geticon_banner());

    $T = new \Template(BANR_PI_PATH . '/templates/');
    $T->set_file('camp_detail', 'campaign.thtml');
    $T->set_var(array(
        'camp_id'       => $C->camp_id,
        'camp_descrip'  => $C->description,
        'camp_pubstart' => $C->start,
        'camp_pubfinish'=> $C->finish,
    ) );
    $T->set_block('camp_detail', 'BannerRow', 'brow');
    foreach ($C->Banners as $B) {
        $T->set_var(array(
            'banner_id'         => $B->bid,
            'banner_pubstart'   => $B->publishstart,
            'banner_pubend'     => $B->publishend,
            'banner_content'    => $B->BuildBanner('', 300, 300, false),
            'banner_hits'       => $B->hits.'/'.$B->max_hits,
        ) );
        $T->parse('brow', 'BannerRow', true);
    }
    $T->parse ('output', 'camp_detail');
    $content .= $T->finish($T->get_var('output'));
    break;

case 'edit':
    if (SEC_hasRights('banner.edit')) {
        $B = new Banner\Banner($_REQUEST['bid']);
        $B->setAdmin(false);
        $content .= $B->Edit('useredit');
    }
    break;

}

echo COM_siteHeader('menu', $view_title);
echo BANR_userMenu($view);
echo $content;
echo COM_siteFooter();
exit;

/**
 * Create the administrator menu.
 *
 * @param   string  $view   View being shown, so set the help text
 * @return  string      Administrator menu
 */
function BANR_userMenu($view='')
{
    global $_CONF, $LANG_ADMIN, $LANG_BANNER, $_CONF_BANR;

    if (isset($LANG_BANNER['admin_hdr_' . $view]) && 
        !empty($LANG_BANNER['admin_hdr_' . $view])) {
        $hdr_txt = $LANG_BANNER['admin_hdr_' . $view];
    } else {
        $hdr_txt = $LANG_BANNER['admin_hdr'];
    }

    if ($view == 'banners') {
        $menu_arr[] = array(
                    'url'  => BANR_URL . '/index.php?edit=x',
                    'text' => '<span class="banrNewAdminItem">' .
                            $LANG_BANNER['new_banner'], '</span>');
    } else {
        $menu_arr[] = array(
                    'url'  => BANR_URL . '/index.php?banners',
                    'text' => $LANG_BANNER['banners']);
    }

    if ($view == 'campaigns') {
        $menu_arr[] = array(
                    'url'  => BANR_URL . '/index.php?edit=x&item=campaign',
                    'text' => '<span class="banrNewAdminItem">' .
                            $LANG_BANNER['new_camp'] . '</span>');
    } else {
        $menu_arr[] = array('url'  => BANR_URL . 
                            '/index.php?campaigns=x',
                    'text' => $LANG_BANNER['campaigns']);
    }

    $T = new \Template(BANR_PI_PATH . '/templates');
    $T->set_file('title', 'banner_admin_title.thtml');
    $T->set_var('title', 
        $LANG_BANNER['banner_mgmt'] . ' (Ver. ' . $_CONF_BANR['pi_version'] . ')');
    $retval = $T->parse('', 'title');
    $retval .= ADMIN_createMenu($menu_arr, $hdr_txt, 
            plugin_geticon_banner());

    return $retval;
}

?>
