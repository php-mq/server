<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers\Monitoring\Interfaces;

/**
 * Interface PrintsMonitoringInfo
 * @package PHPMQ\Server\Loggers\Monitoring\Interfaces
 */
interface PrintsMonitoringInfo
{
	public function print( ProvidesMonitoringInfo $monitoringInfo ) : void;
}
