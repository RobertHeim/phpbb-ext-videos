<?php
/**
*
* @package phpBB Extension - RH Videos
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\vidos\tests\service;

class videos_manager_test extends \phpbb_database_test_case
{

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/videos.xml');
	}

	static protected function setup_extensions()
	{
		return array('robertheim/videos');
	}

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function test_something()
	{
		$this->db = $this->new_dbal();
		$manager = new \robertheim\videos\service\videos_manager($this->db, null, null, 'phpbb_');
		$this->assertEquals(1, 1);
	}
}
