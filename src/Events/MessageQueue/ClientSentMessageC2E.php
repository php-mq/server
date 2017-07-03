<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\MessageC2E;

/**
 * Class ClientSentMessageC2E
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientSentMessageC2E implements CarriesEventData
{
	/** @var MessageC2E */
	private $messageC2E;

	/** @var TransfersData */
	private $stream;

	/** @var TracksStreams */
	private $loop;

	public function __construct( MessageC2E $messageC2E, TransfersData $stream, TracksStreams $loop )
	{
		$this->messageC2E = $messageC2E;
		$this->stream     = $stream;
		$this->loop       = $loop;
	}

	public function getMessageC2E() : MessageC2E
	{
		return $this->messageC2E;
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
