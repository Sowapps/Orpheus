<?php
use Orpheus\Rendering\HTMLRendering;

/* @var string $resultingSQL */
/* @var FormToken $FORM_TOKEN */
/* @var array $composerConfig */

HTMLRendering::useLayout('page_skeleton');

global $formData;
$formData = array('composer' => (array) $composerConfig);
if( isset($formData['composer']['keywords']) ) {
// 	$formData['composer']['keywords'] = implode(',', $formData['composer']['keywords']);
	apath_setp($formData, 'composer/keywords', array(), false);
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

<ul class="nav nav-tabs mb15" role="tablist">
	<li role="presentation" class="active"><a href="#ComposerGeneral"
		aria-controls="ComposerGeneral" role="tab" data-toggle="tab"><?php _t('tab_general', DOMAIN_COMPOSER) ?></a></li>
	<li role="presentation"><a href="#ComposerDependencies"
		aria-controls="ComposerDependencies" role="tab" data-toggle="tab"><?php _t('tab_dependencies', DOMAIN_COMPOSER) ?></a></li>
</ul>

<div class="tab-content">
	<div role="tabpanel" class="tab-pane active" id="ComposerGeneral">

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
					<label><?php _t('keywords', DOMAIN_COMPOSER); ?></label> <select
						name="composer[keywords]" multiple id="InputComposerKeywords">
					<?php
					foreach( $formData['composer']['keywords'] as $keyword ) {
						echo '
					<option value="'.$keyword.'" selected>'.$keyword.'</option>';
					}
					?>
				</select>
				
				<?php /*
				<?php _adm_htmlTextInput('composer/keywords', '', 'id="InputComposerKeywords"'); ?>
	<!-- 			<input name="composer[keywords]" id="InputComposerKeywords" class="form-control" value=""/> -->
				*/ ?>
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
			
			<div class="form-group">
					<label><?php _t('applicationFolder', DOMAIN_COMPOSER); ?></label>
					<p class="form-control-static"><?php echo $applicationFolder; ?></p>
				</div>

				<div class="form-group">
					<label><?php _t('composerFile', DOMAIN_COMPOSER); ?></label>
					<p class="form-control-static"><?php echo $composerFile; ?></p>
				</div>

				<div class="form-group">
					<label><?php _t('composerHome', DOMAIN_COMPOSER); ?></label>
					<p class="form-control-static"><?php echo $composerHome; ?></p>
				</div>

				<div class="checkbox">
					<label><input name="update[optimize]" type="checkbox"> <?php _t('installOptimize', DOMAIN_COMPOSER); ?></label>
				</div>

				<div class="checkbox">
					<label><input name="update[refresh]" type="checkbox" checked> <?php _t('installRefresh', DOMAIN_COMPOSER); ?></label>
				</div>

				<div class="checkbox">
					<label><input name="update[withdev]" type="checkbox" checked> <?php _t('installWithDev', DOMAIN_COMPOSER); ?></label>
				</div>

				<p><?php _t('seeComposerDocumentation', DOMAIN_COMPOSER, 'https://getcomposer.org/doc/03-cli.md#install'); ?></p>

				<div class="text-center">
					<button name="submitUpdateInstall" type="submit"
						class="btn btn-primary btn-lg"><?php _t('update_install', DOMAIN_COMPOSER); ?></button>
				</div>
			
			<?php HTMLRendering::endCurrentLayout(array(
				'title' => t('manage_install', DOMAIN_COMPOSER)
			)); ?>
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
				<li class="list-group-item author" style="padding: 6px 15px" data-author="'.htmlFormATtr($author).'"><i class="fa fa-user fa-fw text-success"></i> '.
					$author->name.
					(isset($author->email) ? ' <a href="mailto:'.$author->email.'" target="_blank">&lt;'.$author->email.'&gt;</a>' : '').
					(isset($author->role) ? ' ('.$author->role.')' : '').
					(isset($author->homepage) ? ' - <a href="'.$author->homepage.'" target="_blank">'.parse_url($author->homepage, PHP_URL_HOST).'</a>' : '').'
					<div class="pull-right">
						<button class="btn btn-default btn-sm action-update" type="button"><i class="fa fa-fw fa-edit"></i></button>
						<button class="btn btn-default btn-sm action-delete" type="button"><i class="fa fa-fw fa-times"></i></button>
					</div>
				</li>';
				
			}
			?>
			</ul>
			
			<?php HTMLRendering::endCurrentLayout(array(
				'title' => t('authors', DOMAIN_COMPOSER),
				'footer' => '
	<div class="panel-footer text-right">
		<button class="btn btn-default" type="button" id="BtnAddAuthor">'.t('add').'</button>
		<button class="btn btn-primary" type="submit" name="submitUpdate">'.t('save').'</button>
	</div>')); ?>
		</div>

		</div>
	</div>

	<div role="tabpanel" class="tab-pane" id="ComposerDependencies">
		<div class="row">
			<div class="col-lg-6">
			<?php
			HTMLRendering::useLayout('panel-default');
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
			
			HTMLRendering::endCurrentLayout(array(
				'title' => t('dependencies', DOMAIN_COMPOSER),
				'footer' => '
	<div class="panel-footer text-right">
		<button class="btn btn-default" type="button" id="BtnAddDependency">'.t('add').'</button>
		<button class="btn btn-primary" type="submit" name="submitUpdate">'.t('save').'</button>
	</div>')); ?>
	
		</div>
		</div>
	</div>

</div>

</form>

<div id="EditAuthorDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="POST">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"
						aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title visible-create"><?php _t('addNewAuthor', DOMAIN_COMPOSER); ?></h4>
					<h4 class="modal-title visible-update author_name"></h4>
				</div>
				<div class="modal-body">

					<div class="form-group">
						<label for="InputAuthorName"><?php _t('author_name', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control author_name"
							id="InputAuthorName" required>
					</div>
					<div class="form-group">
						<label for="InputAuthorName"><?php _t('author_email', DOMAIN_COMPOSER); ?></label>
						<input type="email" class="form-control author_email"
							id="InputAuthorName">
					</div>
					<div class="form-group">
						<label for="InputAuthorRole"><?php _t('author_role', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control author_role"
							id="InputAuthorRole">
					</div>
					<div class="form-group">
						<label for="InputAuthorHomepage"><?php _t('author_homepage', DOMAIN_COMPOSER); ?></label>
						<div class="input-group">
							<input type="url" class="form-control author_homepage"
								data-linkbtn="#BtnAuthorHomepage" id="InputAuthorHomepage"> <span
								class="input-group-btn"> <a class="btn btn-default"
								target="_blank" id="BtnAuthorHomepage"><i
									class="fa fa-fw fa-external-link"></i></a>
							</span>
						</div>
					</div>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php _t('cancel'); ?></button>
					<button type="button" class="btn btn-primary"><?php _t('save'); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>

<div id="EditDependencyDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="POST">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"
						aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title visible-create"><?php _t('addNewDependency', DOMAIN_COMPOSER); ?></h4>
					<h4 class="modal-title visible-update dependency_name"></h4>
				</div>
				<div class="modal-body">

					<div class="form-group">
						<label for="InputDependencyName"><?php _t('dependency_name', DOMAIN_COMPOSER); ?></label>
						<select type="text" class="form-control dependency_name"
							id="InputDependencyName"></select>
					</div>
					<div class="form-group">
						<label for="InputDependencyVersion"><?php _t('dependency_version', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control dependency_version"
							id="InputDependencyVersion">
					</div>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php _t('cancel'); ?></button>
					<button type="button" class="btn btn-primary"><?php _t('save'); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
var EditAuthorDialog;
var EditDependencyDialog;
$(function() {
	$("#InputComposerKeywords").select2({
		tags: true,
		/*
		createTag: function (params) {
			return {
				id: params.term,
				text: params.term,
				newOption: true
			};
		},
		*/
		tokenSeparators: [',']
	});
	
	EditAuthorDialog = $("#EditAuthorDialog").modal({show:false});

	$("#BtnAddAuthor").click(function() {
		EditAuthorDialog.removeClass('mode-update').addClass('mode-create');
		EditAuthorDialog.find("form").get(0).reset();
		EditAuthorDialog.modal("show");
	});

	$(".author .action-update").click(function() {
		EditAuthorDialog.removeClass('mode-create').addClass('mode-update');
		EditAuthorDialog.find("form").get(0).reset();
		EditAuthorDialog.fill("author_", $(this).closest("li.author").data("author"));
		EditAuthorDialog.modal("show");
	});
	
	EditDependencyDialog = $("#EditDependencyDialog").modal({show:false});

	$("#BtnAddDependency").click(function() {
		EditDependencyDialog.removeClass('mode-update').addClass('mode-create');
		EditDependencyDialog.find("form").get(0).reset();
		EditDependencyDialog.modal("show");
	});

// 	$(".author .action-update").click(function() {
// 		EditAuthorDialog.removeClass('mode-create').addClass('mode-update');
// 		EditAuthorDialog.find("form").get(0).reset();
// 		EditAuthorDialog.fill("author_", $(this).closest("li.author").data("author"));
// 		EditAuthorDialog.modal("show");
// 	});


	$("#InputDependencyName").select2({
		ajax: {
			//https://packagist.org/apidoc
			url: 'https://packagist.org/search.json',
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term,
					page: params.page
				};
			},
			processResults: function(data, params) {
				
				params.page = params.page || 1;
				
				var items = [];
				for( var k in data.results ) {
					var item = data.results[k];
					if( !isPureObject(item) ) {
						continue;
					}
					// We reject other result than packages
					if( !item.repository ) {
						continue;
					}
					items.push({
						item:item,
						id:item.name,
						text:"<b>"+item.name+"</b><br>"+item.description
					});
// 					items.push({id:item.name, text:item.description+" ("+item.name+")"});
				}

				return {
					results: items,
					pagination: {
						more: !!data.next
					//	more: (params.page * 30) < data.other.total_count
					}
				};
			},
			cache: true
		},
		templateSelection: function(data, container) {
// 			console.log("templateSelection - data", data, data.item);
			return data.item.name;
		},
		escapeMarkup: function (markup) {
			return markup;
		},
		minimumInputLength: 3,
	});
	
});
</script>

<style>
.list-group-item.author {
	line-height: 28px;
}

.list-group-item.author .btn {
	padding: 4px 6px;
}

.visible-create, .visible-update {
	display: none;
}

.mode-create .visible-create {
	display: block !important;
}

.mode-update .visible-update {
	display: block !important;
}
</style>

