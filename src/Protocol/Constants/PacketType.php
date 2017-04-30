<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol\Constants;

/**
 * Class PacketType
 * @package hollodotme\PHPMQ\Protocol\Constants
 */
abstract class PacketType
{
	public const QUEUE_NAME            = 1;

	public const MESSAGE_CONTENT       = 2;

	public const MESSAGE_ID            = 3;

	public const MESSAGE_CONSUME_COUNT = 4;
}
