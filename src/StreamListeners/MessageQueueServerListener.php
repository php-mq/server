<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Events\MessageQueue\ClientConnected;
use PHPMQ\Server\Interfaces\PublishesEvents;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MessageQueueServerListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MessageQueueServerListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

	/** @var PublishesEvents */
	private $eventBus;

	/** @var MessageQueueClientListener */
	private $messageQueueClientListener;

	public function __construct( PublishesEvents $eventBus )
	{
		$this->eventBus                   = $eventBus;
		$this->messageQueueClientListener = new MessageQueueClientListener( $eventBus );
	}

	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		$clientStream = $stream->acceptConnection();

		if ( null === $clientStream )
		{
			return;
		}

		$this->messageQueueClientListener->setLogger( $this->logger );

		$loop->addReadStream( $clientStream, $this->messageQueueClientListener );

		$this->eventBus->publishEvent( new ClientConnected( $clientStream ) );
	}
}
