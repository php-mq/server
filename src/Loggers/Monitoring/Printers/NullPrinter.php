<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers\Monitoring\Printers;

use PHPMQ\Server\Loggers\Monitoring\Interfaces\PrintsMonitoringInfo;
use PHPMQ\Server\Loggers\Monitoring\Interfaces\ProvidesMonitoringInfo;

/**
 * Class NullPrinter
 * @package PHPMQ\Server\Loggers\Monitoring\Printers
 */
final class NullPrinter implements PrintsMonitoringInfo
{
	public function print( ProvidesMonitoringInfo $monitoringInfo ): void
	{
		// Do nothing
	}
}
