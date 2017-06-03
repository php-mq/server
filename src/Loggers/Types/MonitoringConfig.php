<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers\Types;

/**
 * Class MonitoringConfig
 * @package PHPMQ\Server\Loggers\Types
 */
final class MonitoringConfig
{
	/** @var bool */
	private $isEnabled = false;

	public function enable() : void
	{
		$this->isEnabled = true;
	}

	public function disable() : void
	{
		$this->isEnabled = false;
	}

	public function isEnabled() : bool
	{
		return $this->isEnabled;
	}

	public function isDisabled() : bool
	{
		return !$this->isEnabled;
	}

	public static function fromCLIOptions() : self
	{
		$config = new self();

		$options = getopt( 'm', [ 'monitor' ] );

		if ( array_key_exists( 'm', $options ) || array_key_exists( 'monitor', $options ) )
		{
			$config->enable();
		}

		return $config;
	}
}
