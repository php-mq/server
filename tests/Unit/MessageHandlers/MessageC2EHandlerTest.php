<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\MessageHandlers;

use hollodotme\PHPMQ\MessageHandlers\MessageC2EHandler;
use hollodotme\PHPMQ\Protocol\Messages\MessageC2E;
use hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits\StorageMocking;
use hollodotme\PHPMQ\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageC2EHandlerTest
 * @package hollodotme\PHPMQ\Tests\Unit\MessageHandlers
 */
final class MessageC2EHandlerTest extends TestCase
{
	use StorageMocking;

	public function testCanHandleMessage() : void
	{
		$queueName         = new QueueName( 'Test-Queue' );
		$messageC2E        = new MessageC2E( $queueName, 'Unit-Test' );
		$messageC2EHandler = new MessageC2EHandler( $this->messageQueue );

		$this->assertTrue( $messageC2EHandler->acceptsMessageType( $messageC2E->getMessageType() ) );

		$messageC2EHandler->handle( $messageC2E );

		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountUndispatched() );
		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountDispatched() );
		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountTotal() );
	}
}
