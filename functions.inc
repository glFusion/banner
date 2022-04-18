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

/** Global configuration array, in case this is included within a function
 *   @global array */
global $_CONF_BANR;

// Define banner types.  This is done very early because they're used
// in the language files
define('BANR_PI_PATH', $_CONF['path'] . 'plugins/banner');
define('BANR_MAX_DATE', '9999-12-31 23:59:59');
define('BANR_MIN_DATE', '0000-01-01 00:00:00');

$langfile = $_CONF['language'] . '.php';
if (!file_exists(__DIR__ . '/language/' . $langfile)) {
    $langfile = 'english_utf-8.php';
}
/** Import the plugin's language file */
require_once __DIR__ . '/language/' . $langfile;

require_once $_CONF['path_system'] . 'classes/config.class.php';
$ban_config = config::get_instance();
$_CONF_BANR = $ban_config->get_config('banner');

/** Import static config values */
require_once __DIR__ . '/banner.php';
if (file_exists(__DIR__ . '/config.php')) {
    /** Import local configuration overrides, if any */
    include_once __DIR__ . '/config.php';
}

$_CONF_BANR['_now'] = new Date('now', $_CONF['timezone']);

define('BANR_ADMIN_URL',
        $_CONF['site_admin_url'] . '/plugins/'.$_CONF_BANR['pi_name']);
define('BANR_URL',
        $_CONF['site_url'] . '/'.$_CONF_BANR['pi_name']);
