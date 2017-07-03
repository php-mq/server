<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;

/**
 * Class ClientSentConsumeResquest
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientSentConsumeResquest implements CarriesEventData
{
	/** @var ConsumeRequest */
	private $consumeRequest;

	/** @var TransfersData */
	private $stream;

	/** @var TracksStreams */
	private $loop;

	public function __construct( ConsumeRequest $consumeRequest, TransfersData $stream, TracksStreams $loop )
	{
		$this->consumeRequest = $consumeRequest;
		$this->stream         = $stream;
		$this->loop           = $loop;
	}

	public function getConsumeRequest() : ConsumeRequest
	{
		return $this->consumeRequest;
	}

	public function getStream() : TransfersData
	{
		return $this->stream;
	}

	public function getLoop() : TracksStreams
	{
		return $this->loop;
	}
}
