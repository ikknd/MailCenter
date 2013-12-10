<?php
namespace MailCenter\Template\Engine;

interface EngineInterface
{
	/**
	 * @param array $options
	 * @return void
	 */
	public function setOptions($options);

	/**
	 * @return string - HTML template filled with $data
	 */
	public function render();
}