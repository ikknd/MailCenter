<?php
namespace MailCenter\Users;

class UserProvider
{
	/**
	 * Mailing name
	 * @var string
	 */
	protected $_name;

	/**
	 * Mailing emails
	 * @var array
	 */
	protected $_emails;

	/**
	 * Database connection
	 * @var \PDO
	 */
	protected $_db;

	/**
	 * @param string $name
	 * @param array $emails
	 * @param $db \PDO
	 */
	public function __construct($name, $emails, $db)
	{
		$this->_name = $name;
		$this->_emails = $emails;
		$this->_db = $db;
	}

	/**
	 * @return array
	 */
	public function getUsers()
	{
		$users = array();

		if($this->_emails){
			foreach($this->_emails as $email){
				$users[]['email'] = $email;
			}
		} else {
			$users = $this->_getEmailsByMailingName();
		}

		if(!$users){
			throw new \Exception('There are no user emails for this mailing');
		}

		return $users;
	}

	/**
	 * @return array
	 */
	protected function _getEmailsByMailingName()
	{
		$sth = $this->_db->prepare('SELECT * FROM mc_users AS mu
										LEFT JOIN mc_mailing AS mm ON mu.mailing_id = mm.id
										WHERE mm.name = :name');
		$sth->setFetchMode(\PDO::FETCH_ASSOC);
		$sth->execute(array(':name' => $this->_name));
		$data = $sth->fetchAll();

		return $data;
	}
}