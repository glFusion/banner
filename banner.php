<?php
/**
 * Table definitions and other static config variables.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2022 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
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
$_CONF_BANR['pi_version']        = '1.0.0';
$_CONF_BANR['gl_version']        = '2.0.0';
$_CONF_BANR['pi_url']            = 'https://glfusion.org';
$_CONF_BANR['pi_display_name']   = 'Banner Ads';

Banner\Config::set('pi_version', '1.0.0');
Banner\Config::set('gl_version', '2.0.0');

$_CONF_BANR['xicons'] = array(
    'delete'    => '<i class="uk-icon uk-icon-remove uk-text-danger"></i>',
    'copy'      => '<i class="uk-icon uk-icon-clone"></i>',
    'edit'      => '<i class="uk-icon uk-icon-edit"></i>',
    'arrow-up'  => '<i class="uk-icon uk-icon-arrow-up"></i>',
    'arrow-down'=> '<i class="uk-icon uk-icon-arrow-down"></i>',
    'reset'     => '<i class="uk-icon uk-icon-refresh"></i>',
);

