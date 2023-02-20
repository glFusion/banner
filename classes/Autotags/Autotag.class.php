<?php
/**
 * Base class for autotag processing.
 *
 * @copyright   Copyright (c) 2023 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.1.0
 * @since       v1.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Banner\Autotags;
use glFusion\Database\Database;
use glFusion\Log\Log;
use Banner\Config;


if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

/**
 * Base autotag class.
 * @package banner
 */
abstract class Autotag
{

    /** Template name, must be set by the child class.
     * @var string */
    protected $_template = '';


    /**
     * Set the template name to be used.
     *
     * @param   string  $tpl_name   Template filename
     * @return  object  $this
     */
    public function withTemplate(string $tpl_name) : self
    {
        $this->_template = $tpl_name;
        return $this;
    }


    /**
     * Parse the autotag and render the output.
     *
     * @param   string  $p1         First option after the tag name
     * @param   string  $opts       Name=>Vaue array of other options
     * @param   string  $fulltag    Full autotag string
     * @return  string      Replacement HTML, if applicable.
     */
    abstract public function parse(string $p1, array $opts=array(), string $fulltag='') : string;

}
