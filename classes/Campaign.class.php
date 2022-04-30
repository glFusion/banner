<?php
/**
 * Class to handle advertising campaigns.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2022 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Banner;
use glFusion\Database\Database;
use glFusion\Log\Log;
use glFusion\FieldList;


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

    /** Description of this campaign.
     * @var string */
    private $dscp = '';

    /** Publication starting date. NULL indicates no limitation.
     * @var object */
    private $pubStart = NULL;

    /** Publication ending date. NULL indicates no limitation.
     * @var object */
    private $pubEnd = NULL;

    /** Campaign owner's disply nme.
     * @var string */
    private $uname = '';

    /** Topic ID for limiting campaign display.
     * @var string */
    private $tid = '';

    /** Flag to indicate that this campaign's ads can be displaayed.
     * @var boolean */
    private $enabled = 1;

    /** Flag to indicate that ads should be shown to the ad owner.
     * @var boolean */
    private $show_owner = 0;

    /** Flag to indicate that ads should be shown to the plugin admins.
     * @var boolean */
    private $show_admins = 0;

    /** Flag to indicate that ads should be shown in administration pages.
     * @var boolean */
    private $show_adm_pages = 0;

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
            /*$this->setPubStart();
            $this->setPubEnd();*/
        }
    }


    /**
     * Read a single campaign record into the object.
     *
     * @param   string  $id     ID of the campaign to retrieve
     */
    public function Read(string $id)
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $A = $db->conn->executeQuery(
                "SELECT * FROM {$_TABLES['bannercampaigns']}
                WHERE camp_id = ?",
                array($id),
                array(Database::STRING)
            )->fetch(Database::ASSOCIATIVE);
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            $A = NULL;
        }
        if (!empty($A)) {
            $this->setVars($A, true);
            $this->isNew = false;
        }
    }


    /**
     * Set all the variables in this object from values provided.
     *
     * @param   array   $A  Array of values, either from $_POST or database
     */
    public function setVars(?array $A=NULL, bool $fromDB=false) : self
    {
        if (!is_array($A)) {
            return $this;
        }

        $this->camp_id = $A['camp_id'];
        $this->setUID($A['owner_id']);
        $this->group_id = (int)$A['group_id'];
        $this->dscp = $A['description'];
        $this->enabled = isset($A['enabled']) && $A['enabled'] ? 1 : 0;
        $this->hits = (int)$A['hits'];
        $this->max_hits = (int)$A['max_hits'];
        $this->impressions = (int)$A['impressions'];
        $this->max_impressions = (int)$A['max_impressions'];
        $this->tid = $A['tid'];
        $this->show_owner = isset($A['show_owner']) && $A['show_owner'] ? 1 : 0;
        $this->show_admins = isset($A['show_admins']) && $A['show_admins'] ? 1 : 0;
        $this->show_adm_pages = isset($A['show_adm_pages']) && $A['show_adm_pages'] ? 1 : 0;

        if ($fromDB) {
            $this->setPubStart($A['start']);
            $this->setPubEnd($A['finish']);
        } else {
            // Form, get values from two fields
            if (empty($A['start_date'])) {
                $this->setPubStart(NULL);
            } else {
                $this->setPubStart($A['start_date']);
            }
            if (empty($A['end_date'])) {
                $this->setPubEnd(NULL);
            } else {
                $this->setPubEnd($A['end_date']);
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
        return $this;
    }


    /**
     * Set the owner ID of this campaign.
     *
     * @param   integer $uid    glFusion user ID
     * @return  object  $this
     */
    public function setUID(int $uid) : self
    {
        $this->owner_id = (int)$uid;
        $this->uname = COM_getDisplayName($this->owner_id);
        return $this;
    }


    public function getID() : string
    {
        return $this->camp_id;
    }


    /**
     * Set the starting publication date/time.
     *
     * @param   string  $dt_str     MYSQL-formatted date/time string
     * @return  object  $this
     */
    public function setPubStart(?string $dt_str=NULL) : self
    {
        global $_CONF;

        if (!empty($dt_str)) {
            $this->pubStart = new \Date($dt_str, $_CONF['timezone']);
        } else {
            $this->pubStart = NULL;
        }
        return $this;
    }


    /**
     * Set the ending publication date/time.
     *
     * @param   string  $dt_str     MYSQL-formatted date/time string
     * @return  object  $this
     */
    public function setPubEnd(?string $dt_str = NULL) : self
    {
        global $_CONF;

        if (!empty($dt_str)) {
            $this->pubEnd = new \Date($dt_str, $_CONF['timezone']);
        } else {
            $this->pubEnd = NULL;
        }
        return $this;
    }


    /**
     * Get the starting date for publication.
     *
     * @return  string      Starting publication date
     */
    public function getPubStart(?string $fmt=NULL)
    {
        if (!empty($fmt) && !is_null($this->pubStart)) {
            return $this->pubStart->format($fmt, true);
        } else {
            return $this->pubStart;
        }
    }


    /**
     * Get the ending date for publication.
     *
     * @return  string      Ending publication date
     */
    public function getPubEnd($fmt='')
    {
        if (!empty($fmt) && !is_null($this->pubEnd)) {
            return $this->pubEnd->format($fmt, true);
        } else {
            return $this->pubEnd;
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
        $db = Database::getInstance();
        try {
            $status = $db->conn->executeUpdate(
                "UPDATE {$_TABLES['bannercampaigns']}
                SET enabled = ?
                WHERE camp_id = ?",
                array($newval, $id),
                array(Database::INTEGER, Database::STRING)
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            $newval = $oldval;
        }
        return $newval;
    }


    /**
     * Delete a campaign.
     *
     * @param   string  $id     ID of campaign to delete, this object if empty
     */
    public function Delete($id)
    {
        global $_TABLES;

        if (!self::isUsed($this->camp_id)) {
            $db = Database::getInstance();
            try {
                $db->conn->delete(
                    $_TABLES['bannercampaigns'], array('camp_id' => $id), array(Database::STRING)
                );
            } catch (\Exception $e) {
                Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            }
        }
        $this->camp_id = '';
    }


    /**
     * Check if this campaign should show ads to the ad owner.
     *
     * @return  boolean     True/False
     */
    public function showOwner() : bool
    {
        return $this->show_owner ? 1 : 0;
    }


    /**
     * Check if this campaign should show ads to site administrators.
     *
     * @return  boolean     True/False
     */
    public function showAdmins() : bool
    {
        return $this->show_admins ? 1 : 0;
    }


    /**
     * Check if this campaign should show ads on administration pages.
     *
     * @return  boolean     True/False
     */
    public function showAdminPages() : bool
    {
        return $this->show_adm_pages ? 1 : 0;
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

        $db = Database::getInstance();
        return $db->getCount($_TABLES['banner'], 'camp_id', $id, Database::STRING) > 0;
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
            'help_url'      => BANNER_docURL('campaignform'),
            'camp_id'       => $this->camp_id,
            'uname'         => $this->uname,
            'description'   => $this->dscp,
            'start_date'    => $this->getPubStart('Y-m-d H:i'),
            'end_date'      => $this->getPubEnd('Y-m-d H:i'),
            'enabled'       => $this->enabled == 1 ? 'checked="checked"' : '',
            'total_hits'    => $this->hits,
            'max_hits'      => $this->max_hits,
            'impressions'   => $this->impressions,
            'max_impressions'   => $this->max_impressions,
            'banner_ownerid'    => $this->owner_id,
            'owner_username' => COM_getDisplayName($this->owner_id),
            'owner_selection' => COM_optionList($_TABLES['users'],'uid,username',
                                $this->owner_id, 1, 'uid <> 1'),
            'group_dropdown' => SEC_getGroupDropdown(
                                $this->group_id, $this->Access()),
            'permissions_editor' => SEC_getPermissionsHTML(
                                $this->perm_owner, $this->perm_group,
                                $this->perm_members,$this->perm_anon),
            'topic_list'    => $topics,
            'cancel_url'    => BANR_ADMIN_URL . '/index.php?view=campaigns',
            'show_owner_chk' => $this->showOwner() ? 'checked="checked"' : '',
            'show_admins_chk' => $this->showAdmins() ? 'checked="checked"' : '',
            'show_adm_page_chk' => $this->showAdminPages() ? 'checked="checked"' : '',
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
    public function Save(?array $A=NULL)
    {
        global $_TABLES, $LANG_BANNER;

        if (is_array($A)) {
            $this->setVars($A);
        }

        $db = Database::getInstance();
        $qb = $db->conn->createQueryBuilder();

        if ($this->isNew) {
            // Creates a new ID if one not already provided
            $this->camp_id = COM_sanitizeID($this->camp_id, true);
            $allowed = 0;       // no existing campaign by this name allowed
            $qb->insert($_TABLES['bannercampaigns'])
                ->values([
                    'camp_id' => ':camp_id',
                    'description' => ':dscp',
                    'start' => ':start',
                    'finish' => ':finish',
                    'enabled' => ':enabled',
                    'hits' =>  ':hits',
                    'max_hits' => ':max_hits',
                    'impressions' => ':impressions',
                    'max_impressions' => ':max_impressions',
                    'owner_id' => ':owner_id',
                    'group_id' => ':group_id',
                    'perm_owner' => ':perm_owner',
                    'perm_group' => ':perm_group',
                    'perm_members' => ':perm_members',
                    'perm_anon' => ':perm_anon',
                    'tid' => ':tid',
                    'show_owner' => ':show_owner',
                    'show_admins' => ':show_admins',
                    'show_adm_pages' => ':show_adm_pages',
                ]);
        } else {
            $this->camp_id = COM_sanitizeID($this->camp_id, false);
            // If not changing the ID, then one existing record with this
            // camp_id is expected.
            $allowed = $this->camp_id == $this->oldID ? 1 : 0;

            // Not modifying the campaign ID, but it shouldn't be empty
            if ($this->camp_id == '')
                return $LANG_BANNER['err_missing_id'];

            $qb->update($_TABLES['bannercampaigns'])
               ->set('camp_id', ':camp_id')
               ->set('description', ':dscp')
               ->set('start', ':start')
               ->set('finish', ':finish')
               ->set('enabled', ':enabled')
               ->set('hits', ':hits')
               ->set('max_hits', ':max_hits')
               ->set('impressions', ':impressions')
               ->set('max_impressions', ':max_impressions')
               ->set('owner_id', ':owner_id')
               ->set('group_id', ':group_id')
               ->set('perm_owner', ':perm_owner')
               ->set('perm_group', ':perm_group')
               ->set('perm_members', ':perm_members')
               ->set('perm_anon', ':perm_anon')
               ->set('tid', ':tid')
               ->set('show_owner', ':show_owner')
               ->set('show_admins', ':show_admins')
               ->set('show_adm_pages', ':show_adm_pages')
               ->where('camp_id = :old_id')
               ->setParameter('old_id', $this->oldID, Database::STRING);
        }

        if ($db->getCount($_TABLES['bannercampaigns'], 'camp_id', $this->camp_id, Database::STRING) > $allowed) {
            return $LANG_BANNER['err_dup_id'];;
        }

        $qb->setParameter('camp_id', $this->camp_id, Database::STRING)
           ->setParameter('dscp', $this->dscp, Database::STRING)
           ->setParameter('enabled', $this->isEnabled(), Database::INTEGER)
           ->setParameter('hits', $this->hits, Database::INTEGER)
           ->setParameter('max_hits', $this->max_hits, Database::INTEGER)
           ->setParameter('impressions', $this->impressions, Database::INTEGER)
           ->setParameter('max_impressions', $this->max_impressions, Database::INTEGER)
           ->setParameter('owner_id', $this->owner_id, Database::INTEGER)
           ->setParameter('group_id', $this->group_id, Database::INTEGER)
           ->setParameter('perm_owner', $this->perm_owner, Database::INTEGER)
           ->setParameter('perm_group', $this->perm_group, Database::INTEGER)
           ->setParameter('perm_members', $this->perm_members, Database::INTEGER)
           ->setParameter('perm_anon', $this->perm_anon, Database::INTEGER)
           ->setParameter('tid', $this->tid, Database::STRING)
           ->setParameter('show_owner', $this->show_owner, Database::INTEGER)
           ->setParameter('show_admins', $this->show_admins, Database::INTEGER)
           ->setParameter('show_adm_pages', $this->show_adm_pages, Database::INTEGER);
        if (!is_null($this->pubStart)) {
            $qb->setParameter('start', $this->pubStart->toMySQL(true), Database::STRING);
        } else {
            $qb->setParameter('start', NULL, Database::INTEGER);
        }
        if (!is_null($this->pubEnd)) {
            $qb->setParameter('finish', $this->pubEnd->toMySQL(true), Database::STRING);
        } else {
            $qb->setParameter('finish', NULL, Database::INTEGER);
        }

        try {
            $status = $qb->execute();
            if ($this->camp_id != $this->oldID) {
                // Update banners that were associated with the old ID
                Banner::changeCampaignId($this->oldID, $this->camp_id);
            }
            return '';
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            return $LANG_BANNER['err_saving_item'];
        }
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

        $access = (int)$access;
        return COM_optionList(
            $_TABLES['bannercampaigns'],
            'camp_id,description',
            $sel,
            1,
            COM_getPermSQL('', 0, $access)
        );
    }


    /**
     * Get an array of Banner objects associated with this campaign.
     * Sets the internal Banner variable to an array of Banner objects.
     */
    public function getBanners() : void
    {
        global $_TABLES;

        $this->Banners = array();
        $db = Database::getInstance();
        try {
            $data = $db->conn->executeQuery(
                "SELECT bid FROM {$_TABLES['banner']}
                WHERE camp_id = ?",
                array($this->camp_id),
                array(Database::STRING)
            )->fetchAll(Database::ASSOCIATIVE);
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            $data = array();
        }

        foreach ($data as $A) {
            $this->Banners[] = new Banner($A['bid']);
        }
    }


    /**
     * Update the impression counter for this campaign.
     *
     * @param   string  $camp_id    Campaign ID
     */
    public static function updateImpressions(string $camp_id) : void
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $db->conn->executeUpdate(
                "UPDATE {$_TABLES['bannercampaigns']}
                SET impressions = impressions+1
                WHERE camp_id = ?",
                array($camp_id),
                array(Database::STRING)
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
    }


    /**
     * Update the hit counter for this campaign.
     *
     * @param   string  $camp_id    Campaign to update
     */
    public static function updateHits(string $camp_id) : void
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $db->conn->executeUpdate(
                "UPDATE {$_TABLES['bannercampaigns']}
                SET hits = hits+1
                WHERE camp_id = ?",
                array($camp_id),
                array(Database::STRING)
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
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
            $retval .= FieldList::edit(array(
                'url' => "$base_url/index.php?editcampaign=" . urlencode($A['camp_id']),
            ) );
            break;

        case 'enabled':
            $retval .= FieldList::checkbox(array(
                'name' => 'camp_ena_check',
                'id' => "togena{$A['camp_id']}",
                'checked' => $fieldvalue == 1,
                'onclick' => "BANR_toggleEnabled(this, '{$A['camp_id']}','campaign');",
            ) );
            break;

        case 'delete':
            if (self::isUsed($A['camp_id'])) {
                $retval .= FieldList::delete(array(
                    'delete_url' => "$base_url/index.php?delete=x&item=campaign&amp;camp_id={$A['camp_id']}",
                    'attr' => array(
                        'onclick' => "return confirm('{$LANG_BANNER['ok_to_delete']}');",
                    ),
                ) );
            } else {
                $retval .= FieldList::info(array(
                    'title' => $LANG_BANNER['cannot_delete'],
                ) );
            }
            break;

        case 'owner_id':
            $retval = COM_getDisplayName($A[$fieldname]);
            break;

        case 'camp_id':
            $retval = COM_createLink(
                $A['camp_id'],
                "{$base_url}/index.php?view=banners&camp_id=" . urlencode($A['camp_id'])
            );
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

