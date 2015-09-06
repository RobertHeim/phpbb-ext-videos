<?php
/**
 *
 * @package phpBB Extension - RH Videos
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\videos\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class acp_listener implements EventSubscriberInterface
{

	public static function getSubscribedEvents()
	{
		return array(
			'core.acp_manage_forums_initialise_data'   => 'acp_manage_forums_initialise_data',
			'core.acp_manage_forums_display_form'      => 'acp_manage_forums_display_form',
			'core.acp_manage_forums_validate_data'     => 'acp_manage_forums_validate_data',
			'core.acp_manage_forums_update_data_after' => 'acp_manage_forums_update_data_after',
		);
	}

	private $request;

	private $user;

	private $videos_manager;

	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\request\request_interface $request,
		\phpbb\user $user,
		\robertheim\videos\service\videos_manager $videos_manager
	)
	{
		$this->request = $request;
		$this->user = $user;
		$this->videos_manager = $videos_manager;
	}

	public function acp_manage_forums_initialise_data($event)
	{
		$this->user->add_lang_ext('robertheim/videos', 'videos_acp');
	}

	public function acp_manage_forums_display_form($event)
	{
		$data = $event->get_data();

		$data['template_data']['S_RH_VIDEOS_ENABLED'] = $data['forum_data']['rh_videos_enabled'];
		$prune = $this->request->variable('rh_videos_prune', 0);
		$data['template_data']['S_RH_VIDEOS_PRUNE'] = $prune;

		$event->set_data($data);
	}

	public function acp_manage_forums_validate_data($event)
	{
		$data = $event->get_data();

		$status = $this->request->variable('rh_videos_enabled', 0);
		// ensure 0 or 1
		$status = ($status ? 1 : 0);
		$data['forum_data']['rh_videos_enabled'] = $status;

		// pruning requires the videos to be disabled for this forum to prevent accidental deletion
		$prune = $this->request->variable('rh_videos_prune', 0);
		if ($prune && $status)
		{
			$this->user->add_lang_ext('robertheim/videos', 'videos_acp');
			$data['errors'][] = $this->user->lang('ACP_RH_VIDEOS_PRUNING_REQUIRES_VIDEOS_DISABLED');
		}

		$event->set_data($data);
	}

	public function acp_manage_forums_update_data_after($event)
	{

		$status = $this->request->variable('rh_videos_enabled', 0);
		$prune = $this->request->variable('rh_videos_prune', 0);
		if (!$status && $prune)
		{
			$data = $event->get_data();
			$forum_id = (int) $data['forum_data']['forum_id'];
			$this->videos_manager->delete_videos_from_videos_disabled_forums(array($forum_id));
		}
	}
}
