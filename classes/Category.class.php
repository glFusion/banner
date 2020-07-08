<?php
/**
 * Class to handle banner categories.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.00
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Banner;


/**
 * Class to handle category data.
 * @package banner
 */
class Category
{
    /** Indicate whether this is a new submission or existing item.
     * @var boolean */
    private $isNew = 1;

    /** Category ID.
     * @var string */
    private $cid = '';

    /** Old Category ID, used for tracking changes to category ID.
     * @var string */
    private $oldcid = '';

    /** Topic ID for category placement.
     * @var string */
    private $tid = '';

    /** Type of category.
     * @var string */
    private $type = '';

    /** Name of category.
     * @var string */
    private $category = '';

    /** Description of category.
     * @var string */
    private $dscp = '';

    /** Indicate that category can be used.
     * @var boolean */
    private $enabled = 1;

    /** Enable this category as a centerblock.
     * @var boolean */
    private $centerblock = 0;

    /** Group ID with permission to view this category.
     * @var integer */
    private $grp_view = 2;

    /** Max image width, in pixels.
     * @var integer */
    private $max_img_width = 468;

    /** Max image height, in pixels.
     * @var integer */
    private $max_img_height = 80;


    /**
     * Constructor.
     * Create empty object, or read a single category.
     *
     * @param   string  $id     Category ID to load
     */
    public function __construct($id='')
    {
        global $_USER, $_TABLES;

        $this->isNew = true;

        if ($id != '') {
            $this->cid = $id;
            $this->Read($this->cid);
        } else {
            // Set default values
            $this->cid = '';
            $this->oldcid = '';
            $this->tid = '';
            $this->type = '';
            $this->category = '';
            $this->dscp = '';
            $this->enabled = 1;
            $this->grp_view = 2;
            $this->max_img_height = 0;
            $this->max_img_width = 0;
        }
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
     * Check if this category is enabled.
     *
     * @return  integer     1 if enabled, 0 if not
     */
    public function isEnabled()
    {
        return $this->enabled ? 1 : 0;
    }


    /**
     * Get the max image width, in pixels.
     *
     * @return  integer     Max image width
     */
    public function getMaxWidth()
    {
        return (int)$this->max_img_width;
    }


    /**
     * Get the max image height, in pixels.
     *
     * @return  integer     Max image height
     */
    public function getMaxHeight()
    {
        return (int)$this->max_img_height;
    }


    /**
     * Read the data for a single category into the current object.
     *
     * @see     $this->setVars()
     * @param   string  $id     Database ID of category to retrieve
     */
    public function Read($id)
    {
        global $_TABLES;

        $A = DB_fetchArray(DB_query(
            "SELECT * FROM {$_TABLES['bannercategories']}
            WHERE cid='".DB_escapeString($id)."'", 1), false);
        if (!empty($A)) {
            $this->setVars($A);
            $this->oldcid = $this->cid;
            $this->isNew = false;
        }
    }


    /**
     * Sets this object's values from the supplied array.
     * The array may be a database record or a $_POST array.
     * All values will be set, so missing values will result in empty
     * variables.
     *
     * @param   array   $A      Array of values to set
     */
    public function setVars($A)
    {
        if (!is_array($A))
            return;

        $this->cid = $A['cid'];
        $this->tid = $A['tid'];
        $this->type = trim($A['type']);
        $this->category = trim($A['category']);
        $this->dscp = trim($A['description']);
        $this->enabled = isset($A['enabled']) ? (int)$A['enabled'] : 0;
        $this->centerblock = isset($A['centerblock']) ? (int)$A['centerblock'] : 0;
        $this->grp_view = (int)$A['grp_view'];
        $this->max_img_height = (int)$A['max_img_height'];
        $this->max_img_width = (int)$A['max_img_width'];
    }


    /**
     * Toggle boolean database fields.
     *
     * @param   string  $field  Databsae field to update
     * @param   integer $oldval Current value of item
     * @param   string  $id     Category ID
     * @return  integer     New item value, old value on error
     */
    private static function _toggle($field, $oldval, $id)
    {
        global $_TABLES;

        $newval = $oldval == 0 ? 1 : 0;
        DB_query("UPDATE {$_TABLES['bannercategories']}
                SET `$field` = $newval
                WHERE cid = '" . DB_escapeString($id) . "'");
        if (!DB_error()) {
            Cache::clear('cats');
            return $newval;
        } else {
            return $oldval;
        }
    }


    /**
     * Update the "cenberblock" flag for a category.
     *
     * @uses    self::_toggle()
     * @param   integer $oldval     Current value of item
     * @param   string  $id         Category ID.
     * @return  integer     New value, or old value upon error
     */
    public static function toggleCenterblock($oldval, $id)
    {
        if ($oldval == 1) {
            // Was set, now turning it off. Nothing to do except toggle.
            return self::_toggle('centerblock', $oldval, $id);
        } else {
            // Not set as centerblock, turn it on and disable all others.
            $newval = $oldval ? 0 : 1;
            if (self::_toggle('centerblock', $oldval, $id) == $newval) {
                self::unsetCenterblock($id);
                return $newval;
            } else {
                return $oldval;
            }
        }
    }


    /**
     * Update the 'enabled' value for a category.
     *
     * @uses    self::_toggle()
     * @param   integer $oldval     Current value of item
     * @param   string  $id         Category ID
     * @return  integer     New value, or old value upon error
     */
    public static function toggleEnabled($oldval, $id)
    {
        return self::_toggle('enabled', $oldval, $id);
    }


    /**
     * Delete a single category.
     */
    public function Delete()
    {
        global $_TABLES;

        if (self::isRequired($this->type)) {
            return;
        }

        if (!self::isUsed($this->cid)) {
            DB_delete($_TABLES['bannercategories'],
                'cid', DB_escapeString($this->cid));
            Cache::clear('cats');
        }
    }


    /**
     * Determine if the supplied category ID, is used by any banners.
     *
     * @param   string  $id     Category ID to check
     * @return  boolean         True if the category is in use, fales otherwise.
     */
    public static function isUsed($id)
    {
        global $_TABLES;

        if (DB_count($_TABLES['banner'], 'cid', $id) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Display the edit form for the current category.
     *
     * @return  string  HTML for the edit form
     */
    public function Edit()
    {
        global $_CONF_BANR, $LANG_BANNER;

        if (!$this->canEdit()) {
            return COM_showMessage(6, 'banner');
        }

        $T = new \Template(BANR_PI_PATH . '/templates');
        $T->set_file(array(
            'page' => "admin/categoryeditor.thtml",
            'tips' => 'tooltipster.thtml',
        ) );

        $T->set_var(array(
            'help_url'      => BANNER_docURL('categoryform.html'),
            'cancel_url'    => BANR_ADMIN_URL . '/index.php?view=categories',
        ));

        $delete_option = '';
        if ($this->isNew) {
            $this->cid = COM_makeSid();
        } else {
            // Set the delete button if this is an existing category
            // that is not used or required
            if (!self::isUsed($this->cid) && !self::isRequired($this->type)) {
                $delete_option = 'true';
            }
        }

        $T->set_var(array(
            'cid'               => $this->cid,
            'old_cid'           => $this->cid,
            'type'              => $this->type,
            'category'          => $this->category,
            'description'       => $this->dscp,
            'chk_enabled'       => $this->isEnabled() ? 'checked="checked"' : '',
            'chk_cblock'        => $this->centerblock ? 'checked="checked"' : '',
            'max_img_width'     => $this->max_img_width,
            'max_img_height'    => $this->max_img_height,
            'delete_option'     => $delete_option,
            'mapping_form'      => Mapping::Form($this->cid),
        ) );

        if ($this->tid === NULL) {
            $this->tid = 'all';
        }
        $topics = COM_topicList('tid,topic', $this->tid, 1, true);
        $T->set_var('topic_list', $topics);
        $alltopics = '';
        foreach (array('all', 'homeonly') as $tid) {
            $alltopics .= '<option value="' . $tid . '"';
            if ($this->tid == $tid) {
                $alltopics .= ' selected="selected"';
            }
            $alltopics .= '>' . $LANG_BANNER[$tid] . '</option>' . LB;
        }
        $T->set_var('topic_selection',  $alltopics . $topics);

        // user access info
        $T->set_var('group_dropdown', SEC_getGroupDropdown($this->grp_view, 3, 'grp_view'));
        $T->set_var('gltoken_name', CSRF_TOKEN);
        $T->set_var('gltoken', SEC_createToken());

        $T->parse('tooltipster', 'tips');
        $T->parse ('output', 'page');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Save the current category to the database.
     * If an array is passed in, then the values from that array will be
     * set in the current object before saving.
     *
     * @param   array   $A      Optional array of values
     * @return  string          Error message, empty string for success
     */
    public function Save($A='')
    {
        global $_TABLES, $LANG_BANNER;

        if (is_array($A)) {
            $this->setVars($A);
        }

        if ($this->isNew) {
            // Make sure there's a valid category ID
            if ($this->cid == '') {
                $this->cid = COM_makeSid();
            }

            // Make sure there's not a duplicate ID
            if (DB_count(
                $_TABLES['bannercategories'],
                'cid',
                $this->cid
            ) > 0) {
                return $LANG_BANNER['duplicate_cid'];
            }
            $sql1 = "INSERT INTO {$_TABLES['bannercategories']} SET ";
            $sql3 = '';
        } else {
            if ($this->cid == '') {
                $this->cid = $this->oldcid;
            }
            $sql1 = "UPDATE {$_TABLES['bannercategories']} SET ";
            $sql3 = " WHERE cid='" . DB_escapeString($this->oldcid) . "'";
        }
        $sql2 = "cid='" . DB_escapeString($this->cid) . "',
                type='".DB_escapeString($this->type)."',
                category='".DB_escapeString($this->category)."',
                description='".DB_escapeString($this->dscp)."',
                tid='".DB_escapeString($this->tid)."',
                enabled = '{$this->isEnabled()}',
                centerblock = '{$this->centerblock}',
                grp_view = '{$this->grp_view}',
                max_img_height = '{$this->max_img_height}',
                max_img_width = '{$this->max_img_width}'";
        //echo $sql1 . $sql2 . $sql3;die;
        DB_query($sql1 . $sql2 . $sql3);
        if (DB_error()) {
            return $LANG_BANNER['err_saving_item'];
        }
        if ($this->centerblock) {
            // Disable other centerblock categories if this one is set
            self::unsetCenterblock($this->cid);
        }
        if ($this->oldcid != $this->cid) {
            // Update banners that were associated with the old ID
            DB_change($_TABLES['banner'], 'cid', $this->cid, 'cid', $this->oldcid);
        }
        if (isset($_POST['map']) && is_array($_POST['map'])) {
            Mapping::saveAll($_POST['map'], $this->cid);
        }
        Cache::clear('cats');
        return '';
    }


    private static function unsetCenterblock($except)
    {
        global $_TABLES;

        $sql = "UPDATE {$_TABLES['bannercategories']}
            SET centerblock = 0
            WHERE cid <> '" . DB_escapeString($except) . "'";
        DB_query($sql);
    }


    /**
     * Determine if the current user can edit categories.
     * Currently plugin admin rights required.
     *
     * @return  boolean     True if user can edit, False if not.
     */
    public function canEdit()
    {
        return plugin_isadmin_banner();
    }


    /**
     * Determine if the current user can view ads under this category.
     *
     * @return  boolean     True for view access, False if denied
     */
    public function canView()
    {
        return SEC_inGroup($this->grp_view);
    }


    /**
     * Determine if the current user has the required access level.
     *
     * @deprecated
     * @param   integer $required   Required access
     * @return  boolean     True if the current user has the required access
     */
    public function XhasAccess($required = 3)
    {
        static $access = NULL;

        if ($access == NULL) {
            if (SEC_hasRights('banner.admin')) {
                $access = 3;
            } elseif (SEC_inGroup($this->grp_view)) {
                $access = 2;
            }
        }
        return $access >= $required ? true : false;
    }


    /**
     * Return the option elements for a category selection dropdown.
     *
     * @param   integer $access Required access
     * @param   string  $sel    Category ID to show as selected
     * @return  string          HTML for option statements
     */
    public static function DropDown($access = 3, $sel='')
    {
        $retval = '';
        $sel = COM_sanitizeID($sel, false);
        $access = (int)$access;

        foreach (self::getAll() as $C) {
            if (!$C->enabled) continue;
            $selected = $C->cid === $sel ? ' selected="selected"' : '';
            $retval .= '<option value="' .
                        htmlspecialchars($C->cid) .
                        '" ' . $selected . '>' .
                        htmlspecialchars($C->category) .
                        '</option>' . LB;
        }
        return $retval;
    }


    /**
     * Returns True if this category is one of the required ones.
     *
     * @param   string  $type   Optional category ID.  Current id if empty.
     * @return  boolean         True if category is required, False otherwise.
     */
    public static function isRequired($type)
    {
        $req_blocks = array('header', 'footer', 'block');

        if (!empty($type) && in_array($type, $req_blocks)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Create the category administration home page.
     *
     * @see     lib-admin.php
     * @return  string  HTML for administration page
     */
    public static function adminList()
    {
        global $LANG_ADMIN, $LANG_BANNER;

        $retval = '';
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
                'sort' => false,
                'align' => 'center',
            ),
            /*array(
                'text' => $LANG_BANNER['centerblock'],
                'field' => 'centerblock',
                'sort' => true,
                'align' => 'center',
            ),*/
            array(
                'text' => 'ID',
                'field' => 'cid',
                'sort' => 'true',
            ),
            array(
                'text' => $LANG_BANNER['type'],
                'field' => 'type',
                'sort' => 'true',
            ),
            array(
                'text' => $LANG_BANNER['cat_name'],
                'field' => 'category',
                'sort' => true,
            ),
            array(
                'text' => $LANG_BANNER['topic'],
                'field' => 'tid',
                'sort' => true,
            ),
            array(
                'text' => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort' => false,
                'align' => 'center',
            ),
        );

        $text_arr = array(
            'form_url' => BANR_ADMIN_URL . '/index.php?categories=x',
        );
        $data_arr = self::_listCategories();
        $retval .= COM_createLink($LANG_BANNER['new_cat'],
            BANR_ADMIN_URL . '/index.php?editcategory=0&cid=0',
            array(
                'class' => 'uk-button uk-button-success',
                'style' => 'float:left',
            )
        );
        $retval .= ADMIN_simpleList(
            array(__CLASS__, 'getAdminField'),
            $header_arr,
            $text_arr, $data_arr
        );
        return $retval;
    }


    /**
     * Get the data array of categories.
     *
     * @return  array   Array of category information for the admin list
     */
    private static function _listCategories()
    {
        global $_TABLES;

        $sql = "SELECT c.cid, c.category, c.tid, c.type, c.grp_view, c.centerblock, c.enabled,
                        t.topic as topic_text
                FROM {$_TABLES['bannercategories']} c
                LEFT JOIN {$_TABLES['topics']} t
                    ON t.tid = c.tid
                ORDER BY c.category";
        //echo $sql;die;
        $result = DB_query($sql);
        while ($A = DB_fetchArray($result)) {
            $data_arr[] = $A;
        }
        return $data_arr;
    }


    /**
     * Gets all the categories into a static array.
     *
     * @return  array   Array of category objects
     */
    public static function getAll()
    {
        global $_TABLES;
        $cache_key = 'cat_all';
        $cats = Cache::get($cache_key);
        if ($cats === NULL) {
            $cats = array();
            $sql = "SELECT * FROM {$_TABLES['bannercategories']}";
            $res = DB_query($sql);
            while ($A = DB_fetchArray($res, false)) {
                $C = new self();
                $C->setVars($A);
                $cats[$A['cid']] = $C;
            }
            Cache::set($cache_key, $cats, 'cats');
        }
        if (!is_array($cats)) $cats = array();
        return $cats;
    }


    /**
     * Return the display value for a single field in the admin list.
     *
     * @param   string  $fieldname      Name of field in the array
     * @param   mixed   $fieldvalue     Value of the field
     * @param   array   $A              Complete array of fields
     * @param   array   $icon_arr       Array of system icons
     * @return  string                  HTML to display the field
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF, $_TABLES, $LANG_ACCESS, $_CONF_BANR, $LANG_BANNER;

        $retval = '';
        $admin_url = BANR_ADMIN_URL . '/index.php';

        $access = 3;
        switch ($fieldname) {
        case 'edit':
            $retval = COM_createLink(
                $_CONF_BANR['icons']['edit'],
                "$admin_url?editcategory&amp;cid=" . urlencode($A['cid'])
            );
            break;

        case 'enabled':
            if ($A['enabled'] == '1') {
                $switch = 'checked="checked"';
            } else {
                $switch = '';
            }
            $retval .= "<input type=\"checkbox\" $switch value=\"1\" name=\"cat_ena_check\"
                id=\"togena{$A['cid']}\"
                onclick='BANR_toggleEnabled(this, \"{$A['cid']}\",\"category\");' />\n";
            break;

        case 'delete':
            if (!Category::isRequired($A['type']) && !Category::isUsed($A['cid'])) {
                $retval .= COM_createLink(
                    $_CONF_BANR['icons']['delete'],
                    "$admin_url?delete=x&item=category&amp;cid={$A['cid']}",
                    array(
                        'onclick' => "return confirm('{$LANG_BANNER['ok_to_delete']}');",
                    )
                );
            } else {
                $retval .= '<i class="uk-icon uk-icon-trash-o uk-text-muted tooltip"
                    title="' . $LANG_BANNER['cannot_delete'] . '"></i>';
            }
            break;

        case 'centerblock':
            if ($fieldvalue == '1') {
                $switch = 'checked="checked"';
                //$newval = 0;
            } else {
                $switch = '';
                //$newval = 1;
            }
            $retval .= "<input type=\"checkbox\" $switch value=\"1\" name=\"catcb_ena_check\"
                id=\"togcatcb{$A['cid']}\"
                onclick='BANR_toggleEnabled(this, \"{$A['cid']}\",\"cat_cb\");' />\n";
            break;

        case 'access':
            if ($access == 3) {
               $retval = $LANG_ACCESS['edit'];
            } else {
                $retval = $LANG_ACCESS['readonly'];
            }
            break;

        case 'category':
            $indent = isset($A['indent']) ? (int)$A['indent'] : 1;
            $indent = ($indent - 1) * 20;
            $cat = COM_createLink($A['category'],
                        "$admin_url?banners=x&category=" . urlencode($A['cid']));
            $retval = "<span style=\"padding-left:{$indent}px;\">$cat</span>";
            break;

        case 'tid':
            if ($A['tid'] == 'homeonly') {
                $retval = $LANG_BANNER['homeonly'];
            } elseif ($A['tid'] == 'all' || $A['tid'] == NULL) {
                $retval = $LANG_BANNER['all'];
            } elseif (array_key_exists($A['tid'], \Topic::All())) {
                $retval = $A['tid'];
            } else {
                // Bad topic selected.
                $retval = $LANG_BANNER['unknown'];
            }
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
        return $retval;
    }

}

?>
