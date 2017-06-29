<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\FlushAllQueuesCommand;
use PHPMQ\Server\Events\Interfaces\ProvidesMaintenanceClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedFlushingAllQueues
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedFlushingAllQueues implements CarriesEventData, ProvidesMaintenanceClient
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var FlushAllQueuesCommand */
	private $flushAllQueuesCommand;

	public function __construct( MaintenanceClient $maintenanceClient, FlushAllQueuesCommand $flushAllQueuesCommand )
	{
		$this->maintenanceClient     = $maintenanceClient;
		$this->flushAllQueuesCommand = $flushAllQueuesCommand;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getFlushAllQueuesCommand() : FlushAllQueuesCommand
	{
		return $this->flushAllQueuesCommand;
	}
}
