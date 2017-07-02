<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientConnected
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientConnected implements CarriesEventData
{
	/** @var resource */
	private $stream;

	public function __construct( $stream )
	{
		$this->stream = $stream;
	}

	public function getStream()
	{
		return $this->stream;
	}
}
