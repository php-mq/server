<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Monitoring\Types;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Monitoring\Types\MonitoringRequest;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class MonitoringRequestTest
 * @package PHPMQ\Server\Tests\Unit\Monitoring\Types
 */
final class MonitoringRequestTest extends TestCase
{
	public function testCanGetMaintenanceClient() : void
	{
		$socket            = null;
		$maintenanceClient = new MaintenanceClient( new ClientId( 'Unit-Test-Client' ), $socket );
		$queueName         = new QueueName( 'Unit-Test-Queue' );
		$monitoringRequest = new MonitoringRequest( $maintenanceClient, $queueName );

		$this->assertSame( $maintenanceClient, $monitoringRequest->getMaintenanceClient() );
		$this->assertSame( $queueName, $monitoringRequest->getQueueName() );
	}
}
