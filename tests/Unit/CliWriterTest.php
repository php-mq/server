<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit;

use PHPMQ\Server\CliWriter;
use PHPUnit\Framework\TestCase;

final class CliWriterTest extends TestCase
{
	public function testCanClearScreen() : void
	{
		$cliWriter = new CliWriter( '1.2.3' );
		$cliWriter->clearScreen( 'Unit-Test' );

		$expectedOutputSubstr = preg_quote( "\e[34mphpmq\e[39m > ", '#' );

		$this->assertRegExp( "#{$expectedOutputSubstr}$#", $cliWriter->getInteractiveOutput() );
	}

	public function testCanWriteContent() : void
	{
		$cliWriter = new CliWriter( '1.2.3' );
		$cliWriter->write( 'Unit-Test' );

		$expectedOutputSubstr = preg_quote( 'Unit-Test', '#' );

		$this->assertRegExp( "#^{$expectedOutputSubstr}#", $cliWriter->getOutput() );
	}

	public function testCanWriteContentWithFormat() : void
	{
		$cliWriter = new CliWriter( '1.2.3' );
		$cliWriter->write( 'Unit-Test %03d', '20' );

		$expectedOutputSubstr = preg_quote( 'Unit-Test 020', '#' );

		$this->assertRegExp( "#^{$expectedOutputSubstr}#", $cliWriter->getOutput() );
	}

	public function testCanWriteLine() : void
	{
		$cliWriter = new CliWriter( '1.2.3' );
		$cliWriter->writeLn( 'Unit-Test' );

		$expectedOutputSubstr = preg_quote( "Unit-Test\n", '#' );

		$this->assertRegExp( "#^{$expectedOutputSubstr}#", $cliWriter->getOutput() );
	}

	public function testCanWriteLineWithFormat() : void
	{
		$cliWriter = new CliWriter( '1.2.3' );
		$cliWriter->writeLn( 'Unit-Test %03d', '20' );

		$expectedOutputSubstr = preg_quote( "Unit-Test 020\n", '#' );

		$this->assertRegExp( "#^{$expectedOutputSubstr}#", $cliWriter->getOutput() );
	}

	public function testCanWriteFileContent() : void
	{
		$cliWriter = new CliWriter( '1.2.3' );
		$cliWriter->writeFileContent( __FILE__ );

		$expectedOutputSubstr = preg_quote( file_get_contents( __FILE__ ), '#' );

		$this->assertRegExp( "#^{$expectedOutputSubstr}#", $cliWriter->getOutput() );
	}

	public function testCanGetTerminalWidthAndHeight() : void
	{
		$cliWriter = new CliWriter( '1.2.3' );

		$this->assertSame( 80, $cliWriter->getTerminalWidth() );
		$this->assertSame( 24, $cliWriter->getTerminalHeight() );
	}
}
