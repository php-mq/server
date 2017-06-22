<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Clients;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class ConsumptionInfoTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Clients
 */
final class ConsumptionInfoTest extends TestCase
{
	public function testCanGetConsumableMessageCount(): void
	{
		$messageId = MessageId::generate();
		$info      = new ConsumptionInfo( new QueueName( 'Test-Queue' ), 5 );

		$this->assertSame( 5, $info->getMessageCount() );

		$info->addMessageId( $messageId );

		$this->assertSame( 4, $info->getMessageCount() );

		$info->removeMessageId( $messageId );

		$this->assertSame( 5, $info->getMessageCount() );
	}

	public function testCanCheckForConsumption(): void
	{
		$messageId = MessageId::generate();
		$info      = new ConsumptionInfo( new QueueName( 'Test-Queue' ), 1 );

		$this->assertTrue( $info->canConsume() );

		$info->addMessageId( $messageId );

		$this->assertFalse( $info->canConsume() );

		$info->removeMessageId( $messageId );

		$this->assertTrue( $info->canConsume() );
	}

	public function testCanGetConsumedMessageIds(): void
	{
		$messageId = MessageId::generate();
		$info      = new ConsumptionInfo( new QueueName( 'Test-Queue' ), 1 );

		$this->assertCount( 0, $info->getMessageIds() );

		$info->addMessageId( $messageId );

		$this->assertCount( 1, $info->getMessageIds() );
		$this->assertEquals( [ $messageId ], $info->getMessageIds() );
	}

	public function testCanGetConsumptionInfoAsString(): void
	{
		$expectedString = 'Queue name: "Test-Queue", Message count: 4, Currently consumed: 1';
		$messageId      = MessageId::generate();
		$queueName      = new QueueName( 'Test-Queue' );
		$info           = new ConsumptionInfo( $queueName, 4 );

		$info->addMessageId( $messageId );

		$this->assertSame( $expectedString, (string)$info );
		$this->assertSame( $expectedString, $info->toString() );
		$this->assertSame( $queueName, $info->getQueueName() );
	}
}
