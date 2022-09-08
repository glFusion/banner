<?php
/**
 * Installation Defaults used when loading the online configuration.
 * These settings are only used during the initial installation 
 * and upgrade not referenced any more once the plugin is installed.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2018 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */

/** Block execution if not loaded through glFusion */
if (!defined('GVERSION')) {
    die('This file can not be used on its own!');
}

/** @var global config data */
global $bannerConfigData;
$bannerConfigData = array(
    array(
        'name' => 'sg_main',
        'default_value' => NULL,
        'type' => 'subgroup',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'fs_main',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'banner',
    ),
    /*array(
        'name' => 'usersubmit',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 20,
        'set' => true,
        'group' => 'banner',
    ),*/
    array(
        'name' => 'notification',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 30,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'delete_banner',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 40,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'target_blank',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 50,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'img_max_width',
        'default_value' => '1024',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 60,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'img_max_height',
        'default_value' => '1024',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 70,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'def_weight',
        'default_value' => 5,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 80,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'def_rel_tag',
        'default_value' => 'sponsored nofollow noopener noreferrer',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 90,
        'set' => true,
        'group' => 'banner',
    ),

    array(
        'name' => 'fs_adcontrol',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'templatevars',   // Show banners in custom template vars
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 3,
        'sort' => 10,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'adblockvars',     // Show banners in adblock template vars
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 3,
        'sort' => 20,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'headercode',     // Show banners in HTML header
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 3,
        'sort' => 30,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'uagent_dontshow',    // User Agents to block
        'default_value' => array(),
        'type' => '%text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 40,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'ipaddr_dontshow',    // IP addresses to block
        'default_value' => array(),
        'type' => '%text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 50,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'cb_pos',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 5,
        'sort' => 60,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'cb_home',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 3,
        'sort' => 70,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'cb_replhome',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 3,
        'sort' => 80,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'block_limit',
        'default_value' => 0,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 90,
        'set' => true,
        'group' => 'banner',
    ),

    array(
        'name' => 'fs_campaigndef',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'fset_admindisplay',
        'default_value' => NULL,
        'type' => 'fset',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => NULL,
        'sort' => 10,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'show_in_admin',      // show in admin pages?
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 3,
        'sort' => 20,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'adshow_admins',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 3,
        'sort' => 30,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'cntimpr_admins',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 3,
        'sort' => 40,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'cntclicks_admins',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 3,
        'sort' => 50,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'fset_ownerdisplay',
        'default_value' => 1,
        'type' => 'fset',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 3,
        'sort' => 60,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'adshow_owner',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 3,
        'sort' => 70,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'cntimpr_owner',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 3,
        'sort' => 80,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'cntclicks_owner',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 3,
        'sort' => 90,
        'set' => true,
        'group' => 'banner',
    ),

    array(
        'name' => 'fset_permissions',
        'default_value' => NULL,
        'type' => 'fset',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => NULL,
        'sort' => 100,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'defgrpsubmit',
        'default_value' => 0,   // will be reset in initconfig function
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 0,
        'sort' => 110,
        'set' => true,
        'group' => 'banner',
    ),
    array(
        'name' => 'default_permissions',
        'default_value' => array (3, 3, 2, 2),
        'type' => '@select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 12,
        'sort' => 120,
        'set' => true,
        'group' => 'banner',
    ),
);


/**
 * Initialize Banner plugin configuration.
 *
 * @param   integer $admin_group    Admin Group ID created by installation
 * @return  boolean     True: success; False: an error occurred
 */
function plugin_initconfig_banner($admin_group)
{
    global $bannerConfigData;

    $c = config::get_instance();
    if (!$c->group_exists('banner')) {
        USES_lib_install();
        foreach ($bannerConfigData AS $cfgItem) {
            if ($cfgItem['name'] == 'defgrpsubmit') {
                $cfgItem['default_value'] = $admin_group;
            }
            _addConfigItem($cfgItem);
        }
    } else {
        COM_errorLog('initconfig error: Banner config group already exists');
    }
    return true;
}

?>
