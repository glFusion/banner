<?php
/**
*   Installation Defaults used when loading the online configuration.
*   These settings are only used during the initial installation 
*   and upgrade not referenced any more once the plugin is installed.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*   GNU Public License v2 or later
*   @filesource
*/

if (!defined('GVERSION')) {
    die('This file can not be used on its own!');
}

/*
 * Banner default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 *  @global array
 */
global $_BANR_DEFAULT;
$_BANR_DEFAULT = array();

/**
 *  Activate banners in the header and footer templates?
 *  1 = Yes, 0 = No
 *  Include {banner_header} or {banner_footer} in the desired template
 */
$_BANR_DEFAULT['templatevars'] = 1;

/**
 *  Can regular users submit banners at all?
 *  1 = Yes, 0 = No
 */
$_BANR_DEFAULT['usersubmit'] = 0;

/**
 * Submission Settings
 * enable (set to 1) or disable (set to 0) submission queues:
 */
$_BANR_DEFAULT['submissionqueue']  = 1;

/**
 * Set to 1 to hide the "Banner" entry from the top menu:
 */
//$_BANR_DEFAULT['hidebannermenu']    = 0;

/**
 * notify when a new banner was submitted
 */
$_BANR_DEFAULT['notification'] = 0;

/**
 * should we remove banner submitted by users if account is removed? (1)
 * or change owner to root (0)
 */
$_BANR_DEFAULT['delete_banner'] = 0;

/**
 * Define default permissions for new banner created from the Admin panel.
 * Permissions are perm_owner, perm_group, perm_members, perm_anon (in that
 * order). Possible values:<br>
 * - 3 = read + write permissions (perm_owner and perm_group only)
 * - 2 = read-only
 * - 0 = neither read nor write permissions
 * (a value of 1, ie. write-only, does not make sense and is not allowed)
 */
$_BANR_DEFAULT['default_permissions'] = array (3, 3, 2, 2);

/**
 *  Show banners, count clicks & impressions for ad owners and admins?
 *  Show banners on admin pages (under $_CONF['site_admin_url'])?
 *  0 = No
 *  1 = Yes
 */
$_BANR_DEFAULT['adshow_owner'] = 1;
$_BANR_DEFAULT['adshow_admins'] = 1;
$_BANR_DEFUALT['cntclicks_owner'] = 0;
$_BANR_DEFAULT['cntclicks_admins'] = 0;
$_BANR_DEFAULT['cntimpr_owner'] = 0;
$_BANR_DEFAULT['cntimpr_admins'] = 0;
$_BANR_DEFAULT['show_in_admin'] = 0;

/**
*   Centerblock Settings
*/
$_BANR_DEFAULT['cb_enable'] = 0;
$_BANR_DEFAULT['cb_home'] = 1;
$_BANR_DEFAULT['cb_replhome'] = 0;
$_BANR_DEFAULT['cb_pos'] = 1;

/**
 *  Show target pages in a new window, or the current window
 *  0 = current window
 *  1 = new window
 */
$_BANR_DEFAULT['target_blank'] = 1;

/**
*   Maximum image dimensions
*/
$_BANR_DEFAULT['img_max_width'] = 1024;
$_BANR_DEFAULT['img_max_height'] = 1024;

/**
*   Default weight assigned to new banners
*/
$_BANR_DEFAULT['def_weight'] = 5;

/**
*   Users and IP addresses that should not see ads
*/
//$_BANR_DEFAULT['users_dontshow'] = array();
$_BANR_DEFAULT['ipaddr_dontshow'] = array();
$_BANR_DEFAULT['uagent_dontshow'] = array();

/**
*   Limit number of banners shown in a block
*/
$_BANR_DEFAULT['block_limit'] = 0;      // 0 = unlimited

/**
*   Default groups
*/
$_BANR_DEFAULT['defgrpsubmit'] = 1;     // submitter group, default to root


