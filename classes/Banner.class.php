<?php
/**
*   Class to handle banner ads.
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
*   Define a class to deal with banners
*   @package banner
*/
class Banner
{
    /** Holder for the original ID, used when saving edits
    *   @var string */
    var $oldID;

    /** Indicate whether this is a new banner or not
     *  @var boolean */
    var $isNew;

    /** Indicate whether this is an admin or regular user
     *  @var boolean */
    var $isAdmin;

    /** Holder for the banner record properties
    *   @var array */
    var $properties = array();

    /**
    *   Options from the serialized "options" DB field.
    *   These depend on the banner type.
    *   @var array */
    var $options = array();

    /** Database table name currently in use, submission vs. prod
    *   @var string */
    var $table;

    /** Holder for error messages to be returned to callers
    *   @var array */
    var $errors = array();

    /**
    *   Constructor
    *
    *   @param  string  $bid    Banner ID to retrieve, blank for empty class
    *   @param  string  $table  Table, e.g. Submission or prod
    */
    public function __construct($bid='', $table='')
    {
        global $_USER, $_GROUPS, $_CONF_BANR;

        $bid = COM_sanitizeID($bid, false);
        $this->setTable($table);

        if ($bid != '') {
            $this->Read($bid);
            $this->isNew = false;
        } else {
            // Set defaults for new record
            $this->isNew        = true;
            $this->enabled      = 1;
            $this->owner_id     = $_USER['uid'];
            $this->weight       = $_CONF_BANR['def_weight'];
            $this->perm_owner   = $_CONF_BANR['default_permissions'][0];
            $this->perm_group   = $_CONF_BANR['default_permissions'][1];
            $this->perm_members = $_CONF_BANR['default_permissions'][2];
            $this->perm_anon    = $_CONF_BANR['default_permissions'][3];
            $this->hits         = 0;
            $this->max_hits     = 0;
            $this->impressions  = 0;
            $this->max_impressions = 0;
            $this->tid          = 'all';
            $this->options = array(
                'target' => '_blank',
            );
        }
    }


    /**
    *   Setter function. Set a value in the properties array.
    *
    *   @param  string  $key    Name of property to set
    *   @param  mixed   $value  Value to set
    */
    public function __set($key, $value)
    {
        global $_CONF, $_CONF_BANR;

        switch ($key) {
        case 'owner_id':
        case 'hits':
        case 'max_hits':
        case 'impressions':
        case 'max_impressions':
        case 'weight':
        case 'ad_type':
            $this->properties[$key] = (int)$value;
            break;

        case 'bid':
        case 'camp_id':
            $this->properties[$key] = COM_sanitizeID($value, false);
            break;

        case 'cid':
        case 'tid':
            $this->properties[$key] = trim($value);
            break;

        case 'publishstart':
        case 'publishend':
            if (!$value) {      // zero or null
                if ($key == 'publishstart') {
                    $value = BANR_MIN_DATE;
                } else {
                    $value = BANR_MAX_DATE;
                }
            }
        case 'date':
            $this->properties[$key] = new \Date($value, $_CONF['timezone']);
            break;

        case 'enabled':
            $this->properties[$key] = $value == 1 ? 1 : 0;
            break;

        /*case 'options':
            if (!is_array($value)) {
                $value = @unserialize($value);
                if (!$value) $value = array();
            }
            $this->properties[$key] = $value;
            break;
        */

        case 'title':
        case 'notes':
            $this->properties[$key] = COM_checkHTML(COM_checkWords($value));
            break;
        }
    }


