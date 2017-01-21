<?php
/**
*   Class to handle advertising campaigns
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.2.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

/**
*   Class to manage banner campaigns
*   @package banner
*/
class Campaign
{
    /** Properties of a campaign
    *   @var array */
    var $properties;

    /** Flag to indicate whether this item is new
    *   @var boolean */
    var $isNew;

    /** Array to hold associated banner objects
    *   @var array */
    var $Banners = array();


    /**
    *   Constructor. Read campaign data from the database, or create
    *   a blank entry with default values
    *
    *   @param  string  $id     Optional campaign ID to read
    */
    public function __construct($id='')
    {
        global $_USER, $_TABLES, $_CONF_BANR;

        $this->properties = array();
        $this->isNew = true;    // Assume new entry until we read one
        $this->camp_id = $id;

        if ($this->camp_id != '') {
            $this->Read($this->camp_id);
        } else {
            // Set default values
            $this->enabled = 1;
            $this->owner_id = $_USER['uid'];
            $this->group_id = $_CONF_BANR['defgrpsubmit'];
            $this->perm_owner = $_CONF_BANR['default_permissions'][0];
            $this->perm_group = $_CONF_BANR['default_permissions'][1];
            $this->perm_members = $_CONF_BANR['default_permissions'][2];
            $this->perm_anon = $_CONF_BANR['default_permissions'][3];
        }
    }


    /**
    *   Read a single campaign record into the object
    *
    *   @param  string  $id     ID of the campaign to retrieve
    */
    public function Read($id)
    {
        global $_TABLES;

        $A = DB_fetchArray(DB_query("SELECT *
                FROM {$_TABLES['bannercampaigns']}
                WHERE camp_id='" . COM_sanitizeID($id, false) . "'", 1), false);
        if (!empty($A)) {
            $this->setVars($A);
            $this->isNew = false;
        }
    }


    /**
    *   Set a property value
    *
    *   @param  string  $key    Name of property
    *   @param  mixed   $value  Value to set
    */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'camp_id':
        case 'oldID':
            $this->properties[$key] = COM_sanitizeId($value, false);
            break;

        case 'owner_id':
        case 'group_id':
        case 'perm_owner':
        case 'perm_group':
        case 'perm_members':
        case 'perm_anon':
        case 'hits':
        case 'impressions':
        case 'max_impressions':
        case 'max_hits':
        case 'max_banners':
            $this->properties[$key] = (int)$value;
            break;

        case 'description':
        case 'start':
        case 'finish':
        case 'uname':
        case 'tid':
            $this->properties[$key] = $value;
            break;

        case 'enabled':
            $this->properties[$key] = $value ? 1 : 0;
            break;
        }
    }


    /**
    *   Retrieve the value of a property
    *
    *   @param  string  $key    Name of property
    *   @return mixed           Value of property
    */
    public function __get($key)
    {
        if (array_key_exists($key, $this->properties))
            return $this->properties[$key];
        else
            return NULL;
    }


    /**
    *   Set all the variables in this object from values provided.
    *
    *   @param  array   $A  Array of values, either from $_POST or database
    */
    public function setVars($A)
    {
        if (!is_array($A))
            return;

        $this->camp_id = $A['camp_id'];
        $this->setUID($A['owner_id']);
        $this->description = $A['description'];
        $this->start = substr($A['start'], 0, 16);
        $this->finish = substr($A['finish'], 0, 16);
        $this->enabled = $A['enabled'];
        $this->hits = $A['hits'];
        $this->max_hits = $A['max_hits'];
        $this->impressions = $A['impressions'];
        $this->max_impressions = $A['max_impressions'];
        $this->max_banners= $A['max_banners'];
        $this->tid = $A['tid'];

        if (isset($A['old_camp_id'])) {
            $this->oldID = $A['old_camp_id'];
        }

        //$this->owner_id = (int)$A['owner_id'];
        $this->group_id = (int)$A['group_id'];

        // Convert array values to numeric permission values
        if (is_array($A['perm_owner']) || is_array($A['perm_group']) ||
                is_array($A['perm_members']) || is_array($A['perm_anon'])) {
            list($this->perm_owner,$this->perm_group,
            $this->perm_members,$this->perm_anon) =
                SEC_getPermissionValues($A['perm_owner'],$A['perm_group'],
                    $A['perm_members'], $A['perm_anon']);
        } else {
            $this->perm_owner = $A['perm_owner'];
            $this->perm_group = $A['perm_group'];
            $this->perm_members = $A['perm_members'];
            $this->perm_anon = $A['perm_anon'];
        }

    }


