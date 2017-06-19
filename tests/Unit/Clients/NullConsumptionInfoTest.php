<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Clients;

use PHPMQ\Server\Clients\NullConsumptionInfo;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class NullConsumptionInfoTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Clients
 */
final class NullConsumptionInfoTest extends TestCase
{
	public function testCannotConsumeMessages() : void
	{
		$info = new NullConsumptionInfo();

		$this->assertFalse( $info->canConsume() );
	}

	public function testMessageCountIsZero() : void
	{
		$info = new NullConsumptionInfo();

		$this->assertSame( 0, $info->getMessageCount() );
	}

	public function testMessageIdsAreEmpty() : void
	{
		$info = new NullConsumptionInfo();

		$this->assertCount( 0, $info->getMessageIds() );
	}

	/**
	 * @expectedException \PHPMQ\Server\Exceptions\LogicException
	 */
	public function testAddingMessageIdThrowsException() : void
	{
		(new NullConsumptionInfo())->addMessageId( MessageId::generate() );
	}

	/**
	 * @expectedException \PHPMQ\Server\Exceptions\LogicException
	 */
	public function testRemovingMessageIdThrowsException() : void
	{
		(new NullConsumptionInfo())->removeMessageId( MessageId::generate() );
	}

	public function testCanGetInfoAsString() : void
	{
		$this->assertSame( NullConsumptionInfo::class, (string)new NullConsumptionInfo() );
		$this->assertSame( NullConsumptionInfo::class, (new NullConsumptionInfo())->toString() );
	}

	public function testQueueNameIsEmpty() : void
	{
		$this->assertSame( '', (new NullConsumptionInfo())->getQueueName()->toString() );
	}

	public function testQueueNameDoesNotEqualOthers() : void
	{
		$this->assertFalse( (new NullConsumptionInfo())->getQueueName()->equals( new QueueName( '' ) ) );
	}
}
