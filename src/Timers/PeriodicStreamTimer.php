<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Timers;

use PHPMQ\Server\Timers\Interfaces\TimesExecution;

/**
 * Class PeriodicStreamTimer
 * @package PHPMQ\Server\Timers
 */
final class PeriodicStreamTimer implements TimesExecution
{
	/** @var resource */
	private $stream;

	/** @var float */
	private $interval;

	/** @var callable */
	private $callback;

	public function __construct( $stream, float $interval, callable $callback )
	{
		$this->stream   = $stream;
		$this->interval = $interval;
		$this->callback = $callback;
	}

	public function getStream()
	{
		return $this->stream;
	}

	public function getInterval() : float
	{
		return $this->interval;
	}

	public function getCallback() : callable
	{
		return $this->callback;
	}

	public function isPeriodic() : bool
	{
		return true;
	}
}
