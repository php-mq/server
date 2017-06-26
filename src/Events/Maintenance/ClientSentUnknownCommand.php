<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Events\Interfaces\ProvidesMaintenanceClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientSentUnknownCommand
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientSentUnknownCommand implements CarriesEventData, ProvidesMaintenanceClient
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var string */
	private $unknownCommandString;

	public function __construct( MaintenanceClient $maintenanceClient, string $unknownCommandString )
	{
		$this->maintenanceClient    = $maintenanceClient;
		$this->unknownCommandString = $unknownCommandString;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getUnknownCommandString() : string
	{
		return $this->unknownCommandString;
	}
}
