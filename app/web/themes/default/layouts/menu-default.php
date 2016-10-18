<?php
//A tag fills the li space
//span allows to fill A width with a reduced height

echo '
<ul class="nav menu '.$menu.'">';
foreach( $items as $item ) {
	echo '
	<li class="item'.(isset($item->module) ? ' '.$item->module : '').(!empty($item->current) ? ' current active' : '').'"><a href="'.$item->link.'"><span>'.$item->label.'</span></a></li>';
}
echo '
</ul>';
