<?php
/**
 * glFusion API functions for the Banner plugin.
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
use glFusion\Database\Database;
use glFusion\Log\Log;

// Define banner types.  This is done very early because they're used
// in the language files
define('BANR_PI_PATH', $_CONF['path'] . 'plugins/banner');
define('BANR_MAX_DATE', '2037-12-30 23:59:59');
define('BANR_MIN_DATE', '1900-01-01 00:00:00');

$langfile = $_CONF['language'] . '.php';
if (!file_exists(__DIR__ . '/language/' . $langfile)) {
    $langfile = 'english_utf-8.php';
}
/** Import the plugin's language file */
require_once __DIR__ . '/language/' . $langfile;

spl_autoload_register(function ($class)
{
    // project-specific namespace prefix
    $prefix = 'Banner\\';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/classes/';

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.class.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
use Banner\Config;

/** Import static config values */
require_once __DIR__ . '/banner.php';
if (file_exists(__DIR__ . '/config.php')) {
    /** Import local configuration overrides, if any */
    include_once __DIR__ . '/config.php';
}


/**
 * Create the centerblock, if needed.
 *
 * @param   integer $where  Which area is being displayed now
 * @param   integer $page   Page number
 * @param   string  $topic  Topic ID, or empty string
 * @return  string          HTML for centerblock
 */
function plugin_centerblock_banner($where=1, $page=1, $topic ='')
{
    global $_TABLES;

    // Flag whether we're on the homepage or not
    $on_homepage = ($page == 1 && $topic == '') ? true : false;

    // If centerblock not enabled, or just for homepage and
    // we're not on the homepage, just return
    if (
        Config::get('cb_home') == 0 ||
        (Config::get('cb_home') == 1 && !$on_homepage)
    ) {
        return '';
    }

    // Set basic options for banner search
    $options = array('limit' => 1, 'centerblock' => 1);

    // Get the centerblock position.  May be overridden later
    $cntrblkpos = Config::get('cb_pos');

    // If we're not supposed to replace the homepage, then return.
    // Otherwise, do so.
    if ($where == 0 && $topic == '') {
        if (!Config::get('cb_replhome')) {
            return '';
        } else {
            $cntrblkpos = 0;
        }
    }

    // Check if there are no featured articles in this topic
    // and if so then place it at the top of the page
    $db = Database::getInstance();
    if ($topic != "") {
        $names = array('tid', 'featured', 'draft_flag');
        $values = array($topic, 1, 0);
        $types = array(Database::STRING, Database::INTEGER, Database::INTEGER);
        $options['tid'] = $topic;
    } else {
        $names = array('featured', 'draft_flag');
        $values = array(1, 0);
        $types = array(Database::INTEGER, Database::INTEGER);
    }

    $story_count = $db->getCount($_TABLES['stories'], $names, $values, $types);
    if ($story_count == 0 && $cntrblkpos == 2) {
        // If the centerblock comes after the featured story, and there
        // are no stories, put the centerblock at the top.
        $cntrblkpos = 1;
    }

    if ($cntrblkpos != $where) {
        return '';
    }

    $Banners = Banner\Banner::getBanners($options);
    if (!empty($bids)) {
        $B = $Banners[0];
        $B->updateImpressions();

        $T = new Template(BANR_PI_PATH . '/templates');
        $T->set_file('page', 'centerblock.thtml');
        $T->set_var(array(
            'banner'    => $B->BuildBanner(),
            'category'  => $B->getCid(),
            'banner_id' => $B->getBid(),
            'campaign'  => $B->getCampID(),
        ) );
        $T->parse('output','page');

        $banner = $T->finish($T->get_var('output'));
    } else {
        $banner = '';
    }

    if (Config::get('cb_replhome') == 1 && $on_homepage) {
        // Replacing the homepage, and we're currently on the homepage
        echo COM_siteHeader() . $banner . COM_siteFooter(true);
        exit;
    } else {
        // Return only the banner for a normal centerblock
        return $banner;
    }
}


/**
 * Implements the [banner:] [randombanner:] autotag.
 *
 * @param   string  $op         operation to perform
 * @param   string  $content    item (e.g. story text), including the autotag
 * @param   array   $autotag    parameters used in the autotag
 * @param   mixed               tag names (for $op='tagname') or formatted content
 */
function plugin_autotags_banner($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $LANG_BANNER;

    if ($op == 'tagname' ) {
        return array('banner', 'randombanner', 'bannercategory');
    } else if ($op == 'tagusage') {
        $tagUsage = array(
            array('namespace' => 'banner','usage' => 'description')
        );
        return $tagUsage;
    } else if ($op == 'desc' ) {
        switch ($content) {
            case 'banner':
            case 'randombanner':
            case 'bannercategory':
                return $LANG_BANNER['at_dscp_' . $content];
                break;
            default :
                return '';
                break;
       }
    } else if ($op == 'parse') {
        $banner = '';
        $db = Database::getInstance();
        $qb = $db->conn->createQueryBuilder();

        switch( $autotag['tag'] ) {
        case 'banner':
            // Get a specific banner
            $bid = COM_sanitizeID($autotag['parm1'], false);
            $B = new Banner\Banner($bid);
            if (!$B->isNew()) {
                $banner = $B->BuildBanner();
                $B->updateImpressions();
            }
            break;

        case 'randombanner':
            // Get a random banner, optionally from a specific category ID
            $type = isset($autotag['parm1']) ? COM_applyFilter($autotag['parm1']) : '';
            $options = array('limit' => 1);
            if (!empty($type)) {
                $options['type'] = $type;
            }
            $Banners = Banner\Banner::getBanners($options);
            if (!empty($Banners)) {
                $banner = $Banners[0]->updateImpressions()->BuildBanner($title);
            }
            break;

        case 'bannercategory' :
            // Get all banners under a specific categorya
            $cid = COM_applyFilter($autotag['parm1']);
            if (!empty($cid)) {
                $qb->leftJoin('b', $_TABLES['bannercategories'], 'c', 'b.cid = c.cid')
                   ->andWhere('c.cid = :cid')
                   ->andWhere(COM_getPermSQL('', 0, 2, 'c'));
                       /*// if there's a cid, join that table
                $cid_sql = "LEFT JOIN {$_TABLES['bannercategories']} AS c
                    ON b.cid=c.cid
                    WHERE c.cid='{$cid}' " .
                        COM_getPermSQL('AND', 0, 2, 'c') .
                         ' AND ';
            } else {
                $cid_sql = ' WHERE ';*/
            }

            $qb->select('b.bid')
               ->from($_TABLES['banner'], 'b')
               ->where('(b.publishstart IS NULL OR b.publishstart < NOW()')
               ->andWhere('(b.publishend IS NULL OR b.publishend > NOW())')
               ->orderBy('RAND()');
                    /*"SELECT b.bid FROM {$_TABLES['banner']} b
                    $cid_sql
                    (b.publishstart IS NULL OR b.publishstart < NOW())
                    AND (b.publishend IS NULL OR b.publishend > NOW())
                    ORDER BY RAND()");*/
            try {
                $stmt = $qb->execute()->fetchAll(Database::ASSOCIATIVE);
            } catch (\Exception $e) {
                Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
                $data = array();
            }
            if (!empty($data)) {
                $T = new Template(BANR_PI_PATH . '/templates');
                $T->set_file('block', 'bannercategory.thtml');
                $T->set_block('block', 'bannerRow', 'BRow');
                foreach ($data as $A) {
                    $B = new Banner\Banner($A['bid']);
                    $T->set_var(array(
                        'banner'    => $B->BuildBanner(),
                        'category'  => $B->getCid(),
                        'banner_id' => $B->getBid(),
                        'campaign'  => $B->getCampId(),
                    ) );
                    $B->updateImpressions();
                    $T->parse('BRow', 'bannerRow', true);
                }
                $T->parse('output','block');
                $banner = $T->finish($T->get_var('output'));
            } else {
                $banner = '';
            }
            break;
        }
        $content = str_replace($autotag['tagstr'], $banner, $content);
        return $content;
    }
}


/**
 * Called by the plugin Editor to display the current plugin code version.
 * This may be different than the version installed and registered currently.
 * If newer then you may want to run the update
 *
 * @return  string  version number
 */
function plugin_chkVersion_banner()
{
    return Config::get('pi_version');
}


/**
 * New stats plugin API function for proper integration with the site stats.
 *
 * @return  array   Array(item text, item count)
 */
function plugin_statssummary_banner ()
{
    global $LANG_BANNER, $_TABLES;

    $db = Database::getInstance();
    try {
        $data = $db->conn->executeQuery(
            "SELECT COUNT(*) AS count, SUM(hits) AS clicks
            FROM {$_TABLES['banner']}
            WHERE (publishstart IS NULL OR publishstart < NOW())
            AND (publishend IS NULL OR publishend > NOW())" .
            COM_getPermSQL ('AND')
        )->fetch(Database::ASSOCIATIVE);
    } catch (\Exception $e) {
        Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
        $data = NULL;
    }

    if (!empty($data)) {
        $item_count = COM_NumberFormat($data['count'])
        . ' (' . COM_NumberFormat((int)$data['clicks']) . ')';
    } else {
        $item_count = 0;
    }
    return array($LANG_BANNER['pi_name'], $item_count);
}


/**
 * This will put an option for banner in the command and control block on moderation.php.
 *
 * @return  array|boolean   array(title, url, icon), or boolean false when not allowed
 */
function plugin_cclabel_banner()
{
    global $LANG_BANNER;

    if (SEC_hasRights ('banner.edit')) {
        return array ($LANG_BANNER['pi_name'],
                      Config::get('admin_url') . '/index.php',
                      plugin_geticon_banner ());
    }
    return false;
}


/**
 * Returns the administrative option for this plugin.
 *
 * @return  array|boolean   array(title, url, num. banner), or false when not allowed
 */
function plugin_getadminoption_banner()
{
    global $_TABLES, $LANG_BANNER;

    if (SEC_hasRights('banner.edit,banner.delete', 'OR')) {
        $total_banner = Database::getInstance()->getCount($_TABLES['banner']);
        return array(
            $LANG_BANNER['pi_name'],
            Config::get('admin_url') . '/index.php',
            $total_banner
        );
    }
    return false;
}


/**
 * Update banners when the owning user is deleted.
 *
 * @param   integer $uid    User id of deleted user
 */
function plugin_user_delete_banner ($uid)
{
    global $_TABLES;

    $db = Database::getInstance();
    if (Config::get('delete_banner') == 1) {
        // delete the banner
        try {
            $db->conn->delete($_TABLES['banner'], array('owner_id' => (int)$uid));
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
            $data = NULL;
        }
    } else {
        // assign ownership to a user from the Banner Admin group
        $rootgroup = (int)$db->getItem($_TABLES['groups'], 'grp_id', array('grp_name' => 'Root'));
        try {
            $data = $db->conn->executeQuery(
                "SELECT DISTINCT ug_uid
                FROM {$_TABLES['group_assignments']}
                WHERE ug_main_grp_id = ?
                ORDER BY ug_uid
                LIMIT 1",
                array($rootgroup),
                array(Database::INTEGER)
            )->fetch(Database::ASSOCIATIVE);
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
            $data = NULL;
        }
        if ($data !== NULL) {
            $rootuser = $data['ug_uid'];
            if ($rootuser > 0) {
                try {
                    $db->conn->executeUpdate(
                        "UPDATE {$_TABLES['banner']}
                        SET owner_id = ?
                        WHERE owner_id = ?",
                        array($rootuser, $uid),
                        array(Database::INTEGER, Database::INTEGER)
                    );
                } catch (\Exception $e) {
                    Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
                }
            }
        }
    }
}


/**
 * Support merging local accounts into remote accounts.
 *
 * @param   integer $origUID    Original (remote) user ID
 * @param   integer $destUID    Merged (local) user ID
 */
function plugin_user_move_banner($origUID, $destUID)
{
    global $_TABLES;

    $origUID = (int)$origUID;
    $destUID = (int)$destUID;

    // Tables containing a "uid" field
    $tables = array(
        'banner',
        'bannersubmission',
        'banneraccount',
    );
    $db = Database::getInstance();
    foreach ($tables as $table) {
        try {
            $db->conn->executeUpdate(
                "UPDATE {$_TABLES[$table]} SET uid = ? WHERE uid = ?",
                array($destUID, $origUID),
                array(Database::INTEGER, Database::INTEGER)
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
        }
    }
}


/**
 * Update the Banner plugin.
 *
 * @return  boolean     True on success, False on failure
 */
function plugin_upgrade_banner()
{
    require_once __DIR__ . '/upgrade.inc.php';
    return banner_do_upgrade();
}


/**
 * Make changes needed when the plugin is enabled or disabled.
 *
 * Uses the PHP block function name instead of the block name to
 * catch user-created blocks that rely on this plugin.
 *
 * @param   boolean $enabled    True if we're being enabled, False otherwise
 */
function plugin_enablestatechange_banner($enabled)
{
    global $_TABLES, $_PLUGINS;

    $enabled = $enabled == 1 ? 1 : 0;
    $blockfns = array('phpblock_banner_');
    $db = Database::getInstance();

    if ($enabled == 0) {
        foreach ($blockfns as $blockfn) {
            try {
                $db->conn->executeUpdate(
                    "UPDATE {$_TABLES['blocks']}
                    SET is_enabled = 0
                    WHERE phpblockfn like ?",
                    array('%' . $blockfn . '%'),
                    array(Database::STRING)
                );
            } catch (\Exception $e) {
                Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
            }
        }
    }
}


/**
 * Counts the items in the submission queue.
 *
 * @return  integer Number of items
 */
function plugin_submissioncount_banner()
{
    global $_TABLES;

    $retval = 0;
    if (plugin_ismoderator_banner ()) {
        $retval = Database::getInstance()->getCount($_TABLES['bannersubmission']);
    }
    return $retval;
}


/**
 * Checks that the current user has plugin moderator rights.
 *
 * @return  boolean     Returns true if moderator
 */
function plugin_ismoderator_banner()
{
    static $isMod = NULL;

    if ($isMod === NULL) {
        $isMod = plugin_isadmin_banner() || SEC_hasRights('banner.moderate');
    }
    return $isMod;
}


/**
 * Checks that the current user has plugin admin rights.
 * Not a glFusion core function, but similar.
 *
 * @return  boolean     Returns true if administrator
 */
function plugin_isadmin_banner()
{
    static $isAdmin = NULL;

    if ($isAdmin === NULL) {
        $isAdmin = SEC_hasRights('banner.admin');
    }
    return $isAdmin;
}


/**
 * Returns SQL & Language texts to moderation.php.
 *
 * @return  mixed   Plugin object or void if not allowed
 */
function plugin_itemlist_banner()
{
    global $_TABLES, $LANG_BANNER;

    if (plugin_ismoderator_banner()) {
        $plugin = new Plugin();
        $plugin->submissionlabel = $LANG_BANNER['banner_submissions'];
        //$plugin->submissionhelpfile = 'ccbannersubmission.html';
        $plugin->getsubmissionssql =
            "SELECT
                b.bid AS id, b.title, b.owner_id AS uid,
                c.category,
                camp.description
            FROM {$_TABLES['bannersubmission']} AS b
            LEFT JOIN {$_TABLES['bannercategories']} AS c
                ON c.cid = b.cid
            LEFT JOIN {$_TABLES['bannercampaigns']} as camp
                ON b.camp_id = camp.camp_id
            ORDER BY title ASC";
        $plugin->addSubmissionHeading($LANG_BANNER['title']);
        $plugin->addSubmissionHeading($LANG_BANNER['category']);
        $plugin->addSubmissionHeading($LANG_BANNER['campaign']);
        return $plugin;
    }
}


/**
 * Returns list of moderation values.
 * The array returned contains (in order): the row 'id' label, main plugin
 * table, moderation fields (comma seperated), and plugin submission table
 *
 * @return  array   Returns array of useful moderation values
 */
function plugin_moderationvalues_banner()
{
    global $_TABLES;

    return array ('bid',
                  $_TABLES['banner'],
                  'bid, cid, camp_id, ad_type, notes, title,
                    publishstart, publishend, date, enabled,
                    owner_id, options, weight',
                  $_TABLES['bannersubmission']);
}


/**
 * Performs plugin exclusive work for items approved by moderation.
 *
 * While moderation.php handles the actual move from bannersubmission
 * to banner tables, within the function we handle all other approval
 * relate tasks.
 *
 * @param   string  $id     Banner ID
 * @return  string          Any wanted HTML output
 */
function plugin_moderationapprove_banner($id)
{
    // No changes needed since removing the permission matrix in 0.2.0
    return '';
}


/**
 * Performs plugin exclusive work for items deleted by moderation.
 *
 * While moderation.php handles the actual removal from <plugin>submission
 * table, within this function we handle all other deletion
 * related tasks.
 *
 * @param   string  $id     Identifying string
 * @return  string          Any wanted HTML output
 */
function plugin_moderationdelete_banner($id)
{
    $B = new Banner\Banner($id);
    $filename = $B->getOpt('filename');
    if ($filename != NULL) {
        @unlink(Config::get('img_dir') . $filename);
    }
    return '';
}


/**
 * Check submission form values and save if OK. Else show form again.
 *
 * @param   array   $A  The banner record
 * @return  string      Any wanted HTML output
 */
function plugin_savesubmission_banner($A)
{
    global $_CONF, $LANG12;

    $B = new Banner\Banner();
    $retval = '';

    if (!SEC_hasRights('banner.submit')) {
        $B->setTable('bannersubmission');
    }

    if (!$B->Validate($A)) {
        $retval = COM_siteHeader()
                . COM_startBlock($LANG12[22], '',
                      COM_getBlockTemplate ('_msg_block', 'header'))
                . $LANG12[23]
                . COM_endBlock(COM_getBlockTemplate ('_msg_block', 'footer'))
                . plugin_submit_banner($A)
                . COM_siteFooter();
        COM_resetSpeedLimit('submit');
    } else {
        $message = $B->Save($A);
        if ($message != '') {
            $retval = COM_siteHeader()
                . COM_showMessageText($message, '', true, 'error')
                . plugin_submit_banner($A)
                . COM_siteFooter();
        }
    }

    if (!empty($retval)) {
        return $retval;
    } else {
        echo COM_refresh($_CONF['site_url'] . '/index.php?msg=1&plugin=' .
                Config::PI_NAME);
    }
}


/**
 * Shows banner submission form.
 * If this is being shown as the result of a submission form error,
 * $A will contain all the fields to repopulate the form.
 *
 * @deprecated
 * @param   array   $A      Array of form fields.
 * @return  string  HTML for the banner submission form
 */
function Xplugin_submit_banner($A=array())
{
    global $LANG_BANNER, $_USER;
    echo "here";die;

    // Make sure members can submit banners.  Anonymous users can never submit.
    if ( COM_isAnonUser() ||
        (Config::get('usersubmit') == 0 && !SEC_hasRights('banner.submit')) ) {
        COM_accessLog("User {$_USER['username']} tried to illegally access the banner submission screen.");
        return COM_showMessageText($LANG_BANNER['access_denied_msg'], '', true, 'error');
    }

    $B = new Banner\Banner();
    $B->setAdmin(false);
    if (is_array($A)) {
        $B->setVars($A);
    }
    $retval = COM_startBlock($LANG_BANNER['submit_banner'],
                    'submitbanner.html');
    $retval .= $B->Edit('submit');
    $retval .= COM_endBlock();

    return $retval;
}


/**
 * Returns the URL of the plugin's icon.
 *
 * @return  string      URL of the icon
 */
function plugin_geticon_banner()
{
    return  Config::get('url') . '/images/banner.png';
}


/**
 * Set template variables.
 *
 * A plugin should use its name as a prefix for the names of its
 * template variables, e.g. 'banner_xxx' and 'lang_banner_xxx'.
 *
 * Note that there is no way to tell if a template variable is
 * actually defined.  Therefore, the impression count will be incremented
 * for each banner that this function attempts to display.  If the
 * banner_ template vars are not defined in your templates, then set
 * the 'templatevars' config option to false.
 *
 * @param   string  $templatename   Name of template, e.g. 'header'
 * @param   ref     $template       Reference to actual template
 */
function plugin_templateSetVars_banner($templatename, &$template) : void
{
    global $topic;


    // We only operate on the 'footer' templatename since 'header' is
    // called twice (from COM_siteHeader() and COM_siteFooter()).
    // We'll just replace both {banner_header} and {banner_footer} at once.
    if (Config::get('templatevars') > 0) {
        if ($templatename == 'footer') {
            $templatenames = array('header','footer');
        } elseif ($templatename != 'header') {
            // Or, handle any other template names (except header)
            $templatenames = array($templatename);
        }
    } else {
        return;
    }

    if (empty($templatenames)) return;
    $options = array(
        'limit' => '1',
        'topic' => empty($topic) ? 'all' : $topic,
    );

    $B = new Banner\Banner();
    foreach ($templatenames as $type) {
        $options['type'] = $type;
        $Banners = Banner\Banner::getBanners($options);
        if (!empty($Banners)) {
            $banner = $Banners[0]->updateImpressions()->BuildBanner();
            $template->set_var("banner_{$type}", $banner, true, true);
        }
    }
}


/**
 * Get the htmlheader banner template in place for head section banners.
 *
 * @return  array       Array of supported template names
 */
function plugin_supportadblock_banner()
{
    return array(
        'htmlheader',
    );
}


/**
 * Provide a banner for PLG_displayAdBlock().
 *
 * @since   v0.3.0
 * @param   string  $plugin     Calling plugin, e.g. 'story'
 * @param   integer $counter    Item display counter
 * @return  string              Banner image and link code
 */
function plugin_displayadblock_banner($plugin, $counter) : string
{
    global $topic;

    if (!BANR_isMinVersion('0.3.0') || !Config::get('adblockvars')) {
        return '';
    }

    $Cats = Banner\Mapping::showCats($plugin, $counter);
    if (empty($Cats)) return '';

    $options = array(
        'limit' => '1',
        'cid' => $Cats,
    );

    $Banners = Banner\Banner::getBanners($options);
    if (!empty($Banners)) {
        return $Banners[0]->updateImpressions()->buildBanner();
    } else {
        return '';
    }
}


/**
 * Automatic uninstall function for plugins.
 *
 * This code is automatically uninstalling the plugin.
 * It passes an array to the core code function that removes
 * tables, groups, features and php blocks from the tables.
 * Additionally, this code can perform special actions that cannot be
 * foreseen by the core code (interactions with other plugins for example)
 *
 * @return  array     Elements to be removed
 */
function plugin_autouninstall_banner ()
{
    $out = array (
        // give the name of the tables, without $_TABLES
        'tables' => array(
            'banner',
            'bannersubmission',
            'bannercategories',
            'bannercampaigns',
            'banner_mapping',
        ),

        // give the full name of the group, as in the db
        'groups' => array(
            'banner Admin',
        ),

        // give the full name of the feature, as in the db
        'features' => array(
            'banner.admin',
            'banner.edit',
            'banner.moderate',
            'banner.submit',
        ),

        // give the full name of the block, including 'phpblock_', etc
        'php_blocks' => array(
            'phpblock_banner_topic',
            'phpblock_banner_topic_random',
        ),

        // give all vars with their name
        'vars'=> array(),
    );

    // Reset this value to avoid trying to display a banner on the final page.
    $Config::set('templatevars', 0);
    return $out;
}


/**
 * Returns a list of banners that belong to categories associated with
 * the current topic.
 *
 * @param   array   $A  Array of parameters for the block (not used)
 * @param   string  $param  Category name
 * @return  string      HTML for banner display.
 */
function phpblock_banner_topic($A=array(), $param='')
{
    $opts = array(
        'type',
        'limit',
        'topic',
    );
    $extra = array(
        'type' => 'block',
        'limit' => Config::get('block_limit'),
        'topic' => '',
    );
    $param = explode(' ', $param);
    foreach ($opts as $key=>$name) {
        if (isset($param[$key]) && !empty($param[$key])) {
            $extra[$name] = $param[$key];
        }
    }
    return Banner\Banner::renderBlockBanners($extra);
}


/**
 * Returns a random banner that belongs to categories associated with
 * the current topic.
 *
 * @param   array   $A  Array of parameters for the block (not used)
 * @param   string  $param  Block information
 * @return  string      HTML for banner display.
 */
function phpblock_banner_topic_random($A= array(), $param='')
{
    $extra = array('limit' => '1');

    if (!empty($param)) {
        $extra['type'] = $param;
    } else {
        $extra['type'] = 'block';
    }
    return Banner\Banner::renderBlockBanners($extra);
}


/**
 * Get additional Javascript into the site header.
 *
 * @return  array   List of paths to Javascript files
 */
function plugin_getheaderjs_banner()
{
    $path = BANR_PI_PATH . '/js';

    $files = array(
        $path . '/toggleEnabled.js',
    );

    return $files;
}


/**
 * Get additional CSS into the site header.
 *
 * @return  array   List of paths to Javascript files
 */
function plugin_getheadercss_banner()
{
    global $_CONF;

    $styles = array();
    $files = array(
        'style.css',
    );

    $path = Config::get('path') . 'css';
    foreach ($files as $file) {
        $styles[] = $path . '/' . $file;
    }

    $path .= '/custom';
    foreach ($files as $file) {
        if (file_exists($path . '/' . $file)) {
            $styles[] = $path . '/' . $file;
        }
    }
    return $styles;
}


/**
 * Get banners into the htmlheader template.
 *
 * @return  string      Banner code to appear in the <head> section.
 */
function plugin_getheadercode_banner()
{
    $retval = '';
    if (!Config::get('headercode')) {
        // Save cycles by checking if headercode banners are even used.
        return $retval;
    }

    $Cats = Banner\Mapping::showCats('htmlheader');
    if (!empty($Cats)) {
        $options = array(
            'cid' => $Cats,
        );
        $Banners = Banner\Banner::getBanners($options);
        foreach ($Banners as $B) {
            $retval .= $B->BuildBanner() . LB;
        }
    }
    return $retval;
}


/**
 * Get the user menu option for this plugin.
 *
 * @return  array   Array of menu items.
 */
function Xplugin_getuseroption_banner()
{
    global $LANG_BANNER, $_CONF, $_USER, $_TABLES;

    $retval = '';
    $uid = (int)$_USER['uid'];
    if ($uid < 2) return '';

    $banner_cnt = Database::getInstance()->getCount(
        $_TABLES['banner'],
        'owner_id',
        $uid,
        Database::INTEGER
    );
    //if ($banner_cnt > 0) {
        $retval = array(
            $LANG_BANNER['banners'],
             Config::get('url') . '/index.php?banners=x',
            $banner_cnt,
        );
    //}
    return $retval;
}


/**
 * Get the URL to the configuration help file.
 *
 * @param   string  $option     ID of help element to display
 * @param   string  $doclang    Document language
 * @return  string              URL to document
 */
function plugin_getconfigelementhelp_banner($option, $doclang='english')
{
    $url = BANNER_docUrl('config', $doclang);

    if (!empty($option)) {
        $url .= '#desc_' . $option;
    }

    return array($url, 0);
}


/**
 * Gets all the groups in the glFusion groups table to prepare a selection list.
 *
 * @param   string  $grplist    Optional list of groups to include or exclude
 * @param   string  $NOT        Set to "NOT" to exclude $grplist, blank to include it.
 * @return  array       Array of (grp_name=>grp_id)
 */
function plugin_configmanager_select_defgrpsubmit_banner()
{
    global $_TABLES;

    $A = array();
    $db = Database::getInstance();
    try {
        $data = $db->conn->executeQuery(
            "SELECT grp_id, grp_name FROM {$_TABLES['groups']}"
        )->fetchAll(Database::ASSOCIATIVE);
    } catch (\Exception $e) {
        Log::write('system', Log::ERROR, __FUNCTION__ . ': ' . $e->getMessage());
        $data = array();
    }
    foreach ($data as $row) {
        $A[$row['grp_name']] = $row['grp_id'];
    }
    return $A;
}


////////////////////////////////////////////
//
//  Public Plugin-Specific Functions Follow
//
////////////////////////////////////////////

/**
 * Return the url to a documentation file.
 * Looks for the file under the user's language directory, or returns
 * the English version by default.
 * NOTE: Does not check whether the English version exists.
 *
 * @param   string  $file   Filename only, e.g. 'config.thtml'
 * @param   string  $lang   Language string.  Optional.
 * @return  string          Full URL to the file.
 */
function BANNER_docURL($file, $lang='')
{
    global $_CONF;

    if ($lang == '') $lang = $_CONF['language'];
    $doc_url = '';
    if ($file != '') {
        $file .= '.html';
        if (!is_file($_CONF['path_html'] . Config::PI_NAME . "/docs/$lang/$file")) {
            $lang = 'english';
        }
        $doc_url = Config::get('url') . "/docs/$lang/$file";
    } else {
        // just getting the config document directory
        if (!is_dir($doc_path)) {
            $lang = 'english';
        }
        $doc_url = Config::get('url') . "/docs/$lang/";
    }
    return $doc_url;
}


/**
 * Check that the currently installed version matches the code version.
 * This is to disable functions that might not work due to a mismatch.
 * Just check the major.minor.revision values.  Any fourth element is ignored.
 *
 * @param   string  $required   Required version, default to code version
 * @return  boolean     True if current >= installed version, False if not
 */
function BANR_isMinVersion($required = '')
{
    global $_PLUGIN_INFO;

    // If not specified, use the current code version as required.
    if ($required == '') {
        $required = Config::get('pi_version');
    }

    if (isset($_PLUGIN_INFO[Config::PI_NAME])) {
        if (is_array($_PLUGIN_INFO[Config::PI_NAME])) {
            // glFusion > = 1.6.6
            $installed = $_PLUGIN_INFO[Config::PI_NAME]['pi_version'];
        } else {
            $installed = $_PLUGIN_INFO[Config::PI_NAME];
        }
    }
    return COM_checkVersion($installed, $required);
}

