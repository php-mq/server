<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\EventHandlers;

use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\Tests\Unit\EventHandlers\Fixtures\TestEvent;
use PHPUnit\Framework\TestCase;

final class AbstractEventHandlerTest extends TestCase
{
	/**
	 * @expectedException \PHPMQ\Server\EventHandlers\Exceptions\EventHandlerMethodNotCallableException
	 */
	public function testNotCallableMethodThrowsException(): void
	{
		$handler = new class extends AbstractEventHandler
		{
			protected function getAcceptedEvents(): array
			{
				return [ TestEvent::class ];
			}
		};

		$handler->notify( new TestEvent() );
	}

	public function testCanCallHandlerMethodForEvent(): void
	{
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

		$this->expectOutputString( 'TestEvent was handled.' );

		$handler->notify( new TestEvent() );
	}

	public function testCanCheckIfHandlerAcceptsEvent(): void
	{
		$acceptingHandler = new class extends AbstractEventHandler
		{
			protected function getAcceptedEvents(): array
			{
				return [ TestEvent::class ];
			}
		};

		$notAcceptingHandler = new class extends AbstractEventHandler
		{
			protected function getAcceptedEvents(): array
			{
				return [];
			}
		};

		$this->assertTrue( $acceptingHandler->acceptsEvent( new TestEvent() ) );
		$this->assertFalse( $notAcceptingHandler->acceptsEvent( new TestEvent() ) );
	}
}
