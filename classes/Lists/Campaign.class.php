<?php
/**
 * Class to create the admin list of advertising campaigns.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v0.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Banner\Lists;

/**
 * Class to manage banner campaigns.
 * @package banner
 */
class Campaign
{
    /** Indicate whether this is an administrator or not.
    * @var boolean */
    var $isAdmin;

    /** Base url for links in the menus.  May be admin or public.
    * @var string */
    var $url;

    /**
     * Constructor.
     *
     * @param   boolean $isAdmin    True to indicate that this is an administrator
     */
    public function __construct($isAdmin = false)
    {
        $this->setAdmin($isAdmin);
    }


    /**
     * Set a flag and other values for admins.
     *
     * @param   boolean $isAdmin    True if user is an admin, False if not
     */
    public function setAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin == true ? true : false;
        if ($this->isAdmin) {
            $this->url = BANR_ADMIN_URL;
        } else {
            $this->url = BANR_URL;
        }
    }


    /**
     * Create the admin list of campaigns to manage.
     *
     * @param   integer $uid    Limit list by owner
     * @return  string  HTML for admin list
     */
    public function ShowList($uid = 0)
    {
        global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $LANG_BANNER;

        USES_lib_admin();

        $retval = '';

        $header_arr = array(
            array('text' => $LANG_ADMIN['edit'], 'field' => 'edit',
                    'sort' => false, 'align' => 'center'),
            array('text' => $LANG_ADMIN['enabled'], 'field' => 'enabled',
                    'sort' => false, 'align' => 'center'),
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
                    'sort' => false, 'align' => 'center'),
        );

        $defsort_arr = array('field' => 'camp_id', 'direction' => 'asc');

        $text_arr = array(
            'has_extras' => true,
            'form_url' => BANR_ADMIN_URL . "?view=campaigns"
        );

        $query_arr = array('table' => 'bannercampaigns',
            'sql' => "SELECT c.*, $uid as uid
                    FROM {$_TABLES['bannercampaigns']} AS c " .
                    COM_getPermSQL('WHERE', 0, 3, 'c'),
            'query_fields' => array('camp_id', 'description'),
            'default_filter' => ''
        );
        if ($uid > 0) {
            $query_arr['sql'] .= ' AND c.owner_id = ' . (int)$uid;
        }
        $form_arr = array();
        $retval .= ADMIN_list('bannercampaigns',
                __NAMESPACE__ . '\getField_Campaign', $header_arr,
                $text_arr, $query_arr, $defsort_arr, '', $this->isAdmin, '',
                $form_arr);

        return $retval;
    }

}   // class Campaign



/**
 * Return the correct display for a single field in the campaign admin list.
 *
 * @param   string  $fieldname  Field variable name
 * @param   string  $fieldvalue Value of the current field
 * @param   array   $A          Array of all field names and values
 * @param   array   $icon_arr   Array of system icons
 * @param   boolean $isAdmin    True if this is an administrator viewing
 * @return  string              HTML for field display within the list cell
 */
function getField_Campaign($fieldname, $fieldvalue, $A, $icon_arr, $isAdmin)
{
    global $_CONF, $_TABLES, $LANG_ACCESS, $_CONF_BANR, $LANG_BANNER;

    $retval = '';

    $base_url = $isAdmin ? BANR_ADMIN_URL : BANR_URL;
    switch($fieldname) {
    case 'edit':
        $retval .= COM_createLink(
            $_CONF_BANR['icons']['edit'],
            "$base_url/index.php?edit=x&item=campaign&amp;camp_id=" . urlencode($A['camp_id'])
        );
        break;

    case 'enabled':
        $switch = $fieldvalue == 1 ? 'checked="checked"' : '';
        $retval .= "<input type=\"checkbox\" $switch value=\"1\" name=\"camp_ena_check\"
                id=\"togena{$A['camp_id']}\"
                onclick='BANR_toggleEnabled(this, \"{$A['camp_id']}\",\"campaign\");' />\n";
        break;

    case 'delete':
        if (\Banner\Campaign::isUsed($A['camp_id'])) {
            $retval .= COM_createLink(
                $_CONF_BANR['icons']['delete'],
                "$base_url/index.php?delete=x&item=campaign&amp;camp_id={$A['camp_id']}",
                array(
                    'onclick' => "return confirm('{$LANG_BANNER['ok_to_delete']}');",
                )
            );
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