    /**
    *   Getter function. Returns a property value
    *
    *   @param  string  $key    Name of property to retrieve
    *   @return mixed           Value of property
    */
    public function __get($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        } else {
            return NULL;
        }
    }


    /**
    *   Set the admin flag
    *
    *   @param  boolean $isadmin    True or False
    */
    public function setAdmin($isadmin)
    {   $this->isAdmin = $isadmin ? true : false;   }


    /**
    *   Sets the table in use, either submission or production.
    *   Ensures that a valid table name is set
    *
    *   @param  string  $table  Table key
    */
    public function setTable($table)
    {   $this->table = $table == 'bannersubmission' ? 'bannersubmission' : 'banner';
    }


    /**
    *   Get an option value
    *
    *   @param  string  $name       Option name
    *   @param  mixed   $default    Default value
    *   @return mixed           Option value, $default if not set
    */
    public function getOpt($name, $default=NULL)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        } else {
            return $default;
        }
    }


    /**
    *   Read a banner record from the database
    *
    *   @param  string  $bid    Banner ID to read (required)
    */
    public function Read($bid)
    {
        global $_TABLES;

        $A = DB_fetchArray(DB_query("
            SELECT *
            FROM {$_TABLES[$this->table]}
            WHERE bid='".DB_escapeString($bid)."'", false));

        if (!empty($A)) {
            $this->isNew = false;
            $this->setVars($A, true);

            // Save the old ID for use in Update()
            $this->oldID = $this->bid;
        }
    }


    /**
    *   Set the banner variables from the supplied array.
    *   The array may be from a form ($_POST) or database record
    *
    *   @see    _CreateHTMLTemplate()
    *   @param  array   $A          Array of values
    *   @param  boolean $fromDB     Indicates if reading from DB or submission
    */
    public function setVars($A='', $fromDB=false)
    {
        global $_CONF_BANR, $_CONF;

        if (!is_array($A))
            return;

        if ($fromDB) {
            // Coming from the database
            $this->options = @unserialize($A['options']);
            if (!$this->options) $this->options = array();
            $this->weight = $A['weight'];
            $this->publishstart = $A['publishstart'];
            $this->publishend = $A['publishend'];
            $this->date = $A['date'];
        } else {
            $this->options = array();
            // Coming from a form
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
                $this->publishstart = 0;
            } else {
                if ($_CONF['hour_mode'] == 12) {
                    if ($A['start_ampm'] == 'pm') {
                        if ((int)$A['start_hour'] < 12) {
                            $A['start_hour'] += 12;
                        }
                    } elseif ((int)$A['start_hour'] == 12) {
                        $A['start_hour'] = 0;
                    }

                }
                $dt = sprintf('%d-%02d-%02d %02d:%02d:00',
                        $A['start_year'], $A['start_month'], $A['start_day'],
                        $A['start_hour'], $A['start_minute']);
                $this->publishstart = $dt;
            }
            if (!isset($A['end_dt_limit']) || $A['end_dt_limit'] != 1) {
                $this->publishend = 0;
            } else {
                if ($_CONF['hour_mode'] == 12) {
                    if ($A['end_ampm'] == 'pm') {
                        if ((int)$A['end_hour'] < 12) {
                            $A['end_hour'] += 12;
                        }
                    } elseif ((int)$A['end_hour'] == 12) {
                        $A['end_hour'] = 0;
                    }
                }
                $dt = sprintf('%d-%02d-%02d %02d:%02d:00',
                        $A['end_year'], $A['end_month'], $A['end_day'],
                        $A['end_hour'], $A['end_minute']);
                $this->publishend = $dt;
            }

            // Only admins can set some values, use the defaults for others
            if (SEC_hasRights('banner.edit')) {
                $this->weight = (int)$A['weight'];
            } else {
                $this->weight = (int)$_CONF_BANR['def_weight'];
            }

            // Create the HTML template for click tracking if this is
            // an HTML or Javascript ad.
            if ($this->ad_type == BANR_TYPE_SCRIPT) {
                $this->_CreateHTMLTemplate();
            }
        }

        $this->bid = $A['bid'];
        $this->cid = $A['cid'];
        $this->camp_id = $A['camp_id'];
        $this->ad_type = $A['ad_type'];

        $this->notes = isset($A['notes']) ? $A['notes'] : '';
        $this->title = $A['title'];
        $this->enabled = isset($A['enabled']) ? $A['enabled'] : 0;
        $this->impressions = $A['impressions'];
        $this->max_impressions = $A['max_impressions'];
        $this->hits = $A['hits'];
        $this->max_hits = $A['max_hits'];
        $this->tid = $A['tid'];
        $this->owner_id = $A['owner_id'];
    }


    /**
     *  Update the 'enabled' value for a banner ad.
     *  @param  integer $newval New value to set (1 or 0)
     *  @param  string  $bid    Optional ad ID.  Current object if blank
     *  @return integer     New value, or old value if an error occurred
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
    *   Update the impression (display) count.
    */
    public function updateImpressions()
    {
        global $_TABLES, $_CONF_BANR, $_USER;

        // Don't update the count for ads show to admins or owners, if
        // so configured.
        if (
            ($_CONF_BANR['cntimpr_admins'] == 0 &&
                plugin_isadmin_banner())
        || ($_CONF_BANR['cntimpr_owner'] == 0 &&
                $this->owner_id == $_USER['uid'])
        ) {
            return;
        }

        DB_query("UPDATE {$_TABLES['banner']}
                SET impressions=impressions+1
                WHERE bid='{$this->bid}'");

        Campaign::updateImpressions($this->camp_id);
    }


    /**
    *   Increment the hit count.
    */
    public function updateHits()
    {
        global $_TABLES, $_CONF_BANR, $_USER;

        // Don't update the count for ads show to admins or owners, if
        // so configured.
        if (($_CONF_BANR['cntclicks_admins'] == 0 && plugin_isadmin_banner())
            || ($_CONF_BANR['cntclicks_owner'] == 0 && $this->owner_id == $_USER['uid'])
        ) {
            return;
        }

        // Update the banner hits
        $sql = "UPDATE {$_TABLES['banner']}
                SET hits=hits+1
                WHERE bid='{$this->bid}'";
        DB_query($sql);

        Campaign::updateHits($this->camp_id);
    }


    /**
    *   Delete the current banner.
    */
    public function Delete()
    {
        global $_TABLES, $_CONF_BANR, $_USER;

        $filename = $this->getOpt('filename');
        if (!empty($filename) &&
            file_exists($_CONF_BANR['img_dir'] . '/' . $filename)) {
            @unlink($_CONF_BANR['img_dir'] . '/' . $filename);
        }

        DB_delete($_TABLES[$this->table],
            'bid', DB_escapeString(trim($this->bid)));
        BANNER_auditLog("{$_USER['uid']} deleted banner id {$this->bid}");
    }


    /**
    *   Returns the current user's access level to this banner
    *
    *   @return integer     User's access level (1 - 3)
    */
    public function Access()
    {
        global $_USER;

        if ($this->isNew) {
            return 3;
        }
        $access = SEC_hasAccess($this->owner_id, $this->group_id,
                    $this->perm_owner, $this->perm_group,
                    $this->perm_members, $this->perm_anon);
        return $access;
    }


    /**
    *   Determines whether the current user has a given level of access
    *   to this banner object.
    *
    *   @see    Access()
    *   @param  integer $level  Minimum access level required
    *   @return boolean     True if user has access >= level, false otherwise
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
    *   Save the current banner object using the supplied values.
    *
    *   @param  array   $A  Array of values from $_POST or database
    *   @return string      Error message, empty if successful
    */
    public function Save($A = array())
    {
        global $_CONF, $_GROUPS, $_TABLES, $_USER, $MESSAGE,
                $_CONF_BANR, $LANG12, $LANG_BANNER;

        if ($this->isNew) {
            if ( COM_isAnonUser() ||
                    ($_CONF_BANR['usersubmit'] == 0 &&
                    !SEC_hasRights('banner.submit'))
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
        case BANR_TYPE_LOCAL:
            // Unset unused values
            unset($this->options['ad_code']);
            unset($this->options['image_url']);

            // Handle the file upload
            if (isset($_FILES['bannerimage']['name']) &&
                !empty($_FILES['bannerimage']['name'])) {
                $Img = new Image($this->bid, 'bannerimage');

                // Set max image size to the global sanity check.
                // Images will be resized down to the category size for display
                $Img->setMaxDimensions(
                    $_CONF_BANR['img_max_height'],
                    $_CONF_BANR['img_max_width']
                );

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
                    $sizes = @getimagesize($_CONF_BANR['img_dir'] . '/' .
                                    $this->options['filename']);
                    if ($this->options['width'] == 0 ||
                            $this->options['width'] > $sizes[0]) {
                        $this->options['width'] = $sizes[0];
                    }
                    if ($this->options['height'] == 0 ||
                            $this->options['height'] > $sizes[1]) {
                        $this->options['height'] = $sizes[1];
                    }
                }
            }
            break;

        case BANR_TYPE_REMOTE:
            unset($this->options['filename']);
            unset($this->options['ad_code']);
            break;

        case BANR_TYPE_SCRIPT:
            if (empty($this->options['url']))
                unset($this->options['url']);
        case BANR_TYPE_AUTOTAG:
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
                publishstart = '" . $this->publishstart->toMySQL(true) . "',
                publishend = '" . $this->publishend->toMySQL(true) . "',
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
    *   Returns the banner id for a banner or group of banners
    *   Called as a standalone function: Banner::GetBanner($options)
    *
    *   @param  array   $fields Fields to use in where clause
    *   @return array           Array of Banner ids, empty for none available
    */
    public static function GetBanner($fields=array())
    {
        global $_TABLES, $_CONF_BANR, $_CONF, $_USER, $topic;

        $banners = array();

        // Determine if any ads at all should be displayed to this user
        if (!self::canShow()) {
            return $banners;
        }

        if (!is_array($fields)) $fields = array();
        $sql_cond = '';
        $limit_clause = '';
        $topic_sql = '';
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
                if ($value != '' && $value != 'all') {
                    $topic_sql .= " AND c.tid IN ('$value', 'all')
                            AND camp.tid IN ('$value', 'all')
                            AND b.tid IN ('$value', 'all')";
                } else {
                    $topic_sql .= " AND c.tid = 'all'
                            AND camp.tid = 'all'
                            AND b.tid = 'all'";
                }
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

        if ($topic_sql == '' && !empty($topic)) {
            $topic = DB_escapeString($topic);
            $topic_sql .= " AND c.tid IN ('$topic', 'all')
                        AND camp.tid IN ('$topic', 'all')
                        AND b.tid IN ('$topic', 'all')";
        } else {
            $topic_sql .= " AND c.tid = 'all'";
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
    *   Returns an array of the newest banners for the "What's New" block
    *
    *   @return array   Array of banner records
    */
    public static function GetNewest()
    {
        global $_TABLES, $_CONF_BANR;

        $A = array();
        if (!self::CanShow()) return $A;
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
    *   Creates the banner image and href link for display.
    *   The $link parameter is true to create the full banner ad including
    *   the link. False will show only the image, e.g. for admin listings.
    *   Local and remotely-hosted images are sized based on the category size
    *   settings.
    *
    *   @param  string  $title      Banner Title, optional
    *   @param  integer $width      Image width, optional
    *   @param  integer $height     Image height, optional
    *   @param  boolean $link       True to create link, false for only image
    *   @return string              Banner image URL, with or without link
    */
    public function BuildBanner($title = '', $width=0, $height=0, $link = true)
    {
        global $_CONF, $LANG_DIRECTION, $_CONF_BANR, $LANG_BANNER;

        $retval = '';

        $alt = $this->getOpt('alt', '');
        if (empty($title) && !empty($alt)) {
            $title = $alt;
        }

        // Set the ad URL to the portal page only if there is a dest. URL
        $url = $this->getOpt('url', '');
        if (!empty($url)) {
            $url = COM_buildUrl(BANR_URL . '/portal.php?id=' . $this->bid);
        }
        $a_attr = array(
            'target' => $this->getOpt('target', '_blank'),
        );
        $img_attr = array(
            'class' => 'banner_img',
        );
        if (!empty($title)) {
            $img_attr['title'] = htmlspecialchars($title);
            $img_attr['data-uk-tooltip'] = '';
        }

        $C = new Category($this->cid);
        if ($width == 0)
            $width = min($this->getOpt('width', 0), $C->max_img_width);
        if ($height == 0)
            $height = min($this->getOpt('height', 0), $C->max_img_height);

        switch ($this->ad_type) {
        case BANR_TYPE_LOCAL:
            // A bit of a kludge until LGLIB is updated for everyone.
            // The service function returns the image width and height as well
            // as the url.
            $filename = isset($this->options['filename']) ? $this->options['filename'] : '';
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
            } else {
                // Newer lglib plugin not available, call the legacy function.
                $img = LGLIB_ImageUrl(
                    $_CONF_BANR['img_dir'] . '/' . $filename,
                    $width, $height);
                $img_attr['width'] = $width;
                $img_attr['height'] = $height;
            }
            if (!empty($img)) {
                $retval = COM_createImage($img, $alt, $img_attr);
            }
            break;

        case BANR_TYPE_REMOTE:
            $img = $this->options['image_url'];
            if ($img != '') {
                $img_attr['height'] = $height;
                $img_attr['width'] = $width;
                $retval = COM_createImage($img, $alt, $img_attr);
            }
            break;

        case BANR_TYPE_SCRIPT:
            if ($link == true) {
                $retval = $this->options['ad_code'];
            } else {
                $retval = $LANG_BANNER['ad_is_script'];
            }
            break;

        case BANR_TYPE_AUTOTAG:
            $retval = PLG_replaceTags($this->options['ad_code']);
            break;
        }
        if ($link && !empty($url) && !empty($retval)) {
            $retval = COM_createLink($retval, $url, $a_attr);
        }
        return $retval;
    }


    /**
    *   Determine the maximum number of days that a user may run an ad.
    *   Based on the account balance, unless either purchasing is disabled
    *   or the user is exempt (like administrators).
    *
    *   @param  integer $uid    User ID, current user if zero
    *   @return integer         Max ad days available, -1 if unlimited
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
    *   Validate this banner's url
    *
    *   @return string  Response, or empty if no test performed.
    */
    public function validateUrl()
    {
        global $LANG_BANNER_STATUS, $LANG_BANNER;

        // Have to have a valid url to check
        if ($this->options['url'] == '') {
            return 'n/a&nbsp;<i class="tooltip ' . BANR_getIcon('question-circle','') .
                '" title="' . $LANG_BANNER['html_status_na'] .
                '"></i>';
        }

        // Get the header and response code
        $ch = curl_init();
        curl_setopt_array($ch, array(
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
    *   Creates the edit form.
    *
    *   @param  string  $mode   Type of editing being done
    *   @return string          HTML for edit form
    */
    public function Edit($mode = 'edit')
    {
        global $_CONF, $_GROUPS, $_TABLES, $_USER, $_CONF_BANR, $_PLUGINS,
            $LANG_ACCESS, $MESSAGE, $LANG_BANNER, $LANG_ADMIN,
            $LANG12, $_SYSTEM;

        $retval = '';

        switch ($mode) {
        case 'edit':
            $saveoption = $LANG_ADMIN['save'];      // Save
            $sub_type = '<input type="hidden" name="item" value="banner" />';
            $cancel_url = $this->isAdmin ? BANR_ADMIN_URL . '/index.php' :
                $_CONF['site_url'];
        case 'submit':
            $saveoption = $LANG_ADMIN['save'];      // Save
            // override sub_type for submit.php
            $sub_type =
                '<input type="hidden" name="type" value="banner" />'
                .'<input type="hidden" name="mode" value="' .
                    $LANG12[8].'" />';
            $cancel_url = $this->isAdmin ? BANR_ADMIN_URL . '/index.php' :
                $_CONF['site_url'];
            break;

        case 'moderate':
            $saveoption = $LANG_ADMIN['moderate'];  // Save & Approve
            $sub_type = '<input type="hidden" name="type" value="submission" />';
            $cancel_url = $_CONF['site_admin_url'] . '/moderation.php';
            break;
        }

        $T = new \Template(BANR_PI_PATH . '/templates/');
        $tpltype = $_CONF_BANR['_is_uikit'] ? '.uikit' : '';
        $T->set_file(array(
            'editor' => "bannerform$tpltype.thtml",
            'tips' => 'tooltipster.thtml',
        ) );

        $T->set_var(array(
            'help_url'      => BANNER_docUrl('bannerform'),
            'submission_option' => $sub_type,
            'lang_save'     => $saveoption,
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
            if ($mode == 'submit') {
                $T->set_var('action_url', $_CONF['site_url'] . '/submit.php');
            } else {
                $T->set_var('action_url', BANR_URL . '/index.php');
            }
        }

        $access = $this->Access();
        if ($access == 0 OR $access == 2) {
            $retval .= COM_startBlock($LANG_BANNER['access_denied'], '',
                               COM_getBlockTemplate ('_msg_block', 'header'));
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
            $this->publishstart = 0;
            $this->publishend = 0;
        }

        $T->set_var('banner_id', $this->bid);
        $T->set_var('old_banner_id', $this->oldID);

        // Ad Type Selection
        $adtype_select = '';
        foreach ($LANG_BANNER['ad_types'] as $value=>$text) {
            $sel = $this->ad_type === $value ?
                        ' selected="selected"' : '';
            $adtype_select .= "<option value=\"$value\"$sel>$text</option>\n";
        }

        $T->set_var(array(
            //'mootools'      => $_SYSTEM['disable_jquery'] ? 'true' : '',
            'banner_title' => htmlspecialchars($this->title),
            'max_url_length' => 255,
            'category_options' => Category::Dropdown(0, $this->cid),
            'campaign_options' => Campaign::Dropdown($this->camp_id),
            'banner_hits' => $this->hits,
            'banner_maxhits' => $this->max_hits,
            'impressions'   => $this->impressions,
            'max_impressions'   => $this->max_impressions,
            'ena_chk' => $this->enabled == 1 ? ' checked="checked"' : '',
            'image_url' => isset($this->options['image_url']) ? $this->options['image_url'] : '',
            'alt'   => isset($this->options['alt']) ? $this->options['alt'] : '',
            'width' => isset($this->options['width']) ? $this->options['width'] : '',
            'height' => isset($this->options['height']) ? $this->options['height'] : '',
            'target_url' => isset($this->options['url']) ? $this->options['url'] : '',
            'ad_code'   => isset($this->options['ad_code']) ? $this->options['ad_code'] : '',
            'adtype_select' => $adtype_select,
            'filename' => isset($this->options['filename']) ? $this->options['filename'] : '',
            'weight_select' => $weight_select,
            'sel'.$this->options['target'] => 'selected="selected"',
            'req_item_msg' => $LANG_BANNER['req_item_msg'],
            'perm_msg' => $LANG_ACCESS['permmsg'],
            'iconset' => $_CONF_BANR['_iconset'],
        ));

        foreach (Category::getAll() as $C) {
            if (!$C->enabled) continue;
            $cats[$C->cid] = array(
                'img_width' => $C->max_img_width,
                'img_height' => $C->max_img_height,
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
        if ($this->publishstart == BANR_MIN_DATE) {
            $T->set_var(array(
                'start_dt_limit_chk'    => '',
                'startdt_sel_show'      => 'none',
                'startdt_txt_show'      => '',
            ) );
            $startdt = $_CONF_BANR['_now'];
        } else {
            $T->set_var(array(
                'start_dt_limit_chk'    => 'checked="checked"',
                'startdt_sel_show'      => '',
                'startdt_txt_show'      => 'none',
            ) );
            $startdt = $this->publishstart;
        }
        if ($startdt->format('H') > 11) {
            $st_ampm = 'pm';
        } else {
            $st_ampm = 'am';
        }
        if ($this->publishend == BANR_MAX_DATE) {
            $T->set_var(array(
                'end_dt_limit_chk'      => '',
                'enddt_sel_show'        => 'none',
                'enddt_txt_show'        => '',
            ) );
            $enddt = $_CONF_BANR['_now'];
        } else {
            $T->set_var(array(
                'end_dt_limit_chk'      => 'checked="checked"',
                'enddt_sel_show'        => '',
                'enddt_txt_show'        => 'none',
            ) );
            $enddt = $this->publishend;
        }
        if ($enddt->format('H') > 11) {
            $end_ampm = 'pm';
        } else {
            $end_ampm = 'am';
        }
        $h_fmt = $_CONF['hour_mode'] == 12 ? 'h' : 'H';
        $st_hour = $startdt->format($h_fmt, true);
        $end_hour = $enddt->format($h_fmt, true);

        $T->set_var(array(
            'start_hour_options' =>
                        COM_getHourFormOptions($st_hour, $_CONF['hour_mode']),
            'start_ampm_selection' =>
                        self::getAmPmFormSelection('start_ampm', $st_ampm),
            'start_month_options' => COM_getMonthFormOptions($startdt->format('m', true)),
            'start_day_options' => COM_getDayFormOptions($startdt->format('d', true)),
            'start_year_options' => COM_getYearFormOptions($startdt->format('Y', true)),
            'start_minute_options' =>
                        COM_getMinuteFormOptions($startdt->format('i', true)),
            'end_hour_options' =>
                        COM_getHourFormOptions($end_hour, $_CONF['hour_mode']),
            'end_ampm_selection' => self::getAmPmFormSelection('end_ampm', $end_ampm),
            'end_month_options' => COM_getMonthFormOptions($enddt->format('m', true)),
            'end_day_options' => COM_getDayFormOptions($enddt->format('d', true)),
            'end_year_options' => COM_getYearFormOptions($enddt->format('Y', true)),
            'end_minute_options' => COM_getMinuteFormOptions($enddt->format('i', true)),
        ) );
        $T->parse('tooltipster', 'tips');
        $T->parse('output', 'editor');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }   // function Edit()


    /**
    *   Validate that the required fields are filled in.
    *
    *   @param  array   $A  All form variables
    *   @return boolean     True if valid, False otherwise
    */
    public function Validate($A)
    {
        global $LANG_BANNER;

        $this->errors = array();

        // Check that appropriate ad content has been added
        switch ($A['ad_type']) {
        case BANR_TYPE_LOCAL:
            if (empty($_FILES))
                $this->errors[] = $LANG_BANNER['err_missing_upload'];
            break;
        case BANR_TYPE_REMOTE:
            if (COM_sanitizeUrl($A['image_url'], array('http','https')) == '')
                $this->errors[] = $LANG_BANNER['err_invalid_image_url'];
            break;
        case BANR_TYPE_SCRIPT:
        case BANR_TYPE_AUTOTAG:
            if (empty($A['ad_code']))
                $this->errors[] = $LANG_BANNER['err_missing_adcode'];
            break;
        }
        return empty($this->errors) ? true : false;
    }


    /**
    *   Send an email notification for a new submission.
    */
    public function Notify()
    {
        global $_CONF, $_TABLES, $LANG_BANNER, $LANG08;

        $mailsubject = $_CONF['site_name'] . ' ' . $LANG_BANNER['banner_submissions'];
        $mailbody = $LANG_BANNER['title'] . ": $this->title\n";

        if ($this->table == 'bannersubmission') {
            $mailbody .= "$LANG_BANNER[10] <{$_CONF['site_admin_url']}/moderation.php>\n\n";
        } else {
            $mailbody .= "$LANG_BANNER[114] <" . BANR_URL .
                '/index.php?category=' . urlencode ($A['category']) . ">\n\n";
        }

        $mailbody .= "\n------------------------------\n";
        $mailbody .= "\n$LANG08[34]\n";
        $mailbody .= "\n------------------------------\n";

        COM_mail($_CONF['site_mail'], $mailsubject, $mailbody);
    }


    /**
    *   Determine if banners should be shown on this page or to this user.
    *   This is based on global settings, not banner permissions.
    *
    *   @return boolean     True to show banners, False to not.
    */
    public static function canShow()
    {
        global $_CONF_BANR, $_CONF, $_USER;

        // Set some static variables since this function can be called
        // multiple times per page load.
        static $in_admin_url = NULL;
        static $is_blocked_useragent = NULL;
        static $is_blocked_ip = NULL;

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
            if ($in_admin_url) return false;
        }

        // See if this is a banner admin, and we shouldn't show it.
        // plugin_isadmin_banner() stores a static var, so it's low overhead
        if ($_CONF_BANR['adshow_admins'] == 0 &&
                plugin_isadmin_banner()) {
            return false;
        }

        // Now check if this user or IP address is in the blocked lists
        /*if (isset($_USER['uid']) && is_array($_CONF_BANR['users_dontshow'])) {
            if (in_array($_USER['uid'], $_CONF_BANR['users_dontshow'])) {
                return false;
            }
        }*/

        if (is_array($_CONF_BANR['ipaddr_dontshow'])) {
            if ($is_blocked_ip === NULL) {
                $is_blocked_ip = false;
                foreach ($_CONF_BANR['ipaddr_dontshow'] as $addr) {
                    if (empty($addr)) continue;
                    if (strstr($_SERVER['REMOTE_ADDR'], $addr)) {
                        $is_blocked_ip = true;
                        break;
                    }
                }
            }
            if ($is_blocked_ip) return false;
        }

        if (is_array($_CONF_BANR['uagent_dontshow'])) {
            if ($is_blocked_useragent === NULL) {
                $is_blocked_useragent = false;
                foreach ($_CONF_BANR['uagent_dontshow'] as $agent) {
                    if (empty($agent)) continue;
                    if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
                        $is_blocked_useragent = true;
                        break;
                    }
                }
            }
            if ($is_blocked_useragent) return false;
        }

        // Allow the site admin to implement a custom banner control function
        if (function_exists('CUSTOM_banner_control')) {
            if (CUSTOM_banner_control() == false) {
                return false;
            }
        }

        // Passed all the tests, ok to show banners
        return true;
    }


    /**
    *  Create the HTML template for javascript-based banners
    *
    *   @return string  HTML for the banner
    */
    private function _CreateHTMLTemplate()
    {
        $buffer = $this->options['ad_code'];
        if (empty($buffer))
            return;

        // Put our click URL and our target parameter in all anchors...
        // The regexp should handle ", ', \", \' as delimiters
        if (preg_match_all(
                '#<a(.*?)href\s*=\s*(\\\\?[\'"])http(.*?)\2(.*?) *>#is',
                $buffer, $m)) {
            foreach ($m[0] as $k => $v) {
                // Remove target parameters
                $m[4][$k] = trim(preg_replace(
                            '#target\s*=\s*(\\\\?[\'"]).*?\1#i',
                            '', $m[4][$k]));
                $urlDest = preg_replace(
                            '/%7B(.*?)%7D/', '{$1}',
                            "http" . $m[3][$k]);
                //$buffer = str_replace($v, "<a{$m[1][$k]}href={$m[2][$k]}{clickurl}$urlDest{$m[2][$k]}{$m[4][$k]} target={$m[2][$k]}{target}{$m[2][$k]}>", $buffer);
                $buffer = str_replace($v, "<a{$m[1][$k]}href={$m[2][$k]}{clickurl}{$m[2][$k]}{$m[4][$k]} target={$m[2][$k]}{target}{$m[2][$k]}>", $buffer);
            }

            $this->options['url'] = $urlDest;
            $this->options['htmlTemplate'] = $buffer;
        }
    }


    /**
    *   See if this is a banner submission or using the prod table
    *
    *   @return boolean     True if submission, False if prod
    */
    private function _isSubmission()
    {
        return $this->table == 'bannersubmission' ? true : false;
    }


    /**
    *   Get the AM/PM time selector for publication start/end fields.
    *   Same function as COM_getAmPmFormSelection(), but sets the "id"
    *   attribute of the field the same as the "name" to allow
    *   the datepicker to update the field.
    *
    *   @param  string  $name   Name of field, also used for the ID
    *   @param  string  $seleted    "am" or "pm"
    *   @return string      HTML for selection
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

}   // class Banner

?>
