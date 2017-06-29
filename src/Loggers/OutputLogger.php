<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers;

use PHPMQ\Server\Constants\AnsiColors;

/**
 * Class OutputLogger
 * @package PHPMQ\Server\Loggers
 */
final class OutputLogger extends AbstractLogger
{
	public function log( $level, $message, array $context = [] ) : void
	{
		$logMessage = $this->getLogMessage( $level, $message, $context );
		$logMessage = str_replace( array_keys( AnsiColors::COLORS ), AnsiColors::COLORS, $logMessage );

		echo $logMessage . "\n";
	}
}
