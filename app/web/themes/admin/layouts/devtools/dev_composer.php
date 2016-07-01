<?php
use Orpheus\Rendering\HTMLRendering;

/* @var Orpheus\Rendering\HTMLRendering $this */
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

// foreach( $formData['composer']['authors'] as &$author ) {
// 	$author['homepage_host'] = $author['homepage'];
// }

?>
<form method="POST" role="form">
<?php
// echo $FORM_TOKEN;
// debug('$formData[composer]', $formData['composer']);
?>
<input type="hidden" name="items" value="<?php echo htmlFormATtr(array_filterbykeys($formData['composer'], array('authors', 'require'))); ?>" />

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
	// 			debug('$composerConfig', $composerConfig);
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
			
			<ul class="list-group list-authors">
				<li class="list-group-item item item-authors item_model">
					<i class="fa fa-user fa-fw text-success"></i> {{name}}
					<a href="mailto:{{email}}" data-model_require="email" target="_blank">&lt;{{email}}&gt;</a>
					<span data-model_require="role"> ({{role}})</span>
					<span data-model_require="homepage"> - <a href="{{homepage}}" target="_blank">{{homepage|url_host}}</a></span>
					<div class="pull-right">
						<button class="btn btn-default btn-sm action-update" type="button"><i class="fa fa-fw fa-edit"></i></button>
						<button class="btn btn-default btn-sm action-delete" type="button"><i class="fa fa-fw fa-times"></i></button>
					</div>
				</li>
				<li class="list-group-item item item-authors item_placeholder">
					<p>There is currently no authors, <a class="action-create create_authors" href="#">click here</a> to add one.</p>
				</li>
			<?php
			/*
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
			*/
			?>
			</ul>
			
			<?php HTMLRendering::endCurrentLayout(array(
				'title' => t('authors', DOMAIN_COMPOSER),
				'footer' => '
	<div class="panel-footer text-right">
		<button class="btn btn-default action-create create_authors" type="button">'.t('add').'</button>
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
			?>
			
			<ul class="list-group list-require">
				<li class="list-group-item item item-require item_model">
					<i class="fa fa-folder fa-fw text-success"></i> {{_key_}}
					({{_value_}})
					<div class="pull-right">
						<button class="btn btn-default btn-sm action-update" type="button"><i class="fa fa-fw fa-edit"></i></button>
						<button class="btn btn-default btn-sm action-delete" type="button"><i class="fa fa-fw fa-times"></i></button>
					</div>
				</li>
				<li class="list-group-item item item-require item_placeholder">
					<p>There is currently no dependency, <a class="action-create create_require" href="#">click here</a> to add one.</p>
				</li>
			</ul>
			<?php
			/*
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
			}
			*/
			?>
			</ul>
			<?php
			
			HTMLRendering::endCurrentLayout(array(
				'title' => t('dependencies', DOMAIN_COMPOSER),
				'footer' => '
	<div class="panel-footer text-right">
		<button class="btn btn-default action-create create_require" type="button">'.t('add').'</button>
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

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title visible-create"><?php _t('addNewAuthor', DOMAIN_COMPOSER); ?></h4>
				<h4 class="modal-title visible-update author_name"></h4>
			</div>
			
			<form method="POST">
			<div class="modal-body">

				<div class="form-group">
					<label for="InputAuthorName"><?php _t('author_name', DOMAIN_COMPOSER); ?></label>
					<input type="text" class="form-control author_name" data-field="name"
						id="InputAuthorName" required>
				</div>
				<div class="form-group">
					<label for="InputAuthorName"><?php _t('author_email', DOMAIN_COMPOSER); ?></label>
					<input type="email" class="form-control author_email" data-field="email"
						id="InputAuthorName">
				</div>
				<div class="form-group">
					<label for="InputAuthorRole"><?php _t('author_role', DOMAIN_COMPOSER); ?></label>
					<input type="text" class="form-control author_role" data-field="role"
						id="InputAuthorRole">
				</div>
				<div class="form-group">
					<label for="InputAuthorHomepage"><?php _t('author_homepage', DOMAIN_COMPOSER); ?></label>
					<div class="input-group">
						<input type="url" class="form-control author_homepage" data-field="homepage"
							data-linkbtn="#BtnAuthorHomepage" id="InputAuthorHomepage"> <span
							class="input-group-btn"> <a class="btn btn-default"
							target="_blank" id="BtnAuthorHomepage"><i
								class="fa fa-fw fa-external-link"></i></a>
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

function url_host(url) {
// 	console.log("Location of "+url, getLocation(url));
	if( !url ) {
		return "";
	}
	var location = getLocation(url);
	return location.host;
}

var models = {};
function getModel(itemName) {
	if( !isDefined(models[itemName]) ) {
		var model = $(".item_model.item-"+itemName);
		models[itemName] = {
			"list": model.parent(),
			"model": model.removeClass("item_model").addClass("model_item").detach(),
			"placeholder": $(".item_placeholder.item-"+itemName+"").detach()
		};
	}
	return models[itemName].model;
}
function getPlaceholder(itemName) {
	return models[itemName].placeholder;
}

function modelClone(itemName, itemData) {
	var model = getModel(itemName);
// 	console.log("Model ", model);
	var cloneHTML = model.outerHTML();
//		console.log("Model html", cloneHTML);
	// Fill
	// Replace all fields
	cloneHTML = cloneHTML.replace(new RegExp("\\{\\{([^\\}\\|]+)(?:\\|([^\\}\\|]+))?\\}\\}", 'g'), function myFunction(string, field, formatter, offset){
//			console.log("Replace", string, field, formatter, offset);
		if( !isSet(itemData[field]) ) {
			return string;
		}
		var value = itemData[field];
		if( isDefined(formatter) ) {
//				console.log("formatter is defined");
			var fn = window[formatter];
//				console.log("formatter function", fn);
			if( isFunction(fn) ) {
//					console.log("formatter is a function");
				value = fn(value);
			}
		}
		return value;
	});
// 	console.log("cloneHTML ", cloneHTML);
	/*
	// Search fields
	for( var key in itemData ) {
		var value = data[itemName];
		if( !isString(value) ) {
			continue;
		}
		cloneHTML = cloneHTML.replace(new RegExp("\{\{"++"\}\}", 'g'), function myFunction(x){return x.toUpperCase();});
	}
	*/
	var clone = $(cloneHTML).data("itemdata", itemData).data("itemtype", itemName).uniqueId();
// 	var clone = $(cloneHTML).removeClass("item_model").addClass("model_item").data("itemdata", itemData).data("itemtype", itemName).uniqueId();
	
	// Hide invalid requires
	clone.find("[data-model_require]").each(function() {
		if( !itemData[$(this).data("model_require")] ) {
			// Remove (or hide ?)
			$(this).remove();
		}
	});
	
// 	console.log("Generated clone ", clone);
	return clone;
}

function modelItemAdd(itemName, itemData) {
// 	console.log("Model of "+itemName, $(".item_model.item-"+itemName), itemData);
	// Add clone to the end
// 	console.log("model", getModel(itemName));
// 	console.log("model", getModel(itemName));
	// Add after last item or model
	var clone = modelClone(itemName, itemData);// do it before, init model
	models[itemName].list.append(clone);
// 	getModel(itemName).parent().find(".item.item-"+itemName).last().after(modelClone(itemName, itemData));
}

function modelItemUpdate(itemRow, itemData) {
	// Update clone, preserve ID
	itemRow = $(itemRow);
// 	console.log("itemRow.data ", itemRow.data(), itemRow);
	itemRow.after(modelClone(itemRow.data("itemtype"), itemData).attr("id", itemRow.attr("id"))).remove();
}

var Config;
function saveItems() {
	for( var itemName in Config ) {
		if( !isObject(Config[itemName]) ) {
			continue;
		}
		Config[itemName] = isArray(Config[itemName]) ? [] : {};
		console.log("Scan existing "+itemName);
		var count = 0;
		$(".item.model_item.item-"+itemName).each(function(index) {
			var data = jQuery.extend({}, $(this).data("itemdata"));
			console.log(data);
			if( isDefined(data._key_) ) {
				index = data._key_;
				delete data._key_;
			}
			if( isDefined(data._value_) ) {
				data = data._value_;
			}
			console.log(index+" => ", data);
			Config[itemName][index] = data;
			count++;
		});
		console.log("Config["+itemName+"]", Config[itemName]);
		if( isDefined(models[itemName]) ) {
// 			if( Config[itemName].length ) {
			if( count ) {
				models[itemName].placeholder.hide().detach();
			} else {
				models[itemName].list.append(models[itemName].placeholder.show());
			}
		}
// 		if( Config[itemName].length ) {
// // 			console.log("Not Empty");
// 			$(".item-"+itemName+".item_placeholder:visible").hide();
// 		} else {
// // 			console.log("Empty");
// 			$(".item-"+itemName+".item_placeholder:hidden").show();
// 		}
	}
	$(":input[name=items]").val(JSON.stringify(Config));
}

$(function() {
	$("#InputComposerKeywords").select2({
		tags: true,
		tokenSeparators: [',']
	});
	
	EditAuthorDialog = $("#EditAuthorDialog").modal({show:false});

	$(".action-create.create_authors").click(function() {
		EditAuthorDialog.removeClass('mode-update').addClass('mode-create');
		EditAuthorDialog.find("form").get(0).reset();
		EditAuthorDialog.find(".save_author").data("itemtype", "authors");
		EditAuthorDialog.modal("show");
		return false;
	});

	$(".list-authors").on("click", ".item-authors .action-update", function() {
		EditAuthorDialog.removeClass('mode-create').addClass('mode-update');
		EditAuthorDialog.find("form").get(0).reset();
		var itemRow = $(this).closest(".model_item.item-authors");
		EditAuthorDialog.fill("author_", itemRow.data("itemdata"));
		EditAuthorDialog.find(".save_author").data("itemid", itemRow.attr("id"));
		EditAuthorDialog.modal("show");
	});

	$(".list-authors").on("click", ".item-authors .action-delete", function() {
		var itemRow = $(this).closest(".model_item.item-authors");
		itemRow.remove();
		saveItems();
	});

	EditAuthorDialog.find(".save_author").click(function() {
		// Update - Require data "itemid" - Preserve old object
		// Create - Require data "itemtype" - Create new object
		var update = EditAuthorDialog.hasClass("mode-update");
		var itemRow = null;
		var itemData = {};
		if( update ) {
			itemRow = $("#"+$(this).data("itemid"));
			itemData = itemRow.data("itemdata");
		}
		EditAuthorDialog.find(":input[data-field]").each(function() {
			itemData[$(this).data("field")] = $(this).val();
		});
		if( !itemData.name ) {
			return;
		}
		if( update ) {
			modelItemUpdate(itemRow, itemData);
		} else {
			modelItemAdd($(this).data("itemtype"), itemData);
		}
		EditAuthorDialog.modal("hide");
		saveItems();
	});
	
	EditDependencyDialog = $("#EditDependencyDialog").modal({show:false});

	$(".action-create.create_require").click(function() {
		EditDependencyDialog.removeClass('mode-update').addClass('mode-create');
		EditDependencyDialog.find("form").get(0).reset();
		EditDependencyDialog.find(".save_dependency").data("itemtype", "require");
		EditDependencyDialog.modal("show");
		return false;
	});

	$(".list-require").on("click", ".item-require .action-update", function() {
		EditDependencyDialog.removeClass('mode-create').addClass('mode-update');
		EditDependencyDialog.find("form").get(0).reset();
		var itemRow = $(this).closest(".model_item.item-require");
		var data = itemRow.data("itemdata");
		data.name = data._key_;
		EditDependencyDialog.fill("author_", data);
		EditDependencyDialog.find(".save_dependency").data("itemid", itemRow.attr("id"));
		EditDependencyDialog.modal("show");
	});

	$(".list-require").on("click", ".item-require .action-delete", function() {
		var itemRow = $(this).closest(".model_item.item-require");
		itemRow.remove();
		saveItems();
	});

	// TODO: Remove/Disable already registered dependencies
	EditDependencyDialog.find(".save_dependency").click(function() {
		// Update - Require data "itemid" - Preserve old object
		// Create - Require data "itemtype" - Create new object
		var update = EditDependencyDialog.hasClass("mode-update");
		console.log("Save dependency with mode, update ? "+update);
		var itemRow = null;
		var itemData = {};
		if( update ) {
			itemRow = $("#"+$(this).data("itemid"));
			itemData = itemRow.data("itemdata");
		}
		EditDependencyDialog.find(":input[data-field]").each(function() {
			itemData[$(this).data("field")] = $(this).val();
		});
		if( !itemData._key_ || !itemData._value_ ) {
			return;
		}
		if( update ) {
			modelItemUpdate(itemRow, itemData);
		} else {
			modelItemAdd($(this).data("itemtype"), itemData);
		}
		EditDependencyDialog.modal("hide");
		saveItems();
	});

	/*
	$("#BtnAddDependency").click(function() {
		EditDependencyDialog.removeClass('mode-update').addClass('mode-create');
		EditDependencyDialog.find("form").get(0).reset();
// 		EditDependencyDialog.find(":input").reset();
		EditDependencyDialog.modal("show");
	});
	*/

	
	(function() {
// 		console.log("config data", $(":input[name=items]").val());
// 		var data = $.parseJSON($(":input[name=items]").val());
		Config = $.parseJSON($(":input[name=items]").val());
// 		console.log("json Config", Config);
		for( var itemName in Config ) {
			var items = Config[itemName];
// 			console.log("Config-"+itemName+" => ", items);
// 			if( !isArray(items) && !isObject(items) ) {
			if( !isObject(items) ) {
				continue;
			}
// 			console.log("Array ? "+isArray(items));
// 			console.log("Object ? "+isObject(items));
			var c = 0;
			// Associative takes care of key
			// key will be saved in _key_
			var assoc = isPureObject(items);
			for( var i in items ) {
// 				if( !c ) {
// 					assoc = (i != 0);
// 				}
				var itemData = items[i];
				c++;
// 				console.log("itemData", itemData);
				if( isScalar(itemData) ) {
					itemData = {"_value_": itemData};
				} else
				if( !isObject(itemData) ) {
					continue;
				}
				if( assoc ) {
					itemData._key_ = i;
				}
				modelItemAdd(itemName, itemData);
			}
		}
	})();

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
.list-group-item.item_model,
.list-group-item.item_placeholder {
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

