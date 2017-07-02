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
	private $terminalWidth = 80;

	/** @var int */
	private $terminalHeight = 24;

	public function clearScreen( string $title ) : PreparesOutputForCli
	{
		$this->output = "\e[2J\e[0;0H\n";
		$this->output .= "\e[30;42m PHP \e[37;41m MQ \e[30;42m";
		$this->output .= '- ' . $title;
		$this->output .= str_repeat( ' ', $this->terminalWidth - 11 - mb_strlen( $title ) );
		$this->output .= "\e[0m\n\n";

		return $this;
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

	public function getTerminalWidth() : int
	{
		return $this->terminalWidth;
	}

	public function getTerminalHeight() : int
	{
		return $this->terminalHeight;
	}

	public function getOutput() : string
	{
		$cliOutput = str_replace( array_keys( AnsiColors::COLORS ), AnsiColors::COLORS, $this->output );

		$this->output = '';

		return $cliOutput;
	}

	public function getInteractiveOutput() : string
	{
		$this->output .= "\n<fg:blue>phpmq<:fg> > ";

		return $this->getOutput();
	}
}
