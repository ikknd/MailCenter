<?php
namespace MailCenter\Sender;

class SenderSendmail implements SenderInterface
{
	/**
	 * @param array $users
	 * @param string $subject
	 * @param string $template
	 */
	public function send($users, $subject, $template)
	{
		$headers = "From: " . 'Apppicker' . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		foreach($users as $user){
			$this->_sendMail($user['email'], $subject, $template, $headers);
		}
	}

	/**
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string $headers
	 */
	protected function _sendMail($to, $subject, $message, $headers)
	{
		$success = mail($to, $subject, $message, $headers);

		if(!$success){
			throw new \Exception('Email was not sent: ' . $to . ' / ' . $subject);
		}
	}
}