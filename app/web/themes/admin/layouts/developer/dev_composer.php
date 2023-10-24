<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var object $composerConfig
 * @var string $applicationFolder
 * @var string $composerFile
 * @var string $composerHome
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.admin');
$rendering->addJsFile('ModelList.js', HtmlRendering::LINK_TYPE_CUSTOM);

?>
<form method="POST" role="form">
	<input id="InputComposerAuthors" type="hidden" name="composer[authors]" value="<?php echo htmlAttribute($composerConfig['authors']); ?>"/>
	<input id="InputComposerDependencies" type="hidden" name="composer[require]" value="<?php echo htmlAttribute($composerConfig['require']); ?>"/>
	
	<ul class="nav nav-tabs" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" href="#ComposerGeneral" data-bs-toggle="tab" data-bs-target="#ComposerGeneral">
				<?php echo t('tab_general', DOMAIN_COMPOSER) ?>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#ComposerDependencies" data-bs-toggle="tab" data-bs-target="#ComposerDependencies">
				<?php echo t('tab_dependencies', DOMAIN_COMPOSER) ?>
			</a>
		</li>
	</ul>
	
	<div class="tab-content mt-3">
		<div role="tabpanel" class="tab-pane fade show active" id="ComposerGeneral">
			
			<div class="row">
				<div class="col-lg-6">
					<?php $rendering->useLayout('component/panel'); ?>
					
					<div class="mb-3">
						<label class="form-label" for="InputComposerName"><?php echo t('name', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control" id="InputComposerName" name="composer[name]" value="<?php echo $composerConfig['name'] ?? ''; ?>" readonly>
					</div>
					<div class="mb-3">
						<label class="form-label" for="InputComposerDescription"><?php echo t('description', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control" id="InputComposerDescription" name="composer[description]" readonly
							   value="<?php echo $composerConfig['description'] ?? ''; ?>">
					</div>
					<div class="mb-3">
						<label class="form-label" for="InputComposerType"><?php echo t('type', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control" id="InputComposerType" name="composer[type]" value="<?php echo $composerConfig['type'] ?? ''; ?>" readonly>
					</div>
					<div class="mb-3">
						<label class="form-label" for="InputComposerKeywords"><?php echo t('keywords', DOMAIN_COMPOSER); ?></label>
						<select name="composer[keywords][]" multiple id="InputComposerKeywords" readonly class="form-control widget-select">
							<?php echo htmlOptions(null, $composerConfig['keywords'], $composerConfig['keywords'], OPT_VALUE); ?>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label" for="InputComposerLicense"><?php echo t('license', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control" id="InputComposerLicense" name="composer[license]" value="<?php echo $composerConfig['license'] ?? ''; ?>"
							   readonly>
					</div>
					<div class="mb-3">
						<label class="form-label" for="InputComposer"><?php echo t('minimumStability', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control" id="InputComposer" name="composer[name]" value="<?php echo $composerConfig['minimum-stability']; ?>" readonly>
					</div>
					<?php /* $rendering->startNewBlock('footer'); ?>
				<div class="panel-footer text-right">
					<button class="btn btn-primary" type="submit" name="submitUpdate"><?php echo t('save'); ?></button>
				</div>
				<?php */
					$rendering->endCurrentLayout([
						'title' => t('overview', DOMAIN_COMPOSER),
					]); ?>
				</div>
				
				<div class="col-lg-6">
					<?php $rendering->useLayout('component/panel'); ?>
					
					<div class="mb-3">
						<label class="form-label" for="InputInstallApplicationFolder"><?php echo t('applicationFolder', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control" id="InputInstallApplicationFolder" value="<?php echo $applicationFolder; ?>" disabled>
					</div>
					
					<div class="mb-3">
						<label class="form-label" for="InputInstallComposerFile"><?php echo t('composerFile', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control" id="InputInstallComposerFile" value="<?php echo $composerFile; ?>" disabled>
					</div>
					
					<div class="mb-3">
						<label class="form-label" for="InputInstallComposerHome"><?php echo t('composerHome', DOMAIN_COMPOSER); ?></label>
						<input type="text" class="form-control" id="InputInstallComposerHome" value="<?php echo $composerHome; ?>" disabled>
					</div>
					
					<?php /*
				<div class="checkbox">
					<label class="form-label">
						<input name="update[optimize]" type="checkbox">
						<?php echo t('installOptimize', DOMAIN_COMPOSER); ?>
					</label>
				</div>
				
				<div class="checkbox">
					<label class="form-label">
						<input name="update[refresh]" type="checkbox" checked>
						<?php echo t('installRefresh', DOMAIN_COMPOSER); ?>
					
					</label>
				</div>
				
				<div class="checkbox">
					<label class="form-label">
						<input name="update[withdev]" type="checkbox" checked>
						<?php echo t('installWithDev', DOMAIN_COMPOSER); ?>
					</label>
				</div>
				
				<p><?php echo t('seeComposerDocumentation', DOMAIN_COMPOSER, ['https://getcomposer.org/doc/03-cli.md#install']); ?></p>
				
				<div class="text-center">
					<button name="submitUpdateInstall" type="submit" class="btn btn-primary btn-lg" data-submittext="Updating in progress...">
						<?php echo t('update_install', DOMAIN_COMPOSER); ?>
					</button>
				</div>
				
				<?php */
					$rendering->endCurrentLayout([
						'title' => t('manage_install', DOMAIN_COMPOSER),
					]); ?>
				</div>
				
				<div class="col-lg-6">
					<?php $rendering->useLayout('component/panel'); ?>
					
					<ul class="list-group list-authors model-list" data-model-type="author" data-model-input="#InputComposerAuthors"
						data-model-item-template="#TemplateAuthorItem" data-model-placeholder-template="#TemplateAuthorPlaceholder">
					</ul>
					<template id="TemplateAuthorPlaceholder">
						<li class="list-group-item item-placeholder item-author model_placeholder">
							<p><?php echo t('authors_empty', DOMAIN_COMPOSER); ?></p>
						</li>
					</template>
					<template id="TemplateAuthorItem">
						<li class="list-group-item item item-author">
							<i class="fa fa-user fa-fw text-success"></i> {{name}}
							<a href="mailto:{{email}}" data-if="email" target="_blank">&lt;{{email}}&gt;</a>
							<span data-if="role"> ({{role}})</span>
							<span data-if="homepage">
							-
							<a href="{{homepage}}" target="_blank">{{homepage|url_host}}</a>
						</span>
						</li>
					</template>
					
					<?php $rendering->endCurrentLayout([
						'title' => t('authors', DOMAIN_COMPOSER),
					]); ?>
				</div>
			
			</div>
		</div>
		
		<div role="tabpanel" class="tab-pane fade" id="ComposerDependencies">
			<div class="row">
				<div class="col-lg-6">
					<?php $rendering->useLayout('component/panel'); ?>
					
					<ul class="list-group list-dependencies model-list" data-model-type="dependency" data-model-input="#InputComposerDependencies"
						data-model-item-template="#TemplateDependencyItem" data-model-placeholder-template="#TemplateDependencyPlaceholder" data-model-associative="true">
					</ul>
					<template id="TemplateDependencyPlaceholder">
						<li class="list-group-item item-placeholder item-dependency model_placeholder">
							<p><?php echo t('dependencies_empty', DOMAIN_COMPOSER); ?></p>
						</li>
					</template>
					<template id="TemplateDependencyItem">
						<li class="list-group-item item item-require" data-model-type="require">
							<i class="fa-brands fa-php fa-fw text-primary" data-if="'{{_key_}}' == 'php'"></i>
							<i class="fa-solid fa-puzzle-piece fa-fw text-info" data-if="'{{_key_|truncate(4, 9)}}' == 'ext-'"></i>
							<i class="fa fa-folder fa-fw text-success" data-else="siblings"></i>
							{{_key_}} ({{_value_}})
						</li>
					</template>
					<?php
					
					$rendering->endCurrentLayout([
						'title' => t('dependencies', DOMAIN_COMPOSER),
					]); ?>
				
				</div>
			</div>
		</div>
	
	</div>

</form>

<?php /*
<div id="EditAuthorDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			
			<div class="modal-header">
				<h4 class="modal-title visible-create"><?php echo t('addNewAuthor', DOMAIN_COMPOSER); ?></h4>
				<h4 class="modal-title visible-update author_name"></h4>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="<?php echo t('close'); ?>"><span aria-hidden="true">&times;</span></button>
			</div>
			
			<form method="POST">
			<div class="modal-body">
				
				<div class="mb-3">
					<label for="InputAuthorName"><?php echo t('author_name', DOMAIN_COMPOSER); ?></label>
					<input type="text" class="form-control author_name" data-field="name" id="InputAuthorName" required>
				</div>
				<div class="mb-3">
					<label for="InputAuthorName"><?php echo t('author_email', DOMAIN_COMPOSER); ?></label>
					<input type="email" class="form-control author_email" data-field="email" id="InputAuthorName">
				</div>
				<div class="mb-3">
					<label for="InputAuthorRole"><?php echo t('author_role', DOMAIN_COMPOSER); ?></label>
					<input type="text" class="form-control author_role" data-field="role" id="InputAuthorRole">
				</div>
				<div class="mb-3">
					<label for="InputAuthorHomepage"><?php echo t('author_homepage', DOMAIN_COMPOSER); ?></label>
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
				<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
				<button type="button" class="btn btn-primary save_author"><?php echo t('save'); ?></button>
			</div>
		</div>
	</div>
</div>

<div id="EditDependencyDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="POST">
			
			<div class="modal-header">
				<h4 class="modal-title visible-create"><?php echo t('addNewDependency', DOMAIN_COMPOSER); ?></h4>
				<h4 class="modal-title visible-update dependency_name"></h4>
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="<?php echo t('close'); ?>"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				
				<div class="mb-3">
					<label for="InputDependencyName"><?php echo t('dependency_name', DOMAIN_COMPOSER); ?></label>
					<select type="text" class="form-control dependency_name" data-field="_key_"
							id="InputDependencyName" required="required"></select>
				</div>
				<div class="mb-3">
					<label for="InputDependencyVersion"><?php echo t('dependency_version', DOMAIN_COMPOSER); ?></label>
					<input type="text" class="form-control dependency_version" data-field="_value_"
						   id="InputDependencyVersion" required="required">
				</div>
			
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
				<button type="button" class="btn btn-primary save_dependency"><?php echo t('save'); ?></button>
			</div>
			</form>
		</div>
	</div>
</div>
 
 */ ?>

<script>
	// var EditAuthorDialog;
	// var EditDependencyDialog;
	
	ready(() => {
	$("#InputComposerKeywords").select2({
		width: "style",
		tags: true,
		tokenSeparators: [","],
	});
		
		/*
		EditAuthorDialog = $("#EditAuthorDialog").modal({show: false});
		
		$(".action-create.create_authors").click(function () {
			EditAuthorDialog.removeClass('mode-update').addClass('mode-create');
			EditAuthorDialog.find("form").get(0).reset();
			EditAuthorDialog.find(".save_author").data("itemtype", "authors");
			EditAuthorDialog.modal("show");
			return false;
		});
		
		$(".list-authors").on("click", ".item-author .action-update", function () {
			EditAuthorDialog.removeClass('mode-create').addClass('mode-update');
			EditAuthorDialog.find("form").get(0).reset();
			var itemRow = $(this).closest(".model_item.item-author");
			EditAuthorDialog.fill("author_", itemRow.data("itemdata"));
			EditAuthorDialog.find(".save_author").data("itemid", itemRow.attr("id"));
			EditAuthorDialog.modal("show");
		});
		
		$(".list-authors").on("click", ".item-author .action-delete", function () {
			var itemRow = $(this).closest(".model_item.item-author");
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
		*/
});
</script>

<style>
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

