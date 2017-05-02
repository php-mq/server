<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Clients;

use hollodotme\PHPMQ\Clients\Interfaces\IdentifiesClient;
use hollodotme\PHPMQ\Endpoint\Interfaces\ConsumesMessages;
use hollodotme\PHPMQ\Exceptions\RuntimeException;
use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;

/**
 * Class Client
 * @package hollodotme\PHPMQ\Clients
 */
final class Client implements ConsumesMessages
{
	/** @var resource */
	private $socket;

	/** @var IdentifiesClient */
	private $clientId;

	/** @var string */
	private $buffer;

	/** @var bool */
	private $isDisconnected;

	/** @var array|IdentifiesMessage */
	private $consumedMessageIds;

	/** @var int */
	private $messageConsumeCount;

	public function __construct( IdentifiesClient $clientId, $socket )
	{
		$this->clientId            = $clientId;
		$this->socket              = $socket;
		$this->buffer              = '';
		$this->isDisconnected      = false;
		$this->consumedMessageIds  = [];
		$this->messageConsumeCount = 0;
	}

	public function getClientId() : IdentifiesClient
	{
		return $this->clientId;
	}

	public function collectSocket( array &$sockets ) : void
	{
		$sockets[ $this->clientId->toString() ] = $this->socket;
	}

	public function read() : string
	{
		$bytes = socket_recv( $this->socket, $this->buffer, 2048, MSG_DONTWAIT );

		if ( false !== $bytes )
		{
			if ( null === $this->buffer )
			{
				$this->isDisconnected = true;

				return '';
			}

			return $this->buffer;
		}

		throw new RuntimeException(
			'socket_recv() failed; reason: '
			. socket_strerror( socket_last_error( $this->socket ) )
		);
	}

	public function isDisconnected() : bool
	{
		return $this->isDisconnected;
	}

	public function updateConsumptionCount( int $messageCount ) : void
	{
		$this->messageConsumeCount = $messageCount;
	}

	public function canConsumeMessages() : bool
	{
		return ($this->getConsumableMessageCount() > 0);
	}

	public function getConsumableMessageCount() : int
	{
		return ($this->messageConsumeCount - count( $this->consumedMessageIds ));
	}

	public function consumeMessage( MessageE2C $message ) : void
	{
		$bytes = socket_write( $this->socket, $message->toString() );

		if ( false === $bytes )
		{
			throw new RuntimeException( 'Could not write message to client socket.' );
		}

		$this->consumedMessageIds[] = $message->getMessageId();
	}

	public function acknowledgeMessage( IdentifiesMessage $messageId ) : void
	{
		$this->consumedMessageIds = array_diff( $this->consumedMessageIds, [ $messageId ] );
	}
}