    /**
    *   Set the owner ID of this campaign
    *
    *   @param  integer $uid    glFusion user ID
    */
    public function setUID($uid)
    {
        $this->owner_id = (int)$uid;
        $this->uname = COM_getDisplayName($this->owner_id);
    }


    /**
    *   Update the 'enabled' value for a banner ad.
    *
    *   @param  integer $newval     New value to set (1 or 0)
    *   @param  string  $id         Campaign ID to toggle
    */
    public static function toggleEnabled($oldval, $id)
    {
        global $_TABLES;

        if ($id == '') return $oldval;

        $newval = $oldval == 0 ? 1 : 0;
        DB_change($_TABLES['bannercampaigns'],
                'enabled', $newval,
                'camp_id', DB_escapeString(trim($id)));
        return DB_error() ? $oldval : $newval;
    }


    /**
    *   Delete a campaign.
    *   Can be called as Campaign::Delete($id).
    *
    *   @param  string  $id ID of campaign to delete, this object if empty
    */
    public function Delete($id)
    {
        global $_TABLES;

        if (self::isUsed($this->camp_id)) {
            DB_delete($_TABLES['bannercampaigns'],
                'camp_id', DB_escapeString(trim($id)));
        }
    }


    /**
    *   Determine if this campaign has any banners belonging to it.
    *
    *   @param  string  $id ID of campaign to check, this object if empty.
    *   @return boolean     True if this has baners, False if unused
    */
    public static function isUsed($id)
    {
        global $_TABLES;

        if (DB_count($_TABLES['banner'], 'camp_id',
            DB_escapeString(trim($id))) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
    *   Create the editing form for this campaign
    *
    *   @return string      HTML for edit form
    */
    public function Edit()
    {
        global $_CONF, $_CONF_BANR, $_TABLES, $LANG_ACCESS, $LANG_BANNER, $_SYSTEM;

        USES_banner_class_image();      // for banner dimensions

        $T = new Template($_CONF['path'] . 'plugins/' .
                        $_CONF_BANR['pi_name'].'/templates/admin/');
        $tpltype = $_SYSTEM['framework'] == 'uikit' ? '.uikit' : '';
        $T->set_file (array('editform' => "campaignedit$tpltype.thtml"));

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
            'description'   => $this->description,
            'dt_start'      => $this->start,
            'dt_finish'     => $this->finish,
            'enabled'       => $this->enabled == 1 ? 'checked="checked"' : '',
            'total_hits'    => $this->hits,
            'max_hits'      => $this->max_hits,
            'impressions'   => $this->impressions,
            'max_impressions'   => $this->max_impressions,
            'max_banners'   => $this->max_banners,
            'banner_ownerid'    => $this->owner_id,
            'owner_username' => DB_getItem($_TABLES['users'],
                                'username', "uid = {$this->owner_id}"),
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
                list($width, $height) = Image::reDim($B->width, $B->height, 300);
                $url = COM_buildUrl(BANR_ADMIN_URL .
                        '/index.php?edit=banner&bid=' . $B->bid);
                $T->set_var('image', $B->BuildBanner('', $width, $height, $false));
                $T->set_var('ad_id', COM_createLink($B->bid, $url, array()));
                $T->set_var('hits', $B->hits);
                $T->parse('ad', 'AdRow', true);
            }
        }
        $T->parse ('output', 'editform');
        //$menu = BANNER_menu_adminCampaigns();
        return $T->finish($T->get_var('output'));
    }


    /**
    *   Save this campaign
    *
    *   @param  array   $A  Array of values from $_POST (optional)
    */
    public function Save($A='')
    {
        global $_TABLES, $LANG_BANNER;

        if (is_array($A))
            $this->setVars($A);

        $start = $this->start == NULL ? 'NULL' :
            "'" . DB_escapeString($this->start) . "'";
        $finish = $this->finish == NULL ? 'NULL' :
            "'" . DB_escapeString($this->finish) . "'";

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
                return false;

            $sql1 = "UPDATE {$_TABLES['bannercampaigns']}";
            $sql3 = " WHERE camp_id='" . DB_escapeString($this->oldID) . "'";
        }

        if (DB_count($_TABLES['bannercampaigns'], 'camp_id', $this->camp_id) > $allowed) {
            return $LANG_BANNER['duplicate_camp_id'];
        }

        $sql2 = " SET
                camp_id='" . DB_escapeString($this->camp_id) . "',
                description='" . DB_escapeString($this->description) . "',
                start=$start,
                finish=$finish,
                enabled={$this->enabled},
                hits={$this->hits},
                max_hits={$this->max_hits},
                impressions={$this->impressions},
                max_impressions={$this->max_impressions},
                max_banners={$this->max_banners},
                owner_id={$this->owner_id},
                group_id={$this->group_id},
                perm_owner={$this->perm_owner},
                perm_group={$this->perm_group},
                perm_members={$this->perm_members},
                perm_anon={$this->perm_anon},
                tid='" . DB_escapeString($this->tid) . "'";
         //echo $sql;die;
        DB_query($sql);
        return DB_Error() ? false : true;
    }


    /**
    *   Create the user list for assigning campaigns to users
    *
    *   @param  integer $uid    Optional user ID to show as selected.
    *   @return string          HTML for options
    */
    public function XUserList($uid='')
    {
        echo "Campaign::UserList() DEPRECATED";die;
        global $_TABLES;

        // Set up the user id to show as selected
        if ($uid == '' && is_object($this)) {
            $uid = $this->owner_id;
        } else {
            $uid = (int)$uid;
        }

        $sql = "SELECT uid,username
                FROM {$_TABLES['users']}
                ORDER BY username ASC";
        $result = DB_query($sql);
        if (!$result)
            return '';

        $retval = '';
        while ($row = DB_fetcharray($result)) {
            $selected = $row['uid'] == $uid ? 'selected="selected"' : '';
            $retval .= "<option value=\"{$row['uid']}\" $selected>{$row['username']}</option>\n";
        }
        return $retval;
    }


    /**
    *   Get the current user's access level to this campaign
    *
    *   @return integer     Access level from SEC_hasAccess()
    *   @see SEC_hasAccess()
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
    *   Determine if the current user has access at lease equal to the
    *   specified value.
    *
    *   @see Campaign::Access()
    *   @param  integer $level  Level to check current access against
    *   @return boolean         True if the users access >= requested level
    */
    public function hasAccess($level=3)
    {
        if ($this->Access() < (int)$level)
            return false;
        else
            return true;
    }


    /**
    *   Return the option elements for a campaign selection dropdown.
    *
    *   @param  string  $sel    Campaign ID to show as selected
    *   @return string          HTML for option statements
    */
    public static function DropDown($sel='', $access=3)
    {
        global $_TABLES;

        $retval = '';
        $sel = COM_sanitizeID($sel, false);
        $access = (int)$access;

        // Retrieve the campaigns to which the current user has access
        $sql = "SELECT c.camp_id, c.description, c.max_banners,
                    u.username,
                    COUNT(b.bid) as cnt
                FROM {$_TABLES['bannercampaigns']} c
                LEFT JOIN {$_TABLES['banner']} b
                    ON c.camp_id=b.camp_id
                LEFT JOIN {$_TABLES['users']} u
                    ON u.uid = c.owner_id
                WHERE 1=1 " .
                    COM_getPermSQL('AND', 0, $access, 'c') .
                " GROUP BY c.camp_id HAVING
                    (c.max_banners = 0 OR cnt < c.max_banners)";
        //echo $sql;
        $result = DB_query($sql);

        while ($row = DB_fetchArray($result)) {
            $selected = $row['camp_id'] == $sel ? ' selected' : '';
            $retval .= "<option value=\"" .
                        htmlspecialchars($row['camp_id']) .
                        "\"$selected>" .
                        htmlspecialchars($row['username']) . ' : ' .
                        htmlspecialchars($row['description']) .
                        "</option>\n";
        }
        return $retval;
    }


    /**
    *   Return an array of Banner objects for the banners associated
    *   with this campaign.
    *   Sets the internal Banner variable to an array of Banner objects.
    */
    public function getBanners()
    {
        global $_TABLES;

        USES_banner_class_banner();

        $sql = "SELECT bid
                FROM {$_TABLES['banner']}
                WHERE camp_id='{$this->camp_id}'";
        $result = DB_query($sql);
        if (!$result)
            return false;

        $this->Banners = array();
        while ($A = DB_fetchArray($result, MYSQL_FETCH_ASSOC)) {
            $this->Banners[] = new Banner($A['bid']);
        }
    }

}   // class Campaign


/**
*   Create the admin menu for managing campaigns
*/
function BANNER_menu_adminCampaigns()
{
    global $_CONF, $LANG_BANNER, $LANG_ADMIN;

    USES_lib_admin();

    $admin_url = BANR_ADMIN_URL . '/index.php';
    $menu_arr = array(
        array('url' => "$admin_url?view=campaigns",
              'text' => $LANG_BANNER['campaigns']),
        array('url' => "$admin_url?view=banners",
              'text' => $LANG_BANNER['banners']),
        array('url' => "$admin_url?view=categories",
              'text' => $LANG_BANNER['categories']),
        array('url' => $admin_url,
              'text' => $LANG_ADMIN['admin_home']),
    );
    return ADMIN_createMenu($menu_arr,
                $LANG_BANNER['camp_mgr'],
                plugin_geticon_banner());
}

?>
