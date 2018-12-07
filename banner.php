<?php
/**
 * Table definitions and other static config variables.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v0.3.1
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
$_TABLES['banneraccount']       = $_DB_table_prefix . 'banneraccount';
$_TABLES['bannertrans']         = $_DB_table_prefix . 'bannertrans';

$_CONF_BANR['pi_name']           = 'banner';
$_CONF_BANR['pi_version']        = '0.3.1';
$_CONF_BANR['gl_version']        = '1.7.0';
$_CONF_BANR['pi_url']            = 'http://www.leegarner.com';
$_CONF_BANR['pi_display_name']   = 'Banner Ads';

// Fixed config variables
$_CONF_BANR['img_dir'] = $_CONF['path_html'] . $_CONF_BANR['pi_name'] . '/images/banners/';

?>
