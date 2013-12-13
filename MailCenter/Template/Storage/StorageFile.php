<?php
namespace MailCenter\Template\Storage;

class StorageFile implements StorageInterface
{
	/**
	 * @param string $name
	 * @param string $path
	 * @return string
	 * @throws \Exception
	 */
	public function getTemplatePath($name, $path)
	{
		$filePath = $path . '/Template/' . ucfirst($name) . 'Template.php';

		if(file_exists($filePath)){
			return $filePath;
		} else {
			throw new \Exception('This template file does not exist: ' . $filePath);
		}
	}
}