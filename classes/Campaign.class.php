<?php
/**
 * Class to handle advertising campaigns.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v0.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Banner;

/**
 * Class to manage banner campaigns.
 * @package banner
 */
class Campaign
{
    /** Campaign ID.
     * @var string */
    private $camp_id = '';

    /** Old Campaign ID. Used to track if the ID is being changed.
     * @var string */
    private $oldID = '';

    /** Owner ID.
     * @var integer */
    private $owner_id = 0;

    /** Group ID.
     * @var integer */
    private $group_id = 0;

    /** Owner Permission.
     * @var integer */
    private $perm_owner = 3;

    /** Group Permission.
     * @var integer */
    private $perm_group = 2;

    /** Members Permission.
     * @var integer */
    private $perm_members = 2;

    /** Group Permission.
     * @var integer */
    private $perm_anon = 2;

    /** Number of hits to ads under this campaign.
     * @var integer */
    private $hits = 0;

    /** Number of impressions for ads under this campaign.
     * @var integer */
    private $impressions = 0;

    /** Max allowed hits for this campaign.
     * @var integer */
    private $max_hits = 0;

    /** Max allowed impressions for this campaign.
     * @var integer */
    private $max_impressions = 0;

    /** Max number of banners allowed in this campaign.
     * @var integer */
    private $max_banners = 0;

    /** Description of this campaign.
     * @var string */
    private $dscp = '';

    /** Publication starting date.
     * @var object */
    private $PubStart = NULL;

    /** Publication ending date.
     * @var object */
    private $PubEnd = NULL;

    /** Campaign owner's disply nme.
     * @var string */
    private $uname = '';

    /** Topic ID for limiting campaign display.
     * @var string */
    private $tid = '';

    /** Flag to indicate that this campaign's ads can be displaayed.
     * @var boolean */
    private $enabled = 1;

    /** Flag to indicate whether this item is new.
     * @var boolean */
    private $isNew = 1;

    /** Array to hold associated banner objects.
     * @var array */
    private $Banners = array();


    /**
     * Read an existing campaign or create a blank one.
     *
     * @param   string  $id     Optional campaign ID to read
     */
    public function __construct($id='')
    {
        global $_USER, $_TABLES, $_CONF_BANR;

        $this->isNew = true;    // Assume new entry until we read one
        $this->camp_id = $id;

        if ($this->camp_id != '') {
            $this->Read($this->camp_id);
        } else {
            // Set default values
            $this->enabled = 1;
            $this->owner_id = (int)$_USER['uid'];
            $this->group_id = (int)$_CONF_BANR['defgrpsubmit'];
            $this->perm_owner = (int)$_CONF_BANR['default_permissions'][0];
            $this->perm_group = (int)$_CONF_BANR['default_permissions'][1];
            $this->perm_members = (int)$_CONF_BANR['default_permissions'][2];
            $this->perm_anon = (int)$_CONF_BANR['default_permissions'][3];
            $this->setPubStart();
            $this->setPubEnd();
        }
    }


    /**
     * Read a single campaign record into the object.
     *
     * @param   string  $id     ID of the campaign to retrieve
     */
    public function Read($id)
    {
        global $_TABLES;

        $A = DB_fetchArray(
            DB_query(
                "SELECT * FROM {$_TABLES['bannercampaigns']}
                WHERE camp_id='" . COM_sanitizeID($id, false) . "'", 1
            ),
            false
        );
        if (!empty($A)) {
            $this->setVars($A);
            $this->isNew = false;
        }
    }


