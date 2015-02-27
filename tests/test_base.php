<?php
/**
 *
 * @package phpBB Extension - RH Videos
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace robertheim\videos\tests;

use \robertheim\videos\service\videos_manager;

class test_base extends \phpbb_database_test_case
{
	/** @var \robertheim\videos\service\videos_manager */
	protected $videos_manager;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected function setUp()
	{
		parent::setUp();
		global $table_prefix;
		$this->auth = $this->getMock('\phpbb\auth\auth');
		$config = new \phpbb\config\config(array());
		$this->db = $this->get_db();
		$this->videos_manager = new videos_manager($this->db, $config, $auth, $table_prefix);
	}

}
