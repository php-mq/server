<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Protocol\Messages;

use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class AcknowledgementTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Protocol\Messages
 */
final class AcknowledgementTest extends TestCase
{
	/**
	 * @param string $queueName
	 * @param string $messageId
	 * @param string $expectedMessage
	 *
	 * @dataProvider queueNameMessageIdProvider
	 */
	public function testCanEncodeMessage( string $queueName, string $messageId, string $expectedMessage ) : void
	{
		$acknowledgement = new Acknowledgement( new QueueName( $queueName ), new MessageId( $messageId ) );

		$this->assertSame( $queueName, (string)$acknowledgement->getQueueName() );
		$this->assertSame( $messageId, (string)$acknowledgement->getMessageId() );
		$this->assertSame( $expectedMessage, (string)$acknowledgement );
		$this->assertSame( $expectedMessage, $acknowledgement->toString() );
	}

	public function queueNameMessageIdProvider() : array
	{
		return [
			[
				'queueName'       => 'Foo',
				'messageId'       => 'd7e7f68761d34838494b233148b5486c',
				'expectedMessage' => 'H0100402'
				                     . 'P0100000000000000000000000000003'
				                     . 'Foo'
				                     . 'P0300000000000000000000000000032'
				                     . 'd7e7f68761d34838494b233148b5486c',
			],
		];
	}
}
