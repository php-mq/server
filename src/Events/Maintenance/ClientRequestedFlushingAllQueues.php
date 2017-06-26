<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\FlushAllQueues;
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

	/** @var FlushAllQueues */
	private $flushAllQueuesCommand;

	public function __construct( MaintenanceClient $maintenanceClient, FlushAllQueues $flushAllQueuesCommand )
	{
		$this->maintenanceClient     = $maintenanceClient;
		$this->flushAllQueuesCommand = $flushAllQueuesCommand;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getFlushAllQueuesCommand() : FlushAllQueues
	{
		return $this->flushAllQueuesCommand;
	}
}
