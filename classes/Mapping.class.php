<?php
/**
 * Class to handle banner category to plugin template mappings.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @since       v0.3.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Banner;

/**
 * Class to handle category to template mappings.
 * @package banner
 */
class Mapping
{
    /** Item position, e.g. after first item, second item, etc.
     * @var integer */
    private $pos = 0;

    /** Category ID related to this mapping.
     * @var string */
    private $cid = '';

    /** Template name assigned to this mapping.
     * @var string */
    private $tpl = '';

    /** Flag to show only one banner per page.
     * @var boolean */
    private $once = 0;

    /** Flag to indicate that this mapping is displayed in content.
     * @var boolean */
    private $in_content = 0;


    /**
     * Constructor.
     * Can optionally read a mapping record, or just creates an empty object
     *
     * @param   string  $tpl    Optional template name
     * @param   string  $cid    Optional category name
     */
    public function __construct($tpl = '', $cid = '')
    {
        global $_USER, $_GROUPS, $_CONF_BANR;

        $tpl = COM_sanitizeID($tpl, false);
        $cid = COM_sanitizeID($cid, false);
        if ($tpl != '' && $cid != '') {
            // Have to have both $tpl and $cid to get a record
            $this->Read($tpl, $cid);
        }
    }


    /**
     * Get the position (placement) for this mapping.
     *
     * @return  integer     Placement code
     */
    public function getPos()
    {
        return (int)$this->pos;
    }


    /**
     * Get the template name associated with this mapping.
     *
     * @return  string      Template name
     */
    public function getTpl()
    {
        return $this->tpl;
    }


    /**
     * Get the category ID associated with this mapping.
     *
     * @return  string      Category ID
     */
    public function getCid()
    {
        return $this->cid;
    }


    /**
     * Check if this mapping is shown in content.
     *
     * @return  integer     1 if shown in content, 0 if not
     */
    public function showInContent()
    {
        return $this->in_content ? 1 : 0;
    }


    /**
     * Check if this mapping is shown only once per page.
     *
     * @return  integer     1 if shown only once, 0 if not
     */
    public function showOnce()
    {
        return $this->once ? 1 : 0;
    }


    /**
     * Read a mapping record from the database.
     *
     * @param   string  $tpl    Template name
     * @param   string  $cid    Category ID
     */
    public function Read($tpl, $cid)
    {
        global $_TABLES;

        $sql = "SELECT * FROM {$_TABLES['banner_mapping']}
            WHERE tpl = '" . DB_escapeString($tpl) . "'
            AND cid = '" . DB_escapeString($cid) . "'
            LIMIT 1";
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            if (!empty($A)) {
                $this->setVars($A);
            }
        }
    }


    /**
     * Set the mapping variables from the supplied array.
     * The array may be from a form ($_POST) or database record
     *
     * @param   array   $A          Array of values
     */
    public function setVars($A)
    {
        global $_CONF_BANR, $_CONF;

        if (!is_array($A)) {
            return;
        }

        $this->tpl = $A['tpl'];
        $this->cid = $A['cid'];
        $this->pos = (int)$A['pos'];
        $this->once = isset($A['once']) ? (int)$A['once'] : 0;
        $this->in_content = isset($A['in_content']) ? (int)$A['in_content'] : 0;
    }


    /**
     * Load all mappings into a static array of objects.
     *
     * @return  array   Array of mapping objects
     */
    public static function loadAll()
    {
        global $_TABLES;

        $M = array();
        $sql = "SELECT * FROM {$_TABLES['banner_mapping']}";
        $res = DB_query($sql);
        if ($res && DB_numRows($res) > 0) {
            while ($A = DB_fetchArray($res, false)) {
                $key = $A['tpl'] . '_' . $A['cid'];
                $M[$key] = new self();
                $M[$key]->setVars($A);
            }
        }
        return $M;
    }


    /**
     * Display the mapping edit form.
     * Used with the category edit form.
     *
     * @param   string  $cid    ID of category being edited
     * @return  string  HTML for mapping form
     */
    public static function Form($cid)
    {
        global $_CONF_BANR;

        $A = PLG_supportAdblock();  // get all templates supporting ad blocks
        $M = self::loadAll();
        $T = new \Template(BANR_PI_PATH . '/templates/admin/');
        $T->set_file('mappingform', "mapping.thtml");
        $T->set_block('mappingform', 'MappingItem', 'item');
        foreach ($A as $tpl) {
            $key = $tpl . '_' . $cid;
            if (isset($M[$key])) {
                // Mapping already defined, get the existing settings
                $pos = $M[$key]->getPos();
                $once = $M[$key]->showOnce();
                $enabled = 1;
                $in_content = $M[$key]->showInContent();
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
     * Saves a single mapping.
     * Deletes mappings that are not enabled.
     *
     * @param   array   $A      Array of mapping elements
     * @param   boolean $clear_cache    Flag to immediately clear cache
     */
    public static function Save($A, $clear_cache = true)
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
                        pos = {$M->getPos()},
                        once = {$M->showOnce()},
                        in_content = {$M->showInContent()}
                    ON DUPLICATE KEY UPDATE
                        pos = {$M->getPos()},
                        once = {$M->showOnce()},
                        in_content = {$M->showInContent()}";
        } else {
            $sql = "DELETE FROM {$_TABLES['banner_mapping']}
                    WHERE tpl = '$tpl' AND cid = '$cid'";
        }
        //echo $sql;die;
        DB_query($sql);
    }


    /**
     * Save all the mappings from the category form.
     *
     * @param   array   $A      Array of all mapping fields
     * @param   string  $cat_id Category ID
     */
    public static function saveAll($A, $cat_id)
    {
        foreach ($A as $tpl=>$data) {
            $data['cid'] = $cat_id;
            // delay clearing cache until after all are saved.
            self::Save($data, false);
        }
    }


    /**
     * Get an array of category IDs to use for picking banners.
     *
     * @param   string  $tpl        Template name
     * @param   integer $counter    Item instance counter
     */
    public static function showCats($tpl, $counter)
    {
        $Maps = self::loadAll();
        $cats = array();
        foreach ($Maps as $Map) {
            if ($Map->getTpl() != $tpl) {
                // This mapping doesn't include the current template, skip.
                continue;
            }
            if ($counter == $Map->getPos()) {
                // Whatever $count is, show the enabled category if it matches.
                // Handles fixed placement, e.g. position 2, non-repeating.
                $cats[] = $Map->getCid();
            } elseif ($counter == 0 && $Map->showInContent()) {
                // Check if this is a content page vs. an index.
                // $counter == 0 implies show only once.
                $cats[] = $Map->getCid();
            } elseif (
                $counter > 0 &&
                !$Map->showOnce() &&
                $Map->getPos() > 0 &&
                ($counter % $Map->getPos()) == 0
            ) {
                // Showing every X times on the page.
                $cats[] = $Map->getCid();
            }
        }
        return $cats;
    }

}

?>
