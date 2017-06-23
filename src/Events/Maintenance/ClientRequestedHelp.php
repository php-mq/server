<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\Help;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedHelp
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedHelp implements CarriesEventData
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var Help */
	private $helpCommand;

	public function __construct( MaintenanceClient $maintenanceClient, Help $helpCommand )
	{
		$this->maintenanceClient = $maintenanceClient;
		$this->helpCommand       = $helpCommand;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getHelpCommand() : Help
	{
		return $this->helpCommand;
	}
}
