<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Interfaces;

/**
 * Interface PrintsMonitoringInfo
 * @package PHPMQ\Server\Monitoring\Interfaces
 */
interface PrintsMonitoringInfo
{
	public function print( ProvidesMonitoringInfo $monitoringInfo ) : void;
}
