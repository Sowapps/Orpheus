<?php
/* @var string $resultingSQL */
/* @var FormToken $FORM_TOKEN */
/* @var array $composerConfig */

HTMLRendering::useLayout('page_skeleton');

define('DOMAIN_COMPOSER', 'devtools');
global $formData;
$formData = array('composer' => (array) $composerConfig);
if( isset($formData['composer']['keywords']) ) {
	$formData['composer']['keywords'] = implode(',', $formData['composer']['keywords']);
	apath_setp($formData, 'composer/minimum-stability', 'stable', false);
	apath_setp($formData, 'composer/authors', array(), false);
	apath_setp($formData, 'composer/require', array(), false);
}

includeHTMLAdminFeatures();

?>
<form method="POST" role="form">
<?php
// echo $FORM_TOKEN;
?>

<div class="row">

	<div class="col-lg-6">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		<?php
		debug('$composerConfig', $composerConfig);
// 		debug('$formData', $formData);
		?>
		
		<div class="form-group">
			<label><?php _t('name', DOMAIN_COMPOSER); ?></label>
			<?php _adm_htmlTextInput('composer/name'); ?>
		</div>
		<div class="form-group">
			<label><?php _t('description', DOMAIN_COMPOSER); ?></label>
			<?php _adm_htmlTextInput('composer/description'); ?>
		</div>
		<div class="form-group">
			<label><?php _t('type', DOMAIN_COMPOSER); ?></label>
			<?php _adm_htmlTextInput('composer/type'); ?>
		</div>
		<div class="form-group">
			<label><?php _t('keywords', DOMAIN_COMPOSER); ?></label>
			<?php _adm_htmlTextInput('composer/keywords', '', 'id="InputComposerKeywords"'); ?>
<!-- 			<input name="composer[keywords]" id="InputComposerKeywords" class="form-control" value=""/> -->
		</div>
		<div class="form-group">
			<label><?php _t('license', DOMAIN_COMPOSER); ?></label>
			<?php _adm_htmlTextInput('composer/license'); ?>
		</div>
		<div class="form-group">
			<label><?php _t('minimumStability', DOMAIN_COMPOSER); ?></label>
			<?php _adm_htmlTextInput('composer/minimum-stability'); ?>
		</div>
		
		<?php HTMLRendering::endCurrentLayout(array(
			'title' => t('overview', DOMAIN_COMPOSER),
			'footer' => '
<div class="panel-footer text-right">
	<button class="btn btn-primary" type="submit" name="submitUpdate">'.t('save').'</button>
</div>')); ?>
	</div>

	<div class="col-lg-6">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		
		<ul class="list-group">
		<?php
		foreach( $formData['composer']['authors'] as $author ) {
			if( empty($author->name) ) {
				continue;
			}
			echo '
			<li class="list-group-item author"><i class="fa fa-user fa-fw text-success"></i> '.
				$author->name.
				(isset($author->email) ? ' <a href="mailto:'.$author->email.'" target="_blank">&lt;'.$author->email.'&gt;</a>' : '').
				(isset($author->role) ? ' ('.$author->role.')' : '').
				(isset($author->homepage) ? ' - <a href="'.$author->homepage.'" target="_blank">'.parse_url($author->homepage, PHP_URL_HOST).'</a>' : '').
			'</li>';
			
		}
		?>
		</ul>
		
		<?php HTMLRendering::endCurrentLayout(array(
			'title' => t('authors', DOMAIN_COMPOSER),
			'footer' => '
<div class="panel-footer text-right">
	<button class="btn btn-primary" type="submit" name="submitUpdate">'.t('save').'</button>
</div>')); ?>
	</div>
	
</div>

<div class="row">

	<div class="col-lg-6">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		<?php
		
// 		"require" : {
// 		"orpheus/orpheus-ssh2" : "dev-master@stable"
// 	}
		if( !empty($formData['composer']['require']) ) {
			?>
		<ul class="list-group">
		<?php
		foreach( $formData['composer']['require'] as $dependency => $version ) {
			echo '
			<li class="list-group-item dependency"><i class="fa fa-folder fa-fw text-success"></i> '.
				$dependency.' ('.$version.')'.
			'</li>';
			
		}
		?>
		</ul>
		<?php
		}
		?>
		
		<?php HTMLRendering::endCurrentLayout(array(
			'title' => t('dependencies', DOMAIN_COMPOSER),
			'footer' => '
<div class="panel-footer text-right">
	<button class="btn btn-primary" type="submit" name="submitUpdate">'.t('save').'</button>
</div>')); ?>
	</div>
	
</div>

</form>

<script type="text/javascript">
$(function() {
	$("#InputComposerKeywords").select2({
		tags: true,
		tokenSeparators: [',']
	});
	
});
</script>
