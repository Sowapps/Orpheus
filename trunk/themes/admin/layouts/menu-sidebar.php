<?php

$modIcons = array(
	'adm_projects'	=> 'fa-briefcase',
	'adm_users'		=> 'fa-users',
	'adm_logs'		=> 'fa-bolt',
// 	'adm_logs'		=> 'fa-archive',
);

echo '
<ul class="nav navbar-nav side-nav menu '.$menu.'">';
foreach( $items as $item ) {
	$icon = isset($modIcons[$item->module]) ? '<i class="fa '.$modIcons[$item->module].'"></i>' : '';
	echo '
	<li class="item'.(isset($item->module) ? ' '.$item->module : '').(!empty($item->current) ? ' active' : '').'"><a href="'.$item->link.'">'.$icon.' '.$item->label.'</a></li>';
}
echo '
</ul>';
