<?php
/**
 * Template Functions
 *
 * This file provides template specific custom functions that are
 * not provided by the DokuWiki core.
 * It is common practice to start each function with an underscore
 * to make sure it won't interfere with future core functions.
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

/**
 * Create link/button to discussion page and back
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_discussion($discussionPage,$title,$backTitle,$link=0,$wrapper=0) {
    global $ID;

    $discussPage    = str_replace('@ID@',$ID,$discussionPage);
    $discussPageRaw = str_replace('@ID@','',$discussionPage);
    $isDiscussPage  = strpos($ID,$discussPageRaw)!==false;
    $backID         = str_replace($discussPageRaw,'',$ID);

    if ($wrapper) echo "<$wrapper>";

    if ($isDiscussPage) {
        if ($link)
            tpl_pagelink($backID,$backTitle);
        else
            echo html_btn('back2article',$backID,'',array(),'get',0,$backTitle);
    } else {
        if ($link)
            tpl_pagelink($discussPage,$title);
        else
            echo html_btn('discussion',$discussPage,'',array(),'get',0,$title);
    }

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Create link/button to user page
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_userpage($userPage,$title,$link=0,$wrapper=0) {
    if (!$_SERVER['REMOTE_USER']) return;

    global $conf;
    $userPage = str_replace('@USER@',$_SERVER['REMOTE_USER'],$userPage);

    if ($wrapper) echo "<$wrapper>";

    if ($link)
        tpl_pagelink($userPage,$title);
    else
        echo html_btn('userpage',$userPage,'',array(),'get',0,$title);

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Create link/button to register page
 * DW versions > 2011-02-20 can use the core function tpl_action('register')
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_register($link=0,$wrapper=0) {
    global $conf;
    global $lang;
    global $ID;
    $lang_register = !empty($lang['btn_register']) ? $lang['btn_register'] : $lang['register'];

    if ($_SERVER['REMOTE_USER'] || !$conf['useacl'] || !actionOK('register')) return;

    if ($wrapper) echo "<$wrapper>";

    if ($link)
        tpl_link(wl($ID,'do=register'),$lang_register,'class="action register" rel="nofollow"');
    else
        echo html_btn('register',$ID,'',array('do'=>'register'),'get',0,$lang_register);

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Wrapper around custom template actions
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_action($type,$link=0,$wrapper=0) {
    switch ($type) {
        case 'discussion':
            if (tpl_getConf('discussionPage')) {
                _tpl_discussion(tpl_getConf('discussionPage'),tpl_getLang('discussion'),tpl_getLang('back_to_article'),$link,$wrapper);
            }
            break;
        case 'userpage':
            if (tpl_getConf('userPage')) {
                _tpl_userpage(tpl_getConf('userPage'),tpl_getLang('userpage'),$link,$wrapper);
            }
            break;
        case 'register':
            _tpl_register($link,$wrapper);
            break;
    }
}

/**
 * Returns icon from data/media root directory if it exists, otherwise
 * the one in the template's image directory.
 *
 * @param  bool $abs        - if to use absolute URL
 * @param  string $fileName - file name of icon
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_getFavicon($abs=false, $fileName='favicon.ico') {
    if (file_exists(mediaFN($fileName))) {
        return ml($fileName, '', true, '', $abs);
    }

    if($abs) {
        return DOKU_URL.substr(DOKU_TPL.'images/'.$fileName, strlen(DOKU_REL));
    }
    return DOKU_TPL.'images/'.$fileName;
}

/* use core function if available, otherwise the custom one */
if (!function_exists('tpl_getFavicon')) {
    function tpl_getFavicon($abs=false, $fileName='favicon.ico'){
        _tpl_getFavicon($abs, $fileName);
    }
}


/**
 * Returns <link> tag for various icon types (favicon|mobile|generic)
 *
 * @param  array $types - list of icon types to display (favicon|mobile|generic)
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_favicon($types=array('favicon')) {

    $return = '';

    foreach ($types as $type) {
        switch($type) {
            case 'favicon':
                $return .= '<link rel="shortcut icon" href="'.tpl_getFavicon().'" />'.NL;
                break;
            case 'mobile':
                $return .= '<link rel="apple-touch-icon" href="'.tpl_getFavicon(false, 'apple-touch-icon.png').'" />'.NL;
                break;
            case 'generic':
                // ideal world solution, which doesn't work in any browser yet
                $return .= '<link rel="icon" href="'.tpl_getFavicon(false, 'icon.svg').'" type="image/svg+xml" />'.NL;
                break;
        }
    }

    return $return;
}

/* use core function if available, otherwise the custom one */
if (!function_exists('tpl_favicon')) {
    function tpl_favicon($types=array('favicon')){
        _tpl_favicon($types);
    }
}


/**
 * Include additional html file from conf directory if it exists, otherwise use
 * file in the template's root directory.
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_include($fn) {
    $confFile = DOKU_CONF.$fn;
    $tplFile  = dirname(__FILE__).'/'.$fn;

    if (file_exists($confFile))
        include($confFile);
    else if (file_exists($tplFile))
        include($tplFile);
}


/**
 * Use the sidebar page from current namespace if available, if not use the global one
 *
 * @author Symon Bent hendrybadao@gmail.com
 */

function _tpl_sidebar() {
    global $INFO;

    $id = tpl_getConf('sidebarID');
    $ns = $INFO['namespace'];

    do {
        $sidebar = $ns . ':' . $id;
        if (page_exists($sidebar)) {
            return $sidebar;
        }
        $ns = substr($ns, 0, strrpos($ns, ':'));
    } while ( ! empty($ns));
    return $id;
}


/**
 * Custom styles to allow different site/sidebar widths per namespace
 * Set in template's configuration (nsWidth)
 * Syntax: "<namespace> <site-width> <sidebar-width>;<namespace-2> <site-width-2> <sidebar-width-2>"
 *          (semicolons between namespaces sections, spaces within a section)
 * @author Symon Bent hendrybadao@gmail.com
 */
function _tpl_ns_styles() {
    global $INFO;
    $result = '';

    $cur_ns = $INFO['namespace'];
    $args = explode(';', tpl_getConf('nsWidth'));
    foreach ($args as $arg) {
        list ($ns, $site_width, $sidebar_width) = explode(' ', $arg);
        $ns = ltrim(trim($ns), ':');
        $match = strpos($cur_ns, $ns);
        if ($match == 0 && $match !== false) {
            $result = '<style>' .
                        '.mode_show #dokuwiki__aside { width: ' . $sidebar_width . '; }' . DOKU_LF .
                        '.mode_show #dokuwiki__content { margin-left: -' . $sidebar_width . '; }' . DOKU_LF .
                        '.mode_show #dokuwiki__content .pad { margin-left: ' . $sidebar_width . '; }' . DOKU_LF .
                        '#dokuwiki__site .wrapper { max-width: ' . $site_width . '; }' . DOKU_LF .
                      '</style>';
            echo $result;
            break;
        }
    }
}