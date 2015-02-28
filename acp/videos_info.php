<?php
/**
*
* @package phpBB Extension - RH Videos
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\videos\acp;

class videos_info
{
	public function module()
	{
		return array(
			'filename'	=> '\robertheim\videos\acp\videos_module',
			'title'		=> 'ACP_VIDEOS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_VIDEOS_SETTINGS',
					'auth' => 'ext_robertheim/videos && acl_a_board',
					'cat' => array('ACP_VIDEOS_TITLE')
				),
			),
		);
	}
}
