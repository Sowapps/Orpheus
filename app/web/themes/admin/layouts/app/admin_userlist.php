<?php
HTMLRendering::useLayout('page_skeleton');
?>
<form method="POST">

<div class="row">
	<div class="col-lg-12">
<!-- 		<h2>Bordered Table</h2> -->
		<div class="table-responsive">
			<table class="table table-bordered table-hover tablesorter">
				<thead>
					<tr>
						<th><?php _t('idColumn'); ?> <i class="fa fa-sort" title="Trier par ID"></i></th>
						<th><?php SiteUser::_text('name'); ?> <i class="fa fa-sort" title="<?php SiteUser::_text('sortByName'); ?>"></i></th>
						<th><?php SiteUser::_text('email'); ?> <i class="fa fa-sort" title="<?php SiteUser::_text('sortByEmail'); ?>"></i></th>
						<th><?php SiteUser::_text('role'); ?> <i class="fa fa-sort" title="<?php SiteUser::_text('sortByRole'); ?>"></i></th>
						<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
foreach( $users as $user ) {
	echo "
<tr>
	<td>{$user->id}</td>
	<td>{$user}</td>
	<td>{$user->email}</td>
	<td>{$user->getRoleText()}</td>".
	( $USER_CAN_USER_EDIT ? '<td><a class="fa fa-edit" href="'.$user->getAdminLink().'" title="'.t('edit').'"></a></td>' : '').
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
		<h2><?php SiteUser::_text('addUser'); ?></h2>
		<div class="form-group">
			<label><?php SiteUser::_text('name'); ?></label>
			<input class="form-control" type="text" name="createUser[fullname]" <?php echo htmlValue('fullname'); ?>/>
		</div>
		<div class="form-group">
			<label><?php SiteUser::_text('email'); ?></label>
			<input class="form-control" type="text" name="createUser[email]" <?php echo htmlValue('email'); ?> autocomplete="off">
		</div>
		<div class="form-group">
			<label><?php SiteUser::_text('password'); ?></label>
			<input class="form-control" type="password" name="createUser[password]" autocomplete="off">
		</div>
		<div class="form-group">
			<label><?php SiteUser::_text('confirmPassword'); ?></label>
			<input class="form-control" type="password" name="createUser[password_conf]">
		</div>
		<button class="btn btn-default" type="submit" name="submitCreate"><?php _t('save'); ?></button>
		</div>
	</div>
</div>
</form>
<?php
}
?>
