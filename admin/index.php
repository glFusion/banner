<?php
//  $Id: updatecatxml.php 101 2008-12-12 16:51:21Z root $
/**
 *  Banner admin entry point.
 *
 *  @author     Lee Garner <lee@leegarner.com>
 *  @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
 *  @package    banner
 *  @version    0.1.0
 *  @license    http://opensource.org/licenses/gpl-2.0.php 
 *  GNU Public License v2 or later
 *  @filesource
 */

/** Import core glFusion libraries */
require_once '../../../lib-common.php';

$display = '';

// Must have privileges to access this admin area
if (!SEC_hasRights('banner.edit')) {
    $display .= COM_siteHeader('menu', $MESSAGE[30])
             . COM_showMessageText($MESSAGE[34], $MESSAGE[30])
             . COM_siteFooter();
    COM_accessLog("User {$_USER['username']} tried to illegally access the banner administration screen.");
    echo $display;
    exit;
}

USES_banner_functions();
// Clean $_POST and $_GET, in case magic_quotes_gpc is set
if (GVERSION < '1.3.0') {
    $_POST = BANR_stripslashes($_POST);
    $_GET = BANR_stripslashes($_GET);
}

$mode = '';
$var = '';
$expected = array(
    'edit', 'moderate', 'cancel', 'save', 
    'delete', 'delitem', 'validate', 'mode', 
    'view',
);
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $mode = $provided;
        $var = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
    	$mode = $provided;
        $var = $_GET[$provided];
        break;
    }
}
if ($mode == 'mode') {
    $mode = $var;
}

//$page = isset($_REQUEST['view']) ? $_REQUEST['view'] : $mode;
$item = isset($_REQUEST['item']) ? $_REQUEST['item'] : 'banner';
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : $mode;
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

/*if (isset($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];*/
if ($mode == 'delitem') {
    switch ($item) {
    case 'banner':
        $mode = 'delMultiBanner';
        break;
    default:
        $mode = '';
        break;
    }
}
//var_dump($_POST);
//var_dump($_GET);die;

if (isset($_REQUEST['bid'])) {
    $bid = COM_sanitizeID($_REQUEST['bid'], false);
} else {
    $bid = '';
}

$display .= COM_siteHeader('menu', $LANG_BANNER['banners']);

