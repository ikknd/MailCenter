<?php
namespace MailCenter\Data;

interface DataInterface
{
	/**
	 * @param $db
	 * @return array
	 */
	public static function getData($db);
}