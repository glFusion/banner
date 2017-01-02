<?php
//  $Id: functions.inc 12 2009-07-09 18:53:04Z root $
/**
*   Default English Language file for the Banner plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2016 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.1.7
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

global $LANG32;

/**
* The plugin's lang array
* @global array $LANG_BANNER
*/
$LANG_BANNER = array(
'bid'           => 'Banner ID',
'cid'           => 'Category ID',
'camp_id'       => 'Campaign ID',
'target_url'    => 'Target URL',
'target'        => 'Target',
'new_window'    => 'New Window',
'same_window'   => 'Same Window',
'banner_url'    => 'Banner Image URL',
'upload_img'    => 'Upload a Banner',
'remote_img'    => 'URL to a remote image',
'includehttp'   => 'Include http://',
'alt'           => 'Alt Tag',
'wide'          => 'Wide',
'high'          => 'High',
'dimensions'    => 'Dimensions',
'upload_vs_url' => 'Both an uploaded file and a remote url may be given.  If both are provided, the upload image (if available) will be used.',
'current_image' => 'Current Image',
'ad_campaigns'  => 'Ad Campaigns',
'campaigns'     => 'Campaigns',
'campaign'      => 'Campaign',
'ad_code'       => 'Complete Ad Code',
'ad_is_script'  => 'Script ad not displayed',
'ad_type'       => 'Type of Ad',
'ad_types'      => array(
                    BANR_TYPE_LOCAL     => 'Uploaded Image',
                    BANR_TYPE_REMOTE    => 'Remotely-Hosted Image',
                    BANR_TYPE_SCRIPT    => 'HTML or Javascript',
                    ),
'ok_to_delete'  => 'Are you sure you want to delete this item?',
'banner_content' => 'Banner Content',
'hits'          => 'Hits',
'ads_in_campaign' => 'Ads In This Campaign',
'action'        => 'Action',
'edit'          => 'Edit',
'banners'       => 'Banners',
'visible_members' => 'Visible to Members?',
'visible_anon' => 'Visible to Anonymous?',
'access_denied' => 'Access Denied',
'access_denied_msg' => "You are trying to access a feature that you don't have rights to.  This attempt has been logged.",
'banner_editor' => 'Banner Editor',
'banner_id'     => 'Banner ID',
'banner_title'  => 'Banner Title',
'banner_cat'    => 'Banner Category',
'pubstart'      => 'Publish Start',
'pubend'        => 'Publish End',
'banner_hits'   => 'Banner Hits',
'new_banner'    => 'New Banner',
'validate_banner' => 'Validate Banner',
'categories'    => 'Categories',
'validate_now'  => 'Validate now',
'html_status'   => 'HTML Status',
'validate_instr' => '<p>To validate all banner displayed, please click on the "Validate now" banner below. Please note that this might take some time depending on the amount of banner displayed.</p>',
'banner_mgr'    => 'Banner Manager',
'banner_instr_list' => 'To modify or delete a banner, click on that banner\'s edit icon below.  To create a new banner or a new category, click on "New banner" or "New category" above. To edit multiple categories, click on "Edit categories" above.',
'banner_instr_validate' => 'Click the &quot;Validate now&quot; button to validate the HTTP response from the banner links.',
'enabled'       => 'Enabled',
'centerblock'   => 'Centerblock?',
'click_disable' => 'Click to disable',
'click_enable'  => 'Click to enable',
'before_validate' => 'Not validated yet',
'camp_id'       => 'Campaign ID',
'user_id'       => 'User ID',
'banners'       => 'Banners',
'new_camp'      => 'New Campaign',
'camp_mgr'      => 'Campaign Management',
'camp_mgr_instr' => 'Add, Delete, and Modify banner ad campaigns.',
'cat_mgr_instr' => 'Add, Delete, and Modify banner ad categories.',
'all'           => 'All',
'new_cat'       => 'New Category',
'type'          => 'Type',
'cat_name'      => 'Category Name',
'topic'         => 'Topic',
'cat_mgmt'      => 'Category Management',
'banner_mgmt'   => 'Banner Ad Management',
'category'      => 'Category',
'description'   => 'Description',
'edit_details'  => 'Enter or edit the details below.',
'title'         => 'Title',
'banner_info'   => 'Banner Information',
'max_hits'      => 'Max. Hits',
'impressions'   => 'Impressions',
'max_impressions' => 'Max. Impressions',
'access_control' => 'Access Control',
'submit_banner' => 'Submit a Banner',
'banner_submissions' => 'Banner Submissions',
'stats_headline'    => 'Top Ten Banner',
'stats_page_title'  => 'Banner',
'stats_no_hits'     => 'It appears that there are no banner on this site or no one has ever clicked on one.',
'weight'        => 'Weighting',
'max_img_height'    => 'Max. Image Height (px)',
'max_img_width'    => 'Max. Image Width (px)',
'duplicate_bid'     => 'The banner ID is not unique.',
'duplicate_cid'     => 'The category ID is not unique.',
'duplicate_camp_id' => 'The campaign ID is not unique.',
'no_dt_limit'       => 'No Date Restriction',
'reset'             => 'Reset',
'confirm_delitem'   => 'Are you sure you want to delete this item?',
'req_item_msg'  => 'Items in <span class="required">red</span> are required.',
'user_can_add'  => 'User can submit banners',
'max_banners'   => 'Max. Banners',
'version'       => 'Version',

    10 => 'Submissions',
    14 => 'Banner',
    84 => 'Banner',
    88 => 'No recent new banner',
    114 => 'Banner',
    116 => 'Add A Banner',
    117 => 'Report Broken Banner',
    118 => 'Broken Banner Report',
    119 => 'The following banner has been reported to be broken: ',
    120 => 'To edit the banner, click here: ',
    121 => 'The broken Banner was reported by: ',
    122 => 'Thank you for reporting this broken banner. The administrator will correct the problem as soon as possible',
    123 => 'Thank you',
    124 => 'Go',
    125 => 'Categories',
    126 => 'You are here:',
    'root' => 'Root',   // title used for top level category
    'warn_update_hits' => 'Updating the counter may hurt campaign reporting',
    'zero_eq_unlimited' => 'Zero = Unlimited',
);

