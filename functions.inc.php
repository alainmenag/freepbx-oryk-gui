<?php
// functions.inc.php

function Oryk_gui_configpageinit($page)
{
	$freepbx = \FreePBX::create();
	$oryk = $freepbx->oryk_gui;

	$user = $oryk->getUser();

	// If logged in and module no module access, load assets
	if ($user && !$user['admin'] && !$_SESSION['AMP_user']->checkSection('oryk_gui')) {
		echo '<link rel="stylesheet" type="text/css" href="/admin/assets/oryk_gui/admin/reset.css">';
		echo '<script type="text/javascript" src="/admin/assets/oryk_gui/admin/extend.js"></script>';
	}

	//echo '<script type="text/javascript" src="/admin/assets/oryk_gui/vendor/utterscroll-master/jquery-scrollable.js"></script>';
	echo '<script type="text/javascript" src="/admin/assets/oryk_gui/vendor/touch-scroll.js"></script>';



	// print all $freepbx keys
	// die(json_encode([
	// 	'freepbx' => array_keys(get_object_vars($freepbx)),
	// ]));
}
?>