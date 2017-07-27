<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Protocol\Messages;

use PHPMQ\Server\Protocol\Interfaces\IdentifiesMessageType;
use PHPMQ\Server\Protocol\Messages\MessageReceipt;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageReceiptTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Protocol\Messages
 */
final class MessageReceiptTest extends TestCase
{
	use QueueIdentifierMocking;
	use MessageIdentifierMocking;

	/**
	 * @param string $queueName
	 * @param string $messageId
	 * @param string $expectedMessage
	 *
	 * @dataProvider queueNameMessageIdProvider
	 */
	public function testCanEncodeMessage( string $queueName, string $messageId, string $expectedMessage ) : void
	{
		$receipt = new MessageReceipt( $this->getQueueName( $queueName ), $this->getMessageId( $messageId ) );

		$this->assertSame( $queueName, (string)$receipt->getQueueName() );
		$this->assertSame( $messageId, (string)$receipt->getMessageId() );
		$this->assertSame( $expectedMessage, (string)$receipt );
		$this->assertSame( $expectedMessage, $receipt->toString() );
		$this->assertInstanceOf( IdentifiesMessageType::class, $receipt->getMessageType() );
		$this->assertSame( MessageType::MESSAGE_RECEIPT, $receipt->getMessageType()->getType() );
	}

	public function queueNameMessageIdProvider() : array
	{
		return [
			[
				'queueName'       => 'Foo',
				'messageId'       => 'd7e7f68761d34838494b233148b5486c',
				'expectedMessage' => 'H0100502'
					. 'P0100000000000000000000000000003'
					. 'Foo'
					. 'P0300000000000000000000000000032'
					. 'd7e7f68761d34838494b233148b5486c',
			],
		];
	}
}
