<?php
// functions.inc.php

if (!function_exists('Oryk_gui_asset_url')) {
	/**
	 * URL for a file under assets/, stamped with its mtime.
	 *
	 * The stamp matters: these scripts are cached hard by the browser, and a
	 * stale copy is not merely old, it can be actively wrong -- an older
	 * touch-scroll.js missing setEnabled() threw inside the settings page and
	 * stopped changes from saving at all.
	 */
	function Oryk_gui_asset_url($relative)
	{
		$path = __DIR__ . '/assets/' . $relative;
		$url = '/admin/assets/oryk_gui/' . $relative;

		$mtime = @filemtime($path);

		return $mtime ? $url . '?v=' . $mtime : $url;
	}
}

if (!function_exists('Oryk_gui_configpageinit')) {
	/**
	 * Called by FreePBX on every admin page load, for every active module.
	 * This is how the GUI module hooks itself into pages it does not own.
	 *
	 * Anything echoed here lands in <head> ahead of FreePBX's own bundles, so
	 * injected scripts cannot assume jQuery or <body> exist yet.
	 */
	function Oryk_gui_configpageinit($page)
	{
		$freepbx = \FreePBX::create();
		$oryk = $freepbx->oryk_gui;

		$user = $oryk->getUser();

		// Publishes this user's settings as window.OrykGui and applies dark mode
		// before anything paints. Must come before touch-scroll.js, which reads
		// its setting from there. gui.css itself is loaded by FreePBX from the
		// BRAND_CSS_CUSTOM setting (see install.php).
		echo $oryk->getBootstrapScript();

		// If logged in and module no module access, load assets
		if ($user && !$user['admin'] && !$_SESSION['AMP_user']->checkSection('oryk_gui')) {
			echo '<link rel="stylesheet" type="text/css" href="' . Oryk_gui_asset_url('admin/reset.css') . '">';
			echo '<script type="text/javascript" src="' . Oryk_gui_asset_url('admin/extend.js') . '"></script>';
		}

		echo '<script type="text/javascript" src="' . Oryk_gui_asset_url('vendor/touch-scroll.js') . '"></script>';
	}
}
