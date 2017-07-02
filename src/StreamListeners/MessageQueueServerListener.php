<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Events\MessageQueue\ClientConnected;
use PHPMQ\Server\Interfaces\PublishesEvents;

/**
 * Class MessageQueueServerListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MessageQueueServerListener extends AbstractStreamListener
{
	/** @var PublishesEvents */
	private $eventBus;

	/** @var MessageQueueClientListener */
	private $messageQueueClientListener;

	public function __construct( PublishesEvents $eventBus )
	{
		$this->eventBus                   = $eventBus;
		$this->messageQueueClientListener = new MessageQueueClientListener( $eventBus );
	}

	protected function handleStreamActivity( $stream, TracksStreams $loop ) : void
	{
		$this->handleNewClient( $stream, $loop );
	}

	private function handleNewClient( $stream, TracksStreams $loop ) : void
	{
		$clientStream = @stream_socket_accept( $stream );

		if ( false === $clientStream )
		{
			return;
		}

		if ( !stream_set_blocking( $clientStream, false ) )
		{
			return;
		}

		$this->eventBus->publishEvent( new ClientConnected( $clientStream ) );

		$this->messageQueueClientListener->setLogger( $this->logger );

		$loop->addReadStream( $clientStream, $this->messageQueueClientListener->getListener() );
	}
}
