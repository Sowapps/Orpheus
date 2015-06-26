<?php
echo '
<ul class="nav navbar-nav">';
foreach( $items as $item ) {
	echo '
	<li class="item'.(isset($item->module) ? ' '.$item->module : '').(!empty($item->current) ? ' active' : '').'"><a href="'.$item->link.'">'.$item->label.'</a></li>';
}
echo '
</ul>
';
