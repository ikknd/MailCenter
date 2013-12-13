<?php
namespace MailCenter\lib;


class Registry
{
	private static $instance = null;

	private $storage = array();

	private function __construct(){}

	private function __clone(){}

	public static function getInstance()
	{
		if( self::$instance === null ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function attach($name, $o)
	{
		if( !empty( $name ) && is_object( $o ) ) {
			$this->storage[ $name ] = $o;
		}
	}

	public function detach($name)
	{
		if( isset( $this->storage[ $name ] ) ) {
			$this->storage[ $name ] = null;
			unset( $this->storage[ $name ] );
		}
	}

	public function get($name)
	{
		return (isset( $this->storage[ $name ] ) ? $this->storage[ $name ]() : false);
	}
} 