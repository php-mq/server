<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\HandlesEvents;
use Psr\Log\LoggerAwareTrait;

/**
 * Trait EventHandlerMocking
 * @package PHPMQ\Server\Tests\Unit\Fixtures\Traits
 */
trait EventHandlerMocking
{
	protected function getEventHandler() : HandlesEvents
	{
		return new class implements HandlesEvents
		{
			use LoggerAwareTrait;

			public function acceptsEvent( CarriesEventData $event ) : bool
			{
				return true;
			}

			public function notify( CarriesEventData $event ) : void
			{
				echo get_class( $event ) . "\n";
			}
		};
	}
}
