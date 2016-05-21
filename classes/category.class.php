<?php
//  $Id: category.class.php 16 2009-10-19 04:21:05Z root $
/**
*   Class to handle banner categories
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2016 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.1.7
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

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

        //$id = trim($id);
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
            $this->owner_id = empty($_USER['uid']) ? 0 : (int)$_USER['uid'];
            $this->group_id = (int)DB_getItem($_TABLES['groups'], 
                    'grp_id', "grp_name='banner Admin'");
            $this->perm_owner = 3;
            $this->perm_group = 2;
            $this->perm_members = 2;
            $this->perm_anon = 2;
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

        case 'owner_id':
        case 'group_id':
        case 'perm_owner':
        case 'perm_group':
        case 'perm_members':
        case 'perm_anon':
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
        $this->owner_id = (int)$A['owner_id'];
        $this->group_id = (int)$A['group_id'];
        $this->max_img_height = (int)$A['max_img_height'];
        $this->max_img_width = (int)$A['max_img_width'];

        // If permissions come in via form variables, they'll be in arrays.
        // From the database, they're integers.
        if (is_array($A['perm_owner']) || is_array($A['perm_group']) ||
            is_array($A['perm_members']) || is_array($A['perm_anon'])) {
            list($this->perm_owner, $this->perm_group,
                $this->perm_members, $this->perm_anon) =
                SEC_getPermissionValues($A['perm_owner'],$A['perm_group'],
                    $A['perm_members'], $A['perm_anon']);
        } else {
            $this->perm_owner = (int)$A['perm_owner'];
            $this->perm_group = (int)$A['perm_group'];
            $this->perm_members = (int)$A['perm_members'];
            $this->perm_anon = (int)$A['perm_anon'];
        }
    }


    /**
    *   Update the "cenberblock" flag for a category
    *
    *   @param  integer $newval     New value to set (1 or 0)
    *   @param  string  $bid        Optional category ID.  Current object if blank
    */
    public function toggleCenterblock($newval, $id='')
    {
        global $_TABLES;

        if ($id == '') {
            if (is_object($this)) {
                $id = $this->cid;
            } else {
                return;
            }
        }

        $newval = $newval == 0 ? 0 : 1;
        DB_query("UPDATE {$_TABLES['bannercategories']}
                SET centerblock = $newval
                WHERE cid='" . DB_escapeString($id)."'");
    }


    /**
    *   Update the 'enabled' value for a category
    *
    *   @param  integer $newval     New value to set (1 or 0)
    *   @param  string  $bid        Optional ad ID.  Current object if blank
    */
    public function toggleEnabled($newval, $id='')
    {
        global $_TABLES;

        if ($id == '') {
            if (is_object($this)) {
                $id = $this->cid;
            } else {
                return;
            }
        }

        $newval = $newval == 0 ? 0 : 1;
        DB_query("UPDATE {$_TABLES['bannercategories']}
                SET enabled = $newval
                WHERE cid = '" . DB_escapeString($id) . "'");
    }


    /**
    *   Delete a single category.
    *   This may be called as a standalone function with a supplied
    *   category ID, or as "$object->Delete()"
    *
    *   @param  string  $id     Optional category ID to delete
    */
    public function Delete($id='')
    {
        global $_TABLES;

        if ($id == '') {
            if (is_object($this)) {
                $id = $this->cid;
            } else {
                return;
            }
        }

        if (self::isRequired($id))
            return;

        if (!$this->isUsed($id)) {
            DB_delete($_TABLES['bannercategories'],
                'cid', DB_escapeString(trim($id)));
        }
    }


    /**
    *   Determine if the current category, or supplied category ID, is used
    *   by any banners
    *
    *   @param  string  $id     Optional ID to check
    *   @return boolean         True if the category is in use, fales otherwise.
    */
    public function isUsed($id='')
    {
        global $_TABLES;

        if ($id == '') {
            if (is_object($this)) {
                $id = $this->cid;
            } else {
                return;
            }
        }

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
    public function Edit($cid='')
    {
        global $_CONF, $_CONF_BANR, $MESSAGE, $LANG_BANNER,
                $LANG_ADMIN, $LANG_ACCESS, $LANG_BANNER_ADMIN,
                $_SYSTEM;

        if (!$this->hasAccess()) {
            return COM_showMessage(6, 'banner');
        }

        $T = new Template(BANR_PI_PATH . '/templates/admin');
        $tpltype = $_SYSTEM['framework'] == 'uikit' ? '.uikit' : '';
        $T->set_file(array('page' => "categoryeditor$tpltype.thtml"));

        $T->set_var(array(
            'help_url'      => BANNER_docURL('categoryform.html'),
            'cancel_url'    => BANR_ADMIN_URL . '/index.php?view=categories',
        ));

        if (!empty($this->cid) &&
            !$this->isUsed() && !$this->isRequired()) {
            $T->set_var('delete_option', 'true');
        } else {
            $T->set_var('delete_option', '');
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
        ));

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
        $T->set_var('owner_name', COM_getDisplayName($this->owner_id));
        $T->set_var('cat_ownerid', $A['owner_id']);
        $T->set_var('group_dropdown', SEC_getGroupDropdown($this->group_id, 3));
        $T->set_var('permissions_editor', 
            SEC_getPermissionsHTML($this->perm_owner, $this->perm_group, 
                $this->perm_members, $this->perm_anon));
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
                owner_id={$this->owner_id},
                group_id={$this->group_id},
                perm_owner={$this->perm_owner},
                perm_group={$this->perm_group},
                perm_members={$this->perm_members},
                perm_anon={$this->perm_anon},
                max_img_height={$this->max_img_height},
                max_img_width={$this->max_img_width}";
        DB_query($sql1 . $sql2 . $sql3);
    }


    /**
    *   Get the access level that the current user has to this category
    *
    *   @return integer Access level
    */
    public function hasAccess()
    {
        $access = SEC_hasAccess($this->owner_id, $this->group_id,
                    $this->perm_owner, $this->perm_group, 
                    $this->perm_members, $this->perm_anon);
        return $access;
    }
 

    /**
    *   Return the option elements for a category selection dropdown
    *
    *   @param  string  $sel    Category ID to show as selected
    *   @return string          HTML for option statements
    */
    public function DropDown($access = 3, $sel='')
    {
        global $_TABLES;

        $retval = '';
        $sel = COM_sanitizeID($sel, false);
        $access = (int)$access;

        // Retrieve the campaigns to which the current user has access
        $sql = "SELECT c.cid, c.description
                FROM {$_TABLES['bannercategories']} c
                WHERE 1=1 ";
        if ($access > 0) {
            $sql .= COM_getPermSQL('AND', 0, $access, 'c');
        }
        //echo $sql;
        $result = DB_query($sql);

        while ($row = DB_fetchArray($result)) {
            $selected = $row['cid'] === $sel ? ' selected="selected"' : '';
            $retval .= "<option value=\"" . 
                        htmlspecialchars($row['cid']) .
                        "\"$selected>" .
                        htmlspecialchars($row['description']) .
                        "</option>\n";
        }

        return $retval;
    }


    /**
    *   Returns True if this category is one of the required ones.
    *
    *   @param  string  $id     Optional category ID.  Current id if empty.
    *   @return boolean         True if category is required, False otherwise.
    */
    public function isRequired($id='')
    {
        global $_TABLES;

        $req_blocks = array('header', 'footer', 'block');

        if ($id == '' && is_object($this)) {
            $type = $this->type;
        } else {
            $type = DB_getItem($_TABLES['bannercategories'], 'type',
                    "cid='" . DB_escapeString($id) . "'");
        }

        if (!empty($type) && in_array($type, $req_blocks)) {
            return true;
        } else {
            return false;
        }
    }

}   // class Category


/**
*   Create the category administration home page.
*
*   @return string  HTML for administration page
*   @see lib-admin.php
*/
function BANNER_adminCategories()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_BANNER, $LANG_ACCESS,
           $_IMAGE_TYPE, $_CONF_BANR;

    $retval = '';
    $header_arr = array(      # display 'text' and use table field 'field'
                array('text' => $LANG_BANNER['edit'],
                    'field' => 'edit', 
                    'sort' => false),
                array('text' => $LANG_BANNER['enabled'],
                    'field' => 'enabled',
                    'sort' => false),
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
                    'sort' => false),
    );

    $defsort_arr = array('field' => 'category', 'direction' => 'asc');

    $text_arr = array();
    $dummy = array();
    $data_arr = BANNER_list_categories($dummy, $_CONF_BANR['root'], 0);

    $retval .= ADMIN_simpleList('BANNER_getField_Category', $header_arr,
                                $text_arr, $data_arr);

    return $retval;
}


