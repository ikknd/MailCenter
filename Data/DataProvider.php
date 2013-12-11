<?php
namespace MailCenter\Data;

class DataProvider
{
	/**
	 * Data name
	 * @var string
	 */
	protected $_name;

	/**
	 * Project path
	 * @var string
	 */
	protected $_path;

	/**
	 * Database connection
	 * @var \PDO
	 */
	protected $_db;

	/**
	 * Mailing options
	 * @var array
	 */
	protected $_options;

	/**
	 * @param string $name
	 * @param string $path
	 * @param \PDO $db
	 * @param array $options
	 */
	public function __construct($name, $path, $db, $options)
	{
		$this->_name = ucfirst($name);
		$this->_path = $path;
		$this->_db = $db;
		$this->_options = $options;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getData()
	{
		$className = ucfirst($this->_name) . 'Data';
		$fileName = $this->_path . '/Data/'. ucfirst($this->_name) . 'Data.php';

		if(file_exists($fileName)){
			require_once $fileName;
			$data = $className::getData($this->_db, $this->_options);
		} else {
			throw new \Exception('This data provider is not implemented: '. $className);
		}

		if(!$data){
			throw new \Exception('There is no data to send at this point');
		}

		return $data;
	}

}