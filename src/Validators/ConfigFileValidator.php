<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Validators;

use PHPMQ\Server\Validators\Interfaces\ValidatesEnvironment;

/**
 * Class ConfigFileValidator
 * @package PHPMQ\Server\Validators
 */
final class ConfigFileValidator implements ValidatesEnvironment
{
	/** @var string */
	private $configFilePath;

	/** @var bool|\SimpleXMLElement */
	private $xml;

	/** @var bool */
	private $passed = false;

	/** @var array */
	private $messages = [];

	/** @var array|\LibXMLError[] */
	private $xmlErrors = [];

	public function __construct( string $configFilePath )
	{
		$this->configFilePath = $configFilePath;

		libxml_use_internal_errors( true );

		$this->xml       = simplexml_load_file( $configFilePath );
		$this->xmlErrors = libxml_get_errors();

		libxml_use_internal_errors( false );
	}

	public function failed() : bool
	{
		$this->checkForParseErrors();

		$this->passed && $this->checkMessageQueueServer();

		return !$this->passed;
	}

	private function checkForParseErrors() : void
	{
		if ( $this->xml === false )
		{
			$this->addErrorMessage( 'Could not read configuration file: "%s". Parse errors:', $this->configFilePath );
			$this->addXmlErrors();

			return;
		}

		$this->passed = true;
	}

	private function addErrorMessage( string $format, ...$params ) : void
	{
		$this->messages[] = sprintf( '<bg:red>ERROR:<:bg> ' . $format, ...$params );
	}

	private function addXmlErrors() : void
	{
		/** @var \LibXMLError $xmlError */
		foreach ( $this->xmlErrors as $xmlError )
		{
			$this->addErrorMessage(
				'[%s] %s in line %d and column %d',
				$xmlError->code,
				$xmlError->message,
				$xmlError->line,
				$xmlError->column
			);
		}
	}

	private function checkMessageQueueServer() : void
	{
		$baseXPath = '/PHPMQ/servers/messagequeue';

		if ( !$this->elementExists( $baseXPath ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Missing configuration part: servers/messagequeue' );

			return;
		}

		$networkXPath = $baseXPath . '/network';
		$unixXPath    = $baseXPath . '/unix';

		if ( !$this->elementExists( $networkXPath ) && !$this->elementExists( $unixXPath ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid message queue server socket type. Allowed: network, unix' );

			return;
		}

		$networkHostXPath = $networkXPath . '/config[@name="host"]';
		$networkPortXPath = $networkXPath . '/config[@name="port"]';

		if ( $this->elementExists( $networkXPath )
		     && (!$this->elementExists( $networkHostXPath ) || !$this->elementExists( $networkPortXPath )) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid message queue server socket config. Host and port must be configured.' );

			return;
		}

		$unixPathXPath = $unixXPath . '/config[@name="path"]';

		if ( $this->elementExists( $unixXPath ) && !$this->elementExists( $unixPathXPath ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid message queue server socket config. Path must be configured.' );

			return;
		}
	}

	private function elementExists( string $xpath ) : bool
	{
		return (count( $this->xml->xpath( $xpath ) ) >= 1);
	}

	public function getMessages() : array
	{
		return $this->messages;
	}
}
