<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\ListensToEvents;
use PHPMQ\Server\Interfaces\PublishesEvents;
use Psr\Log\LoggerAwareTrait;

/**
 * Class EventBus
 * @package PHPMQ\Server\Endpoint
 */
final class EventBus implements PublishesEvents
{
	use LoggerAwareTrait;

	/** @var array|ListensToEvents[] */
	private $eventListeners;

	public function __construct()
	{
		$this->eventListeners = [];
	}

	public function addEventListeners( ListensToEvents ...$eventListeners ) : void
	{
		foreach ( $eventListeners as $eventListener )
		{
			$eventListener->setLogger( $this->logger );

			$this->eventListeners[] = $eventListener;
		}
	}

	public function publishEvent( CarriesEventData $event ) : void
	{
		foreach ( $this->eventListeners as $eventListener )
		{
			if ( $eventListener->acceptsEvent( $event ) )
			{
				$eventListener->notify( $event );
			}
		}
	}
}
