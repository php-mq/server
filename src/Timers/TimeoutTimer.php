<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Timers;

/**
 * Class TimeoutTimer
 * @package PHPMQ\Server\Timers
 */
final class TimeoutTimer
{
	private const MICROSECOND_FACTOR = 1000000;

	/** @var float */
	private $startTime;

	/** @var float */
	private $timeout;

	public function __construct( int $microseconds )
	{
		$this->timeout = round( $microseconds / self::MICROSECOND_FACTOR );
	}

	public function start() : void
	{
		if ( null === $this->startTime )
		{
			$this->startTime = microtime( true );
		}
	}

	public function reset() : void
	{
		$this->startTime = null;
	}

	public function timedOut() : bool
	{
		if ( null === $this->startTime )
		{
			return false;
		}

		return (($this->startTime + $this->timeout) < microtime( true ));
	}
}
