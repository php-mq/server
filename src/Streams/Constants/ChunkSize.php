<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Streams\Constants;

/**
 * Class ChunkSize
 * @package PHPMQ\Server\Streams\Constants
 */
abstract class ChunkSize
{
	public const READ  = 1024;

	public const WRITE = 1024;
}
