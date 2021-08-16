<?php
/**
 * @var HTMLRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 */

use Orpheus\Cache\FSCache;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;


$rendering->useLayout('page_skeleton');

$cacheAPCInfos = apcu_cache_info();
?>
	<div class="row">
		<div class="col-md-12">
			<?php $rendering->useLayout('panel-default'); ?>
			
			<div class="row">
				<div class="col-lg-4">
					<h2>Informations</h2>
					<?php
					
					foreach( $cacheAPCInfos as $key => $value ) {
						if( !is_scalar($value) ) {
							continue;
						}
						?>
						<div class="form-group row">
							<label class="col-sm-3 col-lg-6 control-label"><?php echo $key; ?></label>
							<div class="col-sm-9 col-lg-6">
								<p class="form-control-static"><?php echo (stripos($key, '_time') !== false && $value) ? dt($value) : $value; ?></p>
							</div>
						</div>
						<?php
					}
					?>
				
				</div>
				<div class="col-lg-8">
					<h2>Content</h2>
					<?php
					if( empty($cacheAPCInfos['cache_list']) ) {
						echo '<div class="alert alert-info" role="alert">Empty cache</div>';
					} else {
						?>
						<div class="table-responsive">
							<table class="table table-bordered table-hover">
								<thead>
								<tr>
									<?php
									foreach( $cacheAPCInfos['cache_list'][0] as $field => $value ) {
										echo '
							<th>' . $field . '</th>';
									}
									?>
								</tr>
								</thead>
								<tbody>
								<?php
								foreach( $cacheAPCInfos['cache_list'] as &$cache ) {
									echo '
						<tr>';
									foreach( $cache as $field => $value ) {
										echo '
							<td title="' . $value . '">' . ((stripos($field, '_time') !== false && $value) ? dt($value) : $value) . '</td>';
									}
									echo '
						</tr>';
								}
								?>
								</tbody>
							</table>
						</div>
						
						<?php
					}
					?>
				</div>
			</div>
			
			<?php $rendering->endCurrentLayout([
				'title'  => 'Cache APC',
				'footer' => '
			<div class="panel-footer text-right"><form method="POST">
				<button name="submitClearAllAPCCache" type="submit" class="btn btn-primary" data-submittext="Clearing APC cache...">Clear APC Cache</button>
			</form></div>',
			]); ?>
		</div>
	</div>

<?php
unset($cacheAPCInfos);
$cacheFileInfos = FSCache::listAll();
?>
	
	<div class="row">
		<div class="col-md-12">
			<?php $rendering->useLayout('panel-default'); ?>
			
			<div class="col-lg-8">
				<h2>Content</h2>
				<?php
				if( empty($cacheFileInfos) ) {
					echo '<div class="alert alert-info" role="alert">Empty cache.</div>';
				} else {
					?>
					<div class="table-responsive">
						<table class="table table-bordered table-hover">
							<thead>
							<tr>
								<th>Class</th>
								<th>Name</th>
								<th>Size</th>
								<th>Modification Time</th>
							</tr>
							</thead>
							<tbody>
							<?php
							foreach( $cacheFileInfos as $class => $classCaches ) {
								foreach( $classCaches as $cacheName => $cacheFile ) {
									echo '
<tr>
	<td>' . $class . '</td>
	<td>' . $cacheName . '</td>
	<td>' . filesize($cacheFile) . '</td>
	<td>' . dt(filemtime($cacheFile)) . '</td>
</tr>';
								}
							}
							?>
							</tbody>
						</table>
					</div>
					
					<?php
				}
				?>
			</div>
			
			<?php $rendering->endCurrentLayout([
				'title'  => 'Cache Files',
				'footer' => '
			<div class="panel-footer text-right"><form method="POST">
				<button name="submitClearAllFSCache" type="submit" class="btn btn-primary" data-submittext="Clearing File cache...">Clear File Cache</button>
			</form></div>',
			]); ?>
		</div>
	</div>
<?php
unset($cacheFileInfos);
