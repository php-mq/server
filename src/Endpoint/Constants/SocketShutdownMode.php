<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Constants;

/**
 * Class SocketShutdownMode
 * @package PHPMQ\Server\Endpoint\Constants
 */
abstract class SocketShutdownMode
{
	public const READING         = 0;

	public const WRITING         = 1;

	public const READING_WRITING = 2;
}
