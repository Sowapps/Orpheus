<?php
/* @var $USER User */

$formData = array();
if( isPOST('submitCreate') ) {
	
	try {
		$formData = POST('createData');
		$newUser = User::create($formData);
		reportSuccess('successCreate', 'users');
		$formData = array();
	
	} catch(UserException $e) {
		reportError($e, 'users');
	}
}

$USER_CAN_USER_EDIT	= $USER->canUserEdit();

$UsersArr = User::get(array(
	'where'		=> $USER->canSeeDevelopers() ? '' : 'accesslevel<='.Config::get('user_roles/administrator'),
	'orderby'	=> 'fullname ASC',
	'output'	=> SQLAdapter::ARR_OBJECTS
));

?>
<form method="POST">

<div class="row">
	<div class="col-lg-12">
<!-- 		<h2>Bordered Table</h2> -->
		<div class="table-responsive">
			<table class="table table-bordered table-hover tablesorter">
				<thead>
					<tr>
						<th># <i class="fa fa-sort" title="Trier par ID"></i></th>
						<th>Nom <i class="fa fa-sort" title="Trier par Nom"></i></th>
						<th>Email <i class="fa fa-sort" title="Trier par Email"></i></th>
						<th>Rôle <i class="fa fa-sort" title="Trier par Rôle"></i></th>
						<th class="sorter-false">Actions</th>
					</tr>
				</thead>
				<tbody>
<?php
foreach( $UsersArr as $user ) {
	echo "
<tr>
	<td>{$user->id}</td>
	<td>{$user}</td>
	<td>{$user->email}</td>
	<td>{$user->getRoleText()}</td>".
	( $USER_CAN_USER_EDIT ? '<td><a class="fa fa-edit" href="'.$user->getAdminLink().'" title="Éditer"></a></td>' : '').
	"
</tr>";
}
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
</form>

<?php
if( $USER_CAN_USER_EDIT ) {
	?>
<form method="POST">
<div class="row">
	<div class="col-lg-6">
		<div class="adduserform">
		<h2>Ajouter un utilisateur</h2>
		<div class="form-group">
			<label>Nom</label>
			<input class="form-control" type="text" name="createData[fullname]" <?php echo htmlValue('fullname'); ?>/>
		</div>
		<div class="form-group">
			<label>Email</label>
			<input class="form-control" type="text" name="createData[email]" <?php echo htmlValue('email'); ?> autocomplete="off">
		</div>
		<div class="form-group">
			<label>Mot de passe</label>
			<input class="form-control" type="password" name="createData[password]" autocomplete="off">
		</div>
		<div class="form-group">
			<label>Confirmation</label>
			<input class="form-control" type="password" name="createData[password_conf]">
		</div>
		<button class="btn btn-default" type="submit" name="submitCreate">Enregistrer</button>
		</div>
	</div>
</div>
</form>
<?php
}
?>

