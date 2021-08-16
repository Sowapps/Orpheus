<?php
/**
 * @var HTMLRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var array $composerConfig
 * @var string $applicationFolder
 * @var string $composerFile
 * @var string $composerHome
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;
use Sowapps\Controller\Admin\AdminController;

$rendering->useLayout('page_skeleton');
$rendering->addJSFile('model.js', HTMLRendering::LINK_TYPE_CUSTOM);

global $formData;
$formData = ['composer' => (array) $composerConfig];
if( !isset($formData['composer']['keywords']) ) {
	apath_setp($formData, 'composer/keywords', [], false);
}
if( !isset($formData['composer']['minimum-stability']) ) {
	apath_setp($formData, 'composer/minimum-stability', 'stable', false);
}
if( !isset($formData['composer']['authors']) ) {
	apath_setp($formData, 'composer/authors', [], false);
}
if( !isset($formData['composer']['require']) ) {
	apath_setp($formData, 'composer/require', [], false);
}

includeAdminComponents();

?>
<form method="POST" role="form">
	<input type="hidden" class="input-authors" name="composer[authors]" value="<?php echo htmlFormATtr((array) $formData['composer']['authors']); ?>"/>
	<input type="hidden" class="input-require" name="composer[require]" value="<?php echo htmlFormATtr((object) $formData['composer']['require']); ?>"/>
	
	<ul class="nav nav-tabs" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" href="#ComposerGeneral" aria-controls="ComposerGeneral" role="tab" data-toggle="tab"><?php _t('tab_general', DOMAIN_COMPOSER) ?></a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#ComposerDependencies" aria-controls="ComposerDependencies" role="tab" data-toggle="tab"><?php _t('tab_dependencies', DOMAIN_COMPOSER) ?></a>
		</li>
	</ul>
	
	<div class="tab-content mt-3">
		<div role="tabpanel" class="tab-pane active" id="ComposerGeneral">
			
			<div class="row">
				<div class="col-lg-6">
					<?php $rendering->useLayout('panel-default'); ?>
					
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
						<select name="composer[keywords][]" multiple id="InputComposerKeywords">
							<?php
							foreach( $formData['composer']['keywords'] as $keyword ) {
								echo '
					<option value="' . $keyword . '" selected>' . $keyword . '</option>';
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<label><?php _t('license', DOMAIN_COMPOSER); ?></label>
						<?php _adm_htmlTextInput('composer/license'); ?>
					</div>
					<div class="form-group">
						<label><?php _t('minimumStability', DOMAIN_COMPOSER); ?></label>
						<?php _adm_htmlTextInput('composer/minimum-stability'); ?>
					</div>
					
					<?php $rendering->endCurrentLayout([
						'title'  => t('overview', DOMAIN_COMPOSER),
						'footer' => '
	<div class="panel-footer text-right">
		<button class="btn btn-primary" type="submit" name="submitUpdate">' . t('save') . '</button>
	</div>']); ?>
				</div>
				
				<div class="col-lg-6">
					<?php $rendering->useLayout('panel-default'); ?>
					
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
						<button name="submitUpdateInstall" type="submit" class="btn btn-primary btn-lg" data-submittext="Updating in progress...">
							<?php _t('update_install', DOMAIN_COMPOSER); ?></button>
					</div>
					
					<?php $rendering->endCurrentLayout([
						'title' => t('manage_install', DOMAIN_COMPOSER),
					]); ?>
				</div>
				
				<div class="col-lg-6">
					<?php $rendering->useLayout('panel-default'); ?>
					
					<ul class="list-group list-authors">
						<li class="list-group-item item item-authors item_model" data-model_type="authors">
							<i class="fa fa-user fa-fw text-success"></i> {{name}}
							<a href="mailto:{{email}}" data-model_require="email" target="_blank">&lt;{{email}}&gt;</a>
							<span data-model_require="role"> ({{role}})</span>
							<span data-model_require="homepage"> - <a href="{{homepage}}" target="_blank">{{homepage|url_host}}</a></span>
							<div class="pull-right">
								<button class="btn btn-outline-secondary btn-sm action-update" type="button"><i class="fa fa-fw fa-edit"></i></button>
								<button class="btn btn-outline-secondary btn-sm action-delete" type="button"><i class="fa fa-fw fa-times"></i></button>
							</div>
						</li>
						<li class="list-group-item item item-authors model_placeholder">
							<p>There is currently no authors, <a class="action-create create_authors" href="#">click here</a> to add one.</p>
						</li>
					</ul>
					
					<?php $rendering->endCurrentLayout([
						'title'  => t('authors', DOMAIN_COMPOSER),
						'footer' => '
	<div class="panel-footer text-right">
		<button class="btn btn-default action-create create_authors" type="button">' . t('add') . '</button>
		<button class="btn btn-primary" type="submit" name="submitUpdate">' . t('save') . '</button>
	</div>']); ?>
				</div>
			
			</div>
		</div>
		
		<div role="tabpanel" class="tab-pane" id="ComposerDependencies">
			<div class="row">
				<div class="col-lg-6">
					<?php $rendering->useLayout('panel-default'); ?>
					
					<ul class="list-group list-require">
						<li class="list-group-item item item-require item_model" data-model_type="require">
							<i class="fa fa-folder fa-fw text-success"></i> {{_key_}}
							({{_value_}})
							<div class="pull-right">
								<button class="btn btn-default btn-sm action-update" type="button"><i class="fa fa-fw fa-edit"></i></button>
								<button class="btn btn-default btn-sm action-delete" type="button"><i class="fa fa-fw fa-times"></i></button>
							</div>
						</li>
						<li class="list-group-item item item-require model_placeholder">
							<p>There is currently no dependency, <a class="action-create create_require" href="#">click here</a> to add one.</p>
						</li>
					</ul>
					<?php
					
					$rendering->endCurrentLayout([
						'title'  => t('dependencies', DOMAIN_COMPOSER),
						'footer' => '
	<div class="panel-footer text-right">
		<button class="btn btn-default action-create create_require" type="button">' . t('add') . '</button>
		<button class="btn btn-primary" type="submit" name="submitUpdate">' . t('save') . '</button>
	</div>']); ?>
				
				</div>
			</div>
		</div>
	
	</div>

</form>

<div id="EditAuthorDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			
			<div class="modal-header">
				<h4 class="modal-title visible-create"><?php _t('addNewAuthor', DOMAIN_COMPOSER); ?></h4>
				<h4 class="modal-title visible-update author_name"></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php _t('close'); ?>"><span aria-hidden="true">&times;</span></button>
			</div>
			
			<form method="POST">
				<div class="modal-body">
					
					<div class="form-group">
						<label for="InputAuthorName"><?php _t('author_name', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control author_name" data-field="name" id="InputAuthorName" required>
					</div>
					<div class="form-group">
						<label for="InputAuthorName"><?php _t('author_email', DOMAIN_COMPOSER); ?></label>
						<input type="email" class="form-control author_email" data-field="email" id="InputAuthorName">
					</div>
					<div class="form-group">
						<label for="InputAuthorRole"><?php _t('author_role', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control author_role" data-field="role" id="InputAuthorRole">
					</div>
					<div class="form-group">
						<label for="InputAuthorHomepage"><?php _t('author_homepage', DOMAIN_COMPOSER); ?></label>
						<div class="input-group">
							<input type="url" class="form-control author_homepage" data-field="homepage" data-linkbtn="#BtnAuthorHomepage" id="InputAuthorHomepage">
							<span class="input-group-btn">
								<a class="btn btn-default" target="_blank" id="BtnAuthorHomepage">
									<i class="fa fa-fw fa-external-link"></i>
								</a>
							</span>
						</div>
					</div>
				</div>
			</form>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php _t('cancel'); ?></button>
				<button type="button" class="btn btn-primary save_author"><?php _t('save'); ?></button>
			</div>
		</div>
	</div>
</div>

<div id="EditDependencyDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="POST">
				
				<div class="modal-header">
					<h4 class="modal-title visible-create"><?php _t('addNewDependency', DOMAIN_COMPOSER); ?></h4>
					<h4 class="modal-title visible-update dependency_name"></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="<?php _t('close'); ?>"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					
					<div class="form-group">
						<label for="InputDependencyName"><?php _t('dependency_name', DOMAIN_COMPOSER); ?></label>
						<select type="text" class="form-control dependency_name" data-field="_key_"
								id="InputDependencyName" required="required"></select>
					</div>
					<div class="form-group">
						<label for="InputDependencyVersion"><?php _t('dependency_version', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control dependency_version" data-field="_value_"
							   id="InputDependencyVersion" required="required">
					</div>
				
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php _t('cancel'); ?></button>
					<button type="button" class="btn btn-primary save_dependency"><?php _t('save'); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
var EditAuthorDialog;
var EditDependencyDialog;

$(function () {
	$("#InputComposerKeywords").select2({
		tags: true,
		tokenSeparators: [',']
	});
	
	EditAuthorDialog = $("#EditAuthorDialog").modal({show: false});
	
	$(".action-create.create_authors").click(function () {
		EditAuthorDialog.removeClass('mode-update').addClass('mode-create');
		EditAuthorDialog.find("form").get(0).reset();
		EditAuthorDialog.find(".save_author").data("itemtype", "authors");
		EditAuthorDialog.modal("show");
		return false;
	});
	
	$(".list-authors").on("click", ".item-authors .action-update", function () {
		EditAuthorDialog.removeClass('mode-create').addClass('mode-update');
		EditAuthorDialog.find("form").get(0).reset();
		var itemRow = $(this).closest(".model_item.item-authors");
		EditAuthorDialog.fill("author_", itemRow.data("itemdata"));
		EditAuthorDialog.find(".save_author").data("itemid", itemRow.attr("id"));
		EditAuthorDialog.modal("show");
	});
	
	$(".list-authors").on("click", ".item-authors .action-delete", function () {
		var itemRow = $(this).closest(".model_item.item-authors");
		itemRow.model("removeItem");
	});
	
	EditAuthorDialog.find(".save_author").click(function () {
		// Update - Require data "itemid" - Preserve old object
		// Create - Require data "itemtype" - Create new object
		var update = EditAuthorDialog.hasClass("mode-update");
		var itemRow = null;
		var itemData = {};
		if( update ) {
			itemRow = $("#" + $(this).data("itemid"));
			itemData = itemRow.data("itemdata");
		}
		EditAuthorDialog.find(":input[data-field]").each(function () {
			itemData[$(this).data("field")] = $(this).val();
		});
		if( !itemData.name ) {
			return;
		}
		if( update && itemRow ) {
			itemRow.model("updateItem", itemData);
		} else {
			Model.get($(this).data("itemtype")).model("addItem", itemData);
		}
		EditAuthorDialog.modal("hide");
	});
	
	EditDependencyDialog = $("#EditDependencyDialog").modal({show: false});
	
	$(".action-create.create_require").click(function () {
		EditDependencyDialog.removeClass('mode-update').addClass('mode-create');
		EditDependencyDialog.find("form").get(0).reset();
		EditDependencyDialog.find(".save_dependency").data("itemtype", "require");
		EditDependencyDialog.modal("show");
		return false;
	});
	
	$(".list-require").on("click", ".item-require .action-update", function () {
		EditDependencyDialog.removeClass('mode-create').addClass('mode-update');
		EditDependencyDialog.find("form").get(0).reset();
		var itemRow = $(this).closest(".model_item.item-require");
		var data = itemRow.data("itemdata");
		data.name = data._key_;
		EditDependencyDialog.fill("author_", data);
		EditDependencyDialog.find(".save_dependency").data("itemid", itemRow.attr("id"));
		EditDependencyDialog.modal("show");
	});
	
	$(".list-require").on("click", ".item-require .action-delete", function () {
		var itemRow = $(this).closest(".model_item.item-require");
		itemRow.model("removeItem");
	});
	
	// TODO: Remove/Disable already registered dependencies
	EditDependencyDialog.find(".save_dependency").click(function () {
		// Update - Require data "itemid" - Preserve old object
		// Create - Require data "itemtype" - Create new object
		var update = EditDependencyDialog.hasClass("mode-update");
		console.log("Save dependency with mode, update ? " + update);
		var itemRow = null;
		var itemData = {};
		if( update ) {
			itemRow = $("#" + $(this).data("itemid"));
			itemData = itemRow.data("itemdata");
		}
		EditDependencyDialog.find(":input[data-field]").each(function () {
			itemData[$(this).data("field")] = $(this).val();
		});
		if( !itemData._key_ || !itemData._value_ ) {
			return;
		}
		if( update && itemRow ) {
			itemRow.model("updateItem", itemData);
		} else {
			Model.get($(this).data("itemtype")).model("addItem", itemData);
		}
		EditDependencyDialog.modal("hide");
	});
	
	$("#InputDependencyName").select2({
		ajax: {
			// https://packagist.org/apidoc
			url: 'https://packagist.org/search.json',
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term,
					page: params.page
				};
			},
			processResults: function (data, params) {
				
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
						item: item,
						id: item.name,
						text: "<b>" + item.name + "</b><br>" + item.description
					});
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
		templateSelection: function (data, container) {
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
.list-group-item.item_model,
.list-group-item.model_placeholder {
	display: none;
}

.list-group-item {
	padding: 6px 15px;
	line-height: 28px;
}

.list-group-item p {
	margin-bottom: 0;
}

.list-group-item .btn {
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

