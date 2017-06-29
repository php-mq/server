<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Validators;

use PHPMQ\Server\Validators\ArgumentValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class ArgumentValidatorTest
 * @package PHPMQ\Server\Tests\Unit\Validators
 */
final class ArgumentValidatorTest extends TestCase
{
	public function testValidationFailsWhenConfigFileDoesNotExist() : void
	{
		$validator = new ArgumentValidator(
			[
				'/script.php',
				'/not/existing/file.xml',
			]
		);

		$expectedMessages = [
			'<bg:red>ERROR:<:bg> Could not read configuration file: "/not/existing/file.xml". File not found.',
		];

		$this->assertTrue( $validator->failed() );
		$this->assertEquals( $expectedMessages, $validator->getMessages() );
	}

	public function testValidationSucceedsWhenConfigFilesExists() : void
	{
		$validator = new ArgumentValidator(
			[
				'/script.php',
				dirname( __DIR__, 3 ) . '/config/phpmq.default.xml',
			]
		);

		$this->assertFalse( $validator->failed() );
		$this->assertEquals( [], $validator->getMessages() );
	}
}
