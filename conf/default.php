<?php
/*
 * default configuration settings
 *
 */

$conf['discussionPage']   = 'discussion:@ID@';
$conf['userPage']         = 'user:@USER@:';
$conf['hideTools']        = 0;
$conf['tagline']          = 'This is the tagline - explaining what this site is about.';
$conf['sidebarID']        = 'sidebar';
//Custom styles to allow different site/sidebar widths per namespace
//Syntax: "namespace site-width sidebar-width;namespace-2 site-width-2 sidebar-width-2"
//(semicolons between namespaces sections, spaces within a section
$conf['nsWidth']          = '';
