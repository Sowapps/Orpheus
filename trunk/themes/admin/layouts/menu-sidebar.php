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

/*
<ul class="nav navbar-nav side-nav">
	<li class="active"><a href="index.html"><i class="fa fa-dashboard"></i> Dashboard</a></li>
	<li><a href="charts.html"><i class="fa fa-bar-chart-o"></i> Charts</a></li>
	<li><a href="tables.html"><i class="fa fa-table"></i> Tables</a></li>
	<li><a href="forms.html"><i class="fa fa-edit"></i> Forms</a></li>
	<li><a href="typography.html"><i class="fa fa-font"></i> Typography</a></li>
	<li><a href="bootstrap-elements.html"><i class="fa fa-desktop"></i> Bootstrap Elements</a></li>
	<li><a href="bootstrap-grid.html"><i class="fa fa-wrench"></i> Bootstrap Grid</a></li>
	<li><a href="blank-page.html"><i class="fa fa-file"></i> Blank Page</a></li>
	<li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-caret-square-o-down"></i> Dropdown <b class="caret"></b></a>
		<ul class="dropdown-menu">
			<li><a href="#">Dropdown Item</a></li>
			<li><a href="#">Another Item</a></li>
			<li><a href="#">Third Item</a></li>
			<li><a href="#">Last Item</a></li>
		</ul>
	</li>
</ul>
*/