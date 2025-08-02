<?php
// install.php

// A simple function to set a FreePBX setting
function set_freepbx_setting($key, $value)
{
	$sql = "UPDATE freepbx_settings SET `value` = ? WHERE `keyword` = ?";
	$sth = \FreePBX::Database()->prepare($sql);
	$sth->execute([$key, $value]);
}

// Set the custom CSS path
//$css_path = '/admin/modules/oryk_gui/assets/css/reset.css';
$css_path = '';

set_freepbx_setting('BRAND_CSS_CUSTOM', $css_path);
set_freepbx_setting('BRAND_IMAGE_FREEPBX_LINK_LEFT', '/admin/config.php?display=index');