<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use PHPMQ\Server\Interfaces\IdentifiesStream;

/**
 * Interface TransfersData
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface TransfersData
{
	public function getStreamId() : IdentifiesStream;

	public function read( int $length ) : string;

	public function readChunked( int $length, int $chunkSize ) : string;

	public function write( string $content ) : int;

	public function writeChunked( string $content, int $chunkSize ) : int;

	public function collectRawStream( array &$rawStreams ) : void;

	public function acceptConnection() : ?TransfersData;

	public function hasUnreadBytes() : bool;

	public function close() : void;

	public function shutDown() : void;
}
