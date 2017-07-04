<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Commands\FlushQueueCommand;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedFlushingQueue
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedFlushingQueue implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var FlushQueueCommand */
	private $flushQueueCommand;

	public function __construct( TransfersData $stream, FlushQueueCommand $flushQueueCommand )
	{
		$this->stream            = $stream;
		$this->flushQueueCommand = $flushQueueCommand;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getFlushQueueCommand() : FlushQueueCommand
	{
		return $this->flushQueueCommand;
	}
}