###############################################################################
# for stats
/**
* The plugin's lang stats array
*
* @global array $LANG_BANNER_STATS
*/
$X_LANG_BANNER_STATS = array(
    'banner' => 'Banner (Clicks) in the System',
    'stats_hits' => 'Hits',
);

###############################################################################
# for the search
/**
* the banner plugin's lang search array
*
* @global array $LANG_BANNER_SEARCH
*/
$X_LANG_BANNER_SEARCH = array(
 'results' => 'Banner Results',
 'title' => 'Title',
 'date' => 'Date Added',
 'author' => 'Submitted by',
 'hits' => 'Clicks'
);

###############################################################################
# for the submission form
/**
* the banner plugin's lang submit form array
*
* @global array $LANG_BANNER_SUBMIT
*/
$X_LANG_BANNER_SUBMIT = array(
    2 => 'Banner',
    3 => 'Category',
    4 => 'Other',
    5 => 'If other, please specify',
    6 => 'Error: Missing Category',
    7 => 'When selecting "Other" please also provide a category name',
    8 => 'Title',
    9 => 'URL',
    10 => 'Category',
    11 => 'Banner Submissions',
    12 => 'Enter the complete image tag as the description.  For example:  "&lt;img src=http://mysite.com/banner.png&gt;".  Do not include the link tag as the value from the Banner field will be used.',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_banner_MESSAGE1 = "Thank-you for submitting a banner to {$_CONF['site_name']}.  It has been submitted to our staff for approval.  If approved, your banner will be seen in the <a href={$_CONF['site_url']}/banner/index.php>banner</a> section.";
$PLG_banner_MESSAGE2 = 'Your banner has been successfully saved.';
$PLG_banner_MESSAGE3 = 'The banner has been successfully deleted.';
$PLG_banner_MESSAGE4 = "Thank-you for submitting a banner to {$_CONF['site_name']}.  You can see it now in the <a href={$_CONF['site_url']}/banner/index.php>banner</a> section.";
$PLG_banner_MESSAGE5 = "You do not have sufficient access rights to view this category.";
$PLG_banner_MESSAGE6 = 'You do not have sufficient rights to edit this category.';
$PLG_banner_MESSAGE7 = 'Please enter a Category Name and Description.';

$PLG_banner_MESSAGE10 = 'Your category has been successfully saved.';
$PLG_banner_MESSAGE11 = 'You are not allowed to set the id of a category to "site" or "user" - these are reserved for internal use.';
$PLG_banner_MESSAGE12 = 'You are trying to make a parent category the child of it\'s own subcategory. This would create an orphan category, so please first move the child category or categories up to a higher level.';
$PLG_banner_MESSAGE13 = 'The category has been successfully deleted.';
$PLG_banner_MESSAGE14 = 'Category contains banner and/or categories. Please remove these first.';
$PLG_banner_MESSAGE15 = 'You do not have sufficient rights to delete this category.';
$PLG_banner_MESSAGE16 = 'No such category exists.';
$PLG_banner_MESSAGE17 = 'This category id is already in use.';

// Messages for the plugin upgrade
$PLG_banner_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_banner_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/banner.php
/**
* the banner plugin's lang admin array
*
* @global array $LANG_BANNER_ADMIN
*/
$LANG_BANNER_ADMIN = array(
    4 => 'Banner URL',
    5 => 'Category',
    6 => '(include http://)',
    7 => 'Other',
    9 => 'Banner Content',
    10 => 'You need to provide a banner Title, URL and Description.',
    20 => 'If other, specify',
    21 => 'save',
    22 => 'cancel',
    23 => 'delete',
    24 => 'Banner not found',
    25 => 'The banner you selected for editing could not be found.',
    28 => 'Edit category',
    30 => 'Category',
    32 => 'Category ID',
    33 => 'Delete Category',
    34 => 'Parent',
    35 => 'Save Category',
    36 => 'Save Campaign',
    37 => 'Delete Campaign',
    40 => 'Edit this category',
    41 => 'Create child category',
    42 => 'Delete this category',
    43 => 'Site categories',
    44 => 'Add&nbsp;child',
    46 => 'User %s tried to delete a category to which they do not have access rights',
    53 => 'Banner banner',
    55 => 'Edit categories below. Note that you cannot delete a category that contains other categories or banner - you should delete these first, or move them to another category.',
    56 => 'Category Editor',
    60 => 'User %s tried illegally to edit category %s.',
    66 => 'Campaigns',
    72 => 'Action',
);

$LANG_BANNER_STATUS = array(
    0   => 'Lookup Error',
    100 => 'Continue',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    999 => 'Connection Timed out',
);


// Localization of the Admin Configuration UI
$LANG_configsections['banner'] = array(
    'label' => 'Banner',
    'title' => 'Banner Configuration',
);

$LANG_confignames['banner'] = array(
    'templatevars' => 'The banner is displayed with the template',
    'usersubmit' => 'Allow submissions from site members',
    'submissionqueue' => 'Enable Submission Queue?',
    'notification' => 'Notification Email?',
    'delete_banner' => 'Delete Banner with Owner?',
    'default_permissions' => 'Banner Default Permissions',
    'show_in_admin' => 'Show banners on admin pages?',
    'target_blank' => 'Show target links in a new window?',
    'img_max_width' => 'Maximum image width (px)',
    'img_max_height' => 'Maximum image height (px)',
    'users_dontshow' => 'Users who will not be shown ads',
    'ipaddr_dontshow' => 'IP Addresses that will not be shown ads',
    'uagent_dontshow' => 'User Agents that will not be shown ads',
    'def_weight'    => 'Default Weight',
    'adshow_owner'  => 'Show ads to the ad owner?',
    'adshow_admins' => 'Show ads to ad administrators?',
    'cntclicks_owner' => 'Count ad clicks made by the ad owner?',
    'cntclicks_admins' => 'Count ad clicks made by ad administrators?',
    'cntimpr_owner' => 'Count ad impressions for the ad owner?',
    'cntimpr_admins' => 'Count ad impressions for ad administrators?',
    'cb_enable'     => 'Centerblock Enabled?',
    'cb_home'       => 'Centerblock on Home Page Only?',
    'cb_pos'        => 'Centerblock Position?',
    'cb_replhome'   => 'Centerblock Replaces Home Page?',
    'block_limit'   => 'Max. ads to show in blocks',
);

$LANG_configsubgroups['banner'] = array(
    'sg_main' => 'Main Settings',
);

$LANG_fs['banner'] = array(
    'fs_main' => 'Main Banner Settings',
    'fs_adcontrol' => 'Ad Display Control',
    'fs_permissions' => 'Default Permissions',
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['banner'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
    3 => array('Yes' => 1, 'No' => 0),
    4 => array('10' => 10, '09' => 9, '08' => 8, '07' => 7, '06' => 6,
            '05' => 5, '04' => 4, '03' => 3, '02' => 2, '01' => 1),
    5 => array('Top of Page' => 1, 'Below Featured Article' => 2, 'Bottom of Page' => 3),
    9 => array('Forward to Bannered Site' => 'item', 'Display Admin Banner' => 'list', 'Display Public Banner' => 'plugin', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
);

?>
