<?php
/**
*   Provides automatic installation of the Banner plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2011 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_dbms;

require_once $_CONF['path'].'plugins/banner/functions.inc';
require_once $_CONF['path'].'plugins/banner/sql/'. $_DB_dbms. '_install.php';

// Plugin installation options
$INSTALL_plugin['banner'] = array(
    'installer' => array('type' => 'installer', 
            'version' => '1', 
            'mode' => 'install',
    ),

    'plugin' => array('type' => 'plugin', 
            'name'      => $_CONF_BANR['pi_name'],
            'ver'       => $_CONF_BANR['pi_version'], 
            'gl_ver'    => $_CONF_BANR['gl_version'],
            'url'       => $_CONF_BANR['pi_url'], 
            'display'   => $_CONF_BANR['pi_display_name'],
    ),

    array(  'type' => 'table', 
            'table'     => $_TABLES['banner'], 
            'sql'       => $_SQL['banner'],
    ),

    array(  'type' => 'table', 
            'table'     => $_TABLES['bannercategories'], 
            'sql'       => $_SQL['bannercategories'],
    ),

    array(  'type' => 'table', 
            'table'     => $_TABLES['bannersubmission'], 
            'sql'       => $_SQL['bannersubmission'],
    ),

    array(  'type' => 'table', 
            'table'     => $_TABLES['bannercampaigns'], 
            'sql'       => $_SQL['bannercampaigns'],
    ),

    array(  'type' => 'group', 
            'group' => 'banner Admin', 
            'desc' => 'Users in this group can administer the Banner plugin',
            'variable' => 'admin_group_id', 
            'admin' => true,
            'addroot' => true,
    ),

    array(  'type' => 'feature', 
            'feature' => 'banner.admin', 
            'desc' => 'Banner Administrator',
            'variable' => 'admin_feature_id',
    ),

    array(  'type' => 'feature', 
            'feature' => 'banner.edit', 
            'desc' => 'Banner Editor',
            'variable' => 'edit_feature_id',
    ),

    array(  'type' => 'feature', 
            'feature' => 'banner.submit', 
            'desc' => 'Bypass Banner Submission Queue',
            'variable' => 'submit_feature_id',
    ),

    array(  'type' => 'feature', 
            'feature' => 'banner.moderate', 
            'desc' => 'Moderate Banner Submissions',
            'variable' => 'moderate_feature_id',
    ),

    array(  'type' => 'mapping', 
            'group' => 'admin_group_id', 
            'feature' => 'admin_feature_id',
            'log' => 'Adding Admin feature to the admin group',
    ),

    array(  'type' => 'mapping', 
            'group' => 'admin_group_id', 
            'feature' => 'edit_feature_id',
            'log' => 'Adding Edit feature to the admin group',
    ),

    array(  'type' => 'mapping', 
            'group' => 'admin_group_id', 
            'feature' => 'submit_feature_id',
            'log' => 'Adding Submit feature to the admin group',
    ),

    array(  'type' => 'mapping', 
            'group' => 'admin_group_id', 
            'feature' => 'moderate_feature_id',
            'log' => 'Adding Moderate feature to the admin group',
    ),

    array(  'type' => 'block', 
            'name' => 'banner_random', 
            'title' => 'Random Banner',
            'phpblockfn' => 'phpblock_banner_topic_random',
            'block_type' => 'phpblock',
            'is_enabled' => 0,
            'group_id' => 'admin_group_id',
    ),

    array(  'type' => 'block', 
            'name' => 'banner_block', 
            'title' => 'Banners',
            'phpblockfn' => 'phpblock_banner_topic',
            'block_type' => 'phpblock',
            'is_enabled' => 0,
            'group_id' => 'admin_group_id',
    ),

    array(  'type' => 'sql',
            'sql' => $DEFVALUES['bannercategories'],
    ),

    array(  'type' => 'sql',
            'sql' => $DEFVALUES['bannercampaigns'],
    ),
);


/**
*   Puts the datastructures for this plugin into the glFusion database
*   Note: Corresponding uninstall routine is in functions.inc
*
*   @return boolean     True if successful False otherwise
*/
function plugin_install_banner()
{
    global $INSTALL_plugin, $_CONF_BANR;

    $pi_name            = $_CONF_BANR['pi_name'];
    $pi_display_name    = $_CONF_BANR['pi_display_name'];
    $pi_version         = $_CONF_BANR['pi_version'];

    COM_errorLog("Attempting to install the $pi_display_name plugin", 1);

    $ret = INSTALLER_install($INSTALL_plugin[$pi_name]);
    if ($ret > 0) {
        return false;
    }

    return true;
}


/**
*   Loads the configuration records for the Online Config Manager
*
*   @return boolean     True = proceed with install, False = an error occured
*/
function plugin_load_configuration_banner()
{
    global $_CONF, $_CONF_BANR, $_TABLES;

    require_once $_CONF['path'].'plugins/'.$_CONF_BANR['pi_name'].'/install_defaults.php';

    // Get the admin group ID that was saved previously.
    $group_id = (int)DB_getItem($_TABLES['groups'], 'grp_id', 
            "grp_name='{$_CONF_BANR['pi_name']} Admin'");

    return plugin_initconfig_banner($group_id);
}

?>
