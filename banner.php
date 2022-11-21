<?php
/**
 * Table definitions and other static config variables.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2022 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * Global array of table names from glFusion.
 * @global array $_TABLES */
global $_TABLES;

/**
 * Global table name prefix.
 * @global string $_DB_table_prefix */
global $_DB_table_prefix;

$_TABLES['bannercategories']    = $_DB_table_prefix . 'bannercategories';
$_TABLES['banner']              = $_DB_table_prefix . 'banner';
$_TABLES['bannersubmission']    = $_DB_table_prefix . 'bannersubmission';
$_TABLES['bannercampaigns']     = $_DB_table_prefix . 'bannercampaigns';
$_TABLES['banner_mapping']      = $_DB_table_prefix . 'banner_mapping';
// experimental support for purchasing or allocating banners
$_TABLES['banner_account']      = $_DB_table_prefix . 'banner_account';
$_TABLES['banner_txn']          = $_DB_table_prefix . 'banner_txn';

Banner\Config::set('pi_version', '1.0.1.1');
Banner\Config::set('gl_version', '2.0.0');

