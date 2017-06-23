<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Interfaces;

/**
 * Interface PreparesOutputForCli
 * @package PHPMQ\Server\Interfaces
 */
interface PreparesOutputForCli
{
	public function clearScreen() : PreparesOutputForCli;

	public function write( string $content, string ...$args ) : PreparesOutputForCli;

	public function writeLn( string $content, string ...$args ) : PreparesOutputForCli;

	public function writeFileContent( string $filePath ) : PreparesOutputForCli;

	public function get() : string;
}
