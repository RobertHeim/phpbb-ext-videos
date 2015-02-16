<?php

/**
 *
 * @package phpBB Extension - RH Videos
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\videos\model;

use robertheim\videos\lib\Alb\OEmbed\Simple;

/**
 * Holds relevant information about a video
 */
class rh_video
{

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $html;

	/**
	 * @var string
	 */
	private $thumbnail_url;

	/**
	 * indicates when the html was updated the last time.
	 *
	 * int
	 */
	private $last_update;

	/**
	 * indicates if there is an error with the video, e.g.
	 * used when the cached html is updated, and the update fails.
	 *
	 * @var boolean
	 */
	private $error;

	/**
	 * Creates a new instance from the given url using OEmbed.
	 *
	 * @param string $url
	 *        	the url to fetch.
	 * @return the new instance or false if an error occured.
	 */
	public static function fromUrl($url)
	{
		if (empty($url))
		{
			return false;
		}
		try
		{
			$response = Simple::request($url);
			if (null == $response)
			{
				return false;
			}
			$title = $response->getTitle();
			if (empty($title))
			{
				return false;
			}
			$html = $response->getHtml();
			if (empty($html))
			{
				return false;
			}
			return new rh_video($title, $url, $html, $response->getThumbnailUrl(), time(), false);
		}
		catch (\Exception $e)
		{
			// probably could not establish a http connection to the given url
			return false;
		}
	}

	/**
	 * Constructor
	 *
	 * @param string $title
	 * @param string $url
	 * @param string $html
	 * @param string $thumbnail_url
	 * @param int $last_update
	 * @param string $has_error
	 */
	public function __construct($title, $url, $html, $thumbnail_url, $last_update, $has_error)
	{
		$this->title = $title;
		$this->url = $url;
		$this->html = $html;
		$this->thumbnail_url = $thumbnail_url;
		$this->last_update = $last_update;
		$this->error = $has_error;
	}

	function get_title()
	{
		return $this->title;
	}

	public function get_url()
	{
		return $this->url;
	}

	public function get_html()
	{
		return $this->html;
	}

	public function get_thumbnail_url()
	{
		return $this->thumbnail_url;
	}

	public function get_last_update()
	{
		return $this->last_update;
	}

	public function has_error()
	{
		return $this->error;
	}

}
