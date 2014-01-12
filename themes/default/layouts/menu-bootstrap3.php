<?php
//A tag fills the li space
//span allows to fill A width with a reduced height

echo '
<nav class="collapse navbar-collapse" role="navigation">
	<ul class="nav navbar-nav">';
foreach( $items as $item ) {
	echo '
<li class="item'.(isset($item->module) ? ' '.$item->module : '').(!empty($item->current) ? ' active' : '').'"><a href="'.$item->link.'">'.$item->label.'</a></li>';
}
echo '
	</ul>
</nav>';
