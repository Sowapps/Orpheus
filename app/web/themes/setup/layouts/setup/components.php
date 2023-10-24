<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

function rowStaticInput(string $id, string $label, string $value): void {
	?>
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label" for="<?php echo $id; ?>">
			<?php echo $label; ?>
		</label>
		<div class="col-sm-9">
			<input id="<?php echo $id; ?>" type="text" readonly class="form-control-plaintext" value="<?php echo $value; ?>">
		</div>
	</div>
	<?php
}
