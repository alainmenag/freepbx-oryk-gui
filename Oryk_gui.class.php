<?php

// Oryk_gui.class.php

namespace FreePBX\modules;
use BMO;
use PDO;
use FreePBX_Helpers;

class Oryk_gui extends FreePBX_Helpers implements \BMO
{
	public function __construct($freepbx = null)
	{
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
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
					//'userType' => $this->userType(),
				]);
			default:
				break;
		}
	}

	public function getUser() {

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
			return array_merge($user, [
				'admin' => true,
			]);
		}

		$sql = "SELECT * FROM userman_users WHERE username = :username";
		$stmt = $this->db->prepare($sql);
		$stmt->execute([':username' => $username]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($user) {
			return array_merge($user, [
				'admin' => false,
			]);
		}

		return $user;
	}

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

	//process form
	public function doConfigPageInit($page)
	{
	}

	public function ajaxRequest($req, &$setting)
	{
		return false;
	}

	public function ajaxHandler()
	{
		return false;
	}
}