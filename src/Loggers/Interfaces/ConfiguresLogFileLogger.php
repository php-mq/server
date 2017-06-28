<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers\Interfaces;

/**
 * Interface ConfiguresLogFileLogger
 * @package PHPMQ\Server\Loggers\Interfaces
 */
interface ConfiguresLogFileLogger
{
	public function getLogFilePath() : string;

	public function getLogLevel() : string;
}
