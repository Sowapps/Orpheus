<?php

try {
	if( isPOST('submitSave') ) {
		text(DemoEntity::getValidator());
		$de = DemoEntity::load(DemoEntity::create(POST('data')));
		text($de);
		reportSuccess('successSave', DemoEntity::getDomain());
	}
} catch( UserException $e ) {
	reportError($e);
}
/*
create_date: datetime
create_ip: ip
edit_date: datetime
edit_ip: ip
name: string(6, 50)
user_id: ref
user_name: string(6, 50)
published: bool=false
*/

displayReportsHTML();
?>
<form method="POST">

<label>Name</label><input type="text" name="data[name]" /><br />
<label>Published</label><input type="checkbox" name="data[published]" /><br />

<button type="submit" name="submitSave">Save</button>
</form>
<style>
<!--
label {
	width: 200px;
}
-->
</style>