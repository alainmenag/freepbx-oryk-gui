<?php
// install.php

// A simple function to set a FreePBX setting
function set_freepbx_setting($key, $value)
{
	$sql = "REPLACE INTO freepbx_settings (`keyword`, `value`, `name`, `type`, `defaultval`, `readonly`, `hidden`) VALUES (?, ?, ?, 'text', '', 0, 0)";
	$sth = \FreePBX::Database()->prepare($sql);
	$sth->execute([$key, $value, $key]);
}

// Set the custom CSS path
//$css_path = '/admin/modules/oryk_gui/assets/css/reset.css';
$css_path = '';

set_freepbx_setting('BRAND_CSS_CUSTOM', $css_path);