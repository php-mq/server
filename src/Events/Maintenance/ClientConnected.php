<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientConnected
 * @package PHPMQ\Server\Events\Maintenance
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
