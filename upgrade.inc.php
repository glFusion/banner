<?php
//  $Id: upgrade.inc.php 75 2010-05-18 18:03:56Z root $
/**
*   Upgrade routines for the Banner plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*   GNU Public License v2 or later
*   @filesource
*/


/**
*   Perform the upgrade starting at the current version.
*
*   @param  string  $current_ver    Current installed version to be upgraded
*   @return integer                 Error code, 0 for success
*/
function banner_do_upgrade($current_ver)
{
    global $_TABLES;

    $error = 0;

    if ($current_ver < '0.1.0') {
        // upgrade from 0.0.x to 0.1.0
        $error = banner_upgrade_0_1_0();
        if ($error)
            return $error;
    }

    if ($current_ver < '0.1.1') {
        // upgrade from 0.1.0 to 0.1.1
        $error = banner_do_upgrade_sql('0.1.1');
        if ($error)
            return $error;
    }

    if ($current_ver < '0.1.4') {
        // upgrade to 0.1.1
        // this adds a field that was missing in the installation of 0.1.0
        // but was added in the upgrade to 0.1.0.
        // an error is to be expected and ignored.
       DB_query("ALTER TABLE {$_TABLES['bannersubmission']}
            ADD `max_impressions` int(11) NOT NULL default '0'
            AFTER `impressions`", 1);

        // 'tid' was added in 0.1.1, but not to the submission table
        DB_query("ALTER TABLE {$_TABLES['bannersubmission']}
            ADD `tid` varchar(20) default 'all'
            AFTER `weight`", 1);

        if ($error)
            return $error;
    }

    return $error;

}


/**
*   Actually perform any sql updates
*   @param string $version  Version being upgraded TO
*   @param array  $sql      Array of SQL statement(s) to execute
*/
function banner_do_upgrade_sql($version)
{
    global $_TABLES, $_CONF_BANR, $_DB_dbms;

    /** Include the table creation strings */
    require_once BANR_PI_PATH . "/sql/{$_DB_dbms}_install.php";

    // If no sql statements passed in, return success
    if (!is_array($UPGRADE[$version]))
        return 0;

    // Execute SQL now to perform the upgrade
    COM_errorLOG("--Updating Banner to version $version");
    foreach($UPGRADE[$version] as $sql) {
        COM_errorLOG("Banner Plugin $version update: Executing SQL => $sql");
        DB_query($sql, '1');
        if (DB_error()) {
            COM_errorLog("SQL Error during Banner Plugin update",1);
            return 1;
            break;
        }
    }

    return 0;

}


/**
*   Upgrade to version 0.1.0.
*   Adds configuration item for centerblock replacing home page.
*/
function banner_upgrade_0_1_0()
{
    global $_CONF_BANR, $BANR_DEFAULT;

    USES_banner_install_defaults();

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_BANR['pi_name'])) {
        $c->add('cb_replhome', $_BANR_DEFAULT['cb_replhome'], 
                'select',0, 1, 3, 120, true, $_CONF_BANR['pi_name']);
        $c->add('block_limit', $_BANR_DEFAULT['block_limit'], 
                'text',0, 0, 3, 130, true, $me);
    }

    return banner_do_upgrade_sql('0.1.0');

}


?>
