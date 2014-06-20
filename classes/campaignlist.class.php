<?php
//  $Id: campaign.class.php 16 2009-10-19 04:21:05Z root $
/**
*   Class to create the admin list of advertising campaigns.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.1.2
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

USES_banner_class_campaign();

/**
*   Class to manage banner campaigns
*   @package banner
*/
class CampaignList
{
    /** Indicate whether this is an administrator or not.
    *   @var boolean */
    var $isAdmin;

    /** Base url for links in the menus.  May be admin or public
    *   @var string */
    var $url;

    /** Value given to SQL query for 'isAdmin'.
    *   This is a hack to inform CAMPAIGN_getField() of the user's
    *   admin status.
    *   @var integer */
    var $sql_value;


    /**
    *   Constructor.
    */
    function CampaignList($isAdmin = false)
    {
        $this->setAdmin($isAdmin);
    }


    function setAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin == true ? true : false;
        if ($this->isAdmin) {
            $this->url = BANR_ADMIN_URL;
            $this->sql_value = 1;
        } else {
            $this->url = BANR_URL;
            $this->sql_value = 0;
        }
    }


    /**
    *   Create the admin list of campaigns to manage.
    *   @return string  HTML for admin list
    */
    function ShowList($uid = 0)
    {
        global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $LANG_BANNER;

        USES_lib_admin();

        $retval = '';

        $header_arr = array(
            array('text' => $LANG_ADMIN['edit'], 'field' => 'action', 
                    'sort' => false),
            array('text' => $LANG_BANNER['camp_id'], 'field' => 'camp_id', 
                    'sort' => true),
            array('text' => $LANG_BANNER['user_id'], 'field' => 'owner_id', 
                    'sort' => true),
            array('text' => $LANG_ADMIN['title'], 'field' => 'description', 
                    'sort' => true),
            array('text' => $LANG_BANNER['pubstart'], 'field' => 'start', 
                    'sort' => true),
            array('text' => $LANG_BANNER['pubend'], 'field' => 'finish', 
                    'sort' => true),
            array('text' => $LANG_BANNER['hits'], 'field' => 'hits', 
                    'sort' => true),
            array('text' => $LANG_BANNER['impressions'], 
                    'field' => 'impressions', 
                    'sort' => true),
            array('text' => $LANG_ADMIN['delete'], 'field' => 'delete',
                    'sort' => false),
        );

        $defsort_arr = array('field' => 'camp_id', 'direction' => 'asc');

        $retval .= COM_startBlock($LANG_BANNER['camp_mgr'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

        // Create the menu
        $menu_arr = array();
        if ($this->isAdmin) {
            $menu_arr[] = array(
                'url' => $this->url . '/index.php?edit=x&item=campaign',
                'text' => $LANG_BANNER['new_camp']);
        }
        $menu_arr[] = array(
                'url' => $this->url . '/index.php?view=banners',
                'text' => $LANG_BANNER['banners']);
        if ($this->isAdmin) {
            $menu_arr[] = array(
                'url' => $this->url . '/index.php?view=categories',
                'text' => 'Categories');
            $menu_arr[] = array('url' => $admin_url,
                  'text' => $LANG_ADMIN['admin_home']);
        }
        $retval .= ADMIN_createMenu($menu_arr, 
                $LANG_BANNER['camp_mgr_instr'], 
                plugin_geticon_banner());

        $text_arr = array(
            'has_extras' => true,
            'form_url' => "$admin_url?view=campaigns$validate"
        );

        $query_arr = array('table' => 'bannercampaigns',
            'sql' => "SELECT 
                    c.*, $uid as uid,
                    {$this->sql_value} as isAdmin
                FROM 
                    {$_TABLES['bannercampaigns']} AS c
                WHERE 1=1" .
                COM_getPermSQL('AND', 0, 3, 'c'),
            'query_fields' => '',
            'default_filter' => ''
        );
        if ($uid > 0) {
            $query_arr['sql'] .= ' AND c.owner_id = ' . (int)$uid;
        }
        //echo $query_arr['sql'];die;

        $retval .= ADMIN_list('bannercampaigns', 
                'BANNER_getField_Campaign', $header_arr,
                $text_arr, $query_arr, $defsort_arr, '', '', '', 
                $form_arr);
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

        return $retval;

    }


}   // class Campaign



/**
*   Return the correct display for a single field in the campaign admin list.
*
*   @param  string  $fieldname  Field variable name
*   @param  string  $fieldvalue Value of the current field
*   @param  array   $A          Array of all field names and values
*   @param  array   $icon_arr   Array of system icons
*   @return string              HTML for field display within the list cell
*/
function BANNER_getField_Campaign($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ACCESS, $_CONF_BANR, $LANG_BANNER;

    $retval = '';

    $access = SEC_hasAccess($A['owner_id'], $A['group_id'],
            $A['perm_owner'], $A['perm_group'],
            $A['perm_members'], $A['perm_anon']);
    if ($access <= 0)
        return '';

    $base_url = $A['isAdmin'] == 1 ? BANR_ADMIN_URL : BANR_URL;
    switch($fieldname) {
    case 'action':
        if ($access < 3) {
            // If they don't have read/write access, no actions are available
            $retval = '';
            break;
        }

        if ($A['enabled'] == 1) {
            $ena_icon = 'on.png';
            $enabled = 0;
        } else {
            $ena_icon = 'off.png';
            $enabled = 1;
        }

        $retval = '';

        // Create action icons.  Some actions are currently only supported
        // for administrators, regardless of access level
        if ($A['isAdmin'] == 1) {
            $retval .= COM_createLink(
                $icon_arr['edit'],
                "$base_url/index.php?edit=x&item=campaign&amp;camp_id=" . 
                urlencode($A['camp_id']));
        }

        $retval .= "<span id=togena{$A['camp_id']}>\n" .
            "<img style=\"display:inline; width:16px; height:16px;\" src=\"" . 
            BANR_URL . "/images/{$ena_icon}\" ".
            "onclick='BANR_toggleEnabled({$enabled}, \"{$A['camp_id']}\", \"campaign\", \"{$_CONF['site_url']}\");'>\n" .
            "</span>\n";
        break;

    case 'delete':
        if (!Campaign::isUsed($A['camp_id']) && $A['isAdmin'] == 1) {
            $retval .= COM_createLink('<img src='. $_CONF['layout_url'] .
                '/images/admin/delete.png>',
                "$base_url/index.php?delete=x&item=campaign&amp;camp_id={$A['camp_id']}",
                array('onclick' => 
                    "return confirm('{$LANG_BANNER['ok_to_delete']}');"));
        }
        break;

    case 'owner_id':
        $retval = COM_getDisplayName($A[$fieldname]);
        break;

    case 'camp_id':
        $retval = COM_createLink($A['camp_id'], 
                "{$base_url}/index.php?view=banners&camp_id="
                . urlencode($A['camp_id']));
        break;

    case 'hits':
        $max = (int)$A['max_hits'] > 0 ? (int)$A['max_hits'] : 'Unltd.';
        $retval = (int)$fieldvalue . ' / ' . $max;
        break;

    case 'impressions':
        $max = (int)$A['max_impressions'] > 0 ? 
            (int)$A['max_impressions'] : 'Unltd.';
        $retval = (int)$fieldvalue . ' / ' . $max;
        break;

    default:
        $retval = htmlspecialchars($A[$fieldname]);
        break;
    }

    return $retval;
}


?>
