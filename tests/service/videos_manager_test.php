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
		$this->assertNotNull($video);
		$this->assertEquals('title', $video->get_title());

		$affected = $this->videos_manager->delete_video_from_topic($topic_id);
		$this->assertEquals(1, $affected);

		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNull($video);
	}

	public function test_get_video_for_topic_id()
	{
		$topic_id = 1;
		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNotNull($video);
		$this->assertEquals('title', $video->get_title());

		$topic_id = -1;
		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNull($video);

		$topic_id = 999;
		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNull($video);
	}

	public function test_get_video_for_topic_ids()
	{
		$topic_ids = array(1);
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(1, sizeof($videos));
		$this->assertEquals('title', $videos[0]['video']->get_title());

		$topic_ids = array(1, 2);
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(2, sizeof($videos));
		$this->assertEquals('title', $videos[0]['video']->get_title());
		$this->assertEquals('title2', $videos[1]['video']->get_title());

		$topic_ids = array(2, 3);
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(1, sizeof($videos));
		$this->assertEquals('title2', $videos[0]['video']->get_title());

		$topic_ids = array(3);
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(0, sizeof($videos));

		$topic_ids = array();
		$videos = $this->videos_manager->get_videos_for_topic_ids($topic_ids);
		$this->assertEquals(0, sizeof($videos));
	}

	public function test_store_video()
	{
		$topic_id = 1;
		$video_url = 'https://www.youtube.com/watch?v=9bZkp7q19f0';
		$video = rh_video::fromUrl($video_url);
		$this->videos_manager->store_video($video, $topic_id);
		$video2 = $this->videos_manager->get_video_for_topic_id($topic_id);
		$this->assertNotNull($video);
		$this->assertEquals($video->get_html(), $video2->get_html());
		$this->assertEquals($video->get_last_update(), $video2->get_last_update());
		$this->assertEquals($video->get_thumbnail_url(), $video2->get_thumbnail_url());
		$this->assertEquals($video->get_title(), $video2->get_title());
		$this->assertEquals($video_url, $video2->get_url());
		$this->assertEquals($video->has_error(), $video2->has_error());
	}
}
