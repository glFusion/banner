<?php
/**
*   Class to handle banner lists for administrators and regular users
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.2.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
namespace Banner;

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
    public function __construct($isAdmin = false)
    {
        $this->setAdmin($isAdmin);
        $this->campID = '';
        $this->catID = '';
    }


    /**
    *   Sets the isAdmin variable and the plugin URL based on it.
    *
    *   @param  boolean $isAdmin    True if the current user is an admin.
    */
    public function setAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin && plugin_isadmin_banner() ? true : false;
        if ($this->isAdmin) {
            $this->url = BANR_ADMIN_URL;
        } else {
            $this->url = BANR_URL;
        }
    }


    /**
    *   Sanitize and set the Campaign ID
    *
    *   @param  string  $id     Campaign ID
    */
    public function setCampID($id)
    {
        $this->campID = COM_sanitizeID($id, false);
    }


    /**
    *   Santize and set the Category ID
    *
    *   @param  string  $id     Category ID
    */
    public function setCatID($id)
    {
        $this->catID = COM_sanitizeID($id, false);
    }


    /**
    *   Create the list
    */
    public function ShowList()
    {
        global $LANG_ADMIN, $LANG_BANNER, $_USER,
                 $_TABLES, $_CONF, $_CONF_BANR;

        USES_lib_admin();

        $uid = (int)$_USER['uid'];
        $retval = '';
        $form_arr = array();

        $header_arr = array(
            array(  'text' => $LANG_BANNER['edit'],
                    'field' => 'edit',
                    'sort' => false,
                    'align' => 'center'),
            array(  'text' => $LANG_BANNER['enabled'],
                    'field' => 'enabled',
                    'align' => 'center',
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

        if ($this->isAdmin) {
            $is_admin = 1;

            $validate = '';
            $token = SEC_createToken();

            if (isset($_POST['validate'])) {
                $header_arr[] = array(
                    'text' => $LANG_BANNER['html_status'],
                    'field' => 'dovalidate', 'sort' => false);
            } else {
                $dovalidate_url = BANR_ADMIN_URL . '/index.php';
                    //'/index.php?validate=validate&amp;'. CSRF_TOKEN.'='.$token;
                $dovalidate_text = '<button class="lgButton green" name="validate">' .
                    $LANG_BANNER['validate_now'] . '</button>';
                $form_arr['top'] = COM_createLink($dovalidate_text, $dovalidate_url);
                $header_arr[] = array(
                        'text' => $LANG_BANNER['html_status'],
                        'field' => 'beforevalidate', 'sort' => false);
            }
            $text_arr = array(
                'has_extras' => true,
                'form_url' => BANR_ADMIN_URL . '/index.php?item=banner',
            );
        } else {
            $is_admin = 0;
        }

        $options = array('chkdelete' => 'true', 'chkfield' => 'bid');

        $defsort_arr = array('field' => 'category', 'direction' => 'asc');

        $query_arr = array('table' => 'banner',
            'sql' => "SELECT
                    b.bid AS bid, b.cid as cid, b.title AS title,
                    c.category AS category,
                    b.enabled AS enabled,
                    b.hits AS hits, b.impressions as impressions,
                    b.max_hits AS max_hits,
                    b.max_impressions as max_impressions,
                    b.publishstart AS publishstart,
                    b.publishend AS publishend, b.owner_id,
                    $is_admin as isAdmin
                FROM {$_TABLES['banner']} AS b
                LEFT JOIN {$_TABLES['bannercategories']} AS c
                    ON b.cid=c.cid
                WHERE ($is_admin = 1 OR b.owner_id = $uid) ",

            'query_fields' => array('title', 'category',
                'b.publishstart', 'b.publishend', 'b.hits'),
        );

        // Limit to a specific campaign, if requested
        if ($this->campID != '') {
            $query_arr['sql'] .= " AND b.camp_id = '{$this->campID}' ";
        }

        // Limit to a specific category, if requested
        if ($this->catID != '') {
            $query_arr['sql'] .= " AND b.cid = '{$this->catID}' ";
        }

        $retval .= ADMIN_list('banner', __NAMESPACE__ . '\getField_banner', $header_arr,
                $text_arr, $query_arr, $defsort_arr, '', '', $options, $form_arr);
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
function getField_banner($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_ACCESS, $_CONF_BANR;

    $retval = '';

    $base_url = $A['isAdmin'] == 1 ? BANR_ADMIN_URL : BANR_URL;

    switch($fieldname) {
    case 'edit':
        $retval = COM_createLink(
                '<i class="' . BANR_getIcon('edit') . '"></i>',
                $base_url . '/index.php?edit=x&item=banner&amp;bid=' .$A['bid']
                );
        break;

    case 'enabled':
        if ($A['enabled'] == '1') {
            $switch = 'checked="checked"';
        } else {
            $switch = '';
        }
        $retval .= "<input type=\"checkbox\" $switch value=\"1\" name=\"banr_ena_check\"
                id=\"togena{$A['bid']}\"
                onclick='BANR_toggleEnabled(this, \"{$A['bid']}\",\"banner\");' />\n";
        break;

    case 'delete':
        $retval = COM_createLink('<i class="' . BANR_getIcon('trash', 'danger') . '"></i>',
                "$base_url/index.php?bid={$A['bid']}&deleteBanner",
                array(
                     'onclick' => "return confirm('Do you really want to delete this item?');",
                ) );
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
                "{$base_url}/index.php?campaigns=x&camp_id="
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

    case 'publishstart':
        $retval = $fieldvalue == BANR_MIN_DATE ? 'n/a' : $fieldvalue;
        break;

    case 'publishend':
        $retval = $fieldvalue == BANR_MAX_DATE ? 'n/a' : $fieldvalue;
        break;

    default:
        $retval = $fieldvalue;
        break;
    }
    return $retval;
}

?>
