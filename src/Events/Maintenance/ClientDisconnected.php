<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ClientDisconnected
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientDisconnected implements CarriesEventData
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
