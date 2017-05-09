<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Interfaces;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface ListensToEvents
 * @package PHPMQ\Server\Interfaces
 */
interface ListensToEvents extends LoggerAwareInterface
{
	public function acceptsEvent( CarriesEventData $event ) : bool;

	public function notify( CarriesEventData $event ) : void;
}
