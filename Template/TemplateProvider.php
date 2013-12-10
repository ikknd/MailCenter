<?php
namespace MailCenter\Template;
use MailCenter\Template\Storage;
use MailCenter\Template\Engine;

class TemplateProvider
{
	const STORAGE_TYPE_FILE = 'file';
	const STORAGE_TYPE_DB = 'db';

	const ENGINE_TYPE_PHP = 'php';

	/**
	 * Template name
	 * @var string
	 */
	protected $_name;

	/**
	 * Project path
	 * @var string
	 */
	protected $_path;

	/**
	 * Template storage
	 * @var string
	 */
	protected $_storage;

	/**
	 * Template engine
	 * @var string
	 */
	protected $_engine;

	/**
	 * Template data
	 * @var array
	 */
	protected $_data;

	/**
	 * @param string $name
	 * @param string $storage
	 * @param string $engine
	 * @param array $data
	 */
	public function __construct($name, $path, $storage, $engine, $data)
	{
		$this->_name = $name;
		$this->_path = $path;
		$this->_storage = $storage;
		$this->_engine = $engine;
		$this->_data = $data;
	}

	/**
	 * @return string - HTML template with data
	 */
	public function getTemplate()
	{
		$storage = $this->_getStorage();
		$templatePath = $storage->getTemplatePath($this->_name, $this->_path);

		$engine = $this->_getEngine();
		$engine->setOptions(array(
			'templatePath'=>$templatePath,
			'data'=>array('data'=>$this->_data)
		));
		$templateFilled = $engine->render();

		if(!$templateFilled){
			throw new \Exception('Template was not found or could not be rendered');
		}

		return $templateFilled;
	}

	/**
	 * @return Storage\StorageInterface
	 * @throws \Exception
	 */
	protected function _getStorage()
	{
		switch($this->_storage){
			case self::STORAGE_TYPE_FILE:
				$storage = new Storage\StorageFile();
				break;
			case self::STORAGE_TYPE_DB:
				break;
			default:
				throw new \Exception('This storage type is not implemented');
		}

		return $storage;
	}

	/**
	 * @return Engine\EngineInterface
	 * @throws \Exception
	 */
	protected function _getEngine()
	{
		switch($this->_engine){
			case self::ENGINE_TYPE_PHP:
				$engine = new Engine\EnginePhp();
				break;
			default:
				throw new \Exception('This engine type is not implemented');
		}

		return $engine;
	}

}