<?php
/**
 * Class to handle banner ads.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Banner;


/**
 * Define a class to deal with banners.
 * @package banner
 */
class Banner
{
    const TYPE_LOCAL    = 0;    // Locally-uploaded image
    const TYPE_REMOTE   = 1;    // Remote image url
    const TYPE_SCRIPT   = 2;    // Script, e.g. Google Adsense
    const TYPE_AUTOTAG  = 3;    // Autotag to be processed

    /** Banner record ID.
     * @var string */
    private $bid = '';

    /** Campaign record ID.
     * @var string */
    private $camp_id = '';

    /** Category ID.
     * @var string */
    private $cid = '';

    /** Topic ID.
     * @var string */
    private $tid = 'all';

    /** Holder for the original ID, used when saving edits.
     * @var string */
    private $oldID = '';

    /** Indicate whether this is a new banner or not.
     * @var boolean */
    private $isNew = 1;

    /** Indicate whether this is an admin or regular user.
     * @var boolean */
    private $isAdmin = 0;

    /** Owner (submitter) user ID.
     * @var integer */
    private $owner_id = 0;

    /** Group ID.
     * @var integer */
    private $group_id = 0;

    /** Owner permission.
     * @var ingeger */
    private $perm_owner = 3;

    /** Group permission.
     * @var ingeger */
    private $perm_group = 2;

    /** Members permission.
     * @var ingeger */
    private $perm_members = 2;

    /** Anonymous permission.
     * @var ingeger */
    private $perm_anon = 2;

    /** Number of clicks recorded for this banner.
     * @var integer */
    private $hits = 0;

    /** Maximum number of hits allowed before the banner stops showing.
     * @var integer */
    private $max_hits = 0;

    /** Number of times the banner has been shown.
     * @var integer */
    private $impressions = 0;

    /** Maximum number of impressions allowed for this banner.
     * @var integer */
    private $max_impressions = 0;

    /** Weight assigned to the banner. Higher = more likely to be shown.
     * @var integer */
    private $weight = 5;

    /** Type of banner ad.
     * @var integer */
    private $ad_type = 0;

    /** Date to start publishing.
     * @var object */
    private $pubstart = NULL;

    /** Date to end publishing.
     * @var object */
    private $pubend = NULL;

    /** Date banner creted.
     * @var object */
    private $date = NULL;

    /** Flag that the banner is allowed to be shown.
     * @var boolean */
    private $enabled = 1;

    /** Banner title.
     * @var string */
    private $title = '';

    /** Notes about the banner.
     * @var string */
    private $notes = '';

    /** Options from the serialized "options" DB field.
     * These depend on the banner type.
     * @var array */
    private $options = array();

    /** Database table name currently in use, submission vs. prod.
     * Default is prod.
     * @var string */
    private $table = 'banner';

    /** Holder for error messages to be returned to callers.
    * @var array */
    private $errors = array();

    /** Default options to be applied to banners.
     * @var array */
    private static $default_opts = array(
        'target' => '_blank',
        'rel' => 'sponsored,nofollow',
    );


    /**
     * Constructor.
     *
     * @param   string  $bid    Banner ID to retrieve, blank for empty class
     */
    public function __construct($bid='')
    {
        global $_USER, $_GROUPS, $_CONF_BANR;

        $bid = COM_sanitizeID($bid, false);

        if ($bid != '') {
            $this->Read($bid);
            $this->isNew = 0;
        } else {
            // Set defaults for new record
            $this->isNew        = 1;
            $this->owner_id     = $_USER['uid'];
            $this->weight       = (int)$_CONF_BANR['def_weight'];
            $this->perm_owner   = (int)$_CONF_BANR['default_permissions'][0];
            $this->perm_group   = (int)$_CONF_BANR['default_permissions'][1];
            $this->perm_members = (int)$_CONF_BANR['default_permissions'][2];
            $this->perm_anon    = (int)$_CONF_BANR['default_permissions'][3];
            $this->options = self::$default_opts;
            $this->setPubStart();
            $this->setPubEnd();
        }
    }


    /**
     * Get an instance of a banner by record ID or contents.
     *
     * @param   array|string    $bid    Banner ID or DB record
     * @return  object      Banner object
     */
    public static function getInstance($bid)
    {
        return new self($bid);
    }


    /**
     * Check if this is a new banner record, maybe not found in the DB.
     *
     * @return  integer     1 if new, 0 if existing
     */
    public function isNew()
    {
        return $this->isNew ? 1 : 0;
    }


    /**
     * Get the banner ID.
     *
     * @return  string      Banner ID
     */
    public function getBid()
    {
        return $this->bid;
    }


    /**
     * Get the category ID.
     *
     * @return  string      Category ID
     */
    public function getCid()
    {
        return $this->cid;
    }


    /**
     * Get the campaign ID.
     *
     * @return  string      Campaign id
     */
    public function getCampId()
    {
        return $this->camp_id;
    }


    /**
     * Get the starting date for publication.
     *
     * @return  string      Starting publication date
     */
    public function getPubStart($fmt='')
    {
        if ($fmt == '') {
            return $this->pubstart;
        } else {
            return $this->pubstart->format($fmt);
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
            return $this->pubend;
        } else {
            return $this->pubend->format($fmt);
        }
    }


    /**
     * Get the number of hits recorded for this banner.
     *
     * @return  integer     Banner hits
     */
    public function getHits()
    {
        return (int)$this->hits;
    }


    /**
     * Get the maximum number of hits allowed.
     *
     * return   integer     Maximum allowed hits
     */
    public function getMaxHits()
    {
        return (int)$this->max_hits;
    }


    /**
     * Get the owner's user ID.
     *
     * @return  integer     User ID
     */
    public function getOwnerId()
    {
        return (int)$this->owner_id;
    }


    /**
     * Set the banner ID.
     *
     * @param   string  $bid        Banner ID
     * @return  object  $this
     */
    public function setBID($bid)
    {
        $this->bid = COM_sanitizeID($bid, false);
        return $this;
    }


    /**
     * Set the campaign ID.
     *
     * @param   string  $camp_id    Campaign ID
     * @return  object  $this
     */
    public function setCampId($camp_id)
    {
        $this->camp_id = COM_sanitizeID($camp_id, false);
        return $this;
    }


    /**
     * Set the starting publication date/time.
     *
     * @param   string  $dt_str     MYSQL-formatted date/time string
     * @return  object  $this
     */
    public function setPubStart($dt_str=NULL)
    {
        global $_CONF;

        if (empty($dt_str)) {
            $dt_str = BANR_MIN_DATE;
        }
        $this->pubstart = new \Date($dt_str, $_CONF['timezone']);
        return $this;
    }


