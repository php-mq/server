<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers;

use PHPMQ\Server\Constants\AnsiColors;
use PHPMQ\Server\Loggers\Constants\LogLevel;
use PHPMQ\Server\Loggers\Interfaces\ConfiguresOutputLogger;

/**
 * Class OutputLogger
 * @package PHPMQ\Server\Loggers
 */
final class OutputLogger extends AbstractLogger
{
	/** @var ConfiguresOutputLogger */
	private $config;

	public function __construct( ConfiguresOutputLogger $config )
	{
		$this->config = $config;
	}

	public function log( $level, $message, array $context = [] ) : void
	{
		if ( !in_array( $level, LogLevel::LOG_LEVEL_ASSOC[ $this->config->getLogLevel() ], true ) )
		{
			return;
		}

		$logMessage = $this->getLogMessage( $level, $message, $context );
		$logMessage = str_replace( array_keys( AnsiColors::COLORS ), AnsiColors::COLORS, $logMessage );

		echo $logMessage . "\n";
	}
}
