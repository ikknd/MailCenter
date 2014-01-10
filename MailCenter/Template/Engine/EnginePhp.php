<?php
namespace MailCenter\Template\Engine;

class EnginePhp implements EngineInterface
{
	/**
	 * @var array
	 */
	protected $_options;

	/**
	 * @param array $options
	 */
	public function setOptions($options)
	{
		$this->_options = $options;
	}

	/**
	 * @return string - HTML template filled with $data
	 */
	public function render()
	{
		extract($this->_options['data']);

		ob_start();
		require($this->_options['templatePath']);
		$renderedTemplate = ob_get_contents();
		ob_end_clean();

		return $renderedTemplate;
	}
}