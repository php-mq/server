<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Commands\ClearScreenCommand;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ClientRequestedClearScreen
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedClearScreen implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var ClearScreenCommand */
	private $clearScreenCommand;

	public function __construct( TransfersData $stream, ClearScreenCommand $clearScreenCommand )
	{
		$this->stream             = $stream;
		$this->clearScreenCommand = $clearScreenCommand;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getClearScreenCommand() : ClearScreenCommand
	{
		return $this->clearScreenCommand;
	}
}
