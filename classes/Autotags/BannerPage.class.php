<?php
/**
 * Create a page of banners.
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
use Banner\Config;
use Banner\Banner;


if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

/**
 * Base autotag class
 * @package banner
 */
class BannerPage extends Autotag
{
    /** Default template name if none requested.
     * @var string */
    protected $_template = 'banner_page';


    /**
     * Parse the autotag and render the output.
     *
     * @param   string  $p1         First option after the tag name
     * @param   string  $opts       Name=>Vaue array of other options
     * @param   string  $fulltag    Full autotag string
     * @return  string      Replacement HTML, if applicable.
     */
    public function parse(string $p1, array $opts=array(), string $fulltag='') : string
    {
        $retval = '';
        $Banners = Banner::getBanners($opts);
        if (count($Banners) > 0) {
            $T = new \Template(Config::path_template() . 'autotags');
            $T->set_file('page', $this->_template . '.thtml');
            $T->set_block('page', 'blkBanners', 'blk');
            foreach ($Banners as $idx=>$Banner) {
                $T->set_var(array(
                    'odd' => ($idx +1) % 2,
                    'banner' => $Banner->BuildBanner(),
                    'dscp' => $Banner->getDscp(),
                    'category' => $Banner->getCid(),
                    'campaign' => $Banner->getCampId(),
                    'banner_id' => $Banner->getBid(),
                ) );
                $T->parse('blk', 'blkBanners', true);
            }
            $retval = $T->finish($T->parse('output', 'page'));
        }
        return $retval;
    }

}
