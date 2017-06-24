<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Monitoring\Types;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Class MonitoringRequest
 * @package PHPMQ\Server\Monitoring\Types
 */
final class MonitoringRequest
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var IdentifiesQueue */
	private $queueName;

	public function __construct( MaintenanceClient $maintenanceClient, IdentifiesQueue $queueName )
	{
		$this->maintenanceClient = $maintenanceClient;
		$this->queueName         = $queueName;
	}

	public function getMaintenanceClient() : MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getQueueName() : IdentifiesQueue
	{
		return $this->queueName;
	}
}
