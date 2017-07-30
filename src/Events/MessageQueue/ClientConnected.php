<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ClientConnected
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientConnected implements CarriesEventData
{
	/** @var TransfersData */
	private $stream;

	public function __construct( TransfersData $stream )
	{
		$this->stream = $stream;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}
}
