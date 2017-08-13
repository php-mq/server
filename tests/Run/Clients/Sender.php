<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run\Clients;

use PHPMQ\Protocol\Interfaces\ProvidesMessageData;
use PHPMQ\Stream\Constants\ChunkSize;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class Sender
 * @package PHPMQ\Server\Tests\Run\Clients
 */
final class Sender
{
	/** @var TransfersData */
	private $stream;

	public function __construct( TransfersData $stream )
	{
		$this->stream = $stream;
	}

	public function writeMessage( ProvidesMessageData $message ) : void
	{
		$this->stream->writeChunked( $message->toString(), ChunkSize::WRITE );
	}

	public function disconnect() : void
	{
		$this->stream->shutDown();
		$this->stream->close();
	}
}
