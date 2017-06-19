<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers;

use PHPMQ\Server\EventHandlers\Exceptions\EventListenerMethodNotCallableException;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\HandlesEvents;
use Psr\Log\LoggerAwareTrait;

/**
 * Class AbstractEventHandler
 * @package PHPMQ\Server\EventHandlers
 */
abstract class AbstractEventHandler implements HandlesEvents
{
	use LoggerAwareTrait;

	final public function acceptsEvent( CarriesEventData $event ) : bool
	{
		return in_array( get_class( $event ), $this->getAcceptedEvents(), true );
	}

	abstract protected function getAcceptedEvents() : array;

	/**
	 * @param CarriesEventData $event
	 *
	 * @throws \PHPMQ\Server\EventHandlers\Exceptions\EventListenerMethodNotCallableException
	 */
	final public function notify( CarriesEventData $event ) : void
	{
		$classNameParts = explode( '\\', get_class( $event ) );
		$eventClass     = end( $classNameParts );
		$methodName     = sprintf( 'when%s', $eventClass );

		if ( !is_callable( [ $this, $methodName ] ) )
		{
			throw new EventListenerMethodNotCallableException(
				'Method ' . $methodName . ' is not callable on ' . get_class( $this )
			);
		}

		$this->$methodName( $event );
	}
}
