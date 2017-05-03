<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\Protocol\Messages;

use hollodotme\PHPMQ\Protocol\Messages\MessageC2E;
use hollodotme\PHPMQ\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageC2ETest
 * @package hollodotme\PHPMQ\Tests\Unit\Protocol\Messages
 */
final class MessageC2ETest extends TestCase
{
	/**
	 * @param string $queueName
	 * @param string $content
	 * @param string $expectedMessage
	 *
	 * @dataProvider queueNameContentProvider
	 */
	public function testCanGetEncodedMessage( string $queueName, string $content, string $expectedMessage ) : void
	{
		$messageC2E = new MessageC2E( new QueueName( $queueName ), $content );

		$this->assertSame( $queueName, (string)$messageC2E->getQueueName() );
		$this->assertSame( $content, $messageC2E->getContent() );
		$this->assertSame( $expectedMessage, (string)$messageC2E );
		$this->assertSame( $expectedMessage, $messageC2E->toString() );
	}

	public function queueNameContentProvider() : array
	{
		return [
			[
				'queueName'       => 'Foo',
				'content'         => 'Hello World',
				'expectedMessage' => 'H0100102'
				                     . 'P0100000000000000000000000000003'
				                     . 'Foo'
				                     . 'P0200000000000000000000000000011'
				                     . 'Hello World',
			],
		];
	}
}