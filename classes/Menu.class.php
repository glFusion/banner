<?php
/**
 * Class to provide admin and user-facing menus.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2019 Lee Garner <lee@leegarner.com>
 * @package     banner
 * @version     v1.0.0
 * @since       v1.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Banner;

/**
 * Class to provide admin and user-facing menus.
 * @package banner
 */
class Menu
{
    /**
     * Create the user menu.
     *
     * @param   string  $view   View being shown, so set the help text
     * @return  string      Administrator menu
     */
    public static function User($view='')
    {
        global $_CONF, $LANG_ADMIN, $LANG_BANNER;

        if (
            isset($LANG_BANNER['admin_hdr_' . $view]) &&
            !empty($LANG_BANNER['admin_hdr_' . $view])
        ) {
            $hdr_txt = $LANG_BANNER['admin_hdr_' . $view];
        } else {
            $hdr_txt = $LANG_BANNER['admin_hdr'];
        }
        $menu_arr = array(
            array(
                'url'  =>  Config::get('url') . '/index.php?edit=x',
                'text' => $LANG_BANNER['banners'],
                'active' => $view == 'banners' ? true : false,
            ),
        );

        $T = new \Template(BANR_PI_PATH . '/templates');
        $T->set_file('title', 'banner_admin_title.thtml');
        $T->set_var('title',
            $LANG_BANNER['banner_mgmt'] . ' (Ver. ' . Config::get('pi_version') . ')');
        $retval = $T->parse('', 'title');
        $retval .= \ADMIN_createMenu(
            $menu_arr, $hdr_txt,
            plugin_geticon_banner()
        );
        return $retval;
    }


    /**
     * Create the administrator menu.
     *
     * @param   string  $view   View being shown, so set the help text
     * @return  string      Administrator menu
     */
    public static function Admin($view='')
    {
        global $_CONF, $LANG_ADMIN, $LANG_BANNER;

        if (
            isset($LANG_BANNER['admin_hdr_' . $view]) &&
            !empty($LANG_BANNER['admin_hdr_' . $view])
        ) {
            $hdr_txt = $LANG_BANNER['admin_hdr_' . $view];
        } else {
            //$hdr_txt = $LANG_BANNER['admin_hdr'];
            $hdr_txt = '';
        }

        $menu_arr = array(
            array(
                'url'  => Config::get('admin_url') . '/index.php',
                'text' => $LANG_BANNER['banners'],
                'active' => $view == 'banners' ? true : false,
            ),
            array(
                'url'  => Config::get('admin_url') . '/index.php?categories=x',
                'text' => $LANG_BANNER['categories'],
                'active' => $view == 'categories' ? true : false,
            ),
            array(
                'url'  => Config::get('admin_url') . '/index.php?campaigns=x',
                'text' => $LANG_BANNER['campaigns'],
                'active' => $view == 'campaigns' ? true : false,
            ),
            array(
                'url'  => $_CONF['site_admin_url'],
                'text' => $LANG_ADMIN['admin_home'],
            ),
        );

        $T = new \Template(BANR_PI_PATH . '/templates');
        $T->set_file('title', 'banner_admin_title.thtml');
        $T->set_var(
            'title',
            $LANG_BANNER['banner_mgmt'] . ' (Ver. ' . Config::get('pi_version') . ')'
        );
        $retval = $T->parse('', 'title');
        $retval .= ADMIN_createMenu(
            $menu_arr,
            $hdr_txt,
            plugin_geticon_banner()
        );
        return $retval;
    }


    /**
     * Display the site header, with or without blocks according to configuration.
     *
     * @param   string  $title  Title to put in header
     * @param   string  $meta   Optional header code
     * @return  string          HTML for site header, from COM_siteHeader()
     */
    public static function siteHeader($title='', $meta='')
    {
        global $LANG_BANNER;

        $retval = '';

        switch(Config::get('displayblocks')) {
        case 2:     // right only
        case 0:     // none
            $retval .= COM_siteHeader('none', $title, $meta);
            break;

        case 1:     // left only
        case 3:     // both
        default :
            $retval .= COM_siteHeader('menu', $title, $meta);
            break;
        }

        return $retval;
    }


    /**
     * Display the site footer, with or without blocks as configured.
     *
     * @return  string      HTML for site footer, from COM_siteFooter()
     */
    public static function siteFooter()
    {
        $retval = '';

        switch(Config::get('displayblocks')) {
        case 2 : // right only
        case 3 : // left and right
            $retval .= COM_siteFooter();
            break;

        case 0: // none
        case 1: // left only
        default :
            $retval .= COM_siteFooter();
            break;
        }
        return $retval;
    }

}

?>


