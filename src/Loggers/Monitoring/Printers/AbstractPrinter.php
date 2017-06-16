<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers\Monitoring\Printers;

use PHPMQ\Server\Loggers\Monitoring\Interfaces\PrintsMonitoringInfo;

/**
 * Class AbstractPrinter
 * @package PHPMQ\Server\Loggers\Monitoring\Printers
 */
abstract class AbstractPrinter implements PrintsMonitoringInfo
{
	/** @var int */
	private $terminalWidth = 0;

	/** @var int */
	private $terminalHeight = 0;

	final protected function clearScreen(): void
	{
		echo "\e[2J\e[0;0H\r\n";
		echo "\e[30;42m PHP \e[37;41m MQ \e[30;42m - Monitor" . str_repeat( ' ', 27 ) . "\e[0m\r\n\n";
	}

	final protected function updateTerminalWidth(): void
	{
		$this->terminalWidth = (int)exec( 'tput cols' );
	}

	final protected function getTerminalWidth(): int
	{
		return $this->terminalWidth;
	}

	final protected function updateTerminalHeight(): void
	{
		$this->terminalHeight = (int)exec( 'tput lines' );
	}

	final protected function getTerminalHeight(): int
	{
		return $this->terminalHeight;
	}
}
