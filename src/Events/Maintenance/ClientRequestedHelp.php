<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\HelpCommand;
use PHPMQ\Server\Events\Interfaces\ProvidesMaintenanceClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedHelp
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedHelp implements CarriesEventData, ProvidesMaintenanceClient
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var HelpCommand */
	private $helpCommand;

	public function __construct( MaintenanceClient $maintenanceClient, HelpCommand $helpCommand )
	{
		$this->maintenanceClient = $maintenanceClient;
		$this->helpCommand       = $helpCommand;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getHelpCommand() : HelpCommand
	{
		return $this->helpCommand;
	}
}