/**
*   Return the display value for a single field in the admin list
*
*   @param  string  $fieldname      Name of field in the array
*   @param  mixed   $fieldvalue     Value of the field
*   @param  array   $A              Complete array of fields
*   @param  array   $icon_arr       Array of system icons
*   @return string                  HTML to display the field
*/
function BANNER_getField_Category($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ACCESS, $_CONF_BANR, $LANG_BANNER;

    $retval = '';
    $admin_url = BANR_ADMIN_URL . '/index.php';

    $access = 3;
    switch ($fieldname) {
    case 'edit':
        $retval = COM_createLink(
                    $icon_arr['edit'],
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
                onclick='BANR_toggleEnabled(this, \"{$A['cid']}\",\"category\", \"{$_CONF['site_url']}\");' />\n";
        break;


    case 'delete':
        if (!Category::isRequired($A['cid']) && !Category::isUsed($A['cid'])) {
            $retval .= COM_createLink('<img src='. $_CONF['layout_url']
                            . '/images/admin/delete.png>',
                        "$admin_url?delete=x&item=category&amp;cid={$A['cid']}",
                        array('onclick' => "return confirm('{$LANG_BANNER['ok_to_delete']}');"));
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
                onclick='BANR_toggleEnabled(this, \"{$A['cid']}\",\"cat_cb\", \"{$_CONF['site_url']}\");' />\n";
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

function BANNER_list_categories($data_arr, $cid)
{
    global $_CONF, $_TABLES, $_CONF_BANR;

    $cid = DB_escapeString($cid);

    // get all children of present category
    $sql = "SELECT 
                cid, category, tid, type, owner_id, group_id, centerblock,
                perm_owner, perm_group, perm_members, perm_anon, enabled
            FROM {$_TABLES['bannercategories']} 
            WHERE (1=1) " 
            . COM_getPermSQL('AND', 0, 3)
            . " ORDER BY category";
    //echo $sql;die;

    $result = DB_query($sql);
    while ($A = DB_fetchArray($result)) {
        $topic = DB_getItem($_TABLES['topics'], 'topic', "tid='{$A['tid']}'");
        $A['topic_text'] = $topic;
        $data_arr[] = $A;
    }

    return $data_arr;
}


?>
