<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Constants;

/**
 * Class SocketType
 * @package PHPMQ\Server\Servers\Constants
 */
abstract class SocketType
{
	public const TCP  = 1;

	public const TLS  = 2;

	public const UNIX = 4;
}
