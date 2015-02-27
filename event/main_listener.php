<?php
/**
 *
 * @package phpBB Extension - RH Videos
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\videos\event;

/**
 *
 * @ignore
 *
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use robertheim\videos\prefixes;
use robertheim\videos\permissions;
use robertheim\videos\model\rh_video;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup' => 'load_language_on_setup',
			'core.modify_posting_parameters' => 'modify_posting_parameters',
			'core.posting_modify_template_vars' => 'posting_modify_template_vars',
			'core.viewtopic_assign_template_vars_before' => 'viewtopic_assign_template_vars_before',
			'core.submit_post_end'							=> 'submit_post_end',
		);
	}

	/**
	 * Used during a new video post to store video data, so we do not need to call oEmbed API twice.
	 *
	 * @var rh_video
	 */
	private $video = false;

	protected $config;

	protected $videos_manager;

	protected $helper;

	protected $request;

	protected $user;

	protected $template;

	protected $auth;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\config\config $config,
		\robertheim\videos\service\videos_manager $videos_manager,
		\phpbb\controller\helper $helper, \phpbb\request\request $request,
		\phpbb\user $user, \phpbb\template\template $template,
		\phpbb\auth\auth $auth)
	{
		$this->config = $config;
		$this->videos_manager = $videos_manager;
		$this->helper = $helper;
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;
		$this->auth = $auth;
	}

	/**
	 * Reads the request variable 'rh_video_url'.
	 *
	 * @return rh_video url
	 */
	private function get_video_url_from_post_request()
	{
		$video_url = $this->request->variable('rh_video_url', '');
		return $video_url;
	}

	/**
	 * Event: core.load_language_on_setup
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'robertheim/videos',
			'lang_set' => 'videos'
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Event: core.modify_posting_parameters
	 * Validate the url or create an error.
	 */
	public function modify_posting_parameters($event)
	{
		if ($this->auth->acl_get(permissions::POST_VIDEO))
		{
			$data = $event->get_data();
			$video_url = $this->get_video_url_from_post_request();
			// we do not enforce a video url to be present.
			if (!empty($video_url))
			{
				$video = rh_video::fromUrl($video_url);
				if (false === $video)
				{
					$this->user->add_lang_ext('robertheim/videos', 'videos');
					$video_link = "<a href=\"$video_url\">$video_url</a>";
					$data['error'][] = $this->user->lang('RH_VIDEO_URL_INVALID', $video_link);
					$event->set_data($data);
				}
				else
				{
					$this->video = $video;
				}
			}
		}
	}

	/**
	 * Event: core.postingsubmit_post_end
	 *
	 * After a posting we store the video and assign it to the topic
	 */
	public function submit_post_end($event)
	{
		if ($this->auth->acl_get(permissions::POST_VIDEO))
		{
			$event_data = $event->get_data();
			$topic_id = (int) $event_data['data']['topic_id'];
			if(false !== $this->video)
			{
				$this->videos_manager->store_video($this->video, $topic_id);
			}
		}
	}

	private function is_videos_enabled_in_forum($forum_id)
	{
		// TODO configure via ACP
		$video_forum_ids = array(1,2);
		return in_array($forum_id, $video_forum_ids);
	}

	/**
	 * helper
	 * @return boolean
	 */
	private function is_new_topic($data)
	{

		$mode = $data['mode'];
		return $mode == 'post';
	}

	/**
	 * helper
	 * @return boolean
	 */
	private function is_edit_first_post($data)
	{
		$mode = $data['mode'];
		if ($mode == 'edit')
		{
			$topic_id = $post_id = $topic_first_post_id = false;
			if (! empty($data['post_data']['topic_id']))
			{
				$topic_id = $data['post_data']['topic_id'];
			}

			if (! empty($data['post_data']['post_id']))
			{
				$post_id = $data['post_data']['post_id'];
			}

			if (! empty($data['post_data']['topic_first_post_id']))
			{
				$topic_first_post_id = $data['post_data']['topic_first_post_id'];
			}
			$is_edit_first_post = $topic_id && $post_id && $post_id == $topic_first_post_id;
			if ($is_edit_first_post)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Event: core.posting_modify_template_vars
	 * Send the video_url on edits or preview to the template
	 *
	 * @param
	 *        	$event
	 */
	public function posting_modify_template_vars($event)
	{
		$data = $event->get_data();

		$is_edit_first_post = $this->is_edit_first_post($data);
		$is_new_topic =  $this->is_new_topic($data);
		if (!($is_new_topic || $is_edit_first_post))
		{
			return;
		}
		if (!$this->auth->acl_get(permissions::POST_VIDEO))
		{
			return;
		}
		$forum_id = $data['forum_id'];
		if (! $this->is_videos_enabled_in_forum($forum_id))
		{
			return;
		}

		$video_url = '';
		// do we got some preview-data?
		if ($this->request->is_set_post('rh_video_url'))
		{
			// use data from post-request
			$video_url = $this->get_video_url_from_post_request();
		}
		else if ($is_edit_first_post)
		{
			// use data from db
			$topic_id = (int) $data['topic_id'];
			$video = $this->videos_manager->get_video_for_topic_id($topic_id);
			$video_url = '';
			if (false !== $video)
			{
				$video_url = $video->get_url();
			}
		}
		$data['page_data']['RH_VIDEOS_SHOW_FIELD'] = true;
		$data['page_data']['RH_VIDEO_URL'] = $video_url;
		$event->set_data($data);
	}

	/**
	 * Event: core.viewtopic_assign_template_vars_before
	 * assign video_url to topic-template
	 *
	 * @param
	 *        	$event
	 */
	public function viewtopic_assign_template_vars_before($event)
	{
		$data = $event->get_data();
		$forum_id = (int) $data['forum_id'];
		if (!$this->is_videos_enabled_in_forum($forum_id))
		{
			return;
		}
		$topic_id = (int) $data['topic_data']['topic_id'];
		$video = $this->videos_manager->get_video_for_topic_id($topic_id);
		if (false === $video)
		{
			return;
		}
		if ($video->has_error()) {

			$video_url = $video->get_url();
			$video_link = "<a href=\"$video_url\">$video_url</a>";
			$error_msg = $this->user->lang('RH_VIDEOS_VIDEO_COULD_NOT_BE_LOADED', $video_link);
			$this->template->assign_vars(
				array(
					'S_RH_VIDEOS_INCLUDE_CSS' => true,
					'S_RH_VIDEOS_SHOW' => true,
					'S_RH_VIDEOS_ERROR' => true,
					'RH_VIDEOS_VIDEO_URL' => $video->get_url(),
					'RH_VIDEOS_ERROR_MSG' => $error_msg,
				));
		}
		else
		{
			$this->template->assign_vars(
				array(
					'S_RH_VIDEOS_INCLUDE_CSS' => true,
					'S_RH_VIDEOS_SHOW' => true,
					'RH_VIDEOS_VIDEO_URL' => $video->get_url(),
					'RH_VIDEOS_VIDEO_TITLE' => $video->get_title(),
					'RH_VIDEOS_VIDEO_HTML' => $video->get_html(),
				));
		}
	}
}
