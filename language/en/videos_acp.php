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

	// forum settings page
	'ACP_RH_VIDEOS_ENABLE'								=> 'Enable RH Videos',
	'ACP_RH_VIDEOS_ENABLE_EXP'							=> 'Whether or not to enable RH Videos in this forum. (When disabled, the videos are NOT REMOVED from the topics in this forum - so when you enable it again, they are still there; If you really want to delete them, then use the "Delete RH Videos from this forum" option.)',
	'ACP_FORUM_SETTINGS_RH_VIDEOS_PRUNE'				=> 'Delete RH Videos from this forum',
	'ACP_FORUM_SETTINGS_RH_VIDEOS_PRUNE_EXP'			=> 'This will DELETE all RH Videos of the topics in this forum. NOTE: To prevent accidental deletion, you need to disabled RH Videos for this forum.',
	'ACP_FORUM_SETTINGS_RH_VIDEOS_PRUNE_CONFIRM'		=> 'This option will DELETE all RH Videos of the topics in this forum and you need to disable RH Videos for this forum, to perform this action.',
	'ACP_RH_VIDEOS_PRUNING_REQUIRES_VIDEOS_DISABLED'	=> 'To prevent accidental deletion, you need to disable RH Videos for this forum to delete the videos.',
));
