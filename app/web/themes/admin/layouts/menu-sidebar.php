<?php
/* @var User $USER */

global $USER;

// http://fortawesome.github.io/Font-Awesome/icons/
// $modIcons = array(
// 	'adm_projects'	=> 'fa-briefcase',
// 	'adm_users'		=> 'fa-users',
// 	'adm_partners'	=> 'fa-hand-o-right',
// 	'adm_logs'		=> 'fa-bolt',
// // 	'adm_logs'		=> 'fa-archive',
// 	'dev_entities'	=> 'fa-magic',
// // 	'dev_entities'	=> 'fa-gears',
// );

// if( empty($items) ) { return; }

echo '
<ul class="nav navbar-nav side-nav menu '.$menu.'">';

foreach( $items as $item ) {
// 	$icon = isset($modIcons[$item->module]) ? '<i class="fa '.$modIcons[$item->module].'"></i>' : '';
//'.$icon.' 
	echo '
	<li class="item'.(isset($item->route) ? ' '.$item->route : '').(!empty($item->current) ? ' active' : '').'"><a href="'.$item->link.'">'.$item->label.'</a></li>';
}
echo '
</ul>';
