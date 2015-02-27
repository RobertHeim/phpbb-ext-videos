<?php
/**
*
* @package phpBB Extension - RH Videos
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\videos\migrations;

use \robertheim\videos\permissions;
use \robertheim\videos\prefixes;
use \robertheim\videos\tables;

class release_0_0_1 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.1-DEV';

	protected $config_prefix = prefixes::CONFIG;

	public function effectively_installed()
	{
		$installed_version = $this->config[$this->config_prefix.'_version'];
		return isset($installed_version) && version_compare($installed_version, $this->version, '>=');
	}

	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . tables::VIDEOS	=> array(
					'COLUMNS'		=> array(
						'id'			=> array('UINT', null, 'auto_increment'),
						'topic_id'		=> array('UINT', 0),
						'title'			=> array('VCHAR:255', ''),
						'html'			=> array('TEXT', ''),
						'url'			=> array('VCHAR:511', ''),
						'thumbnail_url'	=> array('VCHAR:511', ''),
						'last_update'	=> array('TIMESTAMP', 0),
						'error'			=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> array(
						'idx_topic'		=> array('UNIQUE', 'topic_id'),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'    => array(
				$this->table_prefix . tables::VIDEOS,
			),
		);
	}

	public function update_data()
	{
		$re = array();
		// add permissions
		$re[] = array('permission.add', array(permissions::POST_VIDEO));

		// Set permissions for the board roles
		if ($this->role_exists('ROLE_USER_FULL')) {
			$re[] = array('permission.permission_set', array('ROLE_USER_FULL', permissions::POST_VIDEO));
		}
		if ($this->role_exists('ROLE_USER_STANDARD')) {
			$re[] = array('permission.permission_set', array('ROLE_USER_STANDARD', permissions::POST_VIDEO));
		}
		$re[] = array('config.add', array($this->config_prefix.'_version', $this->version));
		return $re;
	}

	/**
	 * Checks whether the given role does exist or not.
	 *
	 * @param String $role the name of the role
	 * @return true if the role exists, false otherwise.
	 */
	protected function role_exists($role)
	{
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . '
			WHERE ' . $this->db->sql_in_set('role_name', $role);
		$result = $this->db->sql_query_limit($sql, 1);
		$role_id = $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);
		return $role_id > 0;
	}
}