    /**
     * Set all the variables in this object from values provided.
     *
     * @param   array   $A  Array of values, either from $_POST or database
     */
    public function setVars($A, $fromDB=false)
    {
        if (!is_array($A))
            return;

        $this->camp_id = $A['camp_id'];
        $this->setUID($A['owner_id']);
        $this->group_id = (int)$A['group_id'];
        $this->dscp = $A['description'];
        $this->enabled = isset($A['enabled']) && $A['enabled'] ? 1 : 0;
        $this->hits = (int)$A['hits'];
        $this->max_hits = (int)$A['max_hits'];
        $this->impressions = (int)$A['impressions'];
        $this->max_impressions = (int)$A['max_impressions'];
        $this->max_banners= (int)$A['max_banners'];
        $this->tid = $A['tid'];

        if ($fromDB) {
            $this->setPubStart($A['start']);
            $this->setPubEnd($A['finish']);
        } else {
            // Form, get values from two fields
            if (empty($A['start_date'])) {
                $this->setPubStart(0);
            } else {
                $this->setPubStart($A['start_date'] . ' ' . $A['start_time']);
            }
            if (empty($A['end_date'])) {
                $this->setPubEnd(0);
            } else {
                $this->setPubEnd($A['end_date'] . ' ' . $A['end_time']);
            }
        }
        if (isset($A['old_camp_id'])) {
            $this->oldID = $A['old_camp_id'];
        }

        // Convert array values to numeric permission values
        if (
            is_array($A['perm_owner']) ||
            is_array($A['perm_group']) ||
            is_array($A['perm_members']) ||
            is_array($A['perm_anon'])
        ) {
            list(
                $this->perm_owner,$this->perm_group,
                $this->perm_members,$this->perm_anon
            ) = SEC_getPermissionValues(
                $A['perm_owner'],$A['perm_group'],
                $A['perm_members'], $A['perm_anon']
            );
        } else {
            $this->perm_owner = (int)$A['perm_owner'];
            $this->perm_group = (int)$A['perm_group'];
            $this->perm_members = (int)$A['perm_members'];
            $this->perm_anon = (int)$A['perm_anon'];
        }
    }


    /**
     * Set the owner ID of this campaign.
     *
     * @param   integer $uid    glFusion user ID
     * @return  object  $this
     */
    public function setUID($uid)
    {
        $this->owner_id = (int)$uid;
        $this->uname = COM_getDisplayName($this->owner_id);
        return $this;
    }


    public function getID()
    {
        return $this->camp_id;
    }


    /**
     * Set the starting publication date/time.
     *
     * @param   string  $dt_str     MYSQL-formatted date/time string
     * @return  object  $this
     */
    public function setPubStart($dt_str='')
    {
        global $_CONF;

        if (empty($dt_str)) {
            $dt_str = BANR_MIN_DATE;
        }
        $this->PubStart = new \Date($dt_str, $_CONF['timezone']);
        return $this;
    }


    /**
     * Set the ending publication date/time.
     *
     * @param   string  $dt_str     MYSQL-formatted date/time string
     * @return  object  $this
     */
    public function setPubEnd($dt_str='')
    {
        global $_CONF;

        if (empty($dt_str)) {
            $dt_str = BANR_MAX_DATE;
        }
        $this->PubEnd = new \Date($dt_str, $_CONF['timezone']);
        return $this;
    }


    /**
     * Get the starting date for publication.
     *
     * @return  string      Starting publication date
     */
    public function getPubStart($fmt='')
    {
        if ($fmt == '') {
            return $this->PubStart;
        } else {
            return $this->PubStart->format($fmt);
        }
    }


    /**
     * Get the ending date for publication.
     *
     * @return  string      Ending publication date
     */
    public function getPubEnd($fmt='')
    {
        if ($fmt == '') {
            return $this->PubEnd;
        } else {
            return $this->PubEnd->format($fmt);
        }
    }


    public function isEnabled()
    {
        return $this->enabled ? 1 : 0;
    }


    /**
     * Update the 'enabled' value for a banner ad.
     *
     * @param   integer $oldval Original value being changed
     * @param   string  $id         Campaign ID to toggle
     * @return  integer     New value, or old value on error
     */
    public static function toggleEnabled($oldval, $id)
    {
        global $_TABLES;

        if ($id == '') return $oldval;

        $newval = $oldval == 0 ? 1 : 0;
        DB_change(
            $_TABLES['bannercampaigns'],
            'enabled', $newval,
            'camp_id', DB_escapeString(trim($id))
        );
        return DB_error() ? $oldval : $newval;
    }


    /**
     * Delete a campaign.
     *
     * @param   string  $id     ID of campaign to delete, this object if empty
     */
    public function Delete($id)
    {
        global $_TABLES;

        if (self::isUsed($this->camp_id)) {
            DB_delete(
                $_TABLES['bannercampaigns'],
                'camp_id',
                DB_escapeString(trim($id))
            );
        }
        $this->camp_id = '';
    }


    /**
     * Determine if a campaign has any banners belonging to it.
     *
     * @param   string  $id ID of campaign to check
     * @return  boolean     True if this has baners, False if unused
     */
    public static function isUsed($id)
    {
        global $_TABLES;

        return DB_count(
            $_TABLES['banner'],
            'camp_id',
            DB_escapeString(trim($id))
        ) > 0; 
    }


