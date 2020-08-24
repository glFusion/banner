<?php
/**
 * Banner admin entry point.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

/** Import core glFusion libraries */
require_once '../../../lib-common.php';

$content = '';

// Must have privileges to access this admin area
if (!plugin_ismoderator_banner()) {
    COM_404();
}

USES_lib_admin();

$action = '';
$actionval = '';
$expected = array(
    'save', 'delete', 'delitem', 'validate',
    'edit', 'moderate', 'cancel',
    'editcampaign', 'editcategory', 'editbanner',
    'banners', 'categories', 'campaigns',
    'mode', 'view',
);
foreach ($expected as $provided) {
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
// Allow for old-style "mode=xxxx" urls
if ($action == 'mode') {
    $action = $actionval;
}
if ($action == '') $action = 'banners';    // default view
$item = isset($_REQUEST['item']) ? $_REQUEST['item'] : 'banner';
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : $action;
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

if ($action == 'delitem') {
    switch ($item) {
    case 'banner':
        $action = 'delMultiBanner';
        break;
    default:
        $action = '';
        break;
    }
}

if (isset($_REQUEST['bid'])) {
    $bid = COM_sanitizeID($_REQUEST['bid'], false);
} else {
    $bid = '';
}

switch ($action) {
case 'toggleEnabled':
    $B = new Banner\Banner($_REQUEST['bid']);
    $B->toggleEabled($_REQUEST['newval']);
    $view = 'banners';
    break;

case 'delete':
    switch ($item) {
    case 'banner':
        if ($type == 'submission') {
            $B = new Banner\Banner($_REQUEST['bid']);
            $B->setTable('bannersubmission');
            $view = 'moderation';
        } else {
            $B = new Banner\Banner($_REQUEST['bid']);
            $view = 'banners';
        }
        $B->Delete();
        break;
    case 'category':
        $C = new Banner\Category($_REQUEST['cid']);
        $content .= $C->Delete();
        $view = 'categories';
        break;
    case 'campaign':
        $C = new Banner\Campaign($_REQUEST['camp_id']);
        $C->Delete();
        $view = 'campaigns';
        break;
    }
    break;

case 'delMultiBanner':
    foreach ($_POST['delitem'] as $item) {
        $B = new Banner\Banner($item);
        $B->Delete();
    }
    $view = 'banners';
    break;

case 'toggleEnabledCategory':
    echo "admin $action deprecated";die;
    Banner\Category::toggleEnabled($_REQUEST['newval'], $_REQUEST['cid']);
    $view = 'categories';
    break;

case 'toggleEnabledCampaign':
    echo "admin $action deprecated";die;
    Banner\Campaign::toggleEnabled($_REQUEST['newval'], $_REQUEST['camp_id']);
    $view = 'campaigns';
    break;

case 'save':
    switch ($item) {
    case 'category':
        // 'oldcid' will be empty for new entries, non-empty for updates
        $C = new Banner\Category($_POST['oldcid']);
        $status = $C->Save($_POST);
        if ($status != '') {
            COM_setMsg(BANNER_errorMessage($status));
            COM_refresh(BANR_ADMIN_URL . '/index.php?editcategory&cid=' . $_POST['oldcid']);
        } else {
            COM_refresh(BANR_ADMIN_URL . '/index.php?categories');
        }
        break;

    case 'campaign':
        $C = new Banner\Campaign($_POST['old_camp_id']);
        $errors = $C->Save($_POST);
        if ($errors != '') {
            $content .= BANNER_errorMessage($errors);
            if (isset($_POST['old_camp_id']) && !empty($_POST['old_camp_id'])) {
                $view = 'editcampaign';
                $mode = 'edit';
            } else {
                $view = 'editcampaign';
            }
        } else {
            COM_refresh(BANR_ADMIN_URL . '/index.php?campaigns');
        }
        break;

    case 'banner':
        $status = '';
        if (SEC_checkToken()) {
            $B = new Banner\Banner();
            $B->setAdmin(true);

            if ($type == 'submission') {
                $B->setTable('bannersubmission');
            }
            if (isset($_POST['oldbid']) && !empty($_POST['oldbid'])) {
                $B->Read($_POST['oldbid']);
            }
            $B->setTable('banner');
            // Delete the submission, if any
            if ($type == 'submission') {
                $B->setVars($_POST)
                    ->setIsNew(true);
                $status = $B->Save();
                if ($status == '') {
                    // Only delete from submission table if status is ok
                    DB_delete($_TABLES['bannersubmission'], 'bid', $B->getBid());
                }
            } else {
                $status = $B->Save($_POST);
            }
        }
        if ($status != '') {
            $content .= BANNER_errorMessage($status);
            $bid = '';      // Reset to force new banner form
            $view = 'edit';
            $mode = 'edit';
        } else {
            $view = 'banners';
        }
        break;
    }
    break;

case 'moderate':
    $view = $action;
    break;

default:
    $view = isset($_REQUEST['view']) ? $_REQUEST['view'] : $action;
    break;
}

switch ($view) {
case 'campaigns':
    $content .= Banner\Campaign::adminList(true);
    break;

case 'categories':
    $content .= Banner\Category::AdminList();
    break;

// Redirect to the system moderation page
case 'moderation':
    echo COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
    break;

case 'editsubmission':
case 'moderate':
    $B = new Banner\Banner($_GET['bid']);
    $B->setTable('bannersubmission')
        ->setAdmin(true);
    if ($B->getBid() != '') {
        $content .= $B->Edit($mode);
    }
    break;

case 'editbanner':
    $B = new Banner\Banner($bid);
    if (!empty($_POST)) {
        $B->SetVars($_POST);
    }
    $B->setAdmin(true);
    $content .= $B->Edit($action);
    break;

case 'edit':
    echo "edit deprecated";die;
    switch ($item) {
    case 'banner':
        $B = new Banner\Banner($bid);
        if (!empty($_POST)) {
            $B->SetVars($_POST);
        }
        $B->setAdmin(true);
        $content .= $B->Edit($action);
        break;
    case 'campaign':
        $camp_id = isset($_REQUEST['camp_id']) ? $_REQUEST['camp_id'] : '';
        $uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : $_USER['uid'];
        $C = new Banner\Campaign($camp_id);
        if (!empty($_POST)) {
            $C->SetVars($_POST);
        }
        if ($C->getID() == '') {
            $C->setUID($uid);
        }
        $content .= $C->Edit();
        break;
    case 'category':
        $cid = isset($_REQUEST['cid']) ? $_REQUEST['cid'] : '';
        $C = new Banner\Category($cid);
        $content .= $C->Edit();
        break;
    }
    break;

case 'newcategory':
    $C = new Banner\Category();
    if (!empty($_POST)) {
        $C->SetVars($_POST);
    }
    $content .= $C->Edit();
    break;

case 'editcategory':
    $C = new Banner\Category($_REQUEST['cid']);
    $content .= $C->Edit();
    break;

case 'newcampaign':
echo "here in newcampaign";die;
    $C = new Banner\Campaign();
    if (!empty($_POST)) {
        $C->SetVars($_POST);
    }
    if ($C->getID() == '')
        $C->setUID($_REQUEST['uid']);
    $content .= $C->Edit();
    break;

case 'editcampaign':
    $C = new Banner\Campaign($actionval);
    if (!empty($_POST)) {
        $C->setVars($_POST);
    }
    if ($C->getID() == '' && isset($REQUEST['uid']) && !empty($_REQUEST['uid'])) {
        $C->setUID($_REQUEST['uid']);
    }
    $content .= $C->Edit();
    break;

case 'banners':
default:
    $camp_id = LGLIB_getVar($_GET, 'camp_id', 'string', '');
    if (isset($_GET['msg'])) {
        $msg = COM_applyFilter($_GET['msg'], true);
        if ($msg > 0) {
            $content .= COM_showMessage($msg, 'banner');
        }
    }
    $content .= Banner\Banner::adminList(true, $camp_id);
    break;
}   // switch ($view)

echo COM_siteHeader('none', $LANG_BANNER['banners']);
echo Banner\Menu::Admin($view);
echo $content;
echo COM_siteFooter();
exit;

?>
