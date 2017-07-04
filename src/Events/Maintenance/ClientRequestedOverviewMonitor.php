<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Commands\StartMonitorCommand;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedOverviewMonitor
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedOverviewMonitor implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var TracksStreams */
	private $loop;

	/** @var StartMonitorCommand */
	private $startMonitorCommand;

	public function __construct( TransfersData $stream, TracksStreams $loop, StartMonitorCommand $startMonitorCommand )
	{
		$this->stream              = $stream;
		$this->loop                = $loop;
		$this->startMonitorCommand = $startMonitorCommand;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getLoop() : TracksStreams
	{
		return $this->loop;
	}

	public function getStartMonitorCommand() : StartMonitorCommand
	{
		return $this->startMonitorCommand;
	}
}
