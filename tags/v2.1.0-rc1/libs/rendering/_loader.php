<?php
/* Loader File for the rendering sources
 */

addAutoload('Rendering',		'rendering/rendering_class.php');
addAutoload('HTMLRendering',	'rendering/htmlrendering_class.php');
addAutoload('RawRendering',		'rendering/rawrendering_class.php');

define('HOOK_MENUITEMACCESS', 'menuItemAccess');
/* Hook HOOK_MENUITEMACCESS
 * Determine access in a menu item for a module
 * 
 * Parameters :
 * - boolean $access True to display menu item. Default: true.
 * - string $module The module name
*/
Hook::create(HOOK_MENUITEMACCESS);
