<?php
namespace MailCenter;

use MailCenter\lib\Registry;

/**
 *
 * @category   MailCenter
 * @package    MailCenter
 */
class Mailing
{
	/**
	 * Mailing config
	 * @var \stdClass
	 */
	private $_config;

	/**
	 * Mailing type
	 * @var string
	 */
	private $_type;

	/**
	 * Mailing options
	 * @var array
	 */
	private $_options;

	/**
	 * Mailing emails
	 * @var array
	 */
	private $_emails;
	
	/**
	 * Construct
	 *
	 * @param \stdClass $config
	 * @param string $type
	 * @param array $options
	 * @param array $emails
	 */
	public function __construct(\stdClass $config, $type, $options, $emails)
	{
		$this->_config = $config;
		$this->_type = strtolower($type);
		$this->_options = $options;
		$this->_emails = $emails;
		$this->_initRegistry();
	}

	/**
	 * Run mailing
	 */
	public function run()
	{
		$className = ucfirst($this->_type) . 'Mailing';
		$fileName = $this->_config->path . '/Mailing/'. ucfirst($this->_type) . 'Mailing.php';

		if(file_exists($fileName)){
			require_once $fileName;
			$mailing = new $className($this->_options, $this->_emails);
			$mailing->run();
		} else {
			throw new \Exception('This mailing type is not implemented: '. $this->_type);
		}
	}

	/**
	 * Init Registry
	 */
	private function _initRegistry()
	{
		$db = function(){
			return new \PDO("mysql:host={$this->_config->host};port={$this->_config->port};dbname={$this->_config->dbname}", $this->_config->username, $this->_config->password);
		};
		Registry::getInstance()->attach('db', $db);

		$name = function(){
			return $this->_type;
		};
		Registry::getInstance()->attach('name', $name);

		$config = function(){
			return 	$this->_config;
		};
		Registry::getInstance()->attach('config', $config);
	}
}