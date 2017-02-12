<?php
/* @var User $USER */

global $USER;

// http://fortawesome.github.io/Font-Awesome/icons/
// $modIcons = array(
// 	'adm_projects'	=> 'fa-briefcase',
// );

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
