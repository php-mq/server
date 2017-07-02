<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

use PHPMQ\Server\Exceptions\RuntimeException;
use PHPMQ\Server\Servers\Interfaces\EstablishesStream;
use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;

/**
 * Class ServerSocket
 * @package PHPMQ\Server\Servers
 */
final class ServerSocket implements EstablishesStream
{
	/** @var resource */
	private $socket;

	/** @var string */
	private $socketAddress;

	/** @var bool */
	private $listening;

	public function __construct( IdentifiesSocketAddress $socketAddress )
	{
		$this->socketAddress = $socketAddress;
		$this->listening     = false;
	}

	public function startListening() : void
	{
		if ( $this->listening )
		{
			return;
		}
		$this->establishSocket();
		$this->makeSocketNonBlocking();

		$this->listening = true;
	}

	private function establishSocket() : void
	{
		$errorNumber = $errorString = null;

		$this->socket = @stream_socket_server(
			$this->socketAddress->getSocketAddress(),
			$errorNumber,
			$errorString
		);

		$this->guardSocketEstablished( $errorNumber, $errorString );
	}

	private function guardSocketEstablished( ?int $errorNumber, ?string $errorString ) : void
	{
		if ( false === $this->socket )
		{
			throw new RuntimeException(
				sprintf(
					'Could not establish server socket at %s: %s [%s].',
					$this->socketAddress->getSocketAddress(),
					$errorString,
					$errorNumber
				)
			);
		}
	}

	private function makeSocketNonBlocking() : void
	{
		if ( !stream_set_blocking( $this->socket, false ) )
		{
			throw new RuntimeException(
				sprintf(
					'Could not set server socket at %s to non-blocking.',
					$this->socketAddress->getSocketAddress()
				)
			);
		}
	}

	public function getStream()
	{
		return $this->socket;
	}
}
