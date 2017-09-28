<?php
/**
*   Class to handle banner categories
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
*   Class to handle category data
*   @package    banner
*/
class Category
{
    /** Properties of the category
        @var array() */
    var $properties;

    /** Indicate whether this is a new submission or existing item.
        @var boolean */
    var $isNew;

    /**
    *   Constructor.
    *   Create empty object, or read a single category.
    *   @param  string  $id     Category ID to load
    */
    public function __construct($id='')
    {
        global $_USER, $_TABLES;

        $this->isNew = true;
        $this->properties = array();

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
            $this->description = '';
            $this->enabled = 1;
            $this->grp_view = 2;
            $this->max_img_height = 0;
            $this->max_img_width = 0;
        }
    }


    /**
    *   Read the data for a single category into the current object
    *
    *   @param  string  $id     Database ID of category to retrieve
    *   @see    $this->setVars()
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
    *   Set a property value
    *
    *   @param  string  $key    Name of property
    *   @param  mixed   $valu   Value to set
    */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'cid':
        case 'oldcid':
        case 'tid':
            $this->properties[$key] = COM_sanitizeId($value, false);
            break;

        case 'type':
        case 'category':
        case 'description':
            $this->properties[$key] = $value;
            break;

        case 'enabled':
        case 'centerblock':
            $this->properties[$key] = $value ? 1 : 0;
            break;

        case 'grp_view':
        case 'max_img_width':
        case 'max_img_height':
            $this->properties[$key] = (int)$value;
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
    *   Sets this object's values from the supplied array.
    *   The array may be a database record or a $_POST array.
    *   All values will be set, so missing values will result in empty
    *   variables.
    *
    *   @param  array   $A      Array of values to set
    */
    public function setVars($A)
    {
        if (!is_array($A))
            return;

        $this->cid = $A['cid'];
        $this->tid = $A['tid'];
        $this->type = trim($A['type']);
        $this->category = trim($A['category']);
        $this->description = trim($A['description']);
        $this->enabled = $A['enabled'] == 1 ? 1 : 0;
        $this->centerblock = $A['centerblock'] == 1 ? 1 : 0;
        $this->grp_view = (int)$A['grp_view'];
        $this->max_img_height = (int)$A['max_img_height'];
        $this->max_img_width = (int)$A['max_img_width'];
    }


    /**
    *   Toggle boolean database fields
    *
    *   @param  string  $field  Databsae field to update
    *   @param  integer $oldval     Current value of item
    *   @param  string  $id     Category ID
    */
    private static function _toggle($field, $oldval, $id)
    {
        global $_TABLES;

        $newval = $oldval == 0 ? 1 : 0;
        DB_query("UPDATE {$_TABLES['bannercategories']}
                SET `$field` = $newval
                WHERE cid = '" . DB_escapeString($id) . "'");
        return DB_error() ? $oldval : $newval;
    }


    /**
    *   Update the "cenberblock" flag for a category
    *
    *   @uses   Category::_toggle()
    *   @param  integer $oldval     Current value of item
    *   @param  string  $id         Category ID.
    *   @return integer     New value, or old value upon error
    */
    public static function toggleCenterblock($oldval, $id)
    {
        return self::_toggle('centerblock', $oldval, $id);
    }


    /**
    *   Update the 'enabled' value for a category
    *
    *   @uses   Category::_toggle()
    *   @param  integer $oldval     Current value of item
    *   @param  string  $id         Category ID
    *   @return integer     New value, or old value upon error
    */
    public function toggleEnabled($oldval, $id)
    {
        return self::_toggle('enabled', $oldval, $id);
    }


    /**
    *   Delete a single category.
    */
    public function Delete()
    {
        global $_TABLES;

        if (self::isRequired($this->type))
            return;

        if (!self::isUsed($this->cid)) {
            DB_delete($_TABLES['bannercategories'],
                'cid', DB_escapeString($this->cid));
        }
    }


    /**
    *   Determine if the current category, or supplied category ID, is used
    *   by any banners
    *
    *   @param  string  $id     Category ID to check
    *   @return boolean         True if the category is in use, fales otherwise.
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
    *   Display the edit form for the current category
    *
    *   @return string  HTML for the edit form
    */
    public function Edit()
    {
        global $_CONF_BANR, $LANG_BANNER;

        if (!$this->canEdit()) {
            return COM_showMessage(6, 'banner');
        }

        $T = new \Template(BANR_PI_PATH . '/templates/admin');
        $tpltype = $_CONF_BANR['_is_uikit'] ? '.uikit' : '';
        $T->set_file(array('page' => "categoryeditor$tpltype.thtml"));

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
            'description'       => $this->description,
            'chk_enabled'       => $this->enabled == 0 ? '' : 'checked="checked"',
            'chk_centerblock'   => $this->centerblock == 0 ? '' : 'checked="checked"',
            'max_img_width'     => $this->max_img_width,
            'max_img_height'    => $this->max_img_height,
            'delete_option'     => $delete_option,
            'iconset'           => $_CONF_BANR['_iconset'],
            'mapping_form'      => Mapping::Form($this->cid),
        ) );

        if ($this->tid === NULL) {
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
        $T->set_var('group_dropdown', SEC_getGroupDropdown($this->grp_view, 3, 'grp_view'));
        $T->set_var('gltoken_name', CSRF_TOKEN);
        $T->set_var('gltoken', SEC_createToken());

        $T->parse ('output', 'page');
        return $retval . $T->finish($T->get_var('output'));
    }


    /**
    *   Save the current category to the database.
    *   If an array is passed in, then the values from that array will be
    *   set in the current object before saving.
    *
    *   @param  array   $A      Optional array of values
    *   @return string          Error message, empty string for success
    */
    public function Save($A='')
    {
        global $_TABLES, $LANG_BANNER;

        if (is_array($A))
            $this->setVars($A);

        if ($this->isNew) {

            // Make sure there's a valid category ID
            if ($this->cid == '') {
                $this->cid = COM_makeSid();
            }

            // Make sure there's not a duplicate ID
            if (DB_count($_TABLES['bannercategories'], 'cid', $this->cid) > 0) {
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
                description='".DB_escapeString($this->description)."',
                tid='".DB_escapeString($this->tid)."',
                enabled={$this->enabled},
                centerblock={$this->centerblock},
                grp_view = {$this->grp_view},
                max_img_height={$this->max_img_height},
                max_img_width={$this->max_img_width}";
        //echo $sql1 . $sql2 . $sql3;die;
        DB_query($sql1 . $sql2 . $sql3);
        if (DB_error()) {
            return $LANG_BANNER['err_saving_item'];
        }
        if ($this->oldcid != $this->cid) {
            // Update banners that were associated with the old ID
            DB_change($_TABLES['banner'], 'cid', $this->cid, 'cid', $this->oldcid);
        }
        if (isset($_POST['map']) && is_array($_POST['map'])) {
            Mapping::saveAll($_POST['map'], $this->cid);
        }
        return '';
    }


    /**
    *   Determine if the current user can edit categories.
    *   Currently plugin admin rights required.
    *
    *   @return boolean     True if user can edit, False if not.
    */
    public function canEdit()
    {
        return plugin_isadmin_banner();
    }


    /**
    *   Determine if the current user can view ads under this category.
    *
    *   @return boolean     True for view access, False if denied
    */
    public function canView()
    {
        return SEC_inGroup($this->grp_view);
    }


    /**
    *   Get the access level that the current user has to this category
    *
    *   @return integer Access level
    */
    public function XhasAccess($requred = 3)
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
    *   Return the option elements for a category selection dropdown
    *
    *   @param  string  $sel    Category ID to show as selected
    *   @return string          HTML for option statements
    */
    public function DropDown($access = 3, $sel='')
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
                        htmlspecialchars($C->description) .
                        '</option>' . LB;
        }
        return $retval;
    }


    /**
    *   Returns True if this category is one of the required ones.
    *
    *   @param  string  $id     Optional category ID.  Current id if empty.
    *   @return boolean         True if category is required, False otherwise.
    */
    public static function isRequired($type)
    {
        global $_TABLES;

        $req_blocks = array('header', 'footer', 'block');

        if (!empty($type) && in_array($type, $req_blocks)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    *   Create the category administration home page.
    *
    *   @return string  HTML for administration page
    *   @see lib-admin.php
    */
    public function AdminList()
    {
        global $LANG_ADMIN, $LANG_BANNER;

        $retval = '';
        $header_arr = array(
                array('text' => $LANG_BANNER['edit'],
                    'field' => 'edit',
                    'sort' => false,
                    'align' => 'center'),
                array('text' => $LANG_BANNER['enabled'],
                    'field' => 'enabled',
                    'sort' => false,
                    'align' => 'center'),
                array('text' => $LANG_BANNER['centerblock'],
                    'field' => 'centerblock',
                    'sort' => true,
                    'align' => 'center'),
                array('text' => 'ID',
                    'field' => 'cid',
                    'sort' => 'true'),
                array('text' => $LANG_BANNER['type'],
                    'field' => 'type',
                    'sort' => 'true'),
                array('text' => $LANG_BANNER['cat_name'],
                    'field' => 'bannercategory',
                    'sort' => true),
                array('text' => $LANG_BANNER['topic'],
                    'field' => 'tid',
                    'sort' => true),
                array('text' => $LANG_ADMIN['delete'],
                    'field' => 'delete',
                    'sort' => false,
                    'align' => 'center'),
        );

        $defsort_arr = array('field' => 'category', 'direction' => 'asc');
        $text_arr = array();
        $dummy = array();
        $data_arr = self::list_categories();
        $retval .= ADMIN_simpleList(__NAMESPACE__ . '\getField_Category', $header_arr,
                                $text_arr, $data_arr);
        return $retval;
    }


    /**
    *   Get the data array of categories
    *
    *   @return array   Array of category information for the admin list
    */
    private function list_categories()
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
    *   Gets all the categories into a static array
    *
    *   @return array   Array of category objects
    */
    public static function getAll()
    {
        global $_TABLES;
        static $cats = NULL;

        if ($cats === NULL) {
            $cats = array();
            $sql = "SELECT * FROM {$_TABLES['bannercategories']}";
            $res = DB_query($sql);
            while ($A = DB_fetchArray($res, false)) {
                $C = new Category();
                $C->setVars($A);
                $cats[$A['cid']] = $C;
            }
        }
        return $cats;
    }

}   // class Category


/**
*   Return the display value for a single field in the admin list
*
*   @param  string  $fieldname      Name of field in the array
*   @param  mixed   $fieldvalue     Value of the field
*   @param  array   $A              Complete array of fields
*   @param  array   $icon_arr       Array of system icons
*   @return string                  HTML to display the field
*/
function getField_Category($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ACCESS, $_CONF_BANR, $LANG_BANNER;

    $retval = '';
    $admin_url = BANR_ADMIN_URL . '/index.php';

    $access = 3;
    switch ($fieldname) {
    case 'edit':
        $retval = COM_createLink('<i class="' . BANR_getIcon('edit') . '"></i>',
                    "$admin_url?edit=x&item=category&amp;cid=" . urlencode($A['cid'])
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
            $retval .= COM_createLink('<i class="' . BANR_getIcon('trash', 'danger') . '"></i>',
                        "$admin_url?delete=x&item=category&amp;cid={$A['cid']}",
                        array(
                            'onclick' => "return confirm('{$LANG_BANNER['ok_to_delete']}');",
                        )
                );
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

    case 'bannercategory':
        $indent = ($A['indent'] - 1) * 20;
        $cat = COM_createLink($A['category'],
                        "$admin_url?banners=x&category=" . urlencode($A['cid']));
        $retval = "<span style=\"padding-left:{$indent}px;\">$cat</span>";
        break;

    case 'tid';
        if ($A['tid'] == 'all' || $A['tid'] == NULL) {
            $retval = $LANG_BANNER['all'];
        } else {
            $retval = DB_getItem($_TABLES['topics'], 'topic',
                                         "tid = '{$A['tid']}'");
        }
        if (empty($retval)) {
            $retval = $A['tid'];
        }
        break;

    default:
        $retval = $fieldvalue;
        break;
    }
    return $retval;
}

?>
