<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

$certificateData = [
	'countryName'            => 'DE',
	'stateOrProvinceName'    => 'Saxony',
	'localityName'           => 'Dresden',
	'organizationName'       => 'PHPMQ',
	'organizationalUnitName' => 'Development',
	'commonName'             => 'phpmq.org',
	'emailAddress'           => 'ca@phpmq.org',
];

// Generate certificate
$privateKey  = openssl_pkey_new();
$certificate = openssl_csr_new( $certificateData, $privateKey );
$certificate = openssl_csr_sign( $certificate, null, $privateKey, 7300 );

// Generate PEM file
# Optionally change the passphrase from 'comet' to whatever you want, or leave it empty for no passphrase
$pemPassphrase = 'root';
$pem           = [];
openssl_x509_export( $certificate, $pem[0] );
openssl_pkey_export( $privateKey, $pem[1], $pemPassphrase );
$pem = implode( '', $pem );

// Save PEM file
$pemFile = __DIR__ . '/server.pem';
file_put_contents( $pemFile, $pem );