switch ($mode) {
//case $LANG_ADMIN['save']:       // TODO- see if this can be removed
case $LANG_ADMIN['submit']:
case 'savebanner':
echo "here in savebanner";die;
    $status = '';
    if (SEC_checkToken()) {
        USES_banner_class_banner();
        $B = new Banner();
        $B->setAdmin(true);

        if ($item == 'submission') {
            $B->setTable('bannersubmission');
        }
        if (isset($_POST['oldbid']) && !empty($_POST['oldbid'])) {
            $B->Read($_POST['oldbid']);
        }
        $B->setTable('banner');
        // Delete the submission, if any
        if ($type == 'submission') {
            $B->setVars($_POST);
            $status = $B->Insert(false);
            if ($status == '') {
                // Only delete from submission table if status is ok
                DB_delete($_TABLES['bannersubmission'], 'bid', $B->getID());
            }
        } else {
            $status = $B->Save($_POST);
        }
    }
    if ($status != '') {
        $display .= BANNER_errorMessage($status);
        $bid = '';      // Reset to force new banner form
        $page = 'edit';
    } else {
        $page = 'banners';
    }
    break;

case 'savecampaign':
case $LANG_BANNER_ADMIN[36]:
echo "here in savecampaign";die;
    if ($mode == $LANG_BANNER_ADMIN[36] && empty($LANG_BANNER_ADMIN[36])) {
        break;
    }
    USES_banner_class_campaign();
    $C = new Campaign($_POST['old_camp_id']);
    $status = $C->Save($_POST);
    if ($status != '') {
        $display .= BANNER_errorMessage($status);
        if (isset($_POST['old_camp_id']) && !empty($_POST['old_camp_id'])) {
            $page = 'editcampaign';
        } else {
            $page = 'newcampaign';
        }
    } else {
        $page = 'campaigns';
    }
    break;

case 'deletecampaign':
case $LANG_BANNER_ADMIN[37]:
echo "here in deletecampaign";die;
    if ($mode == $LANG_BANNER_ADMIN[37] && empty($LANG_BANNER_ADMIN[37])) {
        break;
    }
    USES_banner_class_campaign();
    $C = new Campaign($_REQUEST['camp_id']);
    $C->Delete();
    $page = 'campaigns';
    break;

case 'toggleEnabled':
    USES_banner_class_banner();
    $B = new Banner($_REQUEST['bid']);
    $B->toggleEabled($_REQUEST['newval']);
//    Banner::toggleEnabled($_REQUEST['newval'], $_REQUEST['bid']);
    $page = 'banners';
    break;

case 'delete':
    switch ($item) {
    case 'banner':
        USES_banner_class_banner();
        if ($type == 'submission') {
            $B = new Banner($_REQUEST['bid'], 'bannersubmission');
            $page = 'moderation';
        } else {
            $B = new Banner($_REQUEST['bid']);
            $page = 'banners';
        }
        if ($B->hasAccess(3))
            $B->Delete();
        break;
    case 'category':
        USES_banner_class_category();
        $C = new Category($_REQUEST['cid']);
        $display .= $C->Delete();
        $page = 'categories';
        break;
    case 'campaign':
        USES_banner_class_campaign();
        $C = new Campaign($_REQUEST['camp_id']);
        $C->Delete();
        $page = 'campaigns';
        break;
    }
    break;

case 'deletebanner':
case $LANG_ADMIN['delete']:
echo "here in deletebanner";die;
    USES_banner_class_banner();
    if ($type == 'submission') {
        $B = new Banner($_REQUEST['bid'], 'bannersubmission');
        $page = 'moderation';
    } else {
        $B = new Banner($_REQUEST['bid']);
        $page = 'banners';
    }
    if ($B->hasAccess(3))
        $B->Delete();
    break;

case 'delMultiBanner':
    USES_banner_class_banner();
    foreach ($_POST['delitem'] as $item) {
        $B = new Banner($item);
        if ($B->hasAccess(3))
            $B->Delete();
    }
    $page = 'banners';
    break;

case 'toggleEnabledCategory':
    USES_banner_class_category();
    Category::toggleEnabled($_REQUEST['newval'], $_REQUEST['cid']);
    $page = 'categories';
    break;

case 'toggleEnabledCampaign':
    USES_banner_class_campaign();
    Campaign::toggleEnabled($_REQUEST['newval'], $_REQUEST['camp_id']);
    $page = 'campaigns';
    break;


case 'deleteCategory':
case $LANG_BANNER_ADMIN[33]:
echo "Here in deletecategory";die;
    if ($mode == $LANG_BANNER_ADMIN[33] && empty($LANG_BANNER_ADMIN[33])) {
        break;
    }
    USES_banner_class_category();
    $C = new Category($_REQUEST['cid']);
    $display .= $C->Delete();
    $page = 'categories';
    break;

//case 'savecategory':
//case $LANG_BANNER_ADMIN[35]:
case 'save':
    switch ($item) {
    case 'category':
        /*if ($mode == $LANG_BANNER_ADMIN[35] && empty($LANG_BANNER_ADMIN[35])) {
            break;
        }*/
        USES_banner_class_category();
        // 'oldcid' will be empty for new entries, non-empty for updates
        $C = new Category($_POST['oldcid']);
        $status = $C->Save($_POST);
        if ($status != '') {
            $display .= BANNER_errorMessage($status);
            if (isset($_POST['oldcid']) && !empty($_POST['oldcid'])) {
                //$page = 'editcategory';
                $page = 'edit';
            } else {
                $page = 'newcategory';
            }
        } else {
            $page = 'categories';
        }
        break;

    case 'campaign':
        USES_banner_class_campaign();
        $C = new Campaign($_POST['old_camp_id']);
        $status = $C->Save($_POST);
            if ($status != '') {
            $display .= BANNER_errorMessage($status);
            if (isset($_POST['old_camp_id']) && !empty($_POST['old_camp_id'])) {
                $page = 'edit';
                $mode = 'edit';
            } else {
                $page = 'newcampaign';
            }
        } else {
            $page = 'campaigns';
        }
        break;

    case 'banner':
        $status = '';
        if (SEC_checkToken()) {
            USES_banner_class_banner();
            $B = new Banner();
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
                $B->setVars($_POST);
                $status = $B->Insert(false);
                if ($status == '') {
                    // Only delete from submission table if status is ok
                    DB_delete($_TABLES['bannersubmission'], 'bid', $B->getID());
                }
            } else {
                $status = $B->Save($_POST);
            }
        }
        if ($status != '') {
            $display .= BANNER_errorMessage($status);
            $bid = '';      // Reset to force new banner form
            $page = 'edit';
            $mode = 'edit';
        } else {
            $page = 'banners';
        }
        break;

    }
    break;

