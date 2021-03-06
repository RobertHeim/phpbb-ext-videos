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
use \robertheim\videos\model\rh_video;
use \robertheim\videos\tables;

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

	public function store_video(rh_video $video, $topic_id)
	{
		$topic_id = (int) $topic_id;

		$this->delete_video_from_topic($topic_id);

		$sql_ary = array(
			'topic_id'		=> $topic_id,
			'title'			=> $video->get_title(),
			'html'			=> $video->get_html(),
			'url'			=> $video->get_url(),
			'last_update'	=> $video->get_last_update(),
			'thumbnail_url'	=> $video->get_thumbnail_url(),
		);
		$sql = 'INSERT INTO ' . $this->table_prefix . tables::VIDEOS . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);

		$this->db->sql_query($sql);
	}

	/**
	 * If there is a video assigned to the topic, it is deleted.
	 *
	 * @param $topic_id the id of the topic
	 * @return integer count of deleted videos
	 */
	public function delete_video_from_topic($topic_id)
	{
		$topic_id = (int) $topic_id;
		$sql = 'DELETE
			FROM ' . $this->table_prefix . tables::VIDEOS . '
			WHERE topic_id = ' . $topic_id;
		$this->db->sql_query($sql);
		return $this->db->sql_affectedrows();
	}

	/**
	 * Get video for the given topic_id.
	 *
	 * @param int $topic_id
	 * @return rh_video the video or false if no video was found
	 */
	public function get_video_for_topic_id($topic_id)
	{
		$videos = $this->get_videos_for_topic_ids(array($topic_id));
		if (sizeof($videos) > 0)
		{
			return $videos[0]['video'];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get videos for the given topic_ids.
	 *
	 * @param array $topic_ids
	 * @return array e.g. [['topic_id'=> int, 'video' => object], ... ]
	 */
	public function get_videos_for_topic_ids(array $topic_ids)
	{
		if (is_null($topic_ids) || empty($topic_ids)) {
			return array();
		}
		$sql_array = array(
			'SELECT'	=> '*',
			'FROM'		=> array(
				$this->table_prefix . tables::VIDEOS	=> 'v',
			),
			'WHERE'	=> $this->db->sql_in_set('v.topic_id', $topic_ids),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$topic_to_video_map = array();

		while ($row = $this->db->sql_fetchrow($result)) {
			$video = null;
			// renew html if cachetime is running out otherwise use db data.
			$last_update = $row['last_update'];
			if ($last_update + self::CACHETIME < time())
			{
				$last_update = time();
				$video = rh_video::fromUrl($row['url']);
				$new_title = $new_html = $new_thumbnail_url = $error = false;
				if (null === $video)
				{
					$error = true;
					$video = new rh_video($row['title'], $row['url'], $row['html'], $row['thumbnail_url'], $last_update, $error);
				}
				else
				{
					$new_title = $video->get_title();
					$new_html = $video->get_html();
					$new_thumbnail_url = $video->get_thumbnail_url();
					$last_update = $video->get_last_update();
				}
				$this->update_video($row['id'], $new_title, $new_html, $new_thumbnail_url, $last_update, $error);
			}
			else
			{
				$video = new rh_video($row['title'], $row['url'], $row['html'], $row['thumbnail_url'], $row['last_update'], ((int) $row['error']) > 0);
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
	 * @param string $new_thumbnail_url
	 * @param int $last_update
	 * @param boolean $error
	 */
	private function update_video($video_id, $new_title, $new_html, $new_thumbnail_url, $last_update, $error)
	{
		$video_id = (int) $video_id;
		$sql_ary = array(
			'error'			=> $error,
			'last_update'	=> $last_update,
		);
		if (!$error)
		{
			$sql_ary = array_merge($sql_ary, array(
				'title'			=> $new_title,
				'html'			=> $new_html,
				'thumbnail_url'	=> $new_thumbnail_url,
			));
		}
		$sql = 'UPDATE ' . $this->table_prefix . tables::VIDEOS . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE id = ' . $video_id;
		$this->db->sql_query($sql);
	}


	/**
	 * Deletes all videos where the topic resides in a forum with tagging disabled.
	 *
	 * @param $forum_ids array of forum-ids that should be checked (if null, all are checked).
	 * @return integer count of deleted videos
	 */
	public function delete_videos_from_videos_disabled_forums($forum_ids = null)
	{
		$forums_sql_where = '';

		if (is_array($forum_ids))
		{
			if (empty($forum_ids))
			{
				// performance improvement because we already know the result of querying the db.
				return 0;
			}
			$forums_sql_where = ' AND ' . $this->db->sql_in_set('f.forum_id', $forum_ids);
		}

		// get ids of all videos that reside in a forum with tagging disabled.
		$sql = 'SELECT v.id
			FROM ' . $this->table_prefix . tables::VIDEOS . ' v
			WHERE EXISTS (
				SELECT 1
				FROM ' . TOPICS_TABLE . ' topics,
					' . FORUMS_TABLE . " f
						WHERE topics.topic_id = v.topic_id
						AND f.forum_id = topics.forum_id
						AND f.rh_videos_enabled = 0
						$forums_sql_where
						)";
		$result = $this->db->sql_query($sql);
		$delete_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$delete_ids[] = $row['id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($delete_ids))
		{
			// nothing to do
			return 0;
		}
		// delete these assignments
		$sql = 'DELETE FROM ' . $this->table_prefix . tables::VIDEOS . '
			WHERE ' . $this->db->sql_in_set('id', $delete_ids);
		$this->db->sql_query($sql);
		$removed_count = $this->db->sql_affectedrows();

		return $removed_count;
	}

	/**
	 * Checks if videos are enabled in the given forum.
	 *
	 * @param $forum_id the id of the forum
	 * @return true if videos are enabled in the given forum, false if not
	 */
	public function is_videos_enabled_in_forum($forum_id)
	{
		$field = 'rh_videos_enabled';
		$sql = "SELECT $field
		FROM " . FORUMS_TABLE . '
			WHERE ' . $this->db->sql_build_array('SELECT', array('forum_id' => (int) $forum_id));
		$result = $this->db->sql_query($sql);
		$enabled = ((int) $this->db->sql_fetchfield($field)) > 0;
		$this->db->sql_freeresult($result);
		return $enabled;
	}
}
