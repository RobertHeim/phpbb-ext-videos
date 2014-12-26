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
use robertheim\videos\PREFIXES;
use robertheim\videos\PERMISSIONS;
use robertheim\videos\model\rh_oembed;

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
			'core.modify_submit_post_data' => 'modify_submit_post_data',
			'core.submit_post_end'							=> 'submit_post_end',
		);
	}

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
		if ($this->auth->acl_get(PERMISSIONS::POST_VIDEO))
		{
			$data = $event->get_data();
			$video_url = $this->get_video_url_from_post_request();
			// TODO this validation is expensive, because we discover the service, maybe we should
			// store more information to make embedding later more efficent
			if (!empty($video_url)) {
				$error = false;
				try
				{
					$video = new rh_oembed($video_url);
					if (empty($video->get_html()))
					{
						$error = true;
					}
				} 
				catch (\Exception $e)
				{
					// probably could not establish a http connection to the given url
					$error = true;
				}
				
				if ($error)
				{
					$this->user->add_lang_ext('robertheim/videos', 'videos');
					$data['error'][] = $this->user->lang('RH_VIDEO_URL_INVALID', $video_url);
					$event->set_data($data);
				}				
			}
		}
	}

	/**
	 * Event: core.modify_submit_post_data
	 * Assign the video to the topic
	 */
	public function modify_submit_post_data($event)
	{
		if ($this->auth->acl_get(PERMISSIONS::POST_VIDEO))
		{
			
			$video_url = $this->get_video_url_from_post_request();
			
			$data = $event->get_data();
			$data['data'][PREFIXES::CONFIG . '_url'] = $video_url;
			$event->set_data($data);
		}
	}
	

	/**
	 * Event: core.postingsubmit_post_end
	 *
	 * After a posting we set the video url of the topic
	 */
	public function submit_post_end($event)
	{
		if ($this->auth->acl_get(PERMISSIONS::POST_VIDEO))
		{
			$event_data = $event->get_data();
			$data = $event_data['data'];
			$topic_id = (int) $data['topic_id'];
			$video_url = $data[PREFIXES::CONFIG . '_url'];
			if(!empty($video_url))
			{
				$this->videos_manager->set_video_url_of_topic($topic_id, $video_url);
			}
		}
	}

	private function is_videos_enabled_in_forum($forum_id) {
		// TODO configure via ACP
		$video_forum_ids = array(1,2);
		return in_array($forum_id, $video_forum_ids);		
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
		if ($this->auth->acl_get(PERMISSIONS::POST_VIDEO))
		{
			$data = $event->get_data();
			$forum_id = $data['forum_id'];
			
			if (! $this->is_videos_enabled_in_forum($forum_id))
			{
				return;
			}
			
			$mode = $enable_trader = $topic_id = $post_id = $topic_first_post_id = false;
			
			if (! empty($data['mode']))
			{
				$mode = $data['mode'];
			}
			
			if ($mode == 'reply')
			{
				return;
			}
			
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
			
			$is_new_topic = $mode == 'post';
			$is_edit_first_post = $mode == 'edit' && $topic_id && $post_id &&
				 $post_id == $topic_first_post_id;
			if ($is_new_topic || $is_edit_first_post)
			{
				
				$data['page_data']['RH_VIDEOS_SHOW_FIELD'] = true;
				
				$video_url = '';
				//die("aa");
				// do we got some preview-data?
				if ($this->request->is_set_post('rh_video_url'))
				{
					// use data from post-request
					$video_url = $this->get_video_url_from_post_request();
				}
				else if ($is_edit_first_post)
				{
					// use data from db
					$video_url = $data['post_data'][PREFIXES::CONFIG . '_url'];
				}
				$data['page_data']['RH_VIDEO_URL'] = $video_url;
				$event->set_data($data);
			}
		}
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
		if ($this->is_videos_enabled_in_forum($forum_id))
		{
			$video_url = $data['topic_data'][PREFIXES::CONFIG . '_url'];
			if (! empty($video_url))
			{
				$video = new rh_oembed($video_url);
				$this->template->assign_vars(
					array(
						'S_RH_VIDEOS_SHOW' => true,
						'RH_VIDEOS_VIDEO_URL' => $video_url,
						'RH_VIDEOS_VIDEO_TITLE' => $video->get_title(),
						'RH_VIDEOS_VIDEO_HTML' => $video->get_html(),
					));
			}
		}
	}

}
