<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\QuitRefreshCommand;
use PHPMQ\Server\Events\Interfaces\ProvidesMaintenanceClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedQuittingRefresh
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedQuittingRefresh implements CarriesEventData, ProvidesMaintenanceClient
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var QuitRefreshCommand */
	private $quitRefreshCommand;

	public function __construct( MaintenanceClient $maintenanceClient, QuitRefreshCommand $quitRefreshCommand )
	{
		$this->maintenanceClient  = $maintenanceClient;
		$this->quitRefreshCommand = $quitRefreshCommand;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getQuitRefreshCommand() : QuitRefreshCommand
	{
		return $this->quitRefreshCommand;
	}
}
