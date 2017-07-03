<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;

/**
 * Class ClientSentAcknowledgement
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientSentAcknowledgement implements CarriesEventData
{
	/** @var Acknowledgement */
	private $acknowledgement;

	/** @var TransfersData */
	private $stream;

	/** @var TracksStreams */
	private $loop;

	public function __construct( Acknowledgement $acknowledgement, TransfersData $stream, TracksStreams $loop )
	{
		$this->acknowledgement = $acknowledgement;
		$this->stream          = $stream;
		$this->loop            = $loop;
	}

	public function getAcknowledgement() : Acknowledgement
	{
		return $this->acknowledgement;
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
