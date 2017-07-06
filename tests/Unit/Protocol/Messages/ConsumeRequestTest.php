<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Protocol\Messages;

use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class ConsumeRequestTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Protocol\Messages
 */
final class ConsumeRequestTest extends TestCase
{
	use QueueIdentifierMocking;

	/**
	 * @param string $queueName
	 * @param int    $messageCount
	 * @param string $expectedMessage
	 *
	 * @dataProvider queueNameMessageCountProvider
	 */
	public function testCanGetEncodedMessage( string $queueName, int $messageCount, string $expectedMessage ) : void
	{
		$consumeRequest = new ConsumeRequest( $this->getQueueName( $queueName ), $messageCount );

		$this->assertSame( $queueName, (string)$consumeRequest->getQueueName() );
		$this->assertSame( $messageCount, $consumeRequest->getMessageCount() );
		$this->assertSame( $expectedMessage, (string)$consumeRequest );
		$this->assertSame( $expectedMessage, $consumeRequest->toString() );
	}

	public function queueNameMessageCountProvider() : array
	{
		return [
			[
				'queueName'       => 'Foo',
				'messageCount'    => 5,
				'expectedMessage' => 'H0100202'
					. 'P0100000000000000000000000000003'
					. 'Foo'
					. 'P0400000000000000000000000000001'
					. '5',
			],
		];
	}
}
