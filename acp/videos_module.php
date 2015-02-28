<?php
/**
*
* @package phpBB Extension - RH Videos
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\videos\acp;

/**
* @ignore
*/
use robertheim\videos\prefixes;

class videos_module
{

	/** @var string */
	public $u_action;

	/** @var \robertheim\videos\service\videos_manager */
	protected $videos_manager;

	public function __construct()
	{
		global $phpbb_container;
		$this->videos_manager = $phpbb_container->get('robertheim.videos.videos_manager');
	}

	/**
	 * Delegates to proper functions that handle the specific case
	 *
	 * @param string $id the id of the acp-module (the url-param "i")
	 * @param string $mode the phpbb acp-mode
	 */
	public function main($id, $mode)
	{
		global $user, $phpbb_container;

		$user->add_lang_ext('robertheim/videos', 'videos_acp');

		switch ($mode)
		{
			case 'settings':
			// no break
			default:
				$this->tpl_name = 'videos';
				$this->page_title = 'ACP_VIDEOS_SETTINGS';
				$this->handle_settings();
		}
	}

	/**
	 * Default settings page
	 */
	private function handle_settings()
	{
		global $config, $request, $template, $user;
		// Define the name of the form for use as a form key
		$form_name = 'videos';
		add_form_key($form_name);

		$errors = array();

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				trigger_error('FORM_INVALID');
			}

			$commit_to_db = true;
			$msg = array();

			if ($request->variable(prefixes::CONFIG . '_prune_forums', 0) > 0)
			{
				$deleted_videos_count = $this->videos_manager->delete_videos_from_videos_disabled_forums();
				$delete_unused_videos = true;
				$msg[] = $user->lang('VIDEOS_PRUNE_FORUMS_DONE', $deleted_videos_count);
			}

			if (empty($msg))
			{
				$msg[] = $user->lang('VIDEOS_SETTINGS_SAVED');
			}
			trigger_error(join('<br/>', $msg) . adm_back_link($this->u_action));
		}
		$template->assign_vars(array(
			'VIDEOS_VERSION'	=> $user->lang('R_VIDEOS_INSTALLED', $config[prefixes::CONFIG . '_version']),
			'S_ERROR'			=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $errors),
			'U_ACTION'			=> $this->u_action,
		));
	}
}