case 'moderate':
    $page = $mode;
    break;

default:
    $page = isset($_REQUEST['view']) ? $_REQUEST['view'] : $mode;
    break;
}

switch ($page) {
case 'campaigns':
    USES_banner_class_campaignlist();
    $L = new CampaignList(true);
    $display .= $L->ShowList();
    //$display .= BANNER_adminCampaigns();
    break;

case 'categories':
    USES_banner_class_category();
    $display .= BANNER_adminCategories();
    break;

// Redirect to the system moderation page
case 'moderation':
    echo COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
    break;

case 'editsubmission':
case 'moderate':
    USES_banner_class_banner();
    $B = new Banner($_GET['bid'], 'bannersubmission');
    $B->setAdmin(true);
    if ($B->getID() != '') {
        $display .= $B->Edit($mode);
    }
    break;

case 'edit':
    switch ($item) {
    case 'banner':
        USES_banner_class_banner();
        $B = new Banner($bid);
        if (!empty($_POST)) {
            $B->SetVars($_POST);
        }
        $B->setAdmin(true);
        $display .= $B->Edit($mode);
        break;
    case 'campaign':
        USES_banner_class_campaign();
        $C = new Campaign($_REQUEST['camp_id']);
        if (!empty($_POST)) {
            $C->SetVars($_POST);
        }
        if ($C->camp_id == '')
            $C->setUID($_REQUEST['uid']);
        $display .= $C->Edit();
        break;
    case 'category':
        USES_banner_class_category();
        $C = new Category($_REQUEST['cid']);
        $display .= $C->Edit();
        break;
    }
    break;

case 'newcategory':
    USES_banner_class_category();
    $C = new Category();
    if (!empty($_POST)) {
        $C->SetVars($_POST);
    }
    $display .= $C->Edit();
    break;

case 'editcategory':
    USES_banner_class_category();
    $C = new Category($_REQUEST['cid']);
    $display .= $C->Edit();
    break;

case 'newcampaign':
echo "here in newcampaign";die;
    USES_banner_class_campaign();
    $C = new Campaign();
    if (!empty($_POST)) {
        $C->SetVars($_POST);
    }
    if ($C->camp_id == '')
        $C->setUID($_REQUEST['uid']);
    $display .= $C->Edit();
    break;

case 'editcampaign':
    USES_banner_class_campaign();
    $C = new Campaign($_REQUEST['camp_id']);
    if (!empty($_POST)) {
        $C->SetVars($_POST);
    }
    if ($C->camp_id == '')
        $C->setUID($_REQUEST['uid']);
    $display .= $C->Edit();
    break;

case 'banners':
default:
    if (isset($_GET['msg'])) {
        $msg = COM_applyFilter($_GET['msg'], true);
        if ($msg > 0) {
            $display .= COM_showMessage($msg, 'banner');
        }
    }
    USES_banner_class_bannerlist();
    $L = new BannerList(true);
    if (isset($_REQUEST['category']))
        $L->setCatID($_REQUEST['category']);
    if (isset($_REQUEST['camp_id']))
        $L->setCampID($_REQUEST['camp_id']);
    $display .= $L->ShowList();
    break;

}   // switch ($page)

$display .= COM_siteFooter();
echo $display;

?>
