<?php
namespace MailCenter\Sender;

class SenderMandrill implements SenderInterface
{
	/**
	 * @param array $users
	 * @param string $subject
	 * @param string $template
	 */
	public function send($users, $subject, $template)
	{
		require_once dirname(__FILE__) . '/../lib/mandrill/Mandrill.php';
		$mandrill = new \Mandrill(\MailCenter\lib\Registry::getInstance()->get('config')->options['mandrillApiKey']);


		$userData = $this->_buildUserData($users);
		$message = $this->_buildMessage($subject, $template, $userData);

		$async = false;
		$ip_pool = false;
		$send_at = false;
		$result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
		//print_r($result);
	}

	/**
	 * @param array $users
	 * @return array
	 */
	protected function _buildUserData($users)
	{
		$userData = array();

		foreach($users as $user){
			$userData['to'][] = array(
				'email' => $user['email'],
				'name' => (isset($user['username'])) ? $user['username'] : $user['email'],
				'type' => 'to'
			);

			$userData['merge_vars'][] = array(
				'rcpt' => $user['email'],
				'vars' => array(
					array(
						'name'=>'USER',
						'content'=>(isset($user['username'])) ? $user['username'] : 'Member'
					),
					array(
						'name'=>'UNSUBSCRIBE',
						'content'=> \MailCenter\lib\Helper::unsubscribeLink(\MailCenter\lib\Registry::getInstance()->get('name') ,$user['email'])
					),
					array(
						'name'=>'SUBSCRIBE',
						'content'=> \MailCenter\lib\Helper::subscribeLink(\MailCenter\lib\Registry::getInstance()->get('name') ,$user['email'])
					)
				)
			);
		}

		return $userData;
	}

	/**
	 * @param string $subject
	 * @param string $template
	 * @param array $userData
	 * @return array
	 */
	protected function _buildMessage($subject, $template, $userData)
	{
		$message = array(
			'html' => $template,
			'subject' => $subject,
			'from_email' => 'mailings@apppicker.com',
			'from_name' => 'AppPicker',
			'to' => $userData['to'],
			'track_opens' => true,
			'track_clicks' => true,
			'auto_text' => true,
			'auto_html' => true,
			'merge' => true,
			'global_merge_vars' => array(
				array(
					'name' => 'USER',
					'content' => 'User'
				),
				array(
					'name'=>'UNSUBSCRIBE',
					'content'=> '#'
				)
			),
			'merge_vars' => $userData['merge_vars']
		);

		return $message;
	}
}