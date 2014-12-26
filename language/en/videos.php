<?php
/**
*
* @package phpBB Extension - RH Videos
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

$lang = array_merge($lang, array(
	'RH_VIDEOS'				=> 'Videos',
	'RH_VIDEO'				=> 'Video',
	'RH_VIDEO_URL'			=> 'Video-URL',
	'RH_VIDEO_URL_INVALID'	=> 'The video-url is not supported: %s',
));