/**
* Initialize Banner plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. Initial values will be taken from $_CONF_BANR if available (e.g. from
* an old config.php), uses $_BANR_DEFAULT otherwise.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_banner($admin_group)
{
    global $_CONF_BANR, $_BANR_DEFAULT;

    if (is_array($_CONF_BANR) && (count($_CONF_BANR) > 1)) {
        $_BANR_DEFAULT = array_merge($_BANR_DEFAULT, $_CONF_BANR);
    }
    if ($admin_group < 1) $admin_group = $_BANR_DEFAULT['defgrpsubmit'];

    $me = $_CONF_BANR['pi_name'];
    $c = config::get_instance();
    if (!$c->group_exists($me)) {

        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, $me);

        $c->add('fs_main', NULL, 'fieldset', 0, 0, NULL, 0, true, $me);
        $c->add('templatevars', $_BANR_DEFAULT['templatevars'], 
                'select', 0, 0, 0, 10, true, $me);
        $c->add('usersubmit', $_BANR_DEFAULT['usersubmit'], 
                'select', 0, 0, 3, 20, true, $me);
        $c->add('submissionqueue', $_BANR_DEFAULT['submissionqueue'], 
                'select', 0, 0, 3, 30, true, $me);
        $c->add('notification', $_BANR_DEFAULT['notification'], 
                'select', 0, 0, 0, 40, true, $me);
        $c->add('delete_banner', $_BANR_DEFAULT['delete_banner'], 
                'select', 0, 0, 3, 50, true, $me);
        $c->add('target_blank', $_BANR_DEFAULT['target_blank'], 
                'select', 0, 0, 3, 60, true, $me);
        $c->add('img_max_width', $_BANR_DEFAULT['img_max_width'], 
                'text', 0, 0, 0, 70, true, $me);
        $c->add('img_max_height', $_BANR_DEFAULT['img_max_height'], 
                'text', 0, 0, 0, 80, true, $me);
        $c->add('def_weight', $_BANR_DEFAULT['def_weight'], 
                'text', 0, 0, 4, 90, true, $me);

        $c->add('fs_adcontrol', NULL, 'fieldset', 0, 1, NULL, 0, true, $me);
        $c->add('show_in_admin', $_BANR_DEFAULT['show_in_admin'], 
                'select', 0, 1, 3, 10, true, $me);
        $c->add('ipaddr_dontshow', $_BANR_DEFAULT['ipaddr_dontshow'], 
                '%text', 0, 1, 0, 20, true, $me);
        $c->add('uagent_dontshow', $_BANR_DEFAULT['uagent_dontshow'], 
                '%text', 0, 1, 0, 25, true, $me);
        $c->add('adshow_owner', $_BANR_DEFAULT['adshow_owner'], 
                'select', 0, 1, 3, 30, true, $me);
        $c->add('adshow_admins', $_BANR_DEFAULT['adshow_admins'], 
                'select', 0, 1, 3, 40, true, $me);
        $c->add('cntclicks_owner', $_BANR_DEFAULT['cntclicks_owner'], 
                'select', 0, 1, 3, 50, true, $me);
        $c->add('cntclicks_admins', $_BANR_DEFAULT['cntclicks_admins'], 
                'select', 0, 1, 3, 60, true, $me);
        $c->add('cntimpr_owner', $_BANR_DEFAULT['cntimpr_owner'], 
                'select', 0, 1, 3, 70, true, $me);
        $c->add('cntimpr_admins', $_BANR_DEFAULT['cntimpr_admins'], 
                'select', 0, 1, 3, 80, true, $me);
        $c->add('cb_enable', $_BANR_DEFAULT['cb_enable'], 
                'select', 0, 1, 3, 90, true, $me);
        $c->add('cb_pos', $_BANR_DEFAULT['cb_pos'],
                'select', 0, 1, 5, 100, true, $me);
        $c->add('cb_home', $_BANR_DEFAULT['cb_home'], 
                'select',0, 1, 3, 110, true, $me);
        $c->add('cb_replhome', $_BANR_DEFAULT['cb_replhome'], 
                'select',0, 1, 3, 120, true, $me);
        $c->add('block_limit', $_BANR_DEFAULT['block_limit'], 
                'text',0, 0, 3, 130, true, $me);

        $c->add('fs_permissions', NULL, 'fieldset', 0, 2, NULL, 0, true, $me);
        $c->add('defgrpsubmit', $admin_group,
                'select', 0, 2, 0, 5, true, $me);
        $c->add('default_permissions', $_BANR_DEFAULT['default_permissions'], 
                '@select', 0, 2, 12, 10, true, $me);
    }

    return true;
}

?>
