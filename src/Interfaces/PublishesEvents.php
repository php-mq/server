<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Interfaces;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface PublishesEvents
 * @package PHPMQ\Server\Interfaces
 */
interface PublishesEvents
{
	public function addEventHandlers( HandlesEvents ...$eventListeners ) : void;

	public function publishEvent( CarriesEventData $event ) : void;
}
