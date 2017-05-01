<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\MessageHandlers;

use hollodotme\PHPMQ\MessageHandlers\AcknowledgementHandler;
use hollodotme\PHPMQ\Protocol\Messages\Acknowledgement;
use hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits\StorageMocking;
use hollodotme\PHPMQ\Types\Message;
use hollodotme\PHPMQ\Types\MessageId;
use hollodotme\PHPMQ\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class AcknowledgementHandlerTest
 * @package hollodotme\PHPMQ\Tests\Unit\MessageHandlers
 */
final class AcknowledgementHandlerTest extends TestCase
{
	use StorageMocking;

	public function testCanHandleAcknowledgement() : void
	{
		$messageId       = MessageId::generate();
		$queueName       = new QueueName( 'Test-Queue' );
		$message         = new Message( $messageId, 'Unit-Test' );
		$acknowledgement = new Acknowledgement( $queueName, $messageId );
		$messageHandler  = new AcknowledgementHandler( $this->messageQueue );

		$this->messageQueue->enqueue( $queueName, $message );
		$this->messageQueue->markAsDispached( $queueName, $messageId );

		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountUndispatched() );
		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountDispatched() );
		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountTotal() );

		$this->assertTrue( $messageHandler->acceptsMessageType( $acknowledgement->getMessageType() ) );

		$messageHandler->handle( $acknowledgement );

		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountUndispatched() );
		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountDispatched() );
		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountTotal() );
	}
}
