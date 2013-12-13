<?php
namespace MailCenter\Sender;


interface SenderInterface
{
	/**
	 * @param array $users
	 * @param string $subject
	 * @param string $template
	 * @return void
	 */
	public function send($users, $subject, $template);
}