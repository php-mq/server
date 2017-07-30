<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ClientSentUnknownCommand
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientSentUnknownCommand implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	/** @var string */
	private $unknownCommandString;

	public function __construct( TransfersData $stream, string $unknownCommandString )
	{
		$this->stream               = $stream;
		$this->unknownCommandString = $unknownCommandString;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getUnknownCommandString() : string
	{
		return $this->unknownCommandString;
	}
}
