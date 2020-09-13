<?php
/**
 * Upgrade routines for the Banner plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v0.3.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

global $_DB_dbms;
require_once __DIR__ . "/sql/{$_DB_dbms}_install.php";


/**
 * Perform the upgrade starting at the current version.
 *
 * @param   boolean $dvlp   True to ignore erorrs and continue
 * @return  boolean     True on success, False on failure
 */
function banner_do_upgrade($dvlp=false)
{
    global $_TABLES, $_CONF_BANR, $_PLUGIN_INFO, $BANR_UPGRADE;

    $pi_name = $_CONF_BANR['pi_name'];

    if (isset($_PLUGIN_INFO[$_CONF_BANR['pi_name']])) {
        $code_ver = plugin_chkVersion_banner();
        if (is_array($_PLUGIN_INFO[$_CONF_BANR['pi_name']])) {
            // glFusion 1.6.6+
            $current_ver = $_PLUGIN_INFO[$_CONF_BANR['pi_name']]['pi_version'];
        } else {
            $current_ver = $_PLUGIN_INFO[$_CONF_BANR['pi_name']];
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
        DB_query("ALTER TABLE {$_TABLES['bannersubmission']}
            ADD `max_impressions` int(11) NOT NULL default '0'
            AFTER `impressions`", 1);

        // 'tid' was added in 0.1.1, but not to the submission table
        DB_query("ALTER TABLE {$_TABLES['bannersubmission']}
            ADD `tid` varchar(20) default 'all'
            AFTER `weight`", 1);

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
            $val = DB_getItem($_TABLES['bannercategories'], 'cid', "cid='$cid'");
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
    global $_TABLES, $_CONF_BANR;

    // now update the current version number.
    DB_query("UPDATE {$_TABLES['plugins']} SET
            pi_version = '{$version}',
            pi_gl_version = '{$_CONF_BANR['gl_version']}',
            pi_homepage = '{$_CONF_BANR['pi_url']}'
        WHERE pi_name = 'banner'");

    if (DB_error()) {
        COM_errorLog("Error updating the banner Plugin version to $version",1);
        return false;
    } else {
        COM_errorLog("Succesfully updated the banner Plugin version to $version!",1);
        return true;
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
    global $_TABLES, $_CONF_BANR, $BANR_UPGRADE;

    // If no sql statements needed, return success
    if (
        !isset($BANR_UPGRADE[$version]) ||
        !is_array($BANR_UPGRADE[$version])
    ) {
        return true;
    }

    // Execute SQL now to perform the upgrade
    COM_errorLOG("--Updating Banner to version $version");
    foreach($BANR_UPGRADE[$version] as $sql) {
        COM_errorLOG("Banner Plugin $version update: Executing SQL => $sql");
        DB_query($sql, '1');
        if (DB_error()) {
            COM_errorLog("SQL Error during Banner Plugin update",1);
            if (!$dvlp) return false;
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
                BANNER_auditLog("removing $path/$file");
                @unlink("$path/$file");
            }
        }
    }
}

?>
