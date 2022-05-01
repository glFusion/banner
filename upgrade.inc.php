<?php
/**
 * Upgrade routines for the Banner plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2022 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

global $_DB_dbms;
require_once __DIR__ . "/sql/{$_DB_dbms}_install.php";
use glFusion\Database\Database;
use glFusion\Log\Log;
use Banner\Config;


/**
 * Perform the upgrade starting at the current version.
 *
 * @param   boolean $dvlp   True to ignore erorrs and continue
 * @return  boolean     True on success, False on failure
 */
function banner_do_upgrade($dvlp=false)
{
    global $_TABLES, $_PLUGIN_INFO, $BANR_UPGRADE;

    $db = Database::getInstance();

    if (isset($_PLUGIN_INFO[Config::PI_NAME])) {
        $code_ver = plugin_chkVersion_banner();
        if (is_array($_PLUGIN_INFO[Config::PI_NAME])) {
            // glFusion 1.6.6+
            $current_ver = $_PLUGIN_INFO[Config::PI_NAME]['pi_version'];
        } else {
            $current_ver = $_PLUGIN_INFO[Config::PI_NAME];
        }
        if (COM_checkVersion($current_ver, $code_ver)) {
            // Already updated to the code version, nothing to do
            return true;
        }
    } else {
        // Error determining the installed version
        return false;
    }
    $installed_ver = plugin_chkVersion_banner();

    if (!COM_checkVersion($current_ver, '0.1.0')) {
        $current_ver = '0.1.0';
        // upgrade from 0.0.x to 0.1.0
        if (!banner_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!banner_do_update_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.1.1')) {
        // upgrade from 0.1.0 to 0.1.1
        $current_ver = '0.1.1';
        if (!banner_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!banner_do_update_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.1.4')) {
        // upgrade to 0.1.4
        // this adds a field that was missing in the installation of 0.1.0
        // but was added in the upgrade to 0.1.0.
        // an error is to be expected and ignored.
        $current_ver = '0.1.4';
        try {
            $db->conn->executeQuery(
                "ALTER TABLE {$_TABLES['bannersubmission']}
                ADD `max_impressions` int(11) NOT NULL default '0'
                AFTER `impressions`"
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
        }

        // 'tid' was added in 0.1.1, but not to the submission table
        try {
            $db->conn->executeQuery(
                "ALTER TABLE {$_TABLES['bannersubmission']}
                ADD `tid` varchar(20) default 'all'
                AFTER `weight`"
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
        }

        if (!banner_do_update_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.1.7')) {
        $current_ver = '0.1.7';
        if (!banner_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!banner_do_update_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.2.0')) {
        $current_ver = '0.2.0';
        if (!banner_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!banner_do_update_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.2.1')) {
        $current_ver = '0.2.1';
        if (!banner_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!banner_do_update_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.3.0')) {
        $current_ver = '0.3.0';
        // Add mappings if the default categories weren't deleted by an admin.
        // Included in v0.3.2, only effective if upgrading from < v0.3.0
        foreach (
            array(
                'header' => '20090010100000000',
                'footer' => '20090010100000001',
            ) as $tpl=>$cid
        ) {
            $val = $db->getItem($_TABLES['bannercategories'], 'cid', array('cid' => $cid));
            if ($val == $cid) {
                $BANR_UPGRADE[$current_ver][] = "INSERT INTO {$_TABLES['banner_mapping']}
                    (tpl, cid) VALUES ('$tpl', '$cid')";
            }
        }
        if (!banner_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!banner_do_update_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.3.2')) {
        $current_ver = '0.3.2';
        if (!banner_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!banner_do_update_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.0.0')) {
        $current_ver = '1.0.0';
        if (!is_dir(Config::get('img_dir'))) {
            // Create the new private image path if it doesn't exist, and copy
            // the existing banner images from pubic_html. Leave the original
            // images alone, they won't bother anything.
            $Fs = new \glFusion\FileSystem;
            $Fs->dirCopy(Config::get('public_dir'), $Config::get('img_dir'));
            // Delete the "thumbs" directory that got copied with the images.
            \glFusion\FileSystem::deleteDir(Config::get('img_dir') . 'thumbs');
        }

        if (!BANR_tableHasColumn('bannercampaigns', 'show_admins')) {
            // Set the show_admins and show_owner campaign fields from the config,
            // if not already done.
            if (Config::get('adshow_admins') != 0) {
                $BANR_UPGRADE[$current_ver][] = "UPDATE {$_TABLES['bannercampaigns']} SET show_admins = 1";
            }
            if (Config::get('adshow_owner') != 0) {
                $BANR_UPGRADE[$current_ver][] = "UPDATE {$_TABLES['bannercampaigns']} SET show_owner = 1";
            }
            if (Config::get('show_in_admin') != 0) {
                $BANR_UPGRADE[$current_ver][] = "UPDATE {$_TABLES['bannercampaigns']} SET show_adm_pages = 1";
            }
        }

        // Create the htmlheader mapping, if the admin hasn't removed that template
        $cid = 'HTMLHeader';
        $val = $db->getItem($_TABLES['bannercategories'], 'cid', array('cid' => $cid));
        if ($val == $cid) {
            $BANR_UPGRADE[$current_ver][] = "INSERT INTO {$_TABLES['banner_mapping']}
                (tpl, cid) VALUES ('htmlheader', '$cid')";
        }
        if (!banner_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!banner_do_update_version($current_ver)) return false;
    }

    // Update with any configuration changes
    USES_lib_install();
    global $bannerConfigData;
    require_once __DIR__ . '/install_defaults.php';
    _update_config('banner', $bannerConfigData);

    // Remove deprecated files from old versions
    BANR_remove_old_files();

    // Final extra check to catch code-only patch versions
    if (!COM_checkVersion($current_ver, $installed_ver)) {
        if (!banner_do_update_version($installed_ver)) return false;
    }
    return true;
}


/**
 * Update the plugin version at each step to keep the version up to date.
 *
 * @param   string  $version    Version to set
 * @return  boolean     True on success, False on failure
 */
function banner_do_update_version($version)
{
    global $_TABLES;

    $db = Database::getInstance();
    try {
        $db->conn->executeUpdate(
            "UPDATE {$_TABLES['plugins']} SET
            pi_version = ?,
            pi_gl_version = ?,
            pi_homepage = ?
            WHERE pi_name = 'banner'",
            array(Config::get('pi_version'), Config::get('gl_version'), Config::get('pi_url')),
            array(Database::STRING, Database::STRING, Database::STRING)
        );
        return true;
    } catch (\Exception $e) {
        Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
        return false;
    }
}


/**
 * Actually perform any sql updates.
 *
 * @param   string  $version    Version being upgraded TO
 * @param   boolean $dvlp       True to ignore errors during dvlpupdate
 * @return  boolean     True on success, False on failure
 */
function banner_do_upgrade_sql($version, $dvlp=false)
{
    global $_TABLES, $BANR_UPGRADE;

    // If no sql statements needed, return success
    if (
        !isset($BANR_UPGRADE[$version]) ||
        !is_array($BANR_UPGRADE[$version])
    ) {
        return true;
    }

    $db = Database::getInstance();
    // Execute SQL now to perform the upgrade
    Log::write('system', Log::INFO, "--Updating Banner to version $version");
    foreach($BANR_UPGRADE[$version] as $sql) {
        Log::write('system', Log::INFO, "Banner Plugin $version update: Executing SQL => $sql");
        try {
            $db->conn->executeUpdate($sql);
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
            //if (!$dvlp) return false;
        }
    }
    return true;
}


/**
 * Remove deprecated files.
 * Errors in unlink() are ignored.
 */
function BANR_remove_old_files()
{
    global $_CONF;

    $paths = array(
        // private/plugins/banner
        __DIR__ => array(
            'templates/bannerform.uikit.thtml',
            // 0.3.2
            'language/english.php',
            'language/german.php',
            'language/german_formal.php',
            // 1.0.0
            'banner_functions.php',
            'classes/Image.class.php',
            'classes/image.class.php',
            'classes/bannerlist.class.php',
        ),
        // public_html/banner
        $_CONF['path_html'] . 'banner' => array(
            'docs/english/bannerform.legacy.html',
            'docs/english/campaignform.legacy.html',
            'docs/english/categoryform.legacy.html',
            'docs/english/config.legacy.html',
        ),
        // admin/plugins/banner
        $_CONF['path_html'] . 'admin/plugins/banner' => array(
        ),
    );

    // Files that were renamed, changing case only.
    // Only delete thes on non-case-sensitive systems.
    if (php_uname('s') == "Linux") {
        $files = array(
            'classes/banner.class.php',
            'classes/campaign.class.php',
            'classes/category.class.php',
        );
        $paths[__DIR__] = array_merge($paths[__DIR__], $files);
    }

    foreach ($paths as $path=>$files) {
        foreach ($files as $file) {
            if (is_file("$path/$file")) {
                Log::write('system', Log::INFO, "removing $path/$file");
                @unlink("$path/$file");
            }
        }
    }
}


/**
 * Check if a column exists in a table
 *
 * @param   string  $table      Table Key, defined in shop.php
 * @param   string  $col_name   Column name to check
 * @return  boolean     True if the column exists, False if not
 */
function BANR_tableHasColumn(string $table, string $col_name) : bool
{
    global $_TABLES;

    $db = Database::getInstance();
    try {
        $count = $db->conn->executeQuery(
            "SHOW COLUMNS FROM {$_TABLES[$table]} LIKE ?",
            array($col_name),
            array(Database::STRING)
        )->rowCount();
    } catch (\Exception $e) {
        Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
        $count = 0;
    }
    return $count > 0;
}

