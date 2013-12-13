<?php
namespace MailCenter\Template\Storage;

interface StorageInterface
{
	/**
	 * @param string $name
	 * @param string $path
	 * @return string
	 */
	public function getTemplatePath($name, $path);
}