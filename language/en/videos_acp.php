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
	// config
	'RH_VIDEOS_INSTALLED'					=> 'Installed Version: v%s',
));
