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
	'R_VIDEOS_INSTALLED'					=> 'Installed Version: v%s',

	// ext settings page
	'VIDEOS_MAINTENANCE'			=> 'Maintenance',
	'VIDEOS_PRUNE_FORUMS'			=> 'Prune tags from forums with RH Videos disabled',
	'VIDEOS_PRUNE_FORUMS_EXP'		=> 'This will DELETE all videos of topics where the topic resides in a forum with RH Videos disabled.',
	'VIDEOS_PRUNE_FORUMS_CONFIRM'	=> 'This will DELETE all videos of topics where the topic resides in a forum with RH Videos disabled.',
	'VIDEOS_PRUNE_FORUMS_DONE'			=> array(
		0 => 'There were no topics in RH Videos disabled forums, that had a video.',
		1 => '%d video has been deleted.',
		2 => '%d videos have been deleted.',
	),
	'VIDEOS_SETTINGS_SAVED'			=> 'Configuration updated successfully.',

	// forum settings page
	'ACP_RH_VIDEOS_ENABLE'								=> 'Enable RH Videos',
	'ACP_RH_VIDEOS_ENABLE_EXP'							=> 'Whether or not to enable RH Videos in this forum. (When disabled, the videos are NOT REMOVED from the topics in this forum - so when you enable it again, they are still there; If you really want to delete them, then use the "Delete RH Videos from this forum" option.)',
	'ACP_FORUM_SETTINGS_RH_VIDEOS_PRUNE'				=> 'Delete RH Videos from this forum',
	'ACP_FORUM_SETTINGS_RH_VIDEOS_PRUNE_EXP'			=> 'This will DELETE all RH Videos of the topics in this forum. NOTE: To prevent accidental deletion, you need to disabled RH Videos for this forum.',
	'ACP_FORUM_SETTINGS_RH_VIDEOS_PRUNE_CONFIRM'		=> 'This option will DELETE all RH Videos of the topics in this forum and you need to disable RH Videos for this forum, to perform this action.',
	'ACP_RH_VIDEOS_PRUNING_REQUIRES_VIDEOS_DISABLED'	=> 'To prevent accidental deletion, you need to disable RH Videos for this forum to delete the videos.',
));
