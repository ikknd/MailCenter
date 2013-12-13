<?php
namespace MailCenter\Mailing;

use MailCenter\Data\DataProvider;
use MailCenter\Template\TemplateProvider;
use MailCenter\Users\UserProvider;
use MailCenter\Sender\SenderProvider;
use MailCenter\lib\Registry;

abstract class MailingAbstract
{
	/**
	 * Mailing options
	 * @var array
	 */
	protected $_options;

	/**
	 * Mailing emails
	 * @var array
	 */
	protected $_emails;

	/**
	 * Mailing config
	 * @var \stdClass
	 */
	protected $_config;

	/**
	 * Database connection
	 * @var \PDO
	 */
	protected $_db;

	/**
	 * Mailing name
	 * @var string
	 */
	protected $_name;

	/**
	 * Project path
	 * @var string
	 */
	protected $_path;

	/**
	 * @param array $options
	 * @param array $emails
	 */
	public function __construct($options, $emails)
	{
		$this->_options = $options;
		$this->_emails = $emails;
		$this->_config = $this->getConfig();
		$this->_db = Registry::getInstance()->get('db');
		$this->_name = Registry::getInstance()->get('name');
		$this->_path = Registry::getInstance()->get('path');
	}

	/**
	 * Executes all steps to get data, fill template, get user email and send emails
	 */
	public function run()
	{
		$data = $this->_getData();
		$template = $this->_getTemplate($data);
		$users = $this->_getUsers();

		$sender = $this->_getSender();
		$sender->send($users, $this->_config->subject, $template);
	}

	/**
	 * @return array
	 */
	protected function _getData()
	{
		$dataProvider = new DataProvider($this->_name, $this->_path, $this->_db, $this->_options);
		return $dataProvider->getData();
	}

	/**
	 * @param array $data
	 * @return string - HTML template with data
	 */
	protected function _getTemplate($data)
	{
		$templateProvider = new TemplateProvider($this->_name, $this->_path, $this->_config->storage, $this->_config->engine, $data);
		return $templateProvider->getTemplate();
	}

	/**
	 * @return array
	 */
	protected function _getUsers()
	{
		$userProvider = new UserProvider($this->_name, $this->_emails, $this->_db);
		return $userProvider->getUsers();
	}

	/**
	 * @return \MailCenter\Sender\SenderInterface
	 */
	protected function _getSender()
	{
		$senderProvider = new SenderProvider($this->_config->sender);
		return $senderProvider->getSender();
	}

	/**
	 * @return \stdClass
	 */
	abstract function getConfig();
}