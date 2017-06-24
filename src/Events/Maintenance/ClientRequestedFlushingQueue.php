<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\FlushQueue;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedFlushingQueue
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedFlushingQueue implements CarriesEventData
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var FlushQueue */
	private $flushQueueCommand;

	public function __construct( MaintenanceClient $maintenanceClient, FlushQueue $flushQueueCommand )
	{
		$this->maintenanceClient = $maintenanceClient;
		$this->flushQueueCommand = $flushQueueCommand;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getFlushQueueCommand() : FlushQueue
	{
		return $this->flushQueueCommand;
	}
}
