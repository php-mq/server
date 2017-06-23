<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\HandlesEvents;
use PHPMQ\Server\Interfaces\PublishesEvents;
use Psr\Log\LoggerInterface;

/**
 * Class EventBus
 * @package PHPMQ\Server
 */
final class EventBus implements PublishesEvents
{
	/** @var array|HandlesEvents[] */
	private $eventHandlers;

	/** @var LoggerInterface */
	private $logger;

	public function __construct( LoggerInterface $logger )
	{
		$this->eventHandlers = [];
		$this->logger        = $logger;
	}

	public function addEventHandlers( HandlesEvents ...$eventHandlers ) : void
	{
		foreach ( $eventHandlers as $eventHandler )
		{
			$eventHandler->setLogger( $this->logger );

			$this->eventHandlers[] = $eventHandler;
		}
	}

	public function publishEvent( CarriesEventData $event ) : void
	{
		foreach ( $this->eventHandlers as $eventHandler )
		{
			if ( $eventHandler->acceptsEvent( $event ) )
			{
				$eventHandler->notify( $event );
			}
		}
	}
}
