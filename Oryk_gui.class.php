<?php

// Oryk_gui.class.php

namespace FreePBX\modules;

use PDO;
use FreePBX_Helpers;

class Oryk_gui extends FreePBX_Helpers implements \BMO
{
	/**
	 * CSS class put on <html> when dark mode is on. Matches assets/css/gui.css.
	 */
	const DARK_CLASS = 'oryk-dark';

	public $sets = [
		'DARK_MODE' => [
			'type' => 'bool',
			'default' => 0,
			'title' => 'Dark Mode',
			'help' => 'Inverts the interface colors for a dark appearance. Images and video are re-inverted so they keep their original colors.',
			'disabled' => false,
		],
		'TOUCH_SCROLL' => [
			'type' => 'bool',
			'default' => 1,
			'title' => 'Touch Scroll',
			'help' => 'Drag anywhere on the page to scroll, instead of reaching for the scrollbar. Has no effect on touchscreen devices, which already scroll this way.',
			'disabled' => false,
		],
	];

	/** @var array|null|false Cached result of getUser() for this request. */
	private $userCache = false;

	public function __construct($freepbx = null)
	{
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->astman = $freepbx->astman;
	}

	public function showPage()
	{
		$page = isset($_REQUEST['display']) ? $_REQUEST['display'] : 'default';

		switch ($page) {
			case 'oryk_gui':

				return load_view(__DIR__ . '/views/gui.php', [
					'sets' => $this->sets,
					'values' => $this->getSettings(),
					'user' => $this->getUser(),
				]);
			default:
				break;
		}
	}

