<?php

$users	= SiteUser::get(array(
	'where'		=> ( User::canDo('users_seedev') ) ? '' : 'accesslevel<='.Config::get('perm_status/administrator'),
	'orderby'	=> 'fullname ASC',
// 	'output'	=> SQLAdapter::ARR_OBJECTS
));

displayReportsHTML();
?>
<h3>Users list</h3>
<ul class="userslist">
<?php
foreach( $users as $user ) {
	echo "
	<li>
		<span>{$user->name}</span>
		<span>{$user->public_email}</span>
	</li>";
}
?>
</ul>