<?php
/**
*   Table definitions for the Banner plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.3.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
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
" `bid` varchar(40) NOT NULL default '',
  `cid` varchar(32) default NULL,
  `camp_id` varchar(40) default NULL,
  `ad_type` int(4) default '0',
  `notes` text,
  `title` varchar(96) default NULL,
  `impressions` int(11) NOT NULL default '0',
  `max_impressions` int(11) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  `max_hits` int(11) NOT NULL default '0',
  `publishstart` datetime default '0000-01-01 00:00:00',
  `publishend` datetime default '9999-12-31 23:59:59',
  `date` datetime default NULL,
  `enabled` tinyint(1) default '1',
  `owner_id` mediumint(8) unsigned NOT NULL default '1',
  `options` text,
  `weight` int(2) unsigned default '5',
  `tid` varchar(20) default 'all'";

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
        ('20090010100000000','header','Header','Header Banners',468,60),
        ('20090010100000001','footer','Footer','Footer Banners',468,60),
        ('20090010100000002','block','Block','Block Banners',140,400)";

$DEFVALUES['bannercampaigns'] = "INSERT INTO `{$_TABLES['bannercampaigns']}`
        (camp_id, description)
    VALUES
        ('20090010100000000', 'Default System Campaign')";
$DEFVALUES['banner_mapping'] = "INSERT INTO {$_TABLES['banner_mapping']}
        (tpl, cid, once)
    VALUES
        ('header', '20090010100000000', 1),
        ('footer', '20090010100000001', 1)";

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
);

?>
