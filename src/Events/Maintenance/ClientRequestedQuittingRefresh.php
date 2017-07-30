<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Commands\QuitRefreshCommand;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ClientRequestedQuittingRefresh
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedQuittingRefresh implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var TracksStreams */
	private $loop;

	/** @var QuitRefreshCommand */
	private $quitRefreshCommand;

	public function __construct( TransfersData $stream, TracksStreams $loop, QuitRefreshCommand $quitRefreshCommand )
	{
		$this->stream             = $stream;
		$this->loop               = $loop;
		$this->quitRefreshCommand = $quitRefreshCommand;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getLoop() : TracksStreams
	{
		return $this->loop;
	}

	public function getQuitRefreshCommand() : QuitRefreshCommand
	{
		return $this->quitRefreshCommand;
	}
}
