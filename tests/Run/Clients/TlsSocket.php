<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run\Clients;

use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;

/**
 * Class TlsSocket
 * @package PHPMQ\Server\Tests\Run\Clients
 */
final class TlsSocket implements IdentifiesSocketAddress
{
	/** @var string */
	private $host;

	/** @var int */
	private $port;

	/** @var array */
	private $contextOptions;

	public function __construct( string $host, int $port, array $contextOptions = [] )
	{
		$this->host           = $host;
		$this->port           = $port;
		$this->contextOptions = $contextOptions;

		$this->contextOptions['ssl']['crypto_method'] = $this->getCryptoMethod();
	}

	private function getCryptoMethod() : int
	{
		$cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;

		if ( defined( 'STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT' ) )
		{
			$cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT;
		}
		if ( defined( 'STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT' ) )
		{
			$cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
		}
		if ( defined( 'STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT' ) )
		{
			$cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
		}

		return $cryptoMethod;
	}

	public function getSocketAddress() : string
	{
		return sprintf( 'tls://%s:%s', $this->host, $this->port );
	}

	public function getContextOptions() : array
	{
		return $this->contextOptions;
	}
}