define('BANR_CLASSPATH', BANR_PI_PATH . '/classes');

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
    global $_CONF_BANR, $_TABLES;

    // Flag whether we're on the homepage or not
    $on_homepage = ($page == 1 && $topic == '') ? true : false;

    // If centerblock not enabled, or just for homepage and
    // we're not on the homepage, just return
    if (
        $_CONF_BANR['cb_enable'] == 0 ||
        ($_CONF_BANR['cb_home'] == 1 && !$on_homepage)
    ) {
        return '';
    }

    // Set basic options for banner search
    $options = array('limit' => 1, 'centerblock' => 1);

    // Get the centerblock position.  May be overridden later
    $cntrblkpos = $_CONF_BANR['cb_pos'];

    // If we're not supposed to replace the homepage, then return.
    // Otherwise, do so.
    if ($where == 0 && $topic == '') {
        if (!$_CONF_BANR['cb_replhome']) {
            return '';
        } else {
            $cntrblkpos = 0;
        }
    }

    // Check if there are no featured articles in this topic
    // and if so then place it at the top of the page
    if ($topic != "") {
        $wherenames = array('tid', 'featured', 'draft_flag');
        $wherevalues = array($topic, 1, 0);
        $options['tid'] = $topic;
    } else {
        $wherenames = array('featured', 'draft_flag');
        $wherevalues = array(1, 0);
    }

    $story_count = DB_count($_TABLES['stories'], $wherenames, $wherevalues);
    if ($story_count == 0 && $cntrblkpos == 2) {
        // If the centerblock comes after the featured story, and there
        // are no stories, put the centerblock at the top.
        $cntrblkpos = 1;
    }

    if ($cntrblkpos != $where) {
        return '';
    }

    $bids = Banner\Banner::getBannerIDs($options);
    if (!empty($bids)) {
        $B = new Banner\Banner($bids[0]);
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

    if ($_CONF_BANR['cb_replhome'] == 1 && $on_homepage) {
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
            $bids = Banner\Banner::getBannerIDs($options);
            if (!empty($bids)) {
                $B = new Banner\Banner($bids[0]);
                $banner = $B->BuildBanner($title);
                $B->updateImpressions();
            }
            break;

        case 'bannercategory' :
            // Get all banners under a specific category
            $cid = COM_applyFilter($autotag['parm1']);
            if (!empty($cid)) {
                // if there's a cid, join that table
                $cid_sql = "LEFT JOIN {$_TABLES['bannercategories']} AS c
                    ON b.cid=c.cid
                    WHERE c.cid='{$cid}' " .
                        COM_getPermSQL('AND', 0, 2, 'c') .
                         ' AND ';
            } else {
                $cid_sql = ' WHERE ';
            }
            $result = DB_query("SELECT b.bid
                    FROM {$_TABLES['banner']} AS b
                    $cid_sql
                    (b.publishstart IS NULL OR b.publishstart < NOW())
                    AND (b.publishend IS NULL OR b.publishend > NOW())
                    ORDER BY RAND()");
            $T = new Template(BANR_PI_PATH . '/templates');
            $T->set_file('block', 'bannercategory.thtml');
            $T->set_block('block', 'bannerRow', 'BRow');
            while ($A = DB_fetchArray($result, false)) {
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
    global $_CONF_BANR;
    return $_CONF_BANR['pi_version'];
}


/**
 * Shows the statistics for the banner plugin on stats.php.
 * If $showsitestats is 1 then we are to only print the overall stats in the
 * 'site statistics box' otherwise we show the detailed stats
 *
 * @param   integer $showsitestats  Flag to let us know which stats to get
 * @param   string                  HTML for the stats section
 */
/*function plugin_showstats_banner ($showsitestats)
{
    global $_CONF, $_TABLES, $LANG_BANNER;

    USES_lib_admin();
    USES_banner_class_banner();

    $retval = '';

    $result = DB_query ("SELECT bid
        FROM {$_TABLES['banner']}
        WHERE (hits > 0)
        AND (publishstart IS NULL OR publishstart < NOW())
        AND (publishend IS NULL OR publishend > NOW())" .
        COM_getPermSQL ('AND') .
        " ORDER BY hits DESC LIMIT 10");
    $nrows  = DB_numRows ($result);
    if ($nrows > 0) {
        $header_arr = array(
            array('text'         => $LANG_BANNER['title'],
                  'field'        => 'title',
                  'header_class' => 'stats-header-title'
            ),
            array('text'         => $LANG_BANNER['banner'],
                  'field'        => 'banner',
                  'header_class' => 'stats-header-title'
            ),
            array('text'         => $LANG_BANNER['hits'],
                  'field'        => 'hits',
                  'header_class' => 'stats-header-count',
                  'field_class'  => 'stats-list-count'
            )
        );
        $data_arr = array();
        $text_arr = array('has_menu' => false,
                          'title'    => $LANG_BANNER['stats_headline'],
        );
        $B = new Banner\Banner();
        for ($i = 0; $i < $nrows; $i++) {
            list($bid) = DB_fetchArray ($result);
            $B->Read($bid);
            $data_arr[] = array(
                'title' => $B->getTitle(),
                'banner' => $B->BuildBanner('', 200, 0, false),
                'hits' => COM_numberFormat($B->getHits()),
            );
        }
        $retval .= ADMIN_simpleList ('', $header_arr, $text_arr, $data_arr);
    } else {
        $retval .= COM_startBlock ($LANG_BANNER['stats_headline']);
        $retval .= $LANG_BANNER['stats_no_hits'];
        $retval .= COM_endBlock ();
    }

    return $retval;
}*/


/**
 * New stats plugin API function for proper integration with the site stats.
 *
 * @return  array   Array(item text, item count)
 */
function plugin_statssummary_banner ()
{
    global $LANG_BANNER, $_TABLES;

    $result = DB_query("SELECT COUNT(*) AS count, SUM(hits) AS clicks
        FROM {$_TABLES['banner']}
        WHERE (publishstart IS NULL OR publishstart < NOW())
        AND (publishend IS NULL OR publishend > NOW())" .
        COM_getPermSQL ('AND')
    );
    $A = DB_fetchArray ($result);

    $item_count = COM_NumberFormat($A['count'])
        . ' (' . COM_NumberFormat((int)$A['clicks']) . ')';

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
                      BANR_ADMIN_URL . '/index.php',
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
        $total_banner = (int)DB_count($_TABLES['banner']);
        return array(
            $LANG_BANNER['pi_name'],
            BANR_ADMIN_URL . '/index.php',
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
    global $_TABLES, $_CONF_BANR;

    if ($_CONF_BANR['delete_banner'] == 1) {
        // delete the banner
        DB_delete($_TABLES['banner'], 'owner_id', $uid);
    } else {
        // assign ownership to a user from the Banner Admin group
        $rootgroup = (int)DB_getItem(
            $_TABLES['groups'],
            'grp_id',
            "grp_name = 'Root'"
        );
        $result = DB_query("SELECT DISTINCT ug_uid
                FROM {$_TABLES['group_assignments']}
                WHERE ug_main_grp_id = $rootgroup
                ORDER BY ug_uid
                LIMIT 1");
        $A = DB_fetchArray ($result);
        $rootuser = (int)$A['ug_uid'];
        if ($rootuser > 0) {
            DB_query("UPDATE {$_TABLES['banner']}
                SET owner_id = '$rootuser'
                WHERE owner_id = $uid");
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
    foreach ($tables as $table) {
        DB_query("UPDATE {$_TABLES[$table]}
            SET uid = $destUID WHERE uid = $origUID", 1);
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

    // If trying to enable the plugin but lgLib is not available,
    // take no action and return
    if ($enabled && !in_array('lglib', $_PLUGINS)) {
        // Re-disable the plugin
        DB_change($_TABLES['plugins'], 'pi_enabled', 0, 'pi_name', 'banner');
        $enable = false;
        $enabled = 0;
    }

    if ($enabled == 0) {
        foreach ($blockfns as $blockfn) {
        $sql = "UPDATE {$_TABLES['blocks']}
                SET is_enabled=$enabled
                WHERE phpblockfn like '{$blockfn}%'";
        DB_query($sql);
        }
    }
}


/**
 * Receives notification of anothe plugin's deletion.
 * This plugin relies on the LGLIB plugin.
 *
 * @param   mixed   $id     Item ID
 * @param   string  $type   Plugin Name
 */
function plugin_itemdeleted_banner($id, $type)
{
    global $_TABLES;

    // If the lgLib plugin is disabled or removed, disable thie plugin
    if ($type == 'lglib' && $id == '*') {
        plugin_enablestatechange_banner(false);
        DB_change($_TABLES['plugins'], 'pi_enabled', 0, 'pi_name', 'banner');
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
        $retval = DB_count($_TABLES['bannersubmission']);
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
    global $_CONF_BANR;

    $B = new Banner\Banner($id);
    $filename = $B->getOpt('filename');
    if ($filename != NULL &&
        file_exists($_CONF_BANR['img_dir'] . '/' . $filename)) {
        @unlink($_CONF_BANR['img_dir'] . '/' . $filename);
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
    global $_CONF, $_CONF_BANR, $LANG12;

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
                . BANNER_errorMessage($message)
                . plugin_submit_banner($A)
                . COM_siteFooter();
        }
    }

    if (!empty($retval)) {
        return $retval;
    } else {
        echo COM_refresh($_CONF['site_url'] . '/index.php?msg=1&plugin=' .
                $_CONF_BANR['pi_name']);
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
    global $_CONF_BANR, $LANG_BANNER, $_USER;
    echo "here";die;

    // Make sure members can submit banners.  Anonymous users can never submit.
    if ( COM_isAnonUser() ||
        ($_CONF_BANR['usersubmit'] == 0 && !SEC_hasRights('banner.submit')) ) {
        COM_accessLog("User {$_USER['username']} tried to illegally access the banner submission screen.");
        return BANNER_errorMessage($LANG_BANNER['access_denied_msg']);
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
    return BANR_URL . '/images/banner.png';
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
 * $_CONF_BANR['templatevars'] to false.
 *
 * @param   string  $templatename   Name of template, e.g. 'header'
 * @param   ref     $template       Reference to actual template
 */
function plugin_templateSetVars_banner($templatename, &$template)
{
    global $_TABLES, $_CONF_BANR, $topic;

    $templatenames = array();
    $options = array('limit' => '1');

    // We only operate on the 'footer' templatename since 'header' is
    // called twice (from COM_siteHeader() and COM_siteFooter()).
    // We'll just replace both {banner_header} and {banner_footer} at once.
    if (isset($_CONF_BANR['templatevars']) && $_CONF_BANR['templatevars'] > 0) {
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
    $options['topic'] = empty($topic) ? 'all' : $topic;
//    $options['topic'] = Topic::currentID('all');

    $B = new Banner\Banner();
    foreach ($templatenames as $type) {
        $options['type'] = $type;
        $bids = Banner\Banner::getBannerIDs($options);
        if (is_array($bids) && !empty($bids)) {
            $B->Read($bids[0]);
            $banner = $B->BuildBanner();
            $B->updateImpressions();
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
        'banner_htmlheader',
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
function plugin_displayadblock_banner($plugin, $counter)
{
    global $_CONF_BANR, $topic;

    if (!BANR_isMinVersion('0.3.0')) {
        return '';
    }

    $Cats = Banner\Mapping::showCats($plugin, $counter);
    if (empty($Cats)) return '';

    $options = array(
        'limit' => '1',
        'cid' => $Cats,
    );
    $bids = Banner\Banner::getBannerIDs($options);
    if (is_array($bids) && !empty($bids)) {
        $B = new Banner\Banner($bids[0]);
        $B->updateImpressions();
        return $B->buildBanner();
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
    global $_CONF_BANR;

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

    if (isset($_CONF_BANR['templatevars'])) {
        // Reset this value to avoid trying to display a banner on the final page.
        $_CONF_BANR['templatevars'] = 0;
    }
    return $out;
}


/**
 * Returns a list of banners that belong to categories associated with
 * the current topic.
 *
 * @see     BANNER_block_banner()
 * @param   array   $A  Array of parameters for the block (not used)
 * @param   string  $param  Category name
 * @return  string      HTML for banner display.
 */
function phpblock_banner_topic($A=array(), $param='')
{
    global $_CONF_BANR;

    $opts = array(
        'type',
        'limit',
        'topic',
    );
    $extra = array(
        'type' => 'block',
        'limit' => $_CONF_BANR['block_limit'],
        'topic' => '',
    );
    $param = explode(' ', $param);
    foreach ($opts as $key=>$name) {
        if (isset($param[$key]) && !empty($param[$key])) {
            $extra[$name] = $param[$key];
        } else {
            break;
        }
    }
    return BANNER_block_banner($extra);
}


/**
 * Returns a random banner that belongs to categories associated with
 * the current topic.
 *
 * @see     BANNER_block_banner()
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
    return BANNER_block_banner($extra);
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
    global $_CONF, $_CONF_BANR;

    $styles = array();
    $files = array(
        'style.css',
    );

    $path = "{$_CONF['path']}/plugins/{$_CONF_BANR['pi_name']}/css";
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
    $options = array(
        'type' => 'htmlheader',
    );
    $bids = Banner\Banner::getBannerIDs($options);
    if (is_array($bids) && !empty($bids)) {
        $B = new Banner\Banner();
        foreach ($bids as $bid) {
            $B->Read($bid);
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
    global $LANG_BANNER, $_CONF, $_CONF_BANR, $_USER, $_TABLES;

    $retval = '';
    $uid = (int)$_USER['uid'];
    if ($uid < 2) return '';

    $banner_cnt = (int)DB_count(
        $_TABLES['banner'],
        'owner_id',
        $uid
    );
    //if ($banner_cnt > 0) {
        $retval = array(
            $LANG_BANNER['banners'],
            BANR_URL . '/index.php?banners=x',
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
    global $_CONF_BANR;

    $url = LGLIB_getDocUrl('config', $_CONF_BANR['pi_name'], $doclang);

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
    $query = "SELECT grp_id, grp_name FROM {$_TABLES['groups']}";
    $result = DB_query($query);
    if ($result && DB_numRows($result) > 0) {
        while ($row = DB_fetchArray($result)) {
            $A[$row['grp_name']] = $row['grp_id'];
        }
    }
    return $A;
}


////////////////////////////////////////////
//
//  Public Plugin-Specific Functions Follow
//
////////////////////////////////////////////


/**
 * Create a user dropdown list, optionally including or excluding
 * a specific comma-separated list of user ids.
 * Returns the <option> clauses only.
 *
 * @deprecated
 * @param  integer $sel    ID of selected user, 0 for none.
 * @param  string  $users  Comma separated list of users to include/exclude
 * @param  string  $not    'NOT' to exclude the users in $sel, blank to include
 * @return string          HTML for options in a select list.
 */
function XBANNER_UserDropdown($sel=0, $users='', $not='')
{
    global $_TABLES;

    $sel = (int)$sel;

    $sql = "SELECT uid,username
            FROM {$_TABLES['users']}
            WHERE uid <> 1 ";
    if ($users != '') {
        $not = $not == '' ? '' : 'NOT ';
        $sql .= "AND uid $not IN (" .
                DB_escapeString($users). ") ";
    }
    $sql .= " ORDER BY username ASC";
    $result = DB_query($sql);
    if (!$result)
        return '';

    $retval = '';
    while ($row = DB_fetcharray($result)) {
        $selected = $row['uid'] == $sel ? ' selected' : '';
        $retval .= "<option value=\"{$row['uid']}\" $selected>{$row['username']}</option>\n";
    }
    return $retval;
}


/**
 * Display a formatted error message
 *
 * @param   string  $msg    Error message to display
 * @param   string  $type   Type of message, used for style and header
 * @param   string  $header Optional header text.
 * @return  string      Formatted error message
 */
function BANNER_errorMessage($msg, $type = '', $header = '')
{
    global $LANG_BANNER;

    // If no header text specified, we'll put in a default.
    // Also validate the message type in this switch.
    switch (strtolower($type)) {
    case 'info':
        $class = 'info';
        if ($header == '') $header = $LANG_BANNER['info'];
        break;

    case 'note':
        $class = 'note';
        if ($header == '') $header = $LANG_BANNER['warning'];
        break;

    case 'alert':
    default:
        $class = 'alert';
        if ($header == '') $header = $LANG_BANNER['alert'];
        break;
    }

    if (is_array($msg)) {
        $text = '<ul>';
        foreach ($msg as $m) {
            $text .= "<li>$m</li>\n";
        }
        $text .= '</ul>';
    } else {
        $text = $msg;
    }
    return "<span class=\"$class\">"
            . COM_startBlock($header)
            . $text
            . COM_endBlock()
            . '</span>';
}


/**
 * Backend function for phpblock functions.
 * Returns one or more banners which fit the request.
 *
 * @param   string  $extra  Optional extra SQL.
 * @return  string          HTML for banner display
 */
function BANNER_block_banner($extra='')
{
    global $_CONF, $_TABLES, $LANG_BANNER, $topic;

    if (!Banner\Banner::canShow()) {
        return '';
    }

    $retval = '';
    $params = array();

    if ($extra == '')
        $params[] = array('type' => 'block');
    else
        $params = $extra;

    if (empty($topic)) {
        $tid = 'all';
    }
    $params['tid'] = $tid;

    // Get the banner IDs that fit the requirements.  Could be an array or a
    // single value, so convert into an array
    $bids = Banner\Banner::getBannerIDs($params);

    if (is_array($bids) && !empty($bids)) {
        $retval .= "<div class=\"aligncenter\">\n";
        $B = new Banner\Banner();
        foreach ($bids as $bid) {
            $B->Read($bid);
            $retval .= '<div class="blockbanner">' .
                Banner\Banner::getInstance($bid)
                    ->updateImpressions()
                    ->BuildBanner() .
                '</div>';
        }
        $retval .= "</div>\n";
    }
    return $retval;
}


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
    global $_CONF, $_CONF_BANR;

    if ($lang == '') $lang = $_CONF['language'];
    return LGLIB_getDocUrl($file, $_CONF_BANR['pi_name'], $lang);
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
    global $_CONF_BANR, $_PLUGIN_INFO;

    // If not specified, use the current code version as required.
    if ($required == '') $required = $_CONF_BANR['pi_version'];

    $pi_name = $_CONF_BANR['pi_name'];
    if (isset($_PLUGIN_INFO[$pi_name])) {
        if (is_array($_PLUGIN_INFO[$pi_name])) {
            // glFusion > = 1.6.6
            $installed = $_PLUGIN_INFO[$pi_name]['pi_version'];
        } else {
            $installed = $_PLUGIN_INFO[$pi_name];
        }
    }
    return COM_checkVersion($installed, $required);
}


/**
 * Log activity to a plugin-specific log file.
 *
 * @param   string  $logentry   Text to log
 */
function BANNER_auditLog($logentry = '')
{
    global $_CONF, $_USER, $LANG01, $_CONF_BANR;

    if ($logentry == '') {
        return;
    }

    // A little sanitizing
    $logentry = str_replace(
            array('<?', '?>'),
            array('(@', '@)'),
            $logentry);

    $timestamp = strftime( '%c' );
    $logfile = $_CONF['path_log'] . $_CONF_BANR['pi_name'] . '.log';

    // Can't open the log file?  Return an error
    if (!$file = fopen($logfile, 'a')) {
        return $LANG01[33] . $logfile . ' (' . $timestamp . ')<br />' . LB;
    }

    // Get the user name if it's not anonymous
    if (!COM_isAnonUser()) {
        $byuser = $_USER['uid'] . '-'.
                COM_getDisplayName($_USER['uid'],
                    $_USER['username'], $_USER['fullname']);
    } else {
        $byuser = 'anon';
    }
    $byuser .= '@' . $_SERVER['REMOTE_ADDR'];

    // Write the log entry to the file
    fputs($file, "$timestamp ($byuser) - $logentry\n");
}

