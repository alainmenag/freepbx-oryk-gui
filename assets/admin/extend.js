
document.addEventListener('DOMContentLoaded', function () {

	let activeMenuItem = $('.freepbx-navbar a.active').parents('.nav-item');

	if (!activeMenuItem.length) activeMenuItem = $('.freepbx-navbar a[href*="display=index"]').parents('.nav-item');
	
	if (activeMenuItem) $(activeMenuItem).addClass('active');

	// $('.navbar-brand').on('click', function (e) {
	// 	e.preventDefault();
	// 	e.stopPropagation();
	// 	//window.location.href = 'index.php?display=index';
	// });

});

/*
# FreePBX JavaScript Globals Reference

This document outlines global JavaScript variables and objects available when working within the FreePBX web interface (typically in module development or admin customization).

## Core Globals

extmap
//FreePBX

| Name           | Description                                  |
|----------------|----------------------------------------------|
| `FreePBX`      | Primary FreePBX namespace (may vary)         |
| `fpbx`         | UI and reload helpers                        |
| `fpbxClass`    | UI class helper                              |
| `fpbxToast`    | Notification wrapper                         |
| `fpbxConfirm`  | Confirmation dialogs                         |
| `freepbx_reload_error` | Reload error handler               |
| `fpbx_reload`  | Triggers reload banner                       |
| `fpbx_reload_confirm` | Reload with confirmation            |

## Libraries and Utilities

| Name         | Description                                  |
|--------------|----------------------------------------------|
| `$`, `jQuery`| jQuery library                               |
| `_`          | Lodash or Underscore                         |
| `moment`     | Date/time library                            |
| `flatpickr`  | Lightweight date picker                      |
| `Bloodhound` | Typeahead.js suggestion engine               |
| `Cookies`    | Cookie utilities                             |
| `Jed`        | i18n/l10n translator                         |
| `Sortable`   | Drag-and-drop list sorting                   |
| `SmartWizard`| Multi-step UI wizard                         |
| `alertify`   | Dialog and alert library                     |
| `notie`      | Notification UI                              |

## Validation and Helpers

| Name                      | Description                            |
|---------------------------|----------------------------------------|
| `validateCalSubmit`       | Calendar form validation               |
| `validateDestinations`    | Destination selection check            |
| `validateSingleDestination` | Single destination validator        |
| `setDestinations`, `bind_dests_double_selects` | Destination UI tools |
| `settingsactionformatter`, `actionformatter`, `typeformatter` | Table formatters |

## Popover and UI

| Name                    | Description                       |
|-------------------------|-----------------------------------|
| `popover_box`, `popover_box_class` | Popover content/data |
| `positionActionBar`     | UI layout adjustment              |
| `resetModalForm`        | Clears modal content              |
| `toggle_reload_button`  | Shows/hides reload prompt button  |

## Debugging

To inspect all global variables:

```js
console.log(Object.keys(window).sort());

if (fpbx && fpbx.conf && fpbx.conf.modules && fpbx.conf.modules.sysadmin && fpbx.conf.modules.sysadmin.deployment_id) {

*/