    /**
     * Create the editing form for this campaign.
     *
     * @return  string      HTML for edit form
     */
    public function Edit()
    {
        global $_CONF, $_CONF_BANR, $_TABLES, $LANG_ACCESS, $LANG_BANNER, $_SYSTEM;

        $T = new \Template($_CONF['path'] . 'plugins/' .
                        $_CONF_BANR['pi_name'].'/templates');
        $T->set_file (array(
            'editform' => "admin/campaignedit.thtml",
            'tips' => 'tooltipster.thtml',
        ) );

        $ownername = COM_getDisplayName ($this->owner_id);
        $topics = COM_topicList('tid,topic', $this->tid, 1, true);

        if ($this->isNew && $this->camp_id == '') {
            $this->camp_id = COM_makeSid();
        }

        $T->set_var(array(
            'pi_url'        => BANR_URL,
            'help_url'      => BANNER_docURL('campaignform.html'),
            'camp_id'       => $this->camp_id,
            'uname'         => $this->uname,
            'description'   => $this->dscp,
            'start_date'    => $this->getPubStart('Y-m-d'),
            'start_time'    => $this->getPubStart('H:i:s'),
            'snd_date'      => $this->getPubEnd('Y-m-d'),
            'end_time'      => $this->getPubEnd('H:i:s'),
            'enabled'       => $this->enabled == 1 ? 'checked="checked"' : '',
            'total_hits'    => $this->hits,
            'max_hits'      => $this->max_hits,
            'impressions'   => $this->impressions,
            'max_impressions'   => $this->max_impressions,
            'max_banners'   => $this->max_banners,
            'banner_ownerid'    => $this->owner_id,
            'owner_username' => DB_getItem($_TABLES['users'],
                                'username', "uid = '{$this->owner_id}'"),
            'owner_selection' => COM_optionList($_TABLES['users'],'uid,username',
                                $this->owner_id, 1, 'uid <> 1'),
            'group_dropdown' => SEC_getGroupDropdown(
                                $this->group_id, $this->Access()),
            'permissions_editor' => SEC_getPermissionsHTML(
                                $this->perm_owner, $this->perm_group,
                                $this->perm_members,$this->perm_anon),
            'topic_list'    => $topics,
            'cancel_url'    => BANR_ADMIN_URL . '/index.php?view=campaigns',
        ) );

        if (self::isUsed($this->camp_id)) {
            $T->set_var('delete_option', 'true');
        }

        $alltopics = '<option value="all"';
        if ($this->tid == 'all') {
            $alltopics .= ' selected="selected"';
        }
        $alltopics .= '>' . $LANG_BANNER['all'] . '</option>' . LB;
        $T->set_var('topic_selection',  $alltopics . $topics);

        $T->set_block('editform', 'AdRow', 'ad');
        if (!$this->isNew) {
            $this->getBanners();
            foreach ($this->Banners as $B) {
                $url = COM_buildUrl(BANR_ADMIN_URL .
                        '/index.php?edit=banner&bid=' . $B->getBid());
                $T->set_var('image', $B->BuildBanner('', 300, 300, false));
                $T->set_var('ad_id', COM_createLink($B->getBid(), $url, array()));
                $T->set_var('hits', $B->getHits());
                $T->parse('ad', 'AdRow', true);
            }
        }
        $T->parse('tooltipster', 'tips');
        $T->parse ('output', 'editform');
        //$menu = BANNER_menu_adminCampaigns();
        return $T->finish($T->get_var('output'));
    }


    /**
     * Save this campaign.
     *
     * @param   array   $A  Array of values from $_POST (optional)
     */
    public function Save($A='')
    {
        global $_TABLES, $LANG_BANNER;

        if (is_array($A))
            $this->setVars($A);

        $start = $this->getPubStart()->toMySQL(true);
        $finish = $this->getPubEnd()->toMySQL(true);

        if ($this->isNew) {
            // Creates a new ID if one not already provided
            $this->camp_id = COM_sanitizeID($this->camp_id, true);
            $allowed = 0;       // no existing campaign by this name allowed

            $sql1 = "INSERT INTO {$_TABLES['bannercampaigns']}";
            $sql3 = '';
        } else {
            $this->camp_id = COM_sanitizeID($this->camp_id, false);
            // If not changing the ID, then one existing record with this
            // camp_id is expected.
            $allowed = $this->camp_id == $this->oldID ? 1 : 0;

            // Not modifying the campaign ID, but it shouldn't be empty
            if ($this->camp_id == '')
                return $LANG_BANNER['err_missing_id'];

            $sql1 = "UPDATE {$_TABLES['bannercampaigns']}";
            $sql3 = " WHERE camp_id='" . DB_escapeString($this->oldID) . "'";
        }

        if (DB_count($_TABLES['bannercampaigns'], 'camp_id', $this->camp_id) > $allowed) {
            return $LANG_BANNER['err_dup_id'];;
        }

        $sql2 = " SET
                camp_id='" . DB_escapeString($this->camp_id) . "',
                description='" . DB_escapeString($this->dscp) . "',
                start = '$start',
                finish = '$finish',
                enabled = {$this->isEnabled()},
                hits = '{$this->hits}',
                max_hits = '{$this->max_hits}',
                impressions= '{$this->impressions}',
                max_impressions = '{$this->max_impressions}',
                max_banners = '{$this->max_banners}',
                owner_id = '{$this->owner_id}',
                group_id = '{$this->group_id}',
                perm_owner = '{$this->perm_owner}',
                perm_group = '{$this->perm_group}',
                perm_members = '{$this->perm_members}',
                perm_anon = '{$this->perm_anon}',
                tid='" . DB_escapeString($this->tid) . "'";
        $sql = $sql1 . $sql2 . $sql3;
        //echo $sql;die;
        DB_query($sql);
        if (DB_error()) {
            return $LANG_BANNER['err_saving_item'];
        }
        if ($this->camp_id != $this->oldID) {
            // Update banners that were associated with the old ID
            DB_change($_TABLES['banner'], 'camp_id', $this->camp_id, 'camp_id', $this->oldID);
        }
        return '';
    }


    /**
     * Get the current user's access level to this campaign.
     *
     * @see     SEC_hasAccess()
     * @return  integer     Access level from SEC_hasAccess()
     */
    public function Access()
    {
        static $access = NULL;
        if ($access === NULL) {
            $access = SEC_hasAccess($this->owner_id, $this->group_id,
                    $this->perm_owner, $this->perm_group,
                    $this->perm_members, $this->perm_anon);
        }
        return $access;
    }


    /**
     * Determine if the current user has access at lease equal to the specified value.
     *
     * @see     self::Access()
     * @param   integer $level  Level to check current access against
     * @return  boolean         True if the users access >= requested level
     */
    public function hasAccess($level=3)
    {
        if ($this->Access() < (int)$level)
            return false;
        else
            return true;
    }


    /**
     * Return the option elements for a campaign selection dropdown.
     *
     * @param   string  $sel    Campaign ID to show as selected
     * @param   integer $access Required access level to campaigns
     * @return  string          HTML for option statements
     */
    public static function DropDown($sel='', $access=3)
    {
        global $_TABLES;

        $retval = '';
        $sel = COM_sanitizeID($sel, false);
        $access = (int)$access;

        // Retrieve the campaigns to which the current user has access
        $sql = "SELECT c.camp_id, MAX(c.description) AS description,
            MAX(c.max_banners) AS max_banners,
            COUNT(b.bid) as cnt
            FROM {$_TABLES['bannercampaigns']} c
            LEFT JOIN {$_TABLES['banner']} b
                ON c.camp_id=b.camp_id " .
            COM_getPermSQL('WHERE', 0, $access, 'c') .
            " GROUP BY c.camp_id
            HAVING (max_banners = 0 OR cnt < max_banners)";
        //echo $sql;
        $result = DB_query($sql);

        while ($row = DB_fetchArray($result)) {
            $selected = $row['camp_id'] == $sel ? ' selected' : '';
            $retval .= "<option value=\"" .
                        htmlspecialchars($row['camp_id']) .
                        "\"$selected>" .
                        htmlspecialchars($row['description']) .
                        "</option>\n";
        }
        return $retval;
    }


    /**
     * Get an array of Banner objects associated with this campaign.
     * Sets the internal Banner variable to an array of Banner objects.
     */
    public function getBanners()
    {
        global $_TABLES;

        $sql = "SELECT bid
                FROM {$_TABLES['banner']}
                WHERE camp_id='{$this->camp_id}'";
        $result = DB_query($sql);
        if (!$result)
            return false;

        $this->Banners = array();
        while ($A = DB_fetchArray($result, false)) {
            $this->Banners[] = new Banner($A['bid']);
        }
    }


    /**
     * Update the impression counter for this campaign.
     *
     * @param   string  $camp_id    Campaign ID
     */
    public static function updateImpressions($camp_id)
    {
        global $_TABLES;
        DB_query("UPDATE {$_TABLES['bannercampaigns']}
                SET impressions=impressions+1
                WHERE camp_id='" . DB_escapeString($camp_id) . "'");
    }


    /**
     * Update the hit counter for this campaign.
     *
     * @param   string  $camp_id    Campaign to update
     */
    public static function updateHits($camp_id)
    {
        global $_TABLES;

        // Update the campaign total hits
        $sql = "UPDATE {$_TABLES['bannercampaigns']}
                SET hits=hits+1
                WHERE camp_id='" . DB_escapeString($camp_id) . "'";
        DB_query($sql);
    }
 

    /**
     * Create the admin list of campaigns to manage.
     *
     * @param   integer $uid    Limit list by owner
     * @return  string  HTML for admin list
     */
    public static function adminList($uid = 0)
    {
        global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $LANG_BANNER;

        USES_lib_admin();

        $retval = '';

        $header_arr = array(
            array(
                'text' => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort' => false,
                'align' => 'center',
            ),
            array(
                'text' => $LANG_ADMIN['enabled'],
                'field' => 'enabled',
                'sort' => false,
                'align' => 'center',
            ),
            array(
                'text' => $LANG_BANNER['camp_id'],
                'field' => 'camp_id',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['user_id'],
                'field' => 'owner_id',
                'sort' => true,
            ),
            array(
                'text' => $LANG_ADMIN['title'],
                'field' => 'description',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['pubstart'],
                'field' => 'start',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['pubend'],
                'field' => 'finish',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['hits'],
                'field' => 'hits',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['impressions'],
                'field' => 'impressions',
                'sort' => true,
            ),
            array(
                'text' => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort' => false,
                'align' => 'center',
            ),
        );

        $defsort_arr = array('field' => 'camp_id', 'direction' => 'asc');
        $text_arr = array(
            'has_extras' => true,
            'form_url' => BANR_ADMIN_URL . "?view=campaigns"
        );
        $query_arr = array(
            'table' => 'bannercampaigns',
            'sql' => "SELECT c.*
                    FROM {$_TABLES['bannercampaigns']} AS c ",
            'query_fields' => array('camp_id', 'description'),
            'default_filter' => ''
        );
        $form_arr = array();

        $retval .= COM_createLink($LANG_BANNER['new_camp'],
            BANR_ADMIN_URL . '/index.php?editcampaign',
            array(
                'class' => 'uk-button uk-button-success',
                'style' => 'float:left',
            )
        );
        $retval .= ADMIN_list(
            'bannercampaigns',
            array(__CLASS__ , 'getAdminField'),
            $header_arr, $text_arr, $query_arr,
            $defsort_arr, '', '', '',
            $form_arr
        );
        return $retval;
    }

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
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF, $_TABLES, $LANG_ACCESS, $_CONF_BANR, $LANG_BANNER;

        $retval = '';
        $base_url = BANR_ADMIN_URL;
        switch($fieldname) {
        case 'edit':
            $retval .= COM_createLink(
                $_CONF_BANR['icons']['edit'],
                "$base_url/index.php?editcampaign=" . urlencode($A['camp_id'])
            );
            break;

        case 'enabled':
            $switch = $fieldvalue == 1 ? 'checked="checked"' : '';
            $retval .= "<input type=\"checkbox\" $switch value=\"1\" name=\"camp_ena_check\"
                id=\"togena{$A['camp_id']}\"
                onclick='BANR_toggleEnabled(this, \"{$A['camp_id']}\",\"campaign\");' />\n";
            break;

        case 'delete':
            if (self::isUsed($A['camp_id'])) {
                $retval .= COM_createLink(
                    $_CONF_BANR['icons']['delete'],
                    "$base_url/index.php?delete=x&item=campaign&amp;camp_id={$A['camp_id']}",
                    array(
                        'onclick' => "return confirm('{$LANG_BANNER['ok_to_delete']}');",
                    )
                );
            } else {
                $retval .= '<i class="uk-icon uk-icon-trash-o uk-text-muted tooltip"
                    title="' . $LANG_BANNER['cannot_delete'] . '"></i>';
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

}

?>
