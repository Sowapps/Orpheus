<?php

$users	= SiteUser::get(array(
	'where'		=> ( SiteUser::loggedCanDo('users_seedev') ) ? '' : 'accesslevel<'.Config::get('perm_status/administrator'),
	'orderby'	=> 'fullname ASC',
// 	'output'	=> SQLAdapter::ARR_OBJECTS
));

displayReportsHTML();
?>
<div class="page-header">
  <h1>Members <small>Member list</small></h1>
</div>

<table class="table">
	<tr>
		<th>Name</th>
		<th>Email</th>
	</tr>
<?php
foreach( $users as $user ) {
	echo '
	<tr>
		<td>'.$user.'</td>
		<td>'.( !empty($user->email_public) ? $user->email_public : 'Hidden').'</td>
	</tr>';
}
?>
</table>