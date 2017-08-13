<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers\Interfaces;

/**
 * Interface ConfiguresOutputLogger
 * @package PHPMQ\Server\Loggers\Interfaces
 */
interface ConfiguresOutputLogger
{
	public function getLogLevel() : string;
}
