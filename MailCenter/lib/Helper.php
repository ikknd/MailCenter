<?php
namespace MailCenter\lib;

class Helper
{

	CONST HASH_SALT = 'xEWwYL4yR2ssTWQjf5NenBDVoaOXz1';

	static public function unsubscribeLink($type, $email)
	{
		$link = \MailCenter\lib\Registry::getInstance()->get('config')->siteurl . '/unsubscribe/' . $type . '/' . $email . '/' . \base64_encode(self::createHash($email));
		return $link;
	}

	static public function subscribeLink($type, $email)
	{
		$link = \MailCenter\lib\Registry::getInstance()->get('config')->siteurl . '/subscribe/' . $type . '/' . $email . '/' . \base64_encode(self::createHash($email));
		return $link;
	}

	static public function createHash($var)
	{
		return \crypt($var, self::HASH_SALT);
	}
}