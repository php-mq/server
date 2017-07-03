<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientGotReadyForConsumingMessages
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientGotReadyForConsumingMessages implements CarriesEventData
{
	/** @var resource */
	private $stream;

	/** @var TracksStreams */
	private $loop;

	public function __construct( $stream, TracksStreams $loop )
	{
		$this->stream = $stream;
		$this->loop   = $loop;
	}

	public function getStream()
	{
		return $this->stream;
	}

	public function getLoop() : TracksStreams
	{
		return $this->loop;
	}
}
