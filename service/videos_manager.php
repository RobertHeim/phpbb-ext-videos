<?php
/**
*
* @package phpBB Extension - RH Videos
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\videos\service;

/**
 * @ignore
 */
use robertheim\videos\PREFIXES;

/**
* TODO
*/
class videos_manager
{

	private $db;
	private $config;
	private $auth;
	private $table_prefix;

	public function __construct(
					\phpbb\db\driver\driver_interface $db,
					\phpbb\config\config $config,
					\phpbb\auth\auth $auth,
					$table_prefix)
	{
		$this->db			= $db;
		$this->config		= $config;
		$this->auth			= $auth;
		$this->table_prefix	= $table_prefix;
	}
	
	public function set_video_url_of_topic($topic_id, $video_url)
	{
		$topic_id = (int) $topic_id;
		$sql = 'UPDATE '. TOPICS_TABLE . ' SET ' . PREFIXES::CONFIG . '_url' . '="'.$this->db->sql_escape($video_url).'"
				WHERE topic_id='.$topic_id;
		$this->db->sql_query($sql);		
	}
}

