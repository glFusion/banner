<?php
/**
*   Class to handle banner category to plugin template mappings
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
*   @package    banner
*   @version    0.3.0
*   @since      0.3.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
namespace Banner;

/**
*   Define a class to deal with banners
*   @package banner
*/
class Mapping
{
    private $properties = array();

    /**
    *   Constructor
    *   Can optionally read a mapping record, or just creates an empty object
    *
    *   @param  string  $tpl    Optional template name
    *   @param  string  $cid    Optional category name
    */
    public function __construct($tpl = '', $cid = '')
    {
        global $_USER, $_GROUPS, $_CONF_BANR;

        $tpl = COM_sanitizeID($tpl, false);
        $cid = COM_sanitizeID($cid, false);
        if ($tpl != '' && $cid != '') {
            // Have to have both $tpl and $cid to get a record
            $this->Read($tpl, $cid);
        } else {
            // Set defaults for new record
            $this->pos = 0;
            $this->once = 0;
            $this->tpl = '';
            $this->cid = '';
            $this->enabled = 0;
            $this->in_content = 0;
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
        global $_CONF_BANR;

        switch ($key) {
        case 'pos':
            $this->properties[$key] = (int)$value;
            break;

        case 'cid':
        case 'tpl':
            $this->properties[$key] = trim($value);
            break;

        case 'once':
        case 'enabled':
        case 'in_content':
            $this->properties[$key] = $value == 1 ? 1 : 0;
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
    *   Read a mapping record from the database
    *
    *   @param  string  $bid    Banner ID to read (required)
    */
    public function Read($tpl, $cid)
    {
        global $_TABLES;

        $sql = "SELECT * FROM {$_TABLES['banner_mapping']}
                WHERE tpl = '" . DB_escapeString($tpl) . "'
                AND cid = '" . DB_escapeString($cid) . "'";
        $res = DB_query($sql);
        $A = DB_fetchArray($res, false);
        if (!empty($A)) {
            $this->setVars($A);
        }
    }


    /**
    *   Set the mapping variables from the supplied array.
    *   The array may be from a form ($_POST) or database record
    *
    *   @param  array   $A          Array of values
    */
    public function setVars($A)
    {
        global $_CONF_BANR, $_CONF;

        if (!is_array($A))
            return;

        foreach (array('tpl', 'cid', 'pos', 'once', 'enabled', 'in_content') as $key) {
            $this->$key = isset($A[$key]) ? $A[$key] : '';
        }
    }


    /**
    *   Load all mappings into a static array of objects
    *
    *   @return array   Array of mapping objects
    */
    public static function loadAll()
    {
        global $_TABLES;
        static $M = NULL;

        if ($M === NULL) {
            $sql = "SELECT * FROM {$_TABLES['banner_mapping']}";
            $res = DB_query($sql);
            while ($A = DB_fetchArray($res, false)) {
                $key = $A['tpl'] . '_' . $A['cid'];
                $M[$key] = new self();
                $M[$key]->setVars($A);
            }
            if (empty($M)) $M = array();
        }
        return $M;
    }


    /**
    *   Display the mapping edit form.
    *   Used with the category edit form.
    *
    *   @param  string  $cid    ID of category being edited
    *   @return string  HTML for mapping form
    */
    public static function Form($cid)
    {
        global $_CONF_BANR;

        $A = PLG_supportAdblock();
        $M = self::loadAll();
        $T = new \Template(BANR_PI_PATH . '/templates/admin/');
        $tpltype = $_CONF_BANR['_is_uikit'] ? '.uikit' : '';
        $T->set_file('mappingform', "mapping$tpltype.thtml");
        $T->set_block('mappingform', 'MappingItem', 'item');
        $T->set_var('iconset', $_CONF_BANR['_iconset']);
        foreach ($A as $tpl) {
            $key = $tpl . '_' . $cid;
            if (isset($M[$key])) {
                $pos = $M[$key]->pos;
                $once = $M[$key]->once;
                $enabled = 1;
                $in_content = $M[$key]->in_content;
            } else {
                $pos = 0;
                $once = 0;
                $enabled = 0;
                $in_content = 0;
            }
            $T->set_var(array(
                'tpl'       => $tpl,
                'pos'       => $pos,
                'content_chk' => $in_content ? 'checked="checked"' : '',
                'once_chk'  => $once ? 'checked="checked"' : '',
                'ena_chk'   => $enabled ? 'checked="checked"' : '',
                'pos_sel_' . $pos => 'selected="selected"',
            ) );
            $T->parse('item', 'MappingItem', true);
            $T->clear_var('pos_sel_' . $pos);
        }
        $T->parse ('output', 'mappingform');
        return $T->finish($T->get_var('output'));
    }


    /**
    *   Saves a single mapping
    *   Deletes mappings that are not enabled
    *
    *   @param  array   $A      Array of mapping elements
    */
    public static function Save($A)
    {
        global $_TABLES;

        $tpl = DB_escapeString($A['tpl']);
        $cid = DB_escapeString($A['cid']);
        if (isset($A['enabled']) && $A['enabled']) {
            $M = new self($A['tpl'], $A['cid']);
            $M->setVars($A);

            $sql = "INSERT INTO {$_TABLES['banner_mapping']} SET
                        tpl = '$tpl',
                        cid = '$cid',
                        pos = {$M->pos},
                        once = {$M->once},
                        in_content = {$M->in_content}
                    ON DUPLICATE KEY UPDATE
                        pos = {$M->pos},
                        once = {$M->once},
                        in_content = {$M->in_content}";
        } else {
            $sql = "DELETE FROM {$_TABLES['banner_mapping']}
                    WHERE tpl = '$tpl' AND cid = '$cid'";
        }
        //echo $sql;die;
        DB_query($sql);
    }


    /**
    *   Save all the mappings from the category form.
    *
    *   @param  array   $A      Array of all mapping fields
    *   @param  string  $cat_id Category ID
    */
    public static function saveAll($A, $cat_id)
    {
        foreach ($A as $tpl=>$data) {
            $data['cid'] = $cat_id;
            self::Save($data);
        }
    }


    /**
    *   Get an array of category IDs to use for picking banners.
    *
    *   @param  string  $tpl        Template name
    *   @param  integer $counter    Item instance counter
    */
    public static function showCats($tpl, $counter)
    {
        $Maps = self::loadAll();
        $cats = array();
        foreach ($Maps as $Map) {
            if ($Map->tpl != $tpl) continue;
            if ($counter == $Map->pos) {
                // Whatever $count is, show the enabled category if it matches
                // Handles fixed placement, e.g. position 2, non-repeating
                $cats[] = $Map->cid;
            } elseif ($counter == 0 && $Map->in_content == 1) {
                // Check if this is a content page vs. an index
                // $counter == 0 implies show only once
                $cats[] = $Map->cid;
            } elseif ($counter > 0 && !$Map->once && $Map->pos > 0 && ($counter % $Map->pos) == 0) {
                // If showing every X items, see if this is a matching item
                $cats[] = $Map->cid;
            }
        }
        return $cats;
    }

}

?>
