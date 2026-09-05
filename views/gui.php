<?php
/**
 * views/gui.php
 *
 * @var array $sets   Setting definitions from Oryk_gui::$sets
 * @var array $values Current value per key, already cast to the declared type
 * @var array|null $user
 */
$values = isset($values) ? $values : [];
?>
<div class="container-fluid">
	<div class="fpbx-container">
		<div class="display full-border">
			<form name="submitSettings" id="orykGuiSettings" action="" method="post">

				<input type="hidden" name="action" value="setkey">
				<input type="hidden" name="display" value="oryk_gui">

				<div class="section-title" data-for="AdvancedSettingsDetails">
					<h2>
						<i class="fa fa-minus"></i>
						<span class="title">UX</span>
					</h2>
				</div>

				<div class="section" data-id="AdvancedSettingsDetails" style="">

					<?php foreach ($sets as $key => $set): ?>
						<?php
						$current = array_key_exists($key, $values) ? $values[$key] : $set['default'];
						$current = $set['type'] === 'bool' ? (bool) $current : $current;
						$default = $set['type'] === 'bool' ? ($set['default'] ? 'true' : 'false') : $set['default'];
						$disabled = !empty($set['disabled']);
						?>
						<div class="element-container">
							<div class="row">
								<div class="form-group">
									<div class="col-md-7">
										<label class="control-label" for="<?php echo $key; ?>">
											<?php echo $set['title']; ?></label>
										<?php if (isset($set['help'])): ?>
											<i class="fa fa-question-circle fpbx-help-icon"
												data-for="<?php echo $key; ?>"></i>
										<?php endif; ?>
										<a href="#" data-for="<?php echo $key; ?>"
											data-type="<?php echo $set['type']; ?>"
											data-defval="<?php echo $default; ?>" class="hidden defset">
											<i class="fa fa-refresh"></i>
										</a>
									</div>
									<div class="col-md-5 radioset text-right <?php echo $disabled ? 'disable' : ''; ?>">
										<input type="hidden" id="<?php echo $key; ?>default"
											value="<?php echo $default; ?>" />
										<input type="radio" class="oryk-setting" id="<?php echo $key; ?>true"
											data-oryk-key="<?php echo $key; ?>"
											name="<?php echo $key; ?>" value="true" <?php echo ($current ? 'checked=""' : ''); ?>
											<?php echo $disabled ? 'disabled=""' : ''; ?> />
										<label for="<?php echo $key; ?>true">Yes</label>
										<input type="radio" class="oryk-setting" id="<?php echo $key; ?>false"
											data-oryk-key="<?php echo $key; ?>"
											name="<?php echo $key; ?>" value="false" <?php echo (!$current ? 'checked=""' : ''); ?>
											<?php echo $disabled ? 'disabled=""' : ''; ?> />
										<label for="<?php echo $key; ?>false">No</label>
									</div>
								</div>
							</div>
							<?php if (isset($set['help'])): ?>
								<div class="row">
									<div class="col-md-12">
										<span id="<?php echo $key; ?>-help"
											class="help-block fpbx-help-block"><?php echo $set['help']; ?></span>
									</div>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>

				</div>

			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
(function ($) {
	'use strict';

	// window.OrykGui.apply() is defined by the inline bootstrap in
	// Oryk_gui::getBootstrapScript() and knows what every setting does, so the
	// settings page never carries a second copy of that logic. It swallows its
	// own errors, and is called in a try/catch besides: applying a setting is
	// cosmetic, persisting it is not, and a broken effect must never stop a
	// save. (It used to: a stale cached touch-scroll.js threw here and the
	// handler died before the POST, so only the Submit button saved.)
	function applyLocal(key, on) {
		try {
			if (window.OrykGui && typeof window.OrykGui.apply === 'function') {
				window.OrykGui.apply(key, on);
			}
		} catch (e) {
			if (window.console && console.warn) {
				console.warn('Could not apply ' + key, e);
			}
		}
	}

	// There is no Submit button; every change saves itself.
	$('#orykGuiSettings').on('submit', function (e) {
		e.preventDefault();
	});

	$(document).on('change', '.oryk-setting', function () {
		var $input = $(this);
		var key = $input.data('orykKey');
		var on = $input.val() === 'true';

		// Show the change immediately, then persist. Reverted if the save fails.
		applyLocal(key, on);

		$.post('ajax.php', {
			module: 'oryk_gui',
			command: 'set',
			key: key,
			value: on ? 'true' : 'false'
		}, null, 'json').done(function (res) {
			if (!res || !res.status) {
				applyLocal(key, !on);
				$('#' + key + (on ? 'false' : 'true')).prop('checked', true);
				if (typeof fpbxToast === 'function') {
					fpbxToast((res && res.message) || 'Could not save setting', 'Error', 'error');
				}
				return;
			}
			if (typeof fpbxToast === 'function') {
				fpbxToast('Saved', '', 'success');
			}
		}).fail(function () {
			applyLocal(key, !on);
			$('#' + key + (on ? 'false' : 'true')).prop('checked', true);
			if (typeof fpbxToast === 'function') {
				fpbxToast('Could not save setting', 'Error', 'error');
			}
		});
	});
})(jQuery);
</script>
