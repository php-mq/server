<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Constants;

/**
 * Class SocketType
 * @package PHPMQ\Server\Endpoint\Constants
 */
abstract class SocketDomain
{
	public const UNIX        = AF_UNIX;

	public const IP4_NETWORK = AF_INET;

	public const IP6_NETWORK = AF_INET6;
}
