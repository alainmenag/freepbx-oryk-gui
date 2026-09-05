<?php
// install.php

/**
 * Write a FreePBX advanced setting.
 *
 * Prefers the freepbx_conf settings API, which validates the keyword and
 * flushes FreePBX's settings cache so the value is live immediately. Every
 * call into it is guarded, because an install script that fatals leaves the
 * module half-installed -- if the API is missing or shaped differently on this
 * version, fall back to writing the row directly.
 */
function set_freepbx_setting($key, $value)
{
	try {
		if (class_exists('\freepbx_conf')) {
			$conf = \freepbx_conf::create();

			$exists = method_exists($conf, 'conf_setting_exists')
				? $conf->conf_setting_exists($key)
				: true;

			if ($exists && method_exists($conf, 'set_conf_values')) {
				// (values, commit, override_readonly)
				$conf->set_conf_values([$key => $value], true, true);
				return true;
			}
		}
	} catch (\Throwable $e) {
		// Fall through to the direct write.
	}

	// Note the bind order: value first, keyword second.
	$sql = "UPDATE freepbx_settings SET `value` = ? WHERE `keyword` = ?";
	$sth = \FreePBX::Database()->prepare($sql);

	return $sth->execute([$value, $key]);
}

// gui.css is the global theming layer. BRAND_CSS_CUSTOM is FreePBX's supported
// hook for a stylesheet in <head> on every admin page, which is what dark mode
// needs -- the module's own assets/ are only auto-loaded on its own page.
set_freepbx_setting('BRAND_CSS_CUSTOM', '/admin/assets/oryk_gui/css/gui.css');

set_freepbx_setting('BRAND_IMAGE_FREEPBX_LINK_LEFT', '/admin/config.php?display=index');
