<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Protocol\Messages;

use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageE2CTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Protocol\Messages
 */
final class MessageE2CTest extends TestCase
{
	use QueueIdentifierMocking;
	use MessageIdentifierMocking;

	/**
	 * @param string $messageId
	 * @param string $queueName
	 * @param string $content
	 * @param string $expectedMessage
	 *
	 * @dataProvider messageIdQueueNameContentProvider
	 */
	public function testCanEncodeMessage(
		string $messageId,
		string $queueName,
		string $content,
		string $expectedMessage
	) : void
	{
		$messageE2C = new MessageE2C( $this->getMessageId( $messageId ), $this->getQueueName( $queueName ), $content );

		$this->assertSame( $messageId, (string)$messageE2C->getMessageId() );
		$this->assertSame( $queueName, (string)$messageE2C->getQueueName() );
		$this->assertSame( $content, $messageE2C->getContent() );
		$this->assertSame( $expectedMessage, (string)$messageE2C );
		$this->assertSame( $expectedMessage, $messageE2C->toString() );
	}

	public function messageIdQueueNameContentProvider() : array
	{
		return [
			[
				'messageId'       => 'd7e7f68761d34838494b233148b5486c',
				'queueName'       => 'Foo',
				'content'         => 'Hello World',
				'expectedMessage' => 'H0100303'
					. 'P0100000000000000000000000000003'
					. 'Foo'
					. 'P0200000000000000000000000000011'
					. 'Hello World'
					. 'P0300000000000000000000000000032'
					. 'd7e7f68761d34838494b233148b5486c',
			],
		];
	}
}
