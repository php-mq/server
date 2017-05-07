<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Constants;

/**
 * Class PacketLength
 * @package PHPMQ\Server\Protocol\Constants
 */
abstract class PacketLength
{
	public const MESSAGE_HEADER = 8;

	public const PACKET_HEADER  = 32;
}
