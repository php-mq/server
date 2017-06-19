<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Protocol\Headers;

use PHPMQ\Server\Protocol\Constants\PacketType;
use PHPMQ\Server\Protocol\Headers\PacketHeader;
use PHPUnit\Framework\TestCase;

/**
 * Class PacketHeaderTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Protocol\Headers
 */
final class PacketHeaderTest extends TestCase
{
	/**
	 * @param int    $packetType
	 * @param int    $contentLength
	 * @param string $expextedHeader
	 *
	 * @dataProvider packageTypeContentLengthProvider
	 */
	public function testCanConvertPacketHeaderToString(
		int $packetType,
		int $contentLength,
		string $expextedHeader
	) : void
	{
		$packageHeader = new PacketHeader( $packetType, $contentLength );

		$this->assertSame( $packetType, $packageHeader->getPacketType() );
		$this->assertSame( $contentLength, $packageHeader->getContentLength() );
		$this->assertSame( $expextedHeader, (string)$packageHeader );
		$this->assertSame( $expextedHeader, $packageHeader->toString() );
		$this->assertSame( 32, strlen( $packageHeader->toString() ) );
	}

	public function packageTypeContentLengthProvider() : array
	{
		return [
			[
				'packetType'     => PacketType::QUEUE_NAME,
				'contentLength'  => 3,
				'expectedHeader' => 'P0100000000000000000000000000003',
			],
			[
				'packetType'     => PacketType::MESSAGE_CONTENT,
				'contentLength'  => 11,
				'expectedHeader' => 'P0200000000000000000000000000011',
			],
			[
				'packetType'     => PacketType::MESSAGE_ID,
				'contentLength'  => 32,
				'expectedHeader' => 'P0300000000000000000000000000032',
			],
			[
				'packetType'     => PacketType::MESSAGE_CONSUME_COUNT,
				'contentLength'  => 1,
				'expectedHeader' => 'P0400000000000000000000000000001',
			],
		];
	}

	/**
	 * @param string $string
	 * @param int    $expectedPacketType
	 * @param int    $expectedContentLength
	 *
	 * @dataProvider stringProvider
	 */
	public function testCanGetPacketHeaderFromString(
		string $string,
		int $expectedPacketType,
		int $expectedContentLength
	) : void
	{
		$packetHeader = PacketHeader::fromString( $string );

		$this->assertSame( $expectedPacketType, $packetHeader->getPacketType() );
		$this->assertSame( $expectedContentLength, $packetHeader->getContentLength() );
	}

	public function stringProvider() : array
	{
		return [
			[
				'string'                => 'P0100000000000000000000000000003',
				'expectedPacketType'    => PacketType::QUEUE_NAME,
				'expectedContentLength' => 3,
			],
			[
				'string'                => 'P0200000000000000000000000000011',
				'expectedPacketType'    => PacketType::MESSAGE_CONTENT,
				'expectedContentLength' => 11,
			],
			[
				'string'                => 'P0300000000000000000000000000032',
				'expectedPacketType'    => PacketType::MESSAGE_ID,
				'expectedContentLength' => 32,
			],
			[
				'string'                => 'P0400000000000000000000000000001',
				'expectedPacketType'    => PacketType::MESSAGE_CONSUME_COUNT,
				'expectedContentLength' => 1,
			],
		];
	}
}
