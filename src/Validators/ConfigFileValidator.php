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
	private $passed;

	/** @var array */
	private $messages;

	/** @var array|\LibXMLError[] */
	private $xmlErrors;

	public function __construct( string $configFilePath )
	{
		$this->configFilePath = $configFilePath;
		$this->passed         = false;
		$this->messages       = [];

		libxml_use_internal_errors( true );

		$this->xml       = simplexml_load_file( $configFilePath );
		$this->xmlErrors = libxml_get_errors();

		libxml_use_internal_errors( false );
	}

	public function failed() : bool
	{
		$this->checkForParseErrors();

		$this->passed && $this->checkMessageQueueServer();
		$this->passed && $this->checkMaintenanceServer();

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

		if ( $this->elementExists( $networkXPath )
		     && (!$this->configValueExists( $networkXPath, 'host' )
		         || !$this->configValueExists( $networkXPath, 'port' ))
		)
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid message queue server socket config. Host and port must be configured.' );

			return;
		}

		if ( $this->elementExists( $unixXPath ) && !$this->configValueExists( $unixXPath, 'path' ) )
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

	private function configValueExists( string $baseXPath, string $name ) : bool
	{
		return (count( $this->xml->xpath( $baseXPath . "/config[@name=\"{$name}\"][@value]" ) ) >= 1);
	}

	private function checkMaintenanceServer() : void
	{
		$baseXPath = '/PHPMQ/servers/maintenance';

		if ( !$this->elementExists( $baseXPath ) )
		{
			return;
		}

		$networkXPath = $baseXPath . '/network';
		$unixXPath    = $baseXPath . '/unix';

		if ( !$this->elementExists( $networkXPath ) && !$this->elementExists( $unixXPath ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid maintenance server socket type. Allowed: network, unix' );

			return;
		}

		if ( $this->elementExists( $networkXPath )
		     && (!$this->configValueExists( $networkXPath, 'host' )
		         || !$this->configValueExists( $networkXPath, 'port' ))
		)
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid maintenance server socket config. Host and port must be configured.' );

			return;
		}

		if ( $this->elementExists( $unixXPath ) && !$this->configValueExists( $unixXPath, 'path' ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid maintenance server socket config. Path must be configured.' );

			return;
		}
	}

	public function getMessages() : array
	{
		return $this->messages;
	}
}
