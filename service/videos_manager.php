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

	/**
	 * After 31 days the html-data is updated by requesting the oEmbed API again.
	 * @var int
	 */
	const CACHETIME = 2678400;

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
		if (sizeof($videos) > 0)
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
			$video = false;
			// renew html if cachetime is running out otherwise use db data.
			$last_update = $row['last_update'];
			if ($last_update + self::CACHETIME < time())
			{
				$last_update = time();
				$video = rh_video::fromUrl($row['url']);
				$new_title = $new_html = $error = false;
				if (false === $video)
				{
					$error = true;
					$video = new rh_video($row['title'], $row['url'], $row['html'], $last_update, $error);
				}
				else
				{
					$new_html = $video->get_html();
					$new_title = $video->get_title();
					$last_update = $video->get_last_update();
				}
				$this->update_video($row['id'], $new_title, $new_html, $last_update, $error);
			}
			else
			{
				$video = new rh_video($row['title'], $row['url'], $row['html'], $row['last_update'], $row['error']);
			}
			$topic_to_video_map[] = array(
				'topic_id' => $row['topic_id'],
				'video' => $video,
			);
		}
		$this->db->sql_freeresult($result);		
		return $topic_to_video_map;		
	}
	
	/**
	 * Updates the videos db entry. If error is true only the error and last_update field are changed.
	 * 
	 * @param int $video_id
	 * @param string $new_title
	 * @param string $new_html
	 * @param int $last_update
	 * @param boolean $error
	 */
	private function update_video($video_id, $new_title, $new_html, $last_update, $error)
	{
		$video_id = (int) $video_id;
		$sql_ary = array(
			'error'			=> $error,
			'last_update'	=> $last_update,
		);
		if (!$error)
		{
			$sql_ary = array_merge($sql_ary, array(
				'title'	=> $new_title,
				'html'	=> $new_html,
			));
		}
		$sql = 'UPDATE ' . $this->table_prefix . TABLES::VIDEOS . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE id = ' . $video_id;
		$this->db->sql_query($sql);
	}
}

