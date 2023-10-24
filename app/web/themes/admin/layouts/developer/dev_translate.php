<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var FormToken $formToken
 * @var array $locales
 * @var array $editedDomains
 * @var array $publicDomains
 * @var string $fallbackLocale
 * @var string $translatingLocale
 * @var array $translatingFile
 * @var string $translatingFilePath
 * @warning Experimental feature, unstable
 */

use Orpheus\Form\FormToken;
use Orpheus\Initernationalization\TranslationService;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.admin');
$rendering->addThemeJsFile('developer/dev_translate.js');
$rendering->addThemeCssFile('developer/dev_translate.css');

if( $translatingLocale ) {
	// Compare using selected locale
	$defaultTranslator = TranslationService::getActive();
	$selectedTranslator = new TranslationService($translatingLocale);
	?>
	<div class="row">
		<div class="col-lg-8">
			<h2>
				<?php echo t('translation.edit.title', DOMAIN_TRANSLATIONS, [$translatingLocale]); ?>
				<a class="btn btn-outline-secondary btn-sm float-end" href="<?php echo u(ROUTE_DEV_APP_TRANSLATE); ?>">
					<?php echo t('changeLanguage', DOMAIN_TRANSLATIONS); ?>
				</a>
			</h2>
			
			<div class="alert alert-danger" role="alert">
				This feature is experimental, do not consider using it for production, it's not working fine.
			</div>
			
			<p><?php echo t('pleaseRespectFormatting', DOMAIN_TRANSLATIONS); ?></p>
			<p><?php echo t('notifyDeveloperFromUpdates', DOMAIN_TRANSLATIONS, ['DEV_EMAIL' => DEV_EMAIL, 'APP_NAME' => t('app_name')]); ?></p>
			
			<?php
			if( $translatingFile ) {
				?>
				<p><?php echo t('lastUpdateText', DOMAIN_TRANSLATIONS, [dt(filemtime($translatingFilePath))]); ?></p>
				<div class="text-right clearfix mb10">
				<form method="POST" role="form"><?php echo $formToken; ?>
					<button name="submitAnalyze" class="btn btn-outline-secondary" type="submit"><?php echo t('analyzeTranslations', DOMAIN_TRANSLATIONS); ?></button>
					<button name="submitDownload" class="btn btn-primary" type="submit"><?php echo t('download'); ?></button>
				</form>
				</div><?php
			}
			?>
			
			<form method="POST" role="form"><?php echo $formToken; ?>
				<h4><?php echo t('domains', DOMAIN_TRANSLATIONS); ?></h4>
				<div class="accordion" id="TranslatedDomains" role="tablist" aria-multiselectable="true">
					
					<?php
					foreach( $defaultTranslator->guessAvailableDomains() as $domain ) {
						$domainKeys = array_keys($defaultTranslator->getDomainTranslations($domain));
						$rows = '';
						$missing = 0;
						$total = 0;
						$domainTranslations = [];
						foreach( $domainKeys as $key ) {
							// Can not resolve links because we could use % to translate using sprintf('%s')
							$defaultValue = $defaultTranslator->getTranslation($key, $domain);
							$selectedValue = $selectedTranslator->getTranslation($key, $domain);
							$domainTranslations[$key] = [$defaultValue, $selectedValue];
							$total++;
							if( !$selectedValue ) {
								$missing++;
							}
						}
						
						$panelId = sprintf('ItemTranslationDomain_%s', $domain);
						?>
						
						<div class="accordion-item translation">
							<h5 class="accordion-header">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $panelId; ?>"
										aria-expanded="false" aria-controls="<?php echo $panelId; ?>">
									<?php
									echo t('domainTitle', DOMAIN_TRANSLATIONS, [$domain]);
									if( $missing ) {
										?><small class="ms-2 text-muted"> (<?php echo t('missingXTranslations', DOMAIN_TRANSLATIONS, [$missing]); ?>)</small><?php
									}
									?>
								</button>
							</h5>
							<div id="<?php echo $panelId; ?>" class="accordion-collapse collapse" data-bs-parent="#TranslatedDomains">
								<div class="accordion-body">
									<div class="table-responsive">
										<table class="table table-bordered table-hover">
											<thead>
											<tr>
												<th><?php echo t('key', DOMAIN_TRANSLATIONS); ?></th>
												<th><?php echo $fallbackLocale; ?></th>
												<th><?php echo $translatingLocale; ?></th>
											</tr>
											</thead>
											<tbody>
											<?php
											foreach( $domainTranslations as $key => [$defaultValue, $selectedValue] ) {
												$lineCount = substr_count($defaultValue, "\n");
												?>
												<tr>
													<td class="cellkey"><?php echo $key; ?></td>
													<td class="cellfallback"><?php echo html($defaultValue); ?></td>
													<td class="celltranslation">
														<?php
														if( $lineCount ) {
															?>
															<!--suppress HtmlFormInputWithoutLabel -->
															<textarea name="translate[<?php echo $domain; ?>][<?php echo $key; ?>]" class="form-control">
																	<?php echo $selectedValue; ?>
																</textarea>
															<?php
														} else {
															?>
															<!--suppress HtmlFormInputWithoutLabel -->
															<input name="translate[<?php echo $domain; ?>][<?php echo $key; ?>]" value="<?php echo $selectedValue; ?>"
																   type="text" class="form-control"/>
															<?php
														}
														?>
													</td>
												</tr>
												<?php
												//												}
											}
											?>
											</tbody>
										</table>
									</div>
									<div class="p-3 text-end">
										<button name="submitSave" class="btn btn-primary" type="submit"><?php echo t('saveAll'); ?></button>
									</div>
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
	// No locale selected
	?>
	<form method="GET" role="form">
		<div class="row">
			<div class="col-lg-3">
				<h2><?php echo t('translations', DOMAIN_TRANSLATIONS); ?></h2>
				<div class="mb-3">
					<label class="control-label" for="InputDefaultLocale"><?php echo t('referenceLanguage', DOMAIN_TRANSLATIONS); ?></label>
					<input type="text" class="form-control-plaintext" value="<?php echo $fallbackLocale; ?>" id="InputDefaultLocale">
				</div>
				
				<div class="mb-3">
					<label class="control-label" for="InputLocale"><?php echo t('chooseLanguageToTranslate', DOMAIN_TRANSLATIONS); ?></label>
					<select name="locale" class="form-control widget-select" id="InputLocale">
						<?php
						echo htmlOptions('locale', $locales, null, OPT_LABEL_IS_KEY | OPT_VALUE_IS_KEY);
						?>
					</select>
				</div>
				
				<div class="py-3 text-end">
					<button type="submit" class="btn btn-primary"><?php echo t('translate', DOMAIN_TRANSLATIONS); ?></button>
				</div>
			</div>
		</div>
	</form>
	<?php
}
?>

<script>
	ready(() => {
		provideTranslations(<?php echo json_encode([
			'type_string'   => t('type_string', DOMAIN_TRANSLATIONS),
			'type_number'   => t('type_number', DOMAIN_TRANSLATIONS),
			'type_variable' => t('type_variable', DOMAIN_TRANSLATIONS),
		]); ?>);
	})
</script>
