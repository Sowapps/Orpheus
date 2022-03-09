<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var FormToken $formToken
 */

use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('page_skeleton');

/* @var array $editedDomains */
/* @var array $publicDomains */
/* @var string $fallbackLocale */
/* @var string $translatingLocale */
/* @var array $translatingFile */
/* @var string $translatingFilePath */


if( $translatingLocale ) {
	?>
	<div class="row">
		<div class="col-lg-8">
			<h2><?php echo $translatingLocale; ?> <small><a href="<?php _u(ROUTE_DEV_APPTRANSLATE); ?>"><?php _t('changeLanguage', DOMAIN_TRANSLATIONS); ?></a></small></h2>
			
			<p><?php _t('pleaseRespectFormatting', DOMAIN_TRANSLATIONS); ?></p>
			<p><?php _t('notifiyDeveloperFromUpdates', DOMAIN_TRANSLATIONS, ['DEVEMAIL' => DEVEMAIL, 'APP_NAME' => t('app_name')]); ?></p>
			
			<?php
			if( $translatingFile ) {
				?>
				<p><?php _t('lastUpdateText', DOMAIN_TRANSLATIONS, dt(filemtime($translatingFilePath))); ?></p>
				<div class="text-right clearfix mb10">
				<form method="POST" role="form"><?php echo $formToken; ?>
					<button name="submitAnalyze" class="btn btn-outline-secondary" type="submit"><?php _t('analyzeTranslations', DOMAIN_TRANSLATIONS); ?></button>
					<button name="submitDownload" class="btn btn-primary" type="submit"><?php _t('download'); ?></button>
				</form>
				</div><?php
			}
			?>
			
			<form method="POST" role="form"><?php echo $formToken; ?>
				<div class="panel-group" id="TranslatedDomains" role="tablist" aria-multiselectable="true">
					
					<?php
					foreach( $controller->listDomains() as $domain ) {
						// Foreach domain present in fallback
						// $fallbackDomain is an array of translations for this domain presents in fallback
						$fallbackDomain = getLangDomainFile($fallbackLocale, $domain);
						$translatingDomain = $translatingFile && isset($translatingFile[$domain]) ? $translatingFile[$domain] : getLangDomainFile($translatingLocale, $domain);
						
						$rows = '';
						$missing = 0;
						$total = 0;
						foreach( $fallbackDomain as $key => $fallbackText ) {
							$value = isset($translatingDomain[$key]) ? $translatingDomain[$key] : '';
							if( $fallbackText && $fallbackText[0] === '%' ) {
								$ref = substr($fallbackText, 1);
								if( isset($fallbackDomain[$ref]) ) {
									$fallbackText = $fallbackDomain[$ref];
								}
							}
							$total++;
							if( !$value ) {
								$missing++;
							}
							
							$lineCount = substr_count($fallbackText, "\n");
							$rows .= '
						<tr>
							<td class="cellkey">' . $key . '</td>
							<td class="cellfallback">' . nl2br(escapeText($fallbackText)) . '</td>
							<td class="celltranslation">' . ($lineCount ? '<textarea name="translate[' . $domain . '][' . $key . ']" class="form-control">' . $value . '</textarea>' : '<input name="translate[' . $domain . '][' . $key . ']" value="' . $value . '" type="text" class="form-control"/>') . '</td>
						</tr>';
						}
						
						?>
						
						<div class="panel <?php echo isset($editedDomains[$domain]) ? 'panel-info' : 'panel-default'; ?>">
							<div class="panel-heading" role="tab" id="#Domain<?php echo $domain; ?>Heading">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#TranslatedDomains" href="#Domain<?php echo $domain; ?>" aria-expanded="false"
									   aria-controls="Domain<?php echo $domain; ?>">
										<?php
										echo $domain . ($missing ? ' <small>' . t('missingXTranslations', DOMAIN_TRANSLATIONS, $missing) . '</small>' : '');
										?>
									</a>
								</h4>
							</div>
							<div id="Domain<?php echo $domain; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="Domain<?php echo $domain; ?>Heading">
								<div class="panel-body">
									<div class="table-responsive">
										<table class="table table-bordered table-hover">
											<thead>
											<tr>
												<th><?php _t('key', DOMAIN_TRANSLATIONS); ?></th>
												<th><?php echo $fallbackLocale; ?></th>
												<th><?php echo $translatingLocale; ?></th>
											</tr>
											</thead>
											<tbody>
											<?php echo $rows; ?>
											</tbody>
										</table>
									</div>
								</div>
								<div class="panel-footer text-right">
									<button name="submitSave" class="btn btn-primary" type="submit"><?php _t('saveAll'); ?></button>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				
				</div>
			</form>
		</div>
	</div>
	
	<?php
} else {
	?>
	<form method="GET" role="form">
		<div class="row">
			<div class="col-lg-6">
				<h2><?php _t('translations', DOMAIN_TRANSLATIONS); ?></h2>
				<div class="form-group">
					<label class="control-label"><?php _t('referenceLanguage', DOMAIN_TRANSLATIONS); ?></label>
					<p class="form-control-static"><?php echo $fallbackLocale; ?></p>
				</div>
				
				<div class="form-group">
					<label class="control-label"><?php _t('chooseLanguageToTranslate', DOMAIN_TRANSLATIONS); ?></label>
					<select name="locale" class="form-control">
						<?php
						_htmlOptions('locale', $controller->listAllLocales(), null, OPT_LABEL_IS_KEY | OPT_VALUE_IS_KEY);
						?>
					</select>
				</div>
				
				<button type="submit" class="btn btn-primary pull-right"><?php _t('translate', DOMAIN_TRANSLATIONS); ?></button>
			
			</div>
		</div>
	</form>
	<?php
}
?>
<style>
.cellkey {
	max-width: 33%;
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}

.cellfallback {
	position: relative;
}

.cellfallback button {
	float: right;
}

.celltranslation {
	width: 33%;
}

.celltranslation textarea {
	height: 100%;
}
</style>
<script type="text/javascript">

$(function () {
	provideTranslations(<?php echo json_encode([
		'type_string'   => t('type_string', DOMAIN_TRANSLATIONS),
		'type_number'   => t('type_number', DOMAIN_TRANSLATIONS),
		'type_variable' => t('type_variable', DOMAIN_TRANSLATIONS),
	]); ?>);
	
	$(".cellfallback").each(function () {
		// Save content
		var content = $(this).text();
		
		// Improve content
		$(this).html($(this).html()
			.replace(new RegExp("(%s\\$?\d?)", 'g'), '<b title="' + t('type_string') + '">$1</b>')
			.replace(new RegExp("(%d\\$?\d?)", 'g'), '<b title="' + t('type_number') + '">$1</b>')
			.replace(/(#[^#]*#)/g, '<b title="' + t('type_variable') + '">$1</b>')
		);
		
		// Add copy button
		var input = $(this).next().find(":input");
		var btn = $('<button class="btn btn-default" type="button"><i class="fa fa-fw fa-forward"></i></button>');
		btn.click(function () {
			input.val(content);
		});
		$(this).prepend(btn);
		btn = null;
	});
});
</script>

