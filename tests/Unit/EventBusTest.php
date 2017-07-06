<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit;

use PHPMQ\Server\EventBus;
use PHPMQ\Server\Tests\Unit\EventHandlers\Fixtures\TestEvent;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\EventHandlerMocking;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class EventBusTest extends TestCase
{
	use EventHandlerMocking;

	public function testCanPublishEvents() : void
	{
		$logger   = new NullLogger();
		$eventBus = new EventBus( $logger );

		$eventBus->addEventHandlers( $this->getEventHandler() );

		$this->expectOutputString( TestEvent::class . "\n" );

		$eventBus->publishEvent( new TestEvent() );
	}
}
