<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Unit;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use SpomkyLabs\LexikJoseBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testServerNameIsNotProvided()
    {
        $this->assertConfigurationIsInvalid(
            [
                [],
            ],
            'The child node "server_name" at path "lexik_jose" must be configured.'
        );
    }

    public function testSignatureKeyIsNotProvided()
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'server_name' => 'foo_bar',
                ],
            ],
            'The child node "key_storage_folder" at path "lexik_jose" must be configured.'
        );
    }

    public function testSignatureAlgorithmIsNotProvided()
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'server_name'        => 'foo_bar',
                    'key_storage_folder' => '/tmp',
                ],
            ],
            'The child node "signature_key_configuration" at path "lexik_jose" must be configured.'
        );
    }

    public function testConfigurationIsValidForSignatureOnly()
    {
        $this->assertConfigurationIsValid(
            [
                [
                    'server_name'                 => 'foo_bar',
                    'key_storage_folder'          => '/tmp',
                    'signature_key_configuration' => ['foo_config'],
                ],
            ]
        );
    }

    public function testEncryptionOptionsNotProvided()
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'server_name'                 => 'foo_bar',
                    'key_storage_folder'          => '/tmp',
                    'signature_key_configuration' => ['foo_config'],
                    'encryption'                  => [
                        'enabled' => true,
                    ],
                ],
            ],
            'The child node "encryption_key_configuration" at path "lexik_jose.encryption" must be configured.'
        );
    }

    public function testEncryptionOptionsNotProvided1()
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'server_name'                 => 'foo_bar',
                    'key_storage_folder'          => '/tmp',
                    'signature_key_configuration' => ['foo_config'],
                    'encryption'                  => [
                        'enabled'                      => true,
                        'encryption_key_configuration' => ['foo_config'],
                    ],
                ],
            ],
            'Invalid configuration for path "lexik_jose.encryption": The configuration options for encryption are invalid.'
        );
    }

    public function testEncryptionOptionsNotProvided2()
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'server_name'                 => 'foo_bar',
                    'key_storage_folder'          => '/tmp',
                    'signature_key_configuration' => ['foo_config'],
                    'encryption'                  => [
                        'enabled'                      => true,
                        'encryption_key_configuration' => ['foo_config'],
                        'key_encryption_algorithm'     => 'foo_key_encryption_algorithm',
                    ],
                ],
            ],
            'Invalid configuration for path "lexik_jose.encryption": The configuration options for encryption are invalid.'
        );
    }

    public function testConfigurationIsValidForSignatureAndEncryption()
    {
        $this->assertConfigurationIsValid(
            [
                [
                    'server_name'                 => 'foo_bar',
                    'key_storage_folder'          => '/tmp',
                    'signature_key_configuration' => ['foo_config'],
                    'encryption'                  => [
                        'enabled'                      => true,
                        'encryption_key_configuration' => ['foo_config'],
                        'key_encryption_algorithm'     => 'foo_key_encryption_algorithm',
                        'content_encryption_algorithm' => 'foo_content_encryption_algorithm',
                    ],
                ],
            ]
        );
    }
}
