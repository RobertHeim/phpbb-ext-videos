<?php
/**
*
* @package phpBB Extension - RH Videos
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\videos\tests\service;

use \robertheim\videos\model\rh_video;

class videos_manager_test extends \robertheim\videos\tests\test_base
{

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/videos.xml');
	}

	protected static function setup_extensions()
	{
		return array('robertheim/videos');
	}

	public function test_delete_video_from_topic()
	{
		$topic_id = 1;

		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNotEquals(null, $video);
		$this->assertEquals('title', $video->get_title());

		$affected = $this->videos_manager->delete_video_from_topic($topic_id);
		$this->assertEquals(1, $affected);

		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertEquals(null, $video);
	}

	public function test_delete_videos_from_videos_disabled_forums()
	{
		$topic_ids = array(1, 2, 3);
		$topics = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(3, sizeof($topics));
		$this->videos_manager->delete_videos_from_videos_disabled_forums();
		$topics = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(2, sizeof($topics));
	}

	public function test_get_video_for_topic_id()
	{
		$topic_id = 1;
		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNotEquals(null, $video);
		$this->assertEquals('title', $video->get_title());

		$topic_id = -1;
		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertEquals(null, $video);

		$topic_id = 999;
		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertEquals(null, $video);
	}

	public function test_get_video_for_topic_ids()
	{
		$topic_ids = array(1);
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(1, sizeof($video));
		$this->assertEquals('title', $videos[0]->get_title());

		$topic_ids = array(1, 2);
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(2, sizeof($video));
		$this->assertEquals('title', $videos[0]->get_title());
		$this->assertEquals('title2', $videos[1]->get_title());

		$topic_ids = array(2, 3);
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(1, sizeof($video));
		$this->assertEquals('title2', $videos[0]->get_title());

		$topic_ids = array(3);
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(0, sizeof($video));

		$topic_ids = array();
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(0, sizeof($video));

		$topic_ids = null;
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(0, sizeof($video));
	}

	public function test_is_videos_enabled_in_forum()
	{
		$forum_id = 1;
		$enabled = $this->videos_manager->is_videos_enabled_in_forum($forum_id);
		$this->assertTrue($enabled);

		$forum_id = 2;
		$enabled = $this->videos_manager->is_videos_enabled_in_forum($forum_id);
		$this->assertFalse($enabled);
	}

	public function test_set_video_url_of_topic()
	{
		$topic_id = 1;
		$video_url = 'new_video_url';
		$this->videos_manager->set_video_url_of_topic($topic_id, $video_url);
		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNotEquals(null, $video);
		$this->assertEquals($video_url, $video->get_url());
	}

	public function test_store_video()
	{
		$topic_id = 1;
		$video_url = 'https://www.youtube.com/watch?v=9bZkp7q19f0';
		$video = rh_video::fromUrl($video_url);
		$this->videos_manager->store_video($video, $topic_id);
		$video2 = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNotEquals(null, $video);
		$this->assertEquals($video->get_html(), $video2->get_html());
		$this->assertEquals($video->get_last_update(), $video2->get_last_update());
		$this->assertEquals($video->get_thumbnail_url(), $video2->get_thumbnail_url());
		$this->assertEquals($video->get_title(), $video2->get_title());
		$this->assertEquals($video_url, $video2->get_url());
		$this->assertEquals($video->has_error(), $video2->has_error());
	}
}