	public function getUser()
	{
		if ($this->userCache !== false) {
			return $this->userCache;
		}

		$this->userCache = null;

		$ampUser = isset($_SESSION['AMP_user']) ? $_SESSION['AMP_user'] : null;
		$username = $ampUser ? $ampUser->username : null;

		if (!$username) {
			return null;
		}

		$sql = "SELECT * FROM ampusers WHERE username = :username";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([':username' => $username]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($user) {
			return $this->userCache = array_merge($user, [
				'admin' => true,
			]);
		}

		$sql = "SELECT * FROM userman_users WHERE username = :username";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([':username' => $username]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($user) {
			return $this->userCache = array_merge($user, [
				'admin' => false,
			]);
		}

		return $this->userCache = null;
	}

	/* ------------------------------------------------------------------ */
	/* Settings                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * KVStore bucket id for the logged in user.
	 *
	 * Settings are per-user. The two user tables are keyed differently --
	 * ampusers has no id column at all, its primary key is `username`, while
	 * userman_users has an autoincrement `id`. The prefix keeps the two
	 * namespaces from colliding.
	 *
	 * @return string|null null when nobody is logged in.
	 */
	public function getSettingsId()
	{
		$user = $this->getUser();

		if (!$user) {
			return null;
		}

		if (!empty($user['admin'])) {
			return isset($user['username']) && $user['username'] !== ''
				? 'amp_' . $user['username']
				: null;
		}

		return isset($user['id']) && $user['id'] !== ''
			? 'um_' . $user['id']
			: null;
	}

	/**
	 * Current value of one setting, falling back to its default.
	 */
	public function getSetting($key)
	{
		if (!isset($this->sets[$key])) {
			return null;
		}

		$set = $this->sets[$key];
		$id = $this->getSettingsId();

		if ($id === null) {
			return $this->castSetting($set, $set['default']);
		}

		$value = $this->getConfig($key, $id);

		// getConfig() returns false for a key that has never been written.
		if ($value === false || $value === null) {
			return $this->castSetting($set, $set['default']);
		}

		return $this->castSetting($set, $value);
	}

	/**
	 * All settings, keyed the same as $sets.
	 */
	public function getSettings()
	{
		$values = [];

		foreach ($this->sets as $key => $set) {
			$values[$key] = $this->getSetting($key);
		}

		return $values;
	}

	/**
	 * Persist one setting for the logged in user.
	 */
	public function setSetting($key, $value)
	{
		if (!isset($this->sets[$key]) || !empty($this->sets[$key]['disabled'])) {
			return false;
		}

		$id = $this->getSettingsId();

		if ($id === null) {
			return false;
		}

		$value = $this->castSetting($this->sets[$key], $value);

		if ($this->sets[$key]['type'] === 'bool') {
			$value = $value ? 1 : 0;
		}

		$this->setConfig($key, $value, $id);

		return true;
	}

	/**
	 * Coerce a stored/posted value to the setting's declared type.
	 */
	private function castSetting($set, $value)
	{
		if ($set['type'] === 'bool') {
			if (is_string($value)) {
				return !in_array(strtolower(trim($value)), ['', '0', 'false', 'no', 'off'], true);
			}

			return (bool) $value;
		}

		return $value;
	}

	/* ------------------------------------------------------------------ */
	/* Theme bootstrap                                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Inline <script> that publishes the user's settings to the page and
	 * applies the ones that have to be live before anything else loads.
	 *
	 * This has to be inline and synchronous: an external file or a
	 * DOMContentLoaded handler lets the light page paint first, which reads as
	 * a white flash on every navigation.
	 */
	public function getBootstrapScript()
	{
		$flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
		$settings = json_encode($this->getSettings(), $flags);
		$darkClass = json_encode(self::DARK_CLASS, $flags);

		$js = <<<JS
(function (d, w) {
	var settings = {$settings};
	var darkClass = {$darkClass};
	var root = d.documentElement;

	var api = w.OrykGui = w.OrykGui || {};
	api.settings = settings;

	/* The one place that knows what each setting actually does. Both the
	   bootstrap below and the live toggles on the settings page go through it,
	   so there is never a second copy to keep in sync. */
	api.apply = function (key, value) {
		settings[key] = value;

		/* Each effect is isolated: a setting whose target script is missing,
		   stale or cached must never take down the caller. This runs on the
		   settings page in the same handler that persists the change. */
		try {
			if (key === 'DARK_MODE' && root && root.classList) {
				root.classList.toggle(darkClass, !!value);
			}

			if (key === 'TOUCH_SCROLL') {
				var us = w.debiki && w.debiki.Utterscroll;

				if (us && typeof us.setEnabled === 'function') {
					us.setEnabled(!!value);
				}
			}
		} catch (e) {
			if (w.console && w.console.warn) {
				w.console.warn('OrykGui: could not apply ' + key, e);
			}
		}
	};

	/* Dark mode has to land before first paint. TOUCH_SCROLL is applied by
	   touch-scroll.js itself once jQuery and the DOM exist. */
	if (root && root.classList) {
		root.classList.toggle(darkClass, !!settings.DARK_MODE);
	}
})(document, window);
JS;

		return '<script type="text/javascript">' . $js . '</script>';
	}

	/* ------------------------------------------------------------------ */
	/* BMO                                                                 */
	/* ------------------------------------------------------------------ */

	//Install method. use this or install.php using both may cause weird behavior
	public function install()
	{
	}

	//Uninstall method. use this or install.php using both may cause weird behavior
	public function uninstall()
	{
	}

	//Not yet implemented
	public function backup()
	{
	}

	//not yet implimented
	public function restore($backup)
	{
	}

	/**
	 * Non-ajax form post from views/gui.php.
	 */
	public function doConfigPageInit($page)
	{
		if ($page !== 'oryk_gui') {
			return;
		}

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

		if ($action !== 'setkey') {
			return;
		}

		if ($this->getSettingsId() === null) {
			return;
		}

		foreach ($this->sets as $key => $set) {
			if (!empty($set['disabled']) || !isset($_REQUEST[$key])) {
				continue;
			}

			$this->setSetting($key, $_REQUEST[$key]);
		}
	}

	public function ajaxRequest($req, &$setting)
	{
		// Both commands are per-user and require a session.
		$setting['authenticate'] = true;
		$setting['allowremote'] = false;

		return in_array($req, ['get', 'set'], true);
	}

	public function ajaxHandler()
	{
		$command = isset($_REQUEST['command']) ? $_REQUEST['command'] : '';

		switch ($command) {
			case 'get':
				return [
					'status' => true,
					'settings' => $this->getSettings(),
				];

			case 'set':
				$key = isset($_REQUEST['key']) ? $_REQUEST['key'] : '';
				$value = isset($_REQUEST['value']) ? $_REQUEST['value'] : '';

				if (!isset($this->sets[$key])) {
					return ['status' => false, 'message' => 'Unknown setting'];
				}

				if (!empty($this->sets[$key]['disabled'])) {
					return ['status' => false, 'message' => 'Setting is disabled'];
				}

				if ($this->getSettingsId() === null) {
					return ['status' => false, 'message' => 'No user to save settings against'];
				}

				if (!$this->setSetting($key, $value)) {
					return ['status' => false, 'message' => 'Could not save setting'];
				}

				return [
					'status' => true,
					'key' => $key,
					'value' => $this->getSetting($key),
				];
		}

		return ['status' => false, 'message' => 'Unknown command'];
	}
}
