<?php
/**
 * This is the main user-facing page for the Banner Plugin.
 * Provides a way for users to view and edit banners that they own.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

require_once '../lib-common.php';
COM_404();

USES_lib_admin();

// Nothing here for anonymous users.
if (!in_array('banner', $_PLUGINS) || COM_isAnonUser()) {
    COM_404();
}

// MAIN
$content = '';
$view_title = $LANG_BANNER['pi_name'];
$action = 'campaigns';
$expected = array(
    'savesubmission',
    'banners', 'editbanner',
    'campaigns', 'campaignDetail',
    'edit', 'toggleEnabled', 'toggleEnabledCampaign',
    'deleteBanner', 'save',
    'action', 'view',
);
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
case 'savesubmission':
    $B = new Banner\Banner;
    $status = $B->setVars($_POST, false)->Save();
    break;

case 'deleteBanner':
    $B = new Banner\Banner($bid);
    if (!$B->isNew() && $B->getOwnerId() == $_USER['uid']) {
        $B->Delete();
    }
    echo COM_refresh( Config::get('url'));
    exit;
    break;

default:
    $view = $action;
    break;
}

switch ($view) {
case 'editbanner':
    $B = new Banner\Banner($bid);
    $B->setAdmin(false);
    if (empty($bid)) {
        $content .= $B->Edit('submit');
    } else {
        $content .= $B->Edit();
    }
    break;

case 'banners':
default:
    $content .= Banner\Banner::adminList();
    break;

/*case 'campaigns':
    $content .= Banner\Campaign::adminList();
    break;

case 'campaignDetail':
    $C = new Banner\Campaign($_REQUEST['camp_id']);
    $C->getBanners();
    $menu_arr = array(
        array('url' =>  Config::get('url') . '/index.php?banners=x',
              'text' => $LANG_BANNER['banners']),
        array('url' =>  Config::get('url') . '/index.php?campaigns=x',
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
            'banner_id'         => $B->getBid(),
            'banner_pubstart'   => $B->getPubStart(),
            'banner_pubend'     => $B->getPubEnd(),
            'banner_content'    => $B->BuildBanner('', 300, 300, false),
            'banner_hits'       => $B->getHits().'/'.$B->getMaxHits(),
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
 */
}

echo COM_siteHeader('menu', $view_title);
echo Banner\Menu::User($view);
echo $content;
echo COM_siteFooter();
exit;

?>
