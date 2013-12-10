<?php
namespace MailCenter\Sender;

class SenderProvider
{
	const TYPE_SENDMAIL = 'sendmail';
	const TYPE_MANDRILL = 'mandrill';

	/**
	 * Sender name
	 * @var string
	 */
	protected $_name;

	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->_name = $name;
	}

	/**
	 * @return SenderInterface
	 * @throws \Exception
	 */
	public function getSender()
	{
		switch($this->_name){
			case self::TYPE_SENDMAIL:
				$sender = new SenderSendmail();
				break;
			case self::TYPE_MANDRILL:
				$sender = new SenderMandrill();
				break;
			default:
				throw new \Exception('This sender type is not implemented: ' . $this->_name);
		}

		return $sender;
	}
}