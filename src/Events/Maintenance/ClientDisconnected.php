<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientDisconnected
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientDisconnected implements CarriesEventData
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	public function __construct( MaintenanceClient $maintenanceClient )
	{
		$this->maintenanceClient = $maintenanceClient;
	}

	public function getMaintenanceClient(): MaintenanceClient
	{
		return $this->maintenanceClient;
	}
}
