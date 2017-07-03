<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Timers;

use PHPMQ\Server\Timers\Interfaces\TimesExecution;

/**
 * Class Timers
 * @package PHPMQ\Server\Timers
 */
final class Timers
{
	private $time;

	private $timers;

	private $scheduler;

	public function __construct()
	{
		$this->timers    = new \SplObjectStorage();
		$this->scheduler = new \SplPriorityQueue();
	}

	public function updateTime() : float
	{
		return $this->time = microtime( true );
	}

	public function getTime() : float
	{
		return $this->time ? : $this->updateTime();
	}

	public function add( TimesExecution $timer ) : void
	{
		$interval    = $timer->getInterval();
		$scheduledAt = $interval + microtime( true );
		$this->timers->attach( $timer, $scheduledAt );
		$this->scheduler->insert( $timer, -$scheduledAt );
	}

	public function removeByStream( $stream ) : void
	{
		/** @var TimesExecution $timer */
		foreach ( $this->timers as $timer )
		{
			if ( $stream === $timer->getStream() )
			{
				$this->timers->detach( $timer );
			}
		}
	}

	public function contains( TimesExecution $timer ) : bool
	{
		return $this->timers->contains( $timer );
	}

	public function cancel( TimesExecution $timer ) : void
	{
		$this->timers->detach( $timer );
	}

	public function getFirst() : ?TimesExecution
	{
		while ( $this->scheduler->count() )
		{
			$timer = $this->scheduler->top();

			if ( $this->timers->contains( $timer ) )
			{
				return $this->timers[ $timer ];
			}

			$this->scheduler->extract();
		}

		return null;
	}

	public function isEmpty() : bool
	{
		return count( $this->timers ) === 0;
	}

	public function tick() : void
	{
		$time = $this->updateTime();

		while ( !$this->scheduler->isEmpty() )
		{
			/** @var TimesExecution $timer */
			$timer = $this->scheduler->top();

			if ( !isset( $this->timers[ $timer ] ) )
			{
				$this->scheduler->extract();
				$this->timers->detach( $timer );
				continue;
			}

			if ( $this->timers[ $timer ] >= $time )
			{
				break;
			}

			$this->scheduler->extract();

			call_user_func( $timer->getCallback(), $timer->getStream() );

			if ( isset( $this->timers[ $timer ] ) && $timer->isPeriodic() )
			{
				$scheduledAt            = $timer->getInterval() + $time;
				$this->timers[ $timer ] = $scheduledAt;
				$this->scheduler->insert( $timer, -$scheduledAt );
			}
			else
			{
				$this->timers->detach( $timer );
			}
		}
	}
}
