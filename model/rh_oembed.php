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
 * Wrapper for OEmbed library.
 */
class rh_oembed
{

	private $response;

	public function __construct($url)
	{
		$this->response = Simple::request($url, array(
			'maxwidth' => 400,
			'maxheight' => 300,
		));
	}

	public function get_html()
	{
		if (isset($this->response))
		{
			return $this->response->getHtml();
		}
		else
		{
			return '';
		}
	}

	public function get_title()
	{
		if (isset($this->response))
		{
			return $this->response->getTitle();
		}
		else
		{
			return '';
		}
	}

}