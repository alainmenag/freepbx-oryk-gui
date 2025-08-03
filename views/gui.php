
<div class="container-fluid">
	<div class="fpbx-container">
		<div class="display full-border">
			<form class="fpbx-submit" name="submitSettings" action="" method="post">

				<input type="hidden" name="action" value="setkey">

				<div class="section-title" data-for="AdvancedSettingsDetails">
					<h2>
						<i class="fa fa-minus"></i>
						<span class="title">UX</span>
					</h2>
				</div>

				<div class="section" data-id="AdvancedSettingsDetails" style="">

					<?php foreach ($sets as $key => $set): ?>
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
											data-defval="<?php echo $set['default']; ?>" class="hidden defset">
											<i class="fa fa-refresh"></i>
										</a>
									</div>
									<div class="col-md-5 radioset text-right <?php echo $set['disabled'] ? 'disable' : ''; ?>">
										<input type="hidden" id="<?php echo $key; ?>default"
											value="<?php echo $set['default']; ?>" />
										<input type="radio" class="" id="<?php echo $key; ?>true"
											name="<?php echo $key; ?>" value="true" <?php echo ($set['default'] ? 'checked=""' : ''); ?> />
										<label for="<?php echo $key; ?>true">Yes</label>
										<input type="radio" class="" id="<?php echo $key; ?>false"
											name="<?php echo $key; ?>" value="false" <?php echo (!$set['default'] ? 'checked=""' : ''); ?> />
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