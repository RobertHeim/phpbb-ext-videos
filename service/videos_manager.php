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
use robertheim\videos\model\rh_video;
use robertheim\videos\PREFIXES;
use robertheim\videos\TABLES;

/**
* Handles operations with videos.
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

	public function store_video(rh_video $video, $topic_id)
	{
		$topic_id = (int) $topic_id;
		
		$sql_ary = array(
			'topic_id'	=> $topic_id,
			'title'		=> $video->get_title(),
			'html'		=> $video->get_html(),
			'url'		=> $video->get_url(),
		);
		$sql = 'INSERT INTO ' . $this->table_prefix . TABLES::VIDEOS . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		
		$this->db->sql_query($sql);
	}
	
	/**
	 * Get video for the given topic_id.
	 *
	 * @param int $topic_id
	 * @return the video or false
	 */
	public function get_video_for_topic_id($topic_id)
	{
		$videos = $this->get_video_for_topic_ids(array($topic_id));
		if (sizeof($videos)>0)
		{
			return $videos[0]['video'];
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Get videos for the given topic_ids.
	 * 
	 * @param array $topic_ids
	 * @return array e.g. [['topic_id'=> int, 'video' => object], ... ]
	 */
	public function get_video_for_topic_ids(array $topic_ids)
	{
		$sql_array = array(
			'SELECT'	=> '*',
			'FROM'		=> array(
				$this->table_prefix . TABLES::VIDEOS	=> 'v',
			),
			'WHERE'	=> $this->db->sql_in_set('v.topic_id', $topic_ids),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		
		$topic_to_video_map = array();
		
		while ($row = $this->db->sql_fetchrow($result)) {
			$video = new rh_video($row['title'], $row['url'], $row['html']);
			// TODO renew html after a time
			// if ($video->get_last_html()+cachetime<time()){
			// update_video_data($video)
			// if error then set error_flag of video = true
			// }
			$topic_to_video_map[] = array(
				'topic_id' => $row['topic_id'],
				'video' => $video,
			);
		}
		$this->db->sql_freeresult($result);		
		return $topic_to_video_map;		
	}
}

