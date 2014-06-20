<?php
//  $Id: banner.class.php 16 2009-10-19 04:21:05Z root $
/**
*   Class to handle banner lists for administrators and regular users
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/


/**
*   Define a class for banner lists
*   @package banner
*/
class BannerList
{
    /** Indicate whether this is an admin or not
    *   @var boolean */
    var $isAdmin;

    /** Specific campaign ID to list
    *   @var string */
    var $campID;

    /** Specific category ID to list
    *   @var string */
    var $catID;

    /** Base url, depending on where user is admin or not
    *   @var string */
    var $url;


    /**
    *   Constructor.
    */
    function __construct($isAdmin = false)
    {
        $this->setAdmin($isAdmin);
        $this->campID = '';
        $this->catID = '';
    }


    function setAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin == true ? true : false;
        if ($this->isAdmin) {
            $this->url = BANR_ADMIN_URL;
        } else {
            $this->url = BANR_URL;
        }
    }

    function setCampID($id)
    {
        $this->campID = COM_sanitizeID($id, false);
    }

    function setCatID($id)
    {
        $this->catID = COM_sanitizeID($id, false);
    }


    /**
    *   Create the list
    */
    function ShowList()
    {
        global $LANG_ADMIN, $LANG_BANNER, 
                 $_TABLES, $_CONF, $_CONF_BANR;

        USES_lib_admin();

        $retval = '';

        $form_arr = array();

        $header_arr = array(
            array(  'text' => $LANG_BANNER['edit'], 
                    'field' => 'edit',
                    'sort' => false),
            array(  'text' => $LANG_BANNER['enabled'], 
                    'field' => 'enabled',
                    'sort' => false),
            array(  'text' => $LANG_BANNER['banner_id'], 
                    'field' => 'bid', 
                    'sort' => true),
            array(  'text' => $LANG_BANNER['banner_title'], 
                    'field' => 'title', 
                    'sort' => true),
            array(  'text' => $LANG_BANNER['banner_cat'], 
                    'field' => 'category', 
                    'sort' => true),
            array(  'text' => $LANG_BANNER['pubstart'], 
                    'field' => 'publishstart', 
                    'sort' => true),
            array(  'text' => $LANG_BANNER['pubend'], 
                    'field' => 'publishend', 
                    'sort' => true),
            array(  'text' => $LANG_BANNER['hits'],
                    'field' => 'hits', 
                    'sort' => true),
            array(  'text' => $LANG_BANNER['impressions'],
                    'field' => 'impressions',
                    'sort' => true),
            array(  'text' => $LANG_ADMIN['delete'],
                    'field' => 'delete',
                    'sort' => false,
                    'align' => 'center'),
        );

        $menu_arr = array (
            array(
                'url' => $_CONF['site_url'] . '/submit.php?type=' . 
                    $_CONF_BANR['pi_name'],
              'text' => $LANG_BANNER['new_banner']),
            array(
                'url' => $this->url . '/index.php?view=campaigns',
                'text' => $LANG_BANNER['campaigns']),
        );

        if ($this->isAdmin) {
            $menu_arr[] = array(
                'url' => $this->url . '/index.php?validate=enabled',
                'text' => $LANG_BANNER['validate_banner']);
            $menu_arr[] = array(
                'url' => $this->url . '/index.php?view=categories',
                'text' => $LANG_BANNER['categories']);
            $menu_arr[] = array(
                'url' => $_CONF['site_admin_url'],
                'text' => $LANG_ADMIN['admin_home']);
            $sql_value = 1;

            $validate = '';
            if (isset($_GET['validate'])) {
                $token = SEC_createToken();
                $dovalidate_url = BANR_ADMIN_URL . 
                    '/index.php?validate=validate&amp;'. CSRF_TOKEN.'='.$token;
                $dovalidate_text = $LANG_BANNER['validate_now'];

                $form_arr['top'] = COM_createLink($dovalidate_text, $dovalidate_url);
    
                if ($_GET['validate'] == 'enabled') {
                    $header_arr[] = array(
                        'text' => $LANG_BANNER['html_status'], 
                        'field' => 'beforevalidate', 'sort' => false);
                    $validate = '?validate=enabled';
                } else if ($_GET['validate'] == 'validate') {
                    $header_arr[] = array(
                        'text' => $LANG_BANNER['html_status'], 
                        'field' => 'dovalidate', 'sort' => false);
                    $validate = '&validate=validate&amp;'.CSRF_TOKEN.'='.$token;
                }
                $validate_help = $LANG_BANNER['validate_instr'];
            }
            $validate_help = '';
            $text_arr = array(
                'has_extras' => true,
                'form_url' => BANR_ADMIN_URL . '/index.php?item=banner' . 
                                $validate,
            );
        } else {
            $sql_value = 0;
            $text_arr = array(
                'has_extras' => true,
                'form_url' => BANR_URL . '/index.php?mode=banners',
            );
        }

        $options = array('chkdelete' => 'true', 'chkfield' => 'bid');

        $defsort_arr = array('field' => 'category', 'direction' => 'asc');

        $retval .= COM_startBlock($LANG_BANNER['banner_mgr'] . ' ' . 
                        $LANG_BANNER['version'] . ' ' . 
                        $_CONF_BANR['pi_version']
                        , '',
                        COM_getBlockTemplate('_admin_block', 'header'));

        $retval .= ADMIN_createMenu($menu_arr, 
                $LANG_BANNER['banner_mgr_instr'] . $validate_help, 
                plugin_geticon_banner());

        $query_arr = array('table' => 'banner',
            'sql' => "SELECT
                    b.bid AS bid, b.cid as cid, b.title AS title,
                    c.category AS category, 
                    b.enabled AS enabled,
                    b.hits AS hits, b.impressions as impressions,
                    b.max_hits AS max_hits, 
                    b.max_impressions as max_impressions,
                    b.publishstart AS publishstart,
                    b.publishend AS publishend, b.owner_id, b.group_id,
                    b.perm_owner, b.perm_group, b.perm_members, b.perm_anon,
                    $sql_value as isAdmin
                FROM
                    {$_TABLES['banner']} AS b
                LEFT JOIN
                    {$_TABLES['bannercategories']} AS c
                ON b.cid=c.cid WHERE 1=1 ",

            'query_fields' => array('title', 'category', 
                'b.publishstart', 'b.publishend', 'b.hits'),

            'default_filter' => COM_getPermSql ('AND', 0, 3, 'b')
        );

        // Limit to a specific campaign, if requested
        if ($this->campID != '') {
            $query_arr['sql'] .= " AND b.camp_id = '{$this->campID}' ";
        }

        // Limit to a specific category, if requested
        if ($this->catID != '') {
            $query_arr['sql'] .= " AND b.cid = '{$this->catID}' ";
        }

        /*if (!empty($_GET['bannercategory'])) {
            $query_arr['sql'] .= " AND c.cid = '".
                    addslashes($_GET['bannercategory']). "'";
        }*/

        $retval .= ADMIN_list('banner', 'BANNER_getField_banner', $header_arr,
                $text_arr, $query_arr, $defsort_arr, '', '', $options, $form_arr);
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

        return $retval;
    }

}   // class BannerList


