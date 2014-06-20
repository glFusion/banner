<?php
//  $Id: banner_functions.php 67 2010-03-19 15:01:52Z root $
/**
*   Plugin-specific functions for the Banner plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}



/**
*   This does the grunt work for the centerblock
*
*   @see    plugin_centerblock_banner()
*   @param  integer $where  Which area is being displayed now
*   @param  integer $page   Page number
*   @param  string  $topic  Topic ID, or empty string
*   @return string          HTML for centerblock
*/
function BANNER_centerblock($where = 1, $page = 1, $topic = '')
{
    global $_CONF, $_CONF_BANR, $_TABLES;

    $retval = '';

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

    USES_banner_class_banner();
    $bids = Banner::GetBanner($options);
    if (empty($bids)) {
        return '';
    }
    $B = new Banner($bids[0]);
    $B->updateImpressions();

    $T = new Template(BANR_PI_PATH . '/templates');
    $T->set_file('page', 'centerblock.thtml');
    $T->set_var('banner', $B->BuildBanner());
    $T->parse('output','page');

    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}


/**
*   Strips slashes if magic_quotes_gpc is on.
*
*   @param  mixed   $var    Value or array of values to strip.
*   @return mixed           Stripped value or array of values.
*/
function BANR_stripslashes($var)
{
	if (get_magic_quotes_gpc()) {
		if (is_array($var)) {
			return array_map('BANR_stripslashes', $var);
		} else {
			return stripslashes($var);
		}
	} else {
		return $var;
	}
}



?>
