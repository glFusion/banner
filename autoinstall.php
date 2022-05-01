<?php
/**
 * Provides automatic installation of the Banner plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2022 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_dbms;

require_once __DIR__ . '/functions.inc';
require_once __DIR__ . '/sql/'. $_DB_dbms. '_install.php';
use Banner\Config;
use glFusion\Log\Log;
use glFusion\Database\Database;

// Plugin installation options
$INSTALL_plugin['banner'] = array(
    'installer' => array(
        'type'  => 'installer',
        'version' => '1',
        'mode'  => 'install',
    ),

    'plugin' => array(
        'type'      => 'plugin',
        'name'      => Config::PI_NAME,
        'ver'       => Config::get('pi_version'),
        'gl_ver'    => Config::get('gl_version'),
        'url'       => Config::get('pi_url'),
        'display'   => Config::get('pi_display_name'],
    ),

    array(
        'type'  => 'table',
        'table' => $_TABLES['banner'],
        'sql'   => $_SQL['banner'],
    ),

    array(
        'type'  => 'table',
        'table' => $_TABLES['bannercategories'],
        'sql'   => $_SQL['bannercategories'],
    ),

    array(
        'type'  => 'table',
        'table' => $_TABLES['bannersubmission'],
        'sql'   => $_SQL['bannersubmission'],
    ),

    array(
        'type'  => 'table',
        'table' => $_TABLES['bannercampaigns'],
        'sql'   => $_SQL['bannercampaigns'],
    ),

    array(
        'type'  => 'table',
        'table' => $_TABLES['banner_mapping'],
        'sql'   => $_SQL['banner_mapping'],
    ),

    array(
        'type'      => 'group',
        'group'     => 'banner Admin',
        'desc'      => 'Users in this group can administer the Banner plugin',
        'variable'  => 'admin_group_id',
        'admin'     => true,
        'addroot'   => true,
    ),

    array(
        'type'      => 'feature',
        'feature'   => 'banner.admin',
        'desc'      => 'Banner Administrator',
        'variable'  => 'admin_feature_id',
    ),

    array(
        'type'      => 'feature',
        'feature'   => 'banner.edit',
        'desc'      => 'Banner Editor',
        'variable'  => 'edit_feature_id',
    ),

    array(
        'type'      => 'feature',
        'feature'   => 'banner.submit',
        'desc'      => 'Bypass Banner Submission Queue',
        'variable'  => 'submit_feature_id',
    ),

    array(
        'type'      => 'feature',
        'feature'   => 'banner.moderate',
        'desc'      => 'Moderate Banner Submissions',
        'variable'  => 'moderate_feature_id',
    ),

    array(
        'type'      => 'mapping',
        'group'     => 'admin_group_id',
        'feature'   => 'admin_feature_id',
        'log'       => 'Adding Admin feature to the admin group',
    ),

    array(
        'type'      => 'mapping',
        'group'     => 'admin_group_id',
        'feature'   => 'edit_feature_id',
        'log'       => 'Adding Edit feature to the admin group',
    ),

    array(
        'type'      => 'mapping',
        'group'     => 'admin_group_id',
        'feature'   => 'submit_feature_id',
        'log'       => 'Adding Submit feature to the admin group',
    ),

    array(
        'type'      => 'mapping',
        'group'     => 'admin_group_id',
        'feature'   => 'moderate_feature_id',
        'log'       => 'Adding Moderate feature to the admin group',
    ),

    array(
        'type'      => 'block',
        'name'      => 'banner_random',
        'title'     => 'Random Banner',
        'phpblockfn' => 'phpblock_banner_topic_random',
        'block_type' => 'phpblock',
        'is_enabled' => 0,
        'group_id'  => 'admin_group_id',
    ),

    array(
        'type'      => 'block',
        'name'      => 'banner_block',
        'title'     => 'Banners',
        'phpblockfn' => 'phpblock_banner_topic',
        'block_type' => 'phpblock',
        'is_enabled' => 0,
        'group_id'  => 'admin_group_id',
    ),

    array(
        'type'  => 'sql',
        'sql'   => $DEFVALUES['bannercategories'],
    ),

    array(
        'type'  => 'sql',
        'sql'   => $DEFVALUES['bannercampaigns'],
    ),

    array(
        'type'  => 'sql',
        'sql'   => $DEFVALUES['banner_mapping'],
    ),
);


/**
 * Puts the datastructures for this plugin into the glFusion database.
 * Note: Corresponding uninstall routine is in functions.inc.
 *
 * @return  boolean     True if successful False otherwise
 */
function plugin_install_banner()
{
    global $INSTALL_plugin;

    Log::write('system', Log::INFO, "Attempting to install the " . Config::get('pi_display_name') . " plugin", 1);

    $ret = INSTALLER_install($INSTALL_plugin[Config::PI_NAME]);
    if ($ret > 0) {
        return false;
    }
    return true;
}


/**
 * Create image directories post-installation.
 */
function plugin_postinstall_banner()
{
    glFusion\FileSystem::mkDir(Config::get('img_dir'));
}


/**
 * Loads the configuration records for the Online Config Manager.
 *
 * @return  boolean     True = proceed with install, False = an error occured
 */
function plugin_load_configuration_banner()
{
    global $_TABLES;

    require_once __DIR__ . '/install_defaults.php';

    // Get the admin group ID that was saved previously.
    $db = Database::getInstance();
    $group_id = (int)$db->getItem(
        $_TABLES['groups'],
        'grp_id',
        array('grp_name' => Config::PI_NAME . ' Admin')
    );

    return plugin_initconfig_banner($group_id);
}

