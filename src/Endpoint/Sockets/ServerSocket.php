<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Sockets;

use PHPMQ\Server\Endpoint\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Server\Exceptions\RuntimeException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Class ServerSocket
 * @package PHPMQ\Server\Endpoint\Sockets
 */
final class ServerSocket implements LoggerAwareInterface
{
	use LoggerAwareTrait;

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

		$this->setLogger( new NullLogger() );
	}

	public function getName() : string
	{
		return $this->socketAddress->getSocketAddress();
	}

	public function startListening() : void
	{
		$this->establishSocket();
		$this->makeSocketNonBlocking();

		$this->logger->debug(
			sprintf(
				'Start listening for client connections on: %s',
				$this->socketAddress->getSocketAddress()
			)
		);

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

	public function isListening() : bool
	{
		return $this->listening;
	}

	public function endListening() : void
	{
		$this->listening = false;

		if ( null === $this->socket )
		{
			return;
		}

		if ( !fclose( $this->socket ) )
		{
			throw new RuntimeException(
				sprintf(
					'Could not close socket at %s properly.',
					$this->socketAddress->getSocketAddress()
				)
			);
		}

		$this->socket = null;

		$this->logger->debug(
			sprintf(
				'Stopped listening for client connections on: %s',
				$this->socketAddress->getSocketAddress()
			)
		);
	}

	public function checkForNewClient( callable $newClientHandler ) : void
	{
		if ( !$this->isActive() )
		{
			return;
		}

		$clientSocket = stream_socket_accept( $this->socket, 0 );

		$this->guardClientSocketAccepted( $clientSocket );

		$clientName = $this->getClientSocketName( $clientSocket );

		$this->makeClientSocketNonBlocking( $clientName, $clientSocket );

		$newClientHandler( $this, $clientName, $clientSocket );
	}

	private function isActive() : bool
	{
		if ( !$this->listening )
		{
			return false;
		}

		$reads  = [ $this->socket ];
		$writes = $excepts = null;

		return (bool)stream_select( $reads, $writes, $excepts, 0 );
	}

	private function guardClientSocketAccepted( $clientSocket ) : void
	{
		if ( false === $clientSocket )
		{
			throw new RuntimeException(
				sprintf(
					'Failed to accept client socket at server socket %s.',
					$this->socketAddress->getSocketAddress()
				)
			);
		}
	}

	private function getClientSocketName( $clientSocket ) : string
	{
		$clientSocketName = stream_socket_get_name( $clientSocket, true );

		if ( empty( $clientSocketName ) )
		{
			throw new RuntimeException(
				sprintf(
					'Failed to get client socket name at server socket %s.',
					$this->socketAddress->getSocketAddress()
				)
			);
		}

		return $clientSocketName;
	}

	private function makeClientSocketNonBlocking( string $clientSocketName, $clientSocket ) : void
	{
		if ( !stream_set_blocking( $clientSocket, false ) )
		{
			throw new RuntimeException(
				sprintf(
					'Failed to make client socket at %s non-blocking at server socket %s.',
					$clientSocketName,
					$this->socketAddress->getSocketAddress()
				)
			);
		}
	}
}
