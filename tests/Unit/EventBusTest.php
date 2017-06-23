<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit;

use PHPMQ\Server\EventBus;
use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\Tests\Unit\EventHandlers\Fixtures\TestEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class EventBusTest extends TestCase
{
	public function testCanPublishEvents(): void
	{
		$logger   = new NullLogger();
		$eventBus = new EventBus( $logger );

		$handler = new class extends AbstractEventHandler
		{
			protected function getAcceptedEvents(): array
			{
				return [ TestEvent::class ];
			}

			protected function whenTestEvent( TestEvent $event ): void
			{
				echo 'TestEvent was handled.';
			}
		};

		$eventBus->addEventHandlers( $handler );

		$this->expectOutputString( 'TestEvent was handled.' );

		$eventBus->publishEvent( new TestEvent() );
	}
}
