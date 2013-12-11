<?php
namespace MailCenter\Data;

interface DataInterface
{
	/**
	 * @param $db
	 * @param array $options
	 * @return array
	 */
	public static function getData($db, $options);
}