<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Commands\FlushAllQueuesCommand;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedFlushingAllQueues
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedFlushingAllQueues implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var FlushAllQueuesCommand */
	private $flushAllQueuesCommand;

	public function __construct( TransfersData $stream, FlushAllQueuesCommand $flushAllQueuesCommand )
	{
		$this->stream                = $stream;
		$this->flushAllQueuesCommand = $flushAllQueuesCommand;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getFlushAllQueuesCommand() : FlushAllQueuesCommand
	{
		return $this->flushAllQueuesCommand;
	}
}
