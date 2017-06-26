<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Interfaces;

use PHPMQ\Server\Clients\MaintenanceClient;

/**
 * Interface ProvidesMaintenanceClient
 * @package PHPMQ\Server\Events\Interfaces
 */
interface ProvidesMaintenanceClient
{
	public function getMaintenanceClient() : MaintenanceClient;
}
