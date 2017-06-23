<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Monitoring\Formatters;

use PHPMQ\Server\Monitoring\Formatters\ByteFormatter;
use PHPUnit\Framework\TestCase;

final class ByteFormatterTest extends TestCase
{
	/**
	 * @param int    $bytes
	 * @param string $expectedString
	 *
	 * @dataProvider bytesProvider
	 */
	public function testCanGetBytesAsHumanReadableString( int $bytes, int $precision, string $expectedString ): void
	{
		$formatter = new ByteFormatter();

		$this->assertSame( $expectedString, $formatter->format( $bytes, $precision ) );
	}

	public function bytesProvider(): array
	{
		return [
			[
				'bytes'           => 0,
				'precision'       => 0,
				'exptectedString' => '0 B',
			],
			[
				'bytes'           => 999,
				'precision'       => 0,
				'exptectedString' => '999 B',
			],
			[
				'bytes'           => 1024,
				'precision'       => 0,
				'exptectedString' => '1 KB',
			],
			[
				'bytes'           => 1536,
				'precision'       => 1,
				'exptectedString' => '1,5 KB',
			],
			[
				'bytes'           => 1024*1024,
				'precision'       => 2,
				'exptectedString' => '1,00 MB',
			],
			[
				'bytes'           => 1024*1024*1000,
				'precision'       => 2,
				'exptectedString' => '1.000,00 MB',
			],
		];
	}
}
