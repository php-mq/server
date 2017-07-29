<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Protocol\Messages\MessageClientToServer;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientSentMessage
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientSentMessage implements CarriesEventData
{
	/** @var MessageClientToServer */
	private $messageClientToServer;

	/** @var TransfersData */
	private $stream;

	/** @var TracksStreams */
	private $loop;

	public function __construct(
		MessageClientToServer $messageClientToServer,
		TransfersData $stream,
		TracksStreams $loop
	)
	{
		$this->messageClientToServer = $messageClientToServer;
		$this->stream                = $stream;
		$this->loop                  = $loop;
	}

	public function getMessageClientToServer() : MessageClientToServer
	{
		return $this->messageClientToServer;
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
