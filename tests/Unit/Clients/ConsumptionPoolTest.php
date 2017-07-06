<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Clients;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Clients\NullConsumptionInfo;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StreamIdentifierMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class ConsumptionPoolTest
 * @package PHPMQ\Server\Tests\Unit\Clients
 */
final class ConsumptionPoolTest extends TestCase
{
	use QueueIdentifierMocking;
	use StreamIdentifierMocking;

	public function testCanAddConsumptionInfo() : void
	{
		$streamId        = $this->getStreamId( 'Unit-Test' );
		$queueName       = $this->getQueueName( 'Test-Queue' );
		$consumptionInfo = new ConsumptionInfo( $queueName, 5 );
		$consumptionPool = new ConsumptionPool();

		$consumptionPool->setConsumptionInfo( $streamId, $consumptionInfo );

		$this->assertSame( $consumptionInfo, $consumptionPool->getConsumptionInfo( $streamId ) );
	}

	public function testCanRemoveConsumptionInfo() : void
	{
		$streamId        = $this->getStreamId( 'Unit-Test' );
		$queueName       = $this->getQueueName( 'Test-Queue' );
		$consumptionInfo = new ConsumptionInfo( $queueName, 5 );
		$consumptionPool = new ConsumptionPool();

		$consumptionPool->setConsumptionInfo( $streamId, $consumptionInfo );

		$this->assertSame( $consumptionInfo, $consumptionPool->getConsumptionInfo( $streamId ) );

		$consumptionPool->removeConsumptionInfo( $streamId );

		$this->assertEquals( new NullConsumptionInfo(), $consumptionPool->getConsumptionInfo( $streamId ) );
	}
}
