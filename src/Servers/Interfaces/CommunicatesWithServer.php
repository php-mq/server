<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Interfaces;

use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;

/**
 * Interface CommunicatesWithServer
 * @package PHPMQ\Server\Servers\Interfaces
 */
interface CommunicatesWithServer
{
	public function getClientId(): IdentifiesClient;

	public function read( int $bytes ): string;

	public function collectSocket( array &$sockets ): void;

	public function hasUnreadData(): bool;

	public function write( string $data ): int;

	public function shutDown(): void;
}
