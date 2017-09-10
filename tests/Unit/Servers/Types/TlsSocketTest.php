<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Servers\Types;

use PHPMQ\Server\Servers\Types\TlsSocket;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class TlsSocketTest
 * @package PHPMQ\Server\Tests\Unit\Servers\Types
 */
final class TlsSocketTest extends TestCase
{
	use SocketMocking;

	public function testCryptoMethodIsAutomaticallyAddedToContextOptions() : void
	{
		$socket = new TlsSocket( self::$SERVER_HOST, self::$SERVER_PORT, [] );

		$contextOptions = $socket->getContextOptions();

		$this->assertInternalType( 'int', $contextOptions['ssl']['crypto_method'] );
		$this->assertGreaterThan( 0, $contextOptions['ssl']['crypto_method'] );
	}
}
