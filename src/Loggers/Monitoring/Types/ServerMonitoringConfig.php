<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers\Monitoring\Types;

/**
 * Class ServerMonitoringConfig
 * @package PHPMQ\Server\Loggers\Monitoring\Types
 */
final class ServerMonitoringConfig
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

	public static function fromCLIOptions( ?array $argv = null ) : self
	{
		$config  = new self();
		$options = [ '-m', '--monitor' ];

		if ( null === $argv )
		{
			$argv = $_SERVER['argv'];
		}

		if ( count( array_intersect( $options, $argv ) ) === 1 )
		{
			$config->enable();
		}

		return $config;
	}
}
