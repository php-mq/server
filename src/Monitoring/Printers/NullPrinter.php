<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Printers;

use PHPMQ\Server\Monitoring\Interfaces\PrintsMonitoringInfo;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesMonitoringInfo;

/**
 * Class NullPrinter
 * @package PHPMQ\Server\Monitoring\Printers
 */
final class NullPrinter implements PrintsMonitoringInfo
{
	public function print( ProvidesMonitoringInfo $monitoringInfo ): void
	{
		// Do nothing
	}
}
