<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\FlushQueueCommand;
use PHPMQ\Server\Events\Interfaces\ProvidesMaintenanceClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedFlushingQueue
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedFlushingQueue implements CarriesEventData, ProvidesMaintenanceClient
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var FlushQueueCommand */
	private $flushQueueCommand;

	public function __construct( MaintenanceClient $maintenanceClient, FlushQueueCommand $flushQueueCommand )
	{
		$this->maintenanceClient = $maintenanceClient;
		$this->flushQueueCommand = $flushQueueCommand;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getFlushQueueCommand() : FlushQueueCommand
	{
		return $this->flushQueueCommand;
	}
}
