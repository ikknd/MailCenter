<?php
namespace MailCenter\lib;

class Helper
{

	CONST HASH_SALT = 'xEWwYL4yR2ssTWQjf5NenBDVoaOXz1';

	static public function unsubscribeLink($type, $email)
	{
		$link = '/' . $type . '/' . $email . '/' . self::createHash($email);
		return $link;
	}

	static public function createHash($var)
	{
		$options = [
			'cost' => 11,
			'salt' => self::HASH_SALT,
		];
		return password_hash($var, PASSWORD_BCRYPT, $options);
	}
}