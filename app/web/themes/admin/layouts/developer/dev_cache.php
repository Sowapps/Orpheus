<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var array $caches
 */

use Orpheus\Cache\Cache;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;


$rendering->useLayout('layout.admin');
?>
	
	<div class="row">
		<?php
		// Only supported caches
		foreach( $caches as $cacheManagerKey => $cacheManagerClass ) {
			/** @var Cache $cacheManagerClass */
			$cacheInstances = $cacheManagerClass::list();
			?>
			<div class="col-12 col-xl-6">
				<?php $rendering->useLayout('component/panel'); ?>
				
				<h2><?php echo t('property.contents', DOMAIN_CACHE); ?></h2>
				<?php
				if( !$cacheInstances ) {
					?>
					<div class="alert alert-info" role="alert"><?php echo t('empty', DOMAIN_CACHE); ?></div>
					<?php
				} else {
					$hasUnknownCache = false;
					?>
					<div class="table-responsive">
						<table class="table table-bordered table-hover" id="TableCacheItems_<?php echo $cacheManagerKey; ?>">
							<thead>
							<tr>
								<th><?php echo t('property.class', DOMAIN_CACHE); ?></th>
								<th><?php echo t('property.name', DOMAIN_CACHE); ?> </th>
								<th><?php echo t('property.size', DOMAIN_CACHE); ?> </th>
								<th><?php echo t('property.hits', DOMAIN_CACHE); ?> </th>
							</tr>
							</thead>
							<tbody>
							<?php
							foreach( $cacheInstances as $cacheKey => $cache ) {
								/** @var Cache $cache */
								$isUnknownCache = !$cache->getClass();
								if( $isUnknownCache ) {
									$hasUnknownCache = true;
								}
								?>
								<tr class="item-cache<?php echo $isUnknownCache ? ' cache-unknown' : ''; ?>" <?php echo $isUnknownCache ? ' hidden' : ''; ?>>
									<td><?php echo $cache->getClass(); ?></td>
									<td><?php echo $cache->getName(); ?></td>
									<td><?php echo $cache->getSize(); ?></td>
									<td><?php echo $cache->getHits() ?? 'N/A'; ?></td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</div>
					<?php
					if( $hasUnknownCache ) {
						?>
						<div class="form-check mt-2 py-2">
							<input class="form-check-input" type="checkbox" id="InputShowUnknown_<?php echo $cacheManagerKey; ?>"
								   onchange="domService.toggle('#TableCacheItems_<?php echo $cacheManagerKey; ?> .cache-unknown', this.checked)">
							<label class="form-check-label" for="InputShowUnknown_<?php echo $cacheManagerKey; ?>">
								Show unrecognized cache items
							</label>
						</div>
						<?php
					}
				}
				?>
				<?php $rendering->startNewBlock('footer'); ?>
				<div class="panel-footer text-right">
					<form method="POST">
						<button name="submitClearAll" value="<?php echo $cacheManagerKey; ?>" type="submit" class="btn btn-primary"
								data-submittext="<?php echo t('action.clearAll.processing', DOMAIN_CACHE); ?>">
							<?php echo t('action.clearAll.label', DOMAIN_CACHE); ?>
						</button>
					</form>
				</div>
				
				<?php $rendering->endCurrentLayout(['title' => t(sprintf('manager.%s', $cacheManagerKey), DOMAIN_CACHE)]); ?>
			</div>
			
			<?php
		}
		?>
	</div>
<?php
