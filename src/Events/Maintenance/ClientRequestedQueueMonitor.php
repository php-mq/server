<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Commands\ShowQueueCommand;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedQueueMonitor
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedQueueMonitor implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var TracksStreams */
	private $loop;

	/** @var ShowQueueCommand */
	private $showQueueCommand;

	public function __construct( TransfersData $stream, TracksStreams $loop, ShowQueueCommand $showQueueCommand )
	{
		$this->stream           = $stream;
		$this->loop             = $loop;
		$this->showQueueCommand = $showQueueCommand;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getLoop() : TracksStreams
	{
		return $this->loop;
	}

	public function getShowQueueCommand() : ShowQueueCommand
	{
		return $this->showQueueCommand;
	}
}
