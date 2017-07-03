<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface ListensForStreamActivity
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface ListensForStreamActivity extends LoggerAwareInterface
{
	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void;
}
