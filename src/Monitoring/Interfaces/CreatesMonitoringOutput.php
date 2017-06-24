<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Interfaces;

/**
 * Interface CreatesMonitoringOutput
 * @package PHPMQ\Server\Monitoring\Interfaces
 */
interface CreatesMonitoringOutput
{
	public function getOutput( ProvidesServerMonitoringInfo $serverMonitoringInfo ) : string;
}