/**
*   Get the correct display for a single field in the banner admin list
*
*   @param  string  $fieldname  Field variable name
*   @param  string  $fieldvalue Value of the current field
*   @param  array   $A          Array of all field names and values
*   @param  array   $icon_arr   Array of system icons
*   @return string              HTML for field display within the list cell
*/
function BANNER_getField_banner($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_ACCESS, $_CONF_BANR;

    $retval = '';

    /*$access = SEC_hasAccess($A['owner_id'],$A['group_id'],
            $A['perm_owner'], $A['perm_group'],
            $A['perm_members'], $A['perm_anon']);*/
/*    if ($access <= 0)
        return;
*/
    $base_url = $A['isAdmin'] == 1 ? BANR_ADMIN_URL : BANR_URL;

    switch($fieldname) {
    case 'edit':
/*        if ($access < 3) {
            break;
        }*/
        /*if ($A['enabled'] == 1) {
            $ena_icon = 'on.png';
            $enabled = 0;
            $ena_icon_txt = $LANG_BANNER['enabled'];
        } else {
            $ena_icon = 'off.png';
            $enabled = 1;
            $ena_icon_txt = $LANG_BANNER['click_enable'];
        }*/
        if (!$A['isAdmin']) break;

        /*$retval = '<table border="0"><tr height="22">';
        $retval .= '<td>' . */
        $retval = 
                COM_createLink(
                $icon_arr['edit'],
                $base_url . '/index.php?edit=x&item=banner&amp;bid=' .$A['bid']
                );
            // . '</td>';
        break;

    case 'enabled':
        if ($A['enabled'] == '1') {
            $switch = 'checked="checked"';
            //$newval = 0;
        } else {
            $switch = '';
            //$newval = 1;
        }
        $retval .= "<input type=\"checkbox\" $switch value=\"1\" name=\"banr_ena_check\"
                id=\"togena{$A['bid']}\"
                onclick='BANR_toggleEnabled(this, \"{$A['bid']}\",\"banner\", \"{$_CONF['site_url']}\");' />\n";
        break;

        /*$retval .= '<td>' .
                "<span id=\"togena{$A['bid']}\"> " .
                "<img src=\"" .
                BANR_URL . "/images/{$ena_icon}\" " .
                "border=\"0\" width=\"16\" height=\"16\" " .
                "onclick='BANR_toggleEnabled({$enabled}, \"{$A['bid']}\", \"banner\", \"{$_CONF['site_url']}\");' ".
                '></span></td>' . " \n" ;
        $retval .= '</tr></table>';*/
        break;

    case 'delete':
        $retval = '<form action="'."{$base_url}/index.php".'" method="post">
                <input type=hidden name="bid" value="'.$A['bid'].'">
                <input type=hidden name="mode" value="deleteBanner">
                <input type="image"
                    src="'.$_CONF['layout_url'].'/images/admin/delete.png"
                    height="16" width="16" border="0"
                    alt="Delete this Banner"
                    title="Delete this Baner"
                    onclick="return confirm('."'Do you really want to delete this item?'".');"
                    class="gl_mootip">
            </form>' . "\n";
        break;

    case 'dovalidate':
        $B = new Banner($A['bid']);
        $retval = $B->validateURL();
        break;

    case 'beforevalidate';
        $retval = $LANG_BANNER['before_validate'];
        break;

    case 'camp_id':
        $retval = COM_createLink($A['camp_id'], 
                "{$base_url}/index.php?mode=campaigns&camp_id="
                . urlencode($A['camp_id']));
        break;

    case 'hits':
        $max = (int)$A['max_hits'];
        $hits = (int)$fieldvalue;
        $max_txt = $max > 0 ? $max : 'Unltd.';
        $retval = $hits . ' / ' . $max_txt;
        if ($max > 0 && $hits >= $max) {
            $retval = "<span style=\"background-color:yellow;\">$retval</span>";
        }
        break;

    case 'impressions':
        $max = (int)$A['max_impressions'];
        $impr = (int)$fieldvalue;
        $max_txt = $max > 0 ? $max : 'Unltd.';
        $retval = $impr . ' / ' . $max_txt;
        if ($max > 0 && $impr >= $max) {
            $retval = "<span style=\"background-color:yellow;\">$retval</span>";
        }
        break;

    default:
        $retval = $fieldvalue;
        break;
    }
    return $retval;
}


?>
