<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Commands\SearchQueueCommand;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ClientRequestedQueueSearch
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedQueueSearch implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var SearchQueueCommand */
	private $searchQueueCommand;

	public function __construct( TransfersData $stream, SearchQueueCommand $searchQueueCommand )
	{
		$this->stream             = $stream;
		$this->searchQueueCommand = $searchQueueCommand;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getSearchQueueCommand() : SearchQueueCommand
	{
		return $this->searchQueueCommand;
	}
}
