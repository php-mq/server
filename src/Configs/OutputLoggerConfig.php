<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Configs;

use PHPMQ\Server\Loggers\Interfaces\ConfiguresOutputLogger;

/**
 * Class OutputLoggerConfig
 * @package PHPMQ\Server\Configs
 */
final class OutputLoggerConfig implements ConfiguresOutputLogger
{
	/** @var string */
	private $logLevel;

	public function __construct( string $logLevel )
	{
		$this->logLevel = $logLevel;
	}

	public function getLogLevel() : string
	{
		return $this->logLevel;
	}
}