    /**
     * Set the ending publication date/time.
     *
     * @param   string  $dt_str     MYSQL-formatted date/time string
     * @return  object  $this
     */
    public function setPubEnd($dt_str=NULL)
    {
        global $_CONF;

        if (empty($dt_str)) {
            $dt_str = BANR_MAX_DATE;
        }
        $this->pubend = new \Date($dt_str, $_CONF['timezone']);
        return $this;
    }


    /**
     * Set the creation date.
     *
     * @param   string  $dt_str     MYSQL-formatted date/time string
     * @return  object  $this
     */
    public function setDate($dt_str)
    {
        global $_CONF;
        $this->date = new \Date($dt_str, $_CONF['timezone']);
        return $this;
    }


    /**
     * Set the admin flag.
     *
     * @param   boolean $isadmin    True or False
     * @return  object  $this
     */
    public function setAdmin($isadmin)
    {
        $this->isAdmin = $isadmin ? true : false;
        return $this;
    }


    /**
     * Sets the table in use, either submission or production.
     * Ensures that a valid table name is set
     *
     * @param   string  $table  Table key
     * @return  object  $this
     */
    public function setTable($table)
    {
        $this->table = $table == 'bannersubmission' ? 'bannersubmission' : 'banner';
        return $this;
    }


    /**
     * Get an option value.
     *
     * @param   string  $name       Option name
     * @param   mixed   $default    Default value
     * @return  mixed           Option value, $default if not set
     */
    public function getOpt($name, $default='')
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        } else {
            return $default;
        }
    }


    /**
     * Set the isNew flag to force saving as new.
     * Used when transferring a banner from the submission table to prod.
     *
     * @param   boolean $isnew  True or false
     * @return  object  $this
     */
    public function setIsNew($isnew)
    {
        $this->isNew = (bool)$isnew;
        return $this;
    }


    /**
     * Read a banner record from the database.
     *
     * @param   string  $bid    Banner ID to read (required)
     * @return  object  $this
     */
    public function Read($bid)
    {
        global $_TABLES;

        $A = DB_fetchArray(
            DB_query(
                "SELECT * FROM {$_TABLES[$this->table]}
                WHERE bid='".DB_escapeString($bid)."'"
            ),
            false
        );
        if (!empty($A)) {
            $this->isNew = false;
            $this->setVars($A, true);

            // Save the old ID for use in Update()
            $this->oldID = $this->bid;
        }
        return $this;
    }


    /**
     * Set the banner variables from the supplied array.
     * The array may be from a form ($_POST) or database record
     *
     * @see     self::_createHTMLTemplate()
     * @param   array   $A          Array of values
     * @param   boolean $fromDB     Indicates if reading from DB or submission
     * @return  object  $this
     */
    public function setVars($A='', $fromDB=false)
    {
        global $_CONF_BANR, $_CONF, $_USER;

        if (!is_array($A)) {
            return $this;
        }

        $this->setBID($A['bid']);
        $this->cid = $A['cid'];
        $this->setCampID($A['camp_id']);
        $this->ad_type = (int)$A['ad_type'];
        $this->notes = isset($A['notes']) ? $A['notes'] : '';
        $this->title = $A['title'];
        $this->tid = $A['tid'];
        if ($fromDB) {
            // Coming from the database
            $this->owner_id = (int)$A['owner_id'];
            $this->enabled = (int)$A['enabled'];
            $this->impressions = (int)$A['impressions'];
            $this->max_impressions = (int)$A['max_impressions'];
            $this->hits = (int)$A['hits'];
            $this->max_hits = (int)$A['max_hits'];
            $this->options = @unserialize($A['options']);
            if (!$this->options) {
                $this->options = self::$default_opts;
            }
            $this->weight = (int)$A['weight'];
            $this->setPubStart($A['publishstart'])
                ->setPubEnd($A['publishend'])
                ->setDate($A['date']);
        } else {
            // Coming from a form
            $this->options = self::$default_opts;
            $this->options['url'] = COM_sanitizeUrl($A['url'],
                                    array('http','https'));
            $this->options['image_url'] = COM_sanitizeUrl($A['image_url'],
                            array('http','https'));
            $this->options['filename'] = COM_checkHTML(
                            COM_checkWords($A['filename']));

            // Save the Adsense image code
            if (isset($A['ad_code']) && !empty($A['ad_code'])) {
                $this->options['ad_code'] = $A['ad_code'];
            }
            $this->options['alt'] = COM_checkHTML(COM_checkWords($A['alt']));
            $this->options['width'] = isset($A['width']) ? (int)$A['width'] : '';
            $this->options['height'] = isset($A['height']) ? (int)$A['height'] : '';
            $this->options['target'] = isset($A['target']) ? $A['target'] : '';

            // Assemble the dates from component parts
            if (!isset($A['start_dt_limit']) || $A['start_dt_limit'] != 1) {
                $this->setPubStart(0);
            } else {
                $this->setPubStart($A['start_date'] . ' ' . $A['start_time']);
            }
            if (!isset($A['end_dt_limit']) || $A['end_dt_limit'] != 1) {
                $this->setPubEnd(0);
            } else {
                $this->setPubEnd($A['end_date'] . ' ' . $A['end_time']);
            }

            // Only admins can set some values, use the defaults for others
            if (SEC_hasRights('banner.edit')) {
                $this->weight = (int)$A['weight'];
                $this->enabled = isset($A['enabled']) ? $A['enabled'] : 0;
                $this->impressions = (int)$A['impressions'];
                $this->max_impressions = (int)$A['max_impressions'];
                $this->hits = (int)$A['hits'];
                $this->max_hits = (int)$A['max_hits'];
                $this->owner_id = (int)$A['owner_id'];
            } else {
                $this->weight = (int)$_CONF_BANR['def_weight'];
                $this->owner_id = (int)$_USER['uid'];
            }

            // Create the HTML template for click tracking if this is
            // an HTML or Javascript ad.
            if ($this->ad_type == self::TYPE_SCRIPT) {
                $this->_createHTMLTemplate();
            }
        }
        return $this;
    }


    /**
     * Update the 'enabled' value for this banner.
     *
     * @param   integer $oldval Original value being changed
     * @return  integer     New value, or old value if an error occurred
     */
    public function toggleEnabled($oldval)
    {
        global $_TABLES;

        if ($this->isNew || !$this->hasAccess(3)) {
            return $oldval;
        }

        $newval = $oldval == 0 ? 1 : 0;
        DB_change($_TABLES['banner'],
                'enabled', $newval,
                'bid', DB_escapeString($this->bid));
        return DB_error() ? $oldval : $newval;
    }


    /**
     * Update the impression (display) count.
     *
     * @return  object  $this
     */
    public function updateImpressions()
    {
        global $_TABLES, $_CONF_BANR, $_USER;

        // Don't update the count for ads show to admins or owners, if
        // so configured.
        if (
            $this->isNew()
            ||
            ($_CONF_BANR['cntimpr_admins'] == 0 && plugin_isadmin_banner())
            ||
            ($_CONF_BANR['cntimpr_owner'] == 0 && $this->owner_id == $_USER['uid'])

        ) {
            return $this;
        }

        DB_query("UPDATE {$_TABLES['banner']}
                SET impressions=impressions+1
                WHERE bid='{$this->bid}'");

        Campaign::updateImpressions($this->camp_id);
        return $this;
    }


    /**
     * Increment the hit count.
     */
    public function updateHits()
    {
        global $_TABLES, $_CONF_BANR, $_USER;

        // Don't update the count for ads show to admins or owners, if
        // so configured.
        if (
            ($_CONF_BANR['cntclicks_admins'] == 0 && plugin_isadmin_banner())
            ||
            ($_CONF_BANR['cntclicks_owner'] == 0 && $this->owner_id == $_USER['uid'])
        ) {
            return;
        }

        // Update the banner and campaign hits if not excluded
        $sql = "UPDATE {$_TABLES['banner']}
                SET hits=hits+1
                WHERE bid='{$this->bid}'";
        DB_query($sql);
        Campaign::updateHits($this->camp_id);
        return $this;
    }


    /**
     * Delete the current banner.
     */
    public function Delete()
    {
        global $_TABLES, $_CONF_BANR, $_USER;

        $filename = $this->getOpt('filename');
        if (
            !empty($filename) &&
            file_exists($_CONF_BANR['img_dir'] . '/' . $filename)
        ) {
            @unlink($_CONF_BANR['img_dir'] . '/' . $filename);
        }

        DB_delete(
            $_TABLES[$this->table],
            'bid',
            DB_escapeString(trim($this->bid))
        );
        BANNER_auditLog("{$_USER['uid']} deleted banner id {$this->bid}");
    }


    /**
     * Returns the current user's access level to this banner.
     *
     * @return  integer     User's access level (1 - 3)
     */
    public function Access()
    {
        global $_USER;

        if ($this->isNew) {
            return 3;
        }
        $access = SEC_hasAccess(
            $this->owner_id, $this->group_id,
            $this->perm_owner, $this->perm_group,
            $this->perm_members, $this->perm_anon
        );
        return $access;
    }


    /**
     * Determines whether the current user has a given level of access
     * to this banner object.
     *
     * @see     Access()
     * @param   integer $level  Minimum access level required
     * @return  boolean     True if user has access >= level, false otherwise
     */
    public function hasAccess($level=3)
    {
        if ($this->Access() < $level) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Save the current banner object using the supplied values.
     *
     * @param   array   $A  Array of values from $_POST or database
     * @return  string      Error message, empty if successful
     */
    public function Save($A = array())
    {
        global $_CONF, $_GROUPS, $_TABLES, $_USER, $MESSAGE,
                $_CONF_BANR, $LANG12, $LANG_BANNER;

        if ($this->isNew) {
            if (
                COM_isAnonUser() ||
                (
                    (!isset($_CONF_BANR['usersubmit']) || $_CONF_BANR['usersubmit'] == 0) &&
                    !SEC_hasRights('banner.submit')
                )
            ) {
                return $LANG_BANNER['access_denied'];
            }
        } else {
            if (!plugin_isadmin_banner() &&
                !$this->hasAccess(3)) {
                return $LANG_BANNER['access_denied'];
            }
        }

        if (!empty($A)) {
            $this->setVars($A);
        }

        // Banner ID may be left blank, so generate one
        if ($this->bid == '') {
            $this->bid = COM_makesid();
        }

        // Make sure this isn't a duplicate ID.  If this is a user submission,
        // we also have to make sure this ID isn't in the main table
        // If updating a banner, check if the ID is in use, if it's changing
        $allowed = ($this->isNew || $this->bid != $this->oldID) ? 0 : 1;
        $num1 = DB_count($_TABLES['banner'], 'bid', $this->bid);
        if ($this->_isSubmission()) {
            $num2 = DB_count($_TABLES['bannersubmission'], 'bid', $this->bid);
        } else {
            $num2 = 0;
        }
        if ($num1 > $allowed || $num2 > 0) {
            return $LANG_BANNER['duplicate_bid'];
        }

        // Check required fields and return an error message if any
        // are missing.
        if (!$this->Validate($A)) {
            return $this->errors;
        }

        switch ($this->ad_type) {
        case self::TYPE_LOCAL:
            // Unset unused values
            unset($this->options['ad_code']);
            unset($this->options['image_url']);

            // Handle the file upload
            if (
                isset($_FILES['bannerimage']['name']) &&
                !empty($_FILES['bannerimage']['name'])
            ) {
                $Img = new Image($this->bid, 'bannerimage');
                $Img->uploadFiles();
                if ($Img->areErrors() > 0) {
                    $this->options['filename'] = '';
                    return $Img->printErrors(false);
                } else {
                    $this->options['filename'] = $Img->getFilename();
                }

                // Set the image dimensions in the banner record if either
                // is not specified.
                if (!empty($this->options['filename'])) {
                    $sizes = @getimagesize
                        ($_CONF_BANR['img_dir'] . '/' . $this->options['filename']
                    );
                    if (
                        $this->options['width'] == 0 ||
                        $this->options['width'] > $sizes[0]
                    ) {
                        $this->options['width'] = $sizes[0];
                    }
                    if (
                        $this->options['height'] == 0 ||
                        $this->options['height'] > $sizes[1]
                    ) {
                        $this->options['height'] = $sizes[1];
                    }
                }
            }
            break;

        case self::TYPE_REMOTE:
            unset($this->options['filename']);
            unset($this->options['ad_code']);
            break;

        case self::TYPE_SCRIPT:
            if (empty($this->options['url']))
                unset($this->options['url']);
        case self::TYPE_AUTOTAG:
            unset($this->options['filename']);
            unset($this->options['image_url']);
            unset($this->options['width']);
            unset($this->options['height']);
            unset($this->options['alt']);
            break;
        }

        if (empty($this->owner_id)) {
            // this is new banner from admin, set default values
            $this->owner_id = $_USER['uid'];
            $this->perm_owner = 3;
            $this->perm_group = 3;
            $this->perm_members = 2;
            $this->perm_anon = 2;
        }

        $access = $this->hasAccess(3);
        if ($access < 3) {
            COM_errorLog("User {$_USER['username']} tried to illegally submit or edit banner {$this->bid}.");
            return COM_showMessageText($MESSAGE[31], $MESSAGE[30]);
        }

        $options = serialize($this->options);

        // Determine if this is an INSERT or UPDATE
        if ($this->isNew) {
            $sql1 = "INSERT INTO {$_TABLES[$this->table]} SET
                date = '{$_CONF_BANR['_now']->toMySQL(true)}', ";
            $sql3 = '';
        } else {
            $sql1 = "UPDATE {$_TABLES[$this->table]} SET ";
            $sql3 = " WHERE bid='" . DB_escapeString($this->oldID) . "'";
        }
        $sql2 = "bid = '" . DB_escapeString($this->bid) . "',
                cid = '" . DB_escapeString($this->cid) . "',
                camp_id = '" . DB_escapeString($this->camp_id) . "',
                ad_type = {$this->ad_type},
                options = '" . DB_escapeString($options) . "',
                title = '" . DB_escapeString($this->title). "',
                notes = '" . DB_escapeString($this->notes). "',
                publishstart = '" . $this->pubstart->toMySQL(true) . "',
                publishend = '" . $this->pubend->toMySQL(true) . "',
                enabled = {$this->enabled},
                hits = {$this->hits},
                max_hits = {$this->max_hits},
                impressions = {$this->impressions},
                max_impressions = {$this->max_impressions},
                owner_id = {$this->owner_id},
                weight = {$this->weight},
                tid='" . DB_escapeString($this->tid) . "'";
        //echo "$sql1 $sql2 $sql3";die;
        DB_query($sql1 . $sql2 . $sql3);
        if (!DB_error()) {
            $category = DB_getItem($_TABLES['bannercategories'], "category",
                    "cid='{$this->cid}'");
            COM_rdfUpToDateCheck('banner', $category, $this->bid);
            if ($this->isNew && $_CONF_BANR['notification'] == 1) {
                // Notify the administrator
                $this->Notify();
            }
            return '';
        } else {
            return 'Database error saving banner';
        }
    }


    /**
     * Returns the banner id for a banner or group of banners.
     *
     * @param   array   $fields Fields to use in where clause
     * @return  array           Array of Banner ids, empty if none available
     */
    public static function getBannerIds($fields=array())
    {
        global $_TABLES, $_CONF_BANR, $_CONF, $_USER;

        $banners = array();

        // Determine if any ads at all should be displayed to this user
        if (!self::canShow()) {
            return $banners;
        }

        if (!is_array($fields)) $fields = array();
        $sql_cond = '';
        $limit_clause = '';
        $topic_sql = '';
        if (!isset($fields['topic'])) {
            // If not set, add the current topic for topic_sql
            $fields['topic'] = \Topic::currentID();
        }
        foreach ($fields as $field=>$value) {
            if (!is_array($value)) {
                $value = DB_escapeString($value);
            }
            switch(strtolower($field)) {
            case 'type':
            case 'cid':
                if (is_array($value)) {
                    $sql_vals = array();
                    foreach ($value as $t) {
                        $t = DB_escapeString($t);
                        $sql_vals[] = "c.{$field} = '$t'";
                    }
                    $sql_vals = implode(' OR ', $sql_vals);
                } else {
                    $t = DB_escapeString($value);
                    $sql_vals = "c.{$field} = '$t'";
                }
                $sql_cond = " AND ($sql_vals) ";
                break;
            case 'category':
            case 'centerblock':
                $sql_cond .= " AND c.{$field} = '$value'";
                break;
            case 'tid':
            case 'topic':
                $tids = array('all');
                if ($value != '' && $value != 'all') {
                    // "All" is already included, and $value is already escaped
                    $tids[] = $value;
                }
                if (COM_onFrontpage()) {
                    $tids[] = 'homeonly';
                }
                $tids = "'" . implode("','", $tids) . "'";
                $topic_sql .= " AND c.tid IN ($tids)
                        AND camp.tid IN ($tids)
                        AND b.tid IN ($tids)";
                break;
            case 'campaign':
                if ($value != '') {
                    $sql_cond .= " AND camp.camp_id='$value' ";
                }
                break;
            case 'limit':
                $value = (int)$value;
                $limit_clause = " LIMIT $value";
                break;
            default:
                $field = DB_escapeString($field);
                $sql_cond .= " AND b.{$field} = '$value'";
                break;
            }
        }

        // Eliminate ads owned by the current user
        if ($_CONF_BANR['adshow_owner'] == 0) {
            $sql_cond .= " AND b.owner_id <> '" . (int)$_USER['uid'] . "'";
        }
        $now = $_CONF_BANR['_now']->toMySQL(true);
        $sql = "SELECT b.bid, weight*RAND() as score
                FROM {$_TABLES['banner']} b
                LEFT JOIN {$_TABLES['bannercategories']} c
                    ON c.cid = b.cid
                LEFT JOIN {$_TABLES['bannercampaigns']} camp
                    ON camp.camp_id = b.camp_id
                WHERE b.enabled = 1
                AND c.enabled = 1
                AND camp.enabled = 1
                AND (b.publishstart < '$now')
                AND (b.publishend  > '$now')
                AND (b.max_hits = 0 OR b.hits < b.max_hits)
                AND (b.max_impressions = 0 OR b.impressions < b.max_impressions)
                AND (camp.start IS NULL OR camp.start < '$now')
                AND (camp.finish IS NULL OR camp.finish > '$now')
                AND (camp.hits < camp.max_hits OR camp.max_hits = 0)
                AND (camp.max_impressions = 0
                    OR camp.impressions < camp.max_impressions) "
                . COM_getPermSQL('AND', 0, 2, 'camp')
                . SEC_buildAccessSql('AND', 'c.grp_view')
                . $sql_cond
                . $topic_sql
                . ' ORDER BY score DESC '
                . $limit_clause;
        //echo $sql;die;
        //COM_errorLog($sql);
        $result = DB_query($sql, 1);
        if ($result) {
            while ($A = DB_fetchArray($result, false)) {
                $banners[] = $A['bid'];
            }
        }
        return $banners;
    }


    /**
     * Returns an array of the newest banners for the "What's New" block.
     *
     * @return  array   Array of banner records
     */
    public static function GetNewest()
    {
        global $_TABLES, $_CONF_BANR;

        $A = array();
        if (!self::CanShow()) {
            return $A;
        }
        $now = $_CONF_BANR['_now']->toMySQL(true);

        $sql = "SELECT bid
                FROM {$_TABLES['banner']}
                WHERE (date >= (DATE_SUB('$now',
                        INTERVAL {$_CONF_BANR['newbannerinterval']} DAY)))
                AND (publishstart < '$now')
                AND (publishhend > '$now') " .
                COM_getPermSQL('AND') .
                ' ORDER BY date DESC LIMIT 15';
        $result = DB_query($sql);
        while ($row = DB_fetchArray($result)) {
            $A[] = $row['bid'];
        }
        return $A;
    }


    /**
     * Creates the banner image and href link for display.
     * The $link parameter is true to create the full banner ad including
     * the link. False will show only the image, e.g. for admin listings.
     * Local and remotely-hosted images are sized based on the category size
     * settings.
     *
     * @param   string  $title      Banner Title, optional
     * @param   integer $width      Image width, optional
     * @param   integer $height     Image height, optional
     * @param   boolean $link       True to create link, false for only image
     * @return  string              Banner image URL, with or without link
     */
    public function buildBanner($title = '', $width=0, $height=0, $link = true)
    {
        global $_CONF, $LANG_DIRECTION, $_CONF_BANR, $LANG_BANNER;

        $retval = '';

        if ($this->isNew()) {
            // in case an invalid banner ID was requested.
            return $retval;
        }

        $alt = $this->getOpt('alt');
        if (empty($title) && !empty($alt)) {
            $title = $alt;
        }

        // Set the ad URL to the portal page only if there is a dest. URL
        $url = $this->getOpt('url');
        if (!empty($url)) {
            $url = COM_buildUrl(BANR_URL . '/portal.php?id=' . $this->bid);
        }
        $a_attr = array(
            'target' => $this->getOpt('target', '_blank'),
            'rel' => 'sponsored,nofollow',
        );
        $img_attr = array(
            'class' => 'banner_img',
        );
        if (!empty($title)) {
            $img_attr['title'] = htmlspecialchars($title);
            $img_attr['data-uk-tooltip'] = '';
        }

        $C = new Category($this->cid);
        $width = (int)$this->getOpt('width');
        if ($width == 0 && $C->getMaxWidth() > 0) {
            $width = min($width, $C->getMaxWidth());
        }
        $height = (int)$this->getOpt('height');
        if ($height == 0 && $C->getMaxHeight() > 0) {
            $height = min($height, $C->getMaxHeight());
        }

        switch ($this->ad_type) {
        case self::TYPE_LOCAL:
            // A bit of a kludge until LGLIB is updated for everyone.
            // The service function returns the image width and height as well
            // as the url.
            $filename = $this->getOpt('filename');
            $status = LGLIB_invokeService('lglib', 'imageurl',
                array(
                    'filepath' => $_CONF_BANR['img_dir'] . '/' . $filename,
                    'width'     => $width,
                    'height'    => $height,
                ),
                $output, $svc_msg);
            if ($status == PLG_RET_OK) {
                $img_attr['width'] = $output['width'];
                $img_attr['height'] = $output['height'];
                $img = $output['url'];
            }
            if (!empty($img)) {
                $retval = COM_createImage($img, $alt, $img_attr);
            }
            break;

        case self::TYPE_REMOTE:
            $img = $this->options['image_url'];
            if ($img != '') {
                $img_attr['height'] = $height;
                $img_attr['width'] = $width;
                $retval = COM_createImage($img, $alt, $img_attr);
            }
            break;

        case self::TYPE_SCRIPT:
            if ($link == true) {
                $retval = $this->options['ad_code'];
            } else {
                $retval = $LANG_BANNER['ad_is_script'];
            }
            break;

        case self::TYPE_AUTOTAG:
            $retval = PLG_replaceTags($this->options['ad_code']);
            break;
        }
        if ($link && !empty($url) && !empty($retval)) {
            $retval = COM_createLink($retval, $url, $a_attr);
        }
        return $retval;
    }


    /**
     * Determine the maximum number of days that a user may run an ad.
     * Based on the account balance, unless either purchasing is disabled
     * or the user is exempt (like administrators).
     *
     * @param   integer $uid    User ID, current user if zero
     * @return  integer         Max ad days available, -1 if unlimited
     */
    public function MaxDaysAvailable($uid=0)
    {
        global $_TABLES, $_CONF_BANR, $_USER, $_GROUPS;

        if (!$_CONF_BANR['purchase_enabled'])
            return -1;

        $uid = (int)$uid;
        if ($uid == 0)
            $uid = $_USER['uid'];

        foreach ($_CONF_BANR['purchase_exclude_groups'] as $ex_grp) {
            if (array_key_exists($ex_grp, $_GROUPS)) {
                return -1;
            }
        }

        $max_days = (int)DB_getItem($_TABLES['banneraccount'],
                        'days_balance', "uid=$uid");
        return $max_days;
    }


    /**
     * Validate this banner's url.
     *
     * @return  string  Response, or empty if no test performed.
     */
    public function validateUrl()
    {
        global $LANG_BANNER_STATUS, $LANG_BANNER;

        // Have to have a valid url to check
        if ($this->options['url'] == '') {
            return 'n/a&nbsp;<i class="tooltip uk-icon uk-icon-question-circle" title="' .
                $LANG_BANNER['html_status_na'] .
                '"></i>';
        }

        // Get the header and response code
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $this->options['url'],
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
            )
        );
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (array_key_exists($response, $LANG_BANNER_STATUS)) {
            return $response . ' ' . $LANG_BANNER_STATUS[$response];
        } else {
            return $LANG_BANNER['unknown'];
        }
    }


    /**
     * Creates the edit form.
     *
     * @param   string  $mode   Type of editing being done
     * @return  string          HTML for edit form
     */
    public function Edit($mode = 'edit')
    {
        global $_CONF, $_GROUPS, $_TABLES, $_USER, $_CONF_BANR, $_PLUGINS,
            $LANG_ACCESS, $MESSAGE, $LANG_BANNER, $LANG_ADMIN,
            $LANG12, $_SYSTEM;

        $retval = '';

        switch ($mode) {
        case 'edit':
        case 'editbanner':
            $saveaction = 'save';
            $cancel_url = $this->isAdmin ? BANR_ADMIN_URL . '/index.php' :
                BANR_RUL . '/index.php';
            break;
        case 'submit':
            $saveaction = 'savesubmission';
            $cancel_url = BANR_URL . '/index.php';
            break;

        case 'moderate':
            $saveoption = $LANG_ADMIN['moderate'];  // Save & Approve
            //$sub_type = '<input type="hidden" name="type" value="submission" />';
            $cancel_url = $_CONF['site_admin_url'] . '/moderation.php';
            break;
        }

        $T = new \Template(BANR_PI_PATH . '/templates/');
        $T->set_file(array(
            'editor' => "bannerform.thtml",
            'tips' => 'tooltipster.thtml',
        ) );

        $T->set_var(array(
            'help_url'      => BANNER_docUrl('bannerform'),
            //'submission_option' => $sub_type,
            //'lang_save'     => $saveoption,
            'cancel_url'    => $cancel_url,
        ));

        $weight_select = '';
        if ($this->isAdmin) {
            $T->set_var('action_url', BANR_ADMIN_URL . '/index.php');
            for ($i = 1; $i < 11; $i++) {
                $sel = $i == $this->weight ? 'selected="selected"' : '';
                $weight_select .= "<option value=\"$i\" $sel>$i</option>\n";
            }
        } else {
            $T->set_var('action_url', BANR_URL . '/index.php');
        }

        $access = $this->Access();
        if ($access == 0 || $access == 2) {
            $retval .= COM_startBlock(
                $LANG_BANNER['access_denied'], '',
                COM_getBlockTemplate ('_msg_block', 'header')
            );
            $retval .= $LANG_BANNER['access_denied_msg'];
            $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
            COM_accessLog("User {$_USER['username']} tried to illegally submit or edit banner {$this->bid}.");
            return $retval;
        }

        if (!$this->isNew) {
            // Calculate display dimensions
            $disp_img = $this->BuildBanner('', 0, 0, false);
            $T->set_var('disp_img', $disp_img);
            if (SEC_hasRights('banner.edit')) {
                $T->set_var('can_delete', 'true');
            }
        } else {
            $T->set_var('disp_img', '');
            $this->bid = COM_makeSid();
            //$this->pubstart = 0;
            //$this->pubend = 0;
        }

        $T->set_var('banner_id', $this->bid);
        $T->set_var('old_banner_id', $this->oldID);

        $camp_select = Campaign::Dropdown($this->camp_id);
        if (empty($camp_select)) {
            COM_setMsg("Access Denied");
            COM_refresh($cancel_url);
        }

        // Ad Type Selection
        $adtype_select = '';
        foreach ($LANG_BANNER['ad_types'] as $value=>$text) {
            $sel = $this->ad_type == $value ? ' selected="selected"' : '';
            $adtype_select .= "<option value=\"$value\"$sel>$text</option>\n";
        }

        $T->set_var(array(
            'banner_title' => htmlspecialchars($this->title),
            'max_url_length' => 255,
            'category_options' => Category::Dropdown(0, $this->cid),
            'campaign_options' => $camp_select,
            'banner_hits' => $this->hits,
            'banner_maxhits' => $this->max_hits,
            'impressions'   => $this->impressions,
            'max_impressions'   => $this->max_impressions,
            'ena_chk' => $this->enabled == 1 ? ' checked="checked"' : '',
            'image_url' => $this->getOpt('image_url'),
            'alt'   => $this->getOpt('alt'),
            'width' => $this->getOpt('width'),
            'height' => $this->getOpt('height'),
            'target_url' => $this->getOpt('url'),
            'ad_code'   => $this->getOpt('ad_code'),
            'adtype_select' => $adtype_select,
            'filename' => $this->getOpt('filename'),
            'weight_select' => $weight_select,
            'sel'.$this->options['target'] => 'selected="selected"',
            'req_item_msg' => $LANG_BANNER['req_item_msg'],
            'perm_msg' => $LANG_ACCESS['permmsg'],
            'start_date' => $this->getPubStart('Y-m-d'),
            'start_time' => $this->getPubStart('H:i:s'),
            'end_date' => $this->getPubEnd('Y-m-d'),
            'end_time' => $this->getPubEnd('H:i:s'),
            'saveaction' => $saveaction,
        ));

        foreach (Category::getAll() as $C) {
            if (!$C->isEnabled()) continue;
            $cats[$C->getCid()] = array(
                'img_width' => $C->getMaxWidth(),
                'img_height' => $C->getMaxHeight(),
            );
        }
        $T->set_var('cats_json', json_encode($cats));

        if (!isset($this->tid)) {
            $this->tid = 'all';
        }
        $topics = COM_topicList('tid,topic', $this->tid, 1, true);
        $T->set_var('topic_list', $topics);
        $alltopics = '<option value="all"';
        if ($this->tid == 'all') {
            $alltopics .= ' selected="selected"';
        }
        $alltopics .= '>' . $LANG_BANNER['all'] . '</option>' . LB;
        $T->set_var('topic_selection',  $alltopics . $topics);

        // user access info
        if ($this->owner_id < 2) {
            $this->owner_id = $_USER['uid'];
        }

        if (plugin_isadmin_banner()) {
            $T->set_var(array(
                'isAdmin'        => 'true',
                'owner_dropdown' => COM_optionList($_TABLES['users'],'uid,username',
                                $this->owner_id, 1, 'uid <> 1'),
                'banner_ownerid' => $this->owner_id,
                'ownername'     => COM_getDisplayName($this->owner_id),
            ) );
        } else {
            $T->set_var(array(
                'isAdmin'       => '',
                'owner_id'      => $this->owner_id,
            ) );
        }

        $T->set_var('gltoken_name', CSRF_TOKEN);
        $T->set_var('gltoken', SEC_createToken());
        if ($this->pubstart->toMySQL(true) == BANR_MIN_DATE) {
            $T->set_var(array(
                'start_dt_limit_chk'    => '',
                'startdt_sel_show'      => 'none',
                'startdt_txt_show'      => '',
            ) );
        } else {
            $T->set_var(array(
                'start_dt_limit_chk'    => 'checked="checked"',
                'startdt_sel_show'      => '',
                'startdt_txt_show'      => 'none',
            ) );
        }
        if ($this->pubend->toMySQL(true) == BANR_MAX_DATE) {
            $T->set_var(array(
                'end_dt_limit_chk'      => '',
                'enddt_sel_show'        => 'none',
                'enddt_txt_show'        => '',
            ) );
        } else {
            $T->set_var(array(
                'end_dt_limit_chk'      => 'checked="checked"',
                'enddt_sel_show'        => '',
                'enddt_txt_show'        => 'none',
            ) );
        }
        $T->parse('tooltipster', 'tips');
        $T->parse('output', 'editor');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Validate that the required fields are filled in.
     *
     * @param   array   $A  All form variables
     * @return  boolean     True if valid, False otherwise
     */
    public function Validate($A)
    {
        global $LANG_BANNER;

        $this->errors = array();

        // Check that appropriate ad content has been added
        switch ($A['ad_type']) {
        case self::TYPE_LOCAL:
            if (empty($_FILES)) {
                $this->errors[] = $LANG_BANNER['err_missing_upload'];
            }
            break;
        case self::TYPE_REMOTE:
            if (COM_sanitizeUrl($A['image_url'], array('http','https')) == '') {
                $this->errors[] = $LANG_BANNER['err_invalid_image_url'];
            }
            break;
        case self::TYPE_SCRIPT:
        case self::TYPE_AUTOTAG:
            if (empty($A['ad_code'])) {
                $this->errors[] = $LANG_BANNER['err_missing_adcode'];
            }
            break;
        }
        return empty($this->errors) ? true : false;
    }


    /**
     * Send an email notification for a new submission.
     */
    public function Notify()
    {
        global $_CONF, $_TABLES, $LANG_BANNER, $LANG08;

        $mailsubject = $_CONF['site_name'] . ' ' . $LANG_BANNER['banner_submissions'];
        $mailbody = $LANG_BANNER['title'] . ": $this->title\n";

        if ($this->table == 'bannersubmission') {
            $mailbody .= "{$LANG_BANNER['banner_submissions']} <{$_CONF['site_admin_url']}/moderation.php>\n\n";
        } else {
            $mailbody .= "{$LANG_BANNER['pi_name']} <" . BANR_URL .
                '/index.php?category=' . urlencode ($A['category']) . ">\n\n";
        }

        $mailbody .= "\n------------------------------\n";
        $mailbody .= "\n$LANG08[34]\n";
        $mailbody .= "\n------------------------------\n";

        COM_mail($_CONF['site_mail'], $mailsubject, $mailbody);
    }


    /**
     * Determine if banners should be shown on this page or to this user.
     * This is based on global settings, not banner permissions.
     *
     * @return  boolean     True to show banners, False to not.
     */
    public static function canShow()
    {
        global $_CONF_BANR, $_CONF, $_USER;

        // Set some static variables since this function can be called
        // multiple times per page load.
        static $in_admin_url = NULL;
        //static $is_blocked_useragent = NULL;
        //static $is_blocked_ip = NULL;
        $sess_var = 'glf_banr_canshow';

        // Check if this is an admin URL and the banner should not be shown.
        if ($_CONF_BANR['show_in_admin'] == 0) {
            if ($in_admin_url === NULL) {
                $urlparts = parse_url($_CONF['site_admin_url']);
                if (stristr($_SERVER['REQUEST_URI'], $urlparts['path']) != false) {
                    $in_admin_url = true;
                } else {
                    $in_admin_url = false;
                }
            }
            if ($in_admin_url) {
                return false;
            }
        }

        // See if this is a banner admin, and we shouldn't show it.
        // plugin_isadmin_banner() stores a static var, so it's low overhead
        if (
            $_CONF_BANR['adshow_admins'] == 0 &&
            plugin_isadmin_banner()
        ) {
            return false;
        }

        // Get the status from a session var, if it has been set.
        $canshow = SESS_getVar($sess_var);
        if ($canshow !== 0) {
            return $canshow;
        }

        // Now check if this user or IP address is in the blocked lists
        /*if (isset($_USER['uid']) && is_array($_CONF_BANR['users_dontshow'])) {
            if (in_array($_USER['uid'], $_CONF_BANR['users_dontshow'])) {
                return false;
            }
        }*/

        if (is_array($_CONF_BANR['ipaddr_dontshow'])) {
            $is_blocked_ip = false;
            foreach ($_CONF_BANR['ipaddr_dontshow'] as $addr) {
                if (empty($addr)) continue;
                if (strstr($_SERVER['REMOTE_ADDR'], $addr)) {
                    $is_blocked_ip = true;
                    break;
                }
            }
            if ($is_blocked_ip) {
                SESS_setVar($sess_var, false);
                return false;
            }
        }

        if (is_array($_CONF_BANR['uagent_dontshow'])) {
            $is_blocked_useragent = false;
            foreach ($_CONF_BANR['uagent_dontshow'] as $agent) {
                if (empty($agent)) continue;
                if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
                    $is_blocked_useragent = true;
                    break;
                }
            }
            if ($is_blocked_useragent)  {
                SESS_setVar($sess_var, false);
                return false;
            }
        }

        if (
            isset($_CONF_BANNER['header_dontshow']) &&
            is_array($_CONF_BANR['header_dontshow'])
        ) {
            foreach ($_CONF_BANR['header_dontshow'] as $header) {
                if (isset($_SERVER[$header])) {
                    SESS_setVar($sess_var, false);
                    return false;
                }
            }
        }

        // Allow the site admin to implement a custom banner control function
        if (function_exists('CUSTOM_banner_control')) {
            if (CUSTOM_banner_control() == false) {
                SESS_setVar($sess_var, false);
                return false;
            }
        }

        // Passed all the tests, ok to show banners
        SESS_setVar($sess_var, true);
        return true;
    }


    /**
     * Create the HTML template for javascript-based banners.
     *
     * @return  string  HTML for the banner
     */
    private function _createHTMLTemplate()
    {
        $buffer = $this->options['ad_code'];
        if (empty($buffer)) {
            return;
        }

        // Put our click URL and our target parameter in all anchors...
        // The regexp should handle ", ', \", \' as delimiters
        if (preg_match_all(
                '#<a(.*?)href\s*=\s*(\\\\?[\'"])http(.*?)\2(.*?) *>#is',
                $buffer, $m
            )
        ) {
            foreach ($m[0] as $k => $v) {
                // Remove target parameters
                $m[4][$k] = trim(
                    preg_replace(
                        '#target\s*=\s*(\\\\?[\'"]).*?\1#i',
                        '', $m[4][$k]
                    )
                );
                $urlDest = preg_replace(
                    '/%7B(.*?)%7D/', '{$1}',
                    "http" . $m[3][$k]
                );
                //$buffer = str_replace($v, "<a{$m[1][$k]}href={$m[2][$k]}{clickurl}$urlDest{$m[2][$k]}{$m[4][$k]} target={$m[2][$k]}{target}{$m[2][$k]}>", $buffer);
                $buffer = str_replace(
                    $v,
                    "<a{$m[1][$k]}href={$m[2][$k]}{clickurl}{$m[2][$k]}{$m[4][$k]} target={$m[2][$k]}{target}{$m[2][$k]}>",
                    $buffer
                );
            }
            $this->options['url'] = $urlDest;
            $this->options['htmlTemplate'] = $buffer;
        }
    }


    /**
     * See if this is a banner submission or using the prod table.
     *
     * @return  boolean     True if submission, False if prod
     */
    private function _isSubmission()
    {
        return $this->table == 'bannersubmission' ? true : false;
    }


    /**
     * Get the AM/PM time selector for publication start/end fields.
     * Same function as COM_getAmPmFormSelection(), but sets the "id"
     * attribute of the field the same as the "name" to allow
     * the datepicker to update the field.
     *
     * @param   string  $name       Name of field, also used for the ID
     * @param   string  $selected   "am" or "pm"
     * @return  string      HTML for selection
     */
    private static function getAmPmFormSelection($name, $selected = '')
    {
        global $_CONF;

        $retval = '';

        if ( isset( $_CONF['hour_mode'] ) && ( $_CONF['hour_mode'] == 24 )) {
            $retval = '';
        } else {
            if ( empty( $selected )) {
                $selected = date( 'a' );
            }

            $retval .= '<select id="' . $name . '" name="' . $name . '">' . LB;
            $retval .= '<option value="am"';
            if ($selected == 'am') {
                $retval .= ' selected="selected"';
            }
            $retval .= '>am</option>' . LB . '<option value="pm"';
            if ($selected == 'pm') {
                $retval .= ' selected="selected"';
            }
            $retval .= '>pm</option>' . LB . '</select>' . LB;
        }
        return $retval;
    }


    /**
     * Create the list.
     *
     * @param   boolean $isadmin    True for and admin list, False for a user
     * @return  string      HTML for the banner list
     */
    public static function adminList($isadmin=false, $camp_id='')
    {
        global $LANG_ADMIN, $LANG_BANNER, $_USER,
                 $_TABLES, $_CONF, $_CONF_BANR;

        USES_lib_admin();

        $uid = (int)$_USER['uid'];
        $retval = '';
        $form_arr = array();
        $header_arr = array(
            array(
                'text' => $LANG_BANNER['edit'],
                'field' => 'edit',
                'sort' => false,
                'align' => 'center',
            ),
            array(
                'text' => $LANG_BANNER['enabled'],
                'field' => 'enabled',
                'align' => 'center',
                'sort' => false,
            ),
            array(
                'text' => $LANG_BANNER['banner_id'],
                'field' => 'bid',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['banner_title'],
                'field' => 'title',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['banner_cat'],
                'field' => 'category',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['weight'],
                'field' => 'weight',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['pubstart'],
                'field' => 'publishstart',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['pubend'],
                'field' => 'publishend',
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

        $is_admin = $isadmin ? 1 : 0;
        /*if ($is_admin) {;
            $validate = '';
            $token = SEC_createToken();

            if (isset($_POST['validate'])) {
                $header_arr[] = array(
                    'text' => $LANG_BANNER['html_status'],
                    'field' => 'dovalidate',
                    'sort' => false
                );
            } else {
                $dovalidate_url = BANR_ADMIN_URL . '/index.php';
                    //'/index.php?validate=validate&amp;'. CSRF_TOKEN.'='.$token;
                $dovalidate_text = '<button class="lgButton green" name="validate">' .
                    $LANG_BANNER['validate_now'] . '</button>';
                $form_arr['top'] = COM_createLink($dovalidate_text, $dovalidate_url);
                $header_arr[] = array(
                    'text' => $LANG_BANNER['html_status'],
                    'field' => 'beforevalidate',
                    'sort' => false
                );
            }
            $text_arr = array(
                'has_extras' => true,
                'form_url' => BANR_ADMIN_URL . '/index.php?item=banner',
            );
        }*/

        $text_arr = array(
            'has_extras' => true,
            'form_url' => BANR_ADMIN_URL . '/index.php?banners',
        );

        $options = array(
            'chkdelete' => 'true',
            'chkfield' => 'bid',
        );
        $defsort_arr = array(
            'field' => 'weight',
            'direction' => 'desc',
        );
        $where = '';
        if ($camp_id != '') {
            $where = " AND b.camp_id = '" . DB_escapeString($camp_id) . "'";
        }
        $query_arr = array(
            'table' => 'banner',
            'sql' => "SELECT
                    b.bid AS bid, b.cid as cid, b.title AS title, b.weight,
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
            'query_fields' => array(
                'title', 'category',
                'b.publishstart', 'b.publishend', 'b.hits',
            ),
            'default_filter' => $where,
        );

        $base_url = $isadmin ? BANR_ADMIN_URL : BANR_URL;
        $retval .= COM_createLink($LANG_BANNER['new_banner'],
            $base_url . '/index.php?editbanner=x',
            array(
                'class' => 'uk-button uk-button-success',
                'style' => 'float:left',
            )
        );
        $retval .= ADMIN_list(
            'banner',
            array(__CLASS__,  'getAdminField'),
            $header_arr, $text_arr, $query_arr,
            $defsort_arr, '', '', $options, $form_arr
        );
        return $retval;
    }


    /**
     * Get the correct display for a single field in the banner admin list.
     *
     * @param   string  $fieldname  Field variable name
     * @param   string  $fieldvalue Value of the current field
     * @param   array   $A          Array of all field names and values
     * @param   array   $icon_arr   Array of system icons
     * @return  string              HTML for field display within the list cell
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF, $LANG_ACCESS, $_CONF_BANR, $LANG_BANNER;

        $retval = '';

        $base_url = $A['isAdmin'] == 1 ? BANR_ADMIN_URL : BANR_URL;

        switch($fieldname) {
        case 'edit':
            $retval = COM_createLink(
                $_CONF_BANR['icons']['edit'],
                $base_url . '/index.php?editbanner&amp;bid=' .$A['bid']
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
            $retval = COM_createLink(
                $_CONF_BANR['icons']['delete'],
                "$base_url/index.php?bid={$A['bid']}&delete=banner",
                array(
                     'onclick' => "return confirm('Do you really want to delete this item?');",
                 )
             );
            break;

        case 'dovalidate':
            $B = new self($A['bid']);
            $retval = $B->validateURL();
            break;

        /*case 'beforevalidate';
            $retval = $LANG_BANNER['before_validate'];
            break;

        case 'camp_id':
            $retval = COM_createLink(
                $A['camp_id'],
                "{$base_url}/index.php?campaigns=x&camp_id=" . urlencode($A['camp_id'])
            );
            break;*/

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

}

?>
