<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Constants\AnsiColors;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;

/**
 * Class CliWriter
 * @package PHPMQ\Server
 */
final class CliWriter implements PreparesOutputForCli
{
	/** @var string */
	private $output = '';

	/** @var int */
	private $terminalWidth = 0;

	/** @var int */
	private $terminalHeight = 0;

	public function clearScreen() : PreparesOutputForCli
	{
		$this->updateTerminalWidth();

		$this->output = "\e[2J\e[0;0H\r\n";
		$this->output .= "\e[30;42m PHP \e[37;41m MQ \e[30;42m";
		$this->output .= str_repeat( ' ', $this->terminalWidth - 9 );
		$this->output .= "\e[0m\r\n\n";

		return $this;
	}

	private function updateTerminalWidth() : void
	{
		$this->terminalWidth = (int)exec( 'tput cols' );
	}

	private function updateTerminalHeight() : void
	{
		$this->terminalHeight = (int)exec( 'tput lines' );
	}

	public function write( string $content, string ...$args ) : PreparesOutputForCli
	{
		$this->output .= sprintf( $content, ...$args );

		return $this;
	}

	public function writeLn( string $content, string ...$args ) : PreparesOutputForCli
	{
		return $this->write( $content . "\n", ...$args );
	}

	public function writeFileContent( string $filePath ) : PreparesOutputForCli
	{
		$this->output .= file_get_contents( $filePath );

		return $this;
	}

	public function get() : string
	{
		$this->output .= "\n<fg:blue>phpmq<:fg> > ";

		$cliOutput = str_replace( array_keys( AnsiColors::COLORS ), AnsiColors::COLORS, $this->output );

		$this->output = '';

		return $cliOutput;
	}
}
