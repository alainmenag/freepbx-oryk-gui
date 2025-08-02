<?php
// functions.inc.php

function Oryk_gui_configpageinit($page)
{
	$freepbx = \FreePBX::create();
	$oryk = $freepbx->oryk_gui;

	$user = $oryk->getUser();

	// If logged in and module no module access, load assets
	if ($user && !$user['admin'] && !$_SESSION['AMP_user']->checkSection('oryk_gui')) {
		echo '<link rel="stylesheet" type="text/css" href="/admin/modules/oryk_gui/assets/css/reset.css">';
		echo '<script type="text/javascript" src="/admin/modules/oryk_gui/assets/js/extend.js"></script>';
	}

	// print all $freepbx keys
	// die(json_encode([
	// 	'freepbx' => array_keys(get_object_vars($freepbx)),
	// ]));
}
?>