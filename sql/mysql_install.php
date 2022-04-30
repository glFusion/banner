<?php
/**
 * Table definitions for the Banner plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2022 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

global $_TABLES;

$_SQL = array();
$_SQL['bannercategories'] = "CREATE TABLE {$_TABLES['bannercategories']} (
  `cid` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `category` varchar(32) NOT NULL,
  `description` text,
  `tid` varchar(20) default 'all',
  `enabled` tinyint(1) unsigned default '1',
  `centerblock` tinyint(1) unsigned default '0',
  `grp_view` mediumint(8) unsigned NOT NULL default '2',
  `max_img_width` int(4) unsigned default 0,
  `max_img_height` int(4) unsigned default 0,
  PRIMARY KEY  (`cid`),
  KEY `type` (`type`)
) ENGINE=MyISAM";

// Common table structure for both banners and submissions
$banner_def =
" `bid` varchar(40) NOT NULL DEFAULT '',
  `cid` varchar(32) DEFAULT NULL,
  `camp_id` varchar(40) DEFAULT NULL,
  `ad_type` int(4) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `title` varchar(96) DEFAULT NULL,
  `impressions` int(11) NOT NULL DEFAULT 0,
  `max_impressions` int(11) NOT NULL DEFAULT 0,
  `hits` int(11) NOT NULL DEFAULT 0,
  `max_hits` int(11) NOT NULL DEFAULT 0,
  `publishstart` datetime DEFAULT '0000-01-01 00:00:00',
  `publishend` datetime DEFAULT '9999-12-31 23:59:59',
  `date` datetime DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `owner_id` mediumint(8) unsigned NOT NULL DEFAULT 1,
  `options` text DEFAULT NULL,
  `weight` int(2) unsigned DEFAULT 5,
  `tid` varchar(20) DEFAULT 'all',
  `html_status` varchar(127) NOT NULL DEFAULT '',
  `dt_validated` datetime DEFAULT NULL,
  PRIMARY KEY (`bid`),
  KEY `banner_category` (`cid`),
  KEY `banner_date` (`date`)";

$_SQL['banner'] = "CREATE TABLE {$_TABLES['banner']} (
  $banner_def,
  PRIMARY KEY  (`bid`),
  KEY `banner_category` (`cid`),
  KEY `banner_date` (`date`)
) ENGINE=MyISAM";

$_SQL['bannersubmission'] = "CREATE TABLE {$_TABLES['bannersubmission']} (
  $banner_def,
  PRIMARY KEY  (`bid`)
) ENGINE=MyISAM";

$_SQL['bannercampaigns'] = "CREATE TABLE {$_TABLES['bannercampaigns']} (
  `camp_id` varchar(40) NOT NULL,
  `description` text,
  `start` datetime default NULL,
  `finish` datetime default NULL,
  `enabled` tinyint(1) UNSIGNED NOT NULL default '1',
  `hits` int(11) default '0',
  `max_hits` int(11) NOT NULL default '0',
  `impressions` int(11) NOT NULL default '0',
  `max_impressions` int(11) NOT NULL default '0',
  `owner_id` mediumint(11) unsigned NOT NULL default '2',
  `group_id` mediumint(11) unsigned NOT NULL default '1',
  `perm_owner` tinyint(1) unsigned NOT NULL default '3',
  `perm_group` tinyint(1) unsigned NOT NULL default '3',
  `perm_members` tinyint(1) unsigned NOT NULL default '2',
  `perm_anon` tinyint(1) unsigned NOT NULL default '2',
  `tid` varchar(20) default 'all',
  `show_owner` tinyint(1) unsigned not null default 0,
  `show_admins` tinyint(1) unsigned not null default 0,
  PRIMARY KEY  (`camp_id`)
) ENGINE=MyISAM";

// template-category mapping introduced in 0.3.0
$_SQL['banner_mapping'] = "CREATE TABLE `{$_TABLES['banner_mapping']}` (
  `tpl` varchar(50) NOT NULL,
  `cid` varchar(25) NOT NULL DEFAULT '',
  `pos` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `once` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `in_content` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tpl`,`cid`)
) ENGINE=MyISAM";

$DEFVALUES['bannercategories'] = "INSERT INTO `{$_TABLES['bannercategories']}`
        (cid, type, category, description, max_img_width, max_img_height)
    VALUES
        ('header','header','Header','Header Banners',468,60),
        ('footer','footer','Footer','Footer Banners',468,60),
        ('block','block','Block','Block Banners',140,400),
        ('htmlheader','htmlheader','HTMLHeader','HEAD Section Banners',140,400)";

$DEFVALUES['bannercampaigns'] = "INSERT INTO `{$_TABLES['bannercampaigns']}`
        (camp_id, description)
    VALUES
        ('default', 'Default System Campaign')";
$DEFVALUES['banner_mapping'] = "INSERT INTO {$_TABLES['banner_mapping']}
        (tpl, cid, once)
    VALUES
        ('header', 'header', 1),
        ('footer', 'header', 1),
        ('banner_htmlheader', 'htmlheader', 1)";

$BANR_UPGRADE = array(
'0.1.0' => array(
    "ALTER TABLE {$_TABLES['bannercategories']}
        ADD `max_img_width` int(4) unsigned default 0,
        ADD `max_img_height` int(4) unsigned default 0",
    "ALTER TABLE {$_TABLES['banner']}
        ADD `max_impressions` int(11) NOT NULL default '0'
            AFTER `impressions`",
    ),
'0.1.1' => array(
    "ALTER TABLE {$_TABLES['banner']}
        ADD `tid` varchar(20) default 'all'",
    "ALTER TABLE {$_TABLES['bannercampaigns']}
        ADD `tid` varchar(20) default 'all'",
    ),
'0.2.0' => array(
    "ALTER TABLE {$_TABLES['banner']}
        DROP perm_owner,
        DROP perm_group,
        DROP perm_members,
        DROP perm_anon,
        DROP group_id",
    "ALTER TABLE {$_TABLES['bannersubmission']}
        DROP perm_owner,
        DROP perm_group,
        DROP perm_members,
        DROP perm_anon,
        DROP group_id",
    "UPDATE {$_TABLES['bannercategories']} SET group_id = 2",
    "ALTER TABLE {$_TABLES['bannercategories']}
        DROP perm_owner,
        DROP perm_group,
        DROP perm_members,
        DROP perm_anon,
        CHANGE group_id grp_view mediumint(8) unsigned default 2",
    "ALTER TABLE {$_TABLES['bannercampaigns']}
        DROP usercanadd",
    ),
'0.2.1' => array(
    "ALTER TABLE {$_TABLES['banner']}
        CHANGE `publishstart` `publishstart` datetime default '0000-01-01 00:00:00',
        CHANGE `publishend` `publishend` datetime default '9999-12-31 23:59:59'",
    "ALTER TABLE {$_TABLES['bannersubmission']}
        CHANGE `publishstart` `publishstart` datetime default '0000-01-01 00:00:00',
        CHANGE `publishend` `publishend` datetime default '9999-12-31 23:59:59'",
    "UPDATE {$_TABLES['banner']} SET
        publishstart = '" . BANR_MIN_DATE . "' WHERE publishstart IS NULL",
    "UPDATE {$_TABLES['banner']} SET
        publishend = '" . BANR_MAX_DATE . "' WHERE publishend IS NULL",
    "UPDATE {$_TABLES['bannersubmission']} SET
        publishstart = '" . BANR_MIN_DATE . "' WHERE publishstart IS NULL",
    "UPDATE {$_TABLES['bannersubmission']} SET
        publishend = '" . BANR_MAX_DATE . "' WHERE publishend IS NULL",
    ),
'0.3.0' => array(
    "CREATE TABLE `{$_TABLES['banner_mapping']}` (
      `tpl` varchar(50) NOT NULL,
      `cid` varchar(25) NOT NULL DEFAULT '',
      `pos` tinyint(3) unsigned NOT NULL DEFAULT '0',
      `once` tinyint(1) unsigned NOT NULL DEFAULT '0',
      `in_content` tinyint(1) unsigned NOT NULL DEFAULT '0',
      PRIMARY KEY (`tpl`,`cid`)
    ) ENGINE=MyISAM",
    "UPDATE {$_TABLES['bannercampaigns']}
        SET max_hits = 0 WHERE max_hits IS NULL",
    "UPDATE {$_TABLES['banner']}
        SET publishstart = '0000-01-01 00:00:00'
            WHERE publishstart IS NULL",
    "UPDATE {$_TABLES['banner']}
        SET publishend = '9999-12-31 23:59:59'
            WHERE publishend IS NULL",
    "UPDATE {$_TABLES['bannersubmission']}
        SET publishstart = '0000-01-01 00:00:00'
            WHERE publishstart IS NULL",
    "UPDATE {$_TABLES['bannersubmission']}
        SET publishend = '9999-12-31 23:59:59'
            WHERE publishend IS NULL",
    ),
'0.3.2' => array(
    "ALTER TABLE {$_TABLES['bannercampaigns']} DROP max_banners",
    ),
'1.0.0' => array(
    "INSERT INTO `{$_TABLES['bannercategories']}`
        (cid, type, category, description, max_img_width, max_img_height)
        VALUES ('htmlheader','htmlheader','HTMLHeader','HEAD Section Banners',140,400)",
    "INSERT INTO {$_TABLES['banner_mapping']} (tpl, cid, once)
        VALUES ('banner_htmlheader', 'htmlheader', 0)",
    "ALTER TABLE {$_TABLES['bannercampaigns']} ADD show_owner tinyint(1) unsigned not null default 0",
    "ALTER TABLE {$_TABLES['bannercampaigns']} ADD show_admins tinyint(1) unsigned not null default 0",
    "ALTER TABLE {$_TABLES['banner']} ADD `html_status` varchar(127) NOT NULL DEFAULT '' AFTER `tid`",
    "ALTER TABLE {$_TABLES['banner']} ADD `dt_validated` datetime DEFAULT NULL AFTER `html_status`",
    "ALTER TABLE {$_TABLES['bannersubmission']} ADD `html_status` varchar(127) NOT NULL DEFAULT '' AFTER `tid`",
    "ALTER TABLE {$_TABLES['bannersubmission']} ADD `dt_validated` datetime DEFAULT NULL AFTER `html_status`",

    ),
);

