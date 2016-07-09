<?php

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

    public function testSignatureKeyIsNotProvided()
    {
        $this->assertConfigurationIsInvalid(
            [
                [],
            ],
            'The child node "signature_key" at path "lexik_jose" must be configured.'
        );
    }

    public function testSignatureAlgorithmIsNotProvided()
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'signature_key' => 'foo_key',
                ],
            ],
            'The child node "signature_algorithm" at path "lexik_jose" must be configured.'
        );
    }

    public function testConfigurationIsValidForSignatureOnly()
    {
        $this->assertConfigurationIsValid(
            [
                [
                    'signature_key' => 'foo_key',
                    'signature_algorithm' => 'foo_algorithm',
                ],
            ]
        );
    }

    public function testEncryptionOptionsNotProvided()
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'signature_key' => 'foo_key',
                    'signature_algorithm' => 'foo_algorithm',
                    'encryption' => [
                        'enabled' => true,
                    ],
                ],
            ],
            'Invalid configuration for path "lexik_jose.encryption": The configuration options for encryption are invalid.'
        );
    }

    public function testEncryptionOptionsNotProvided1()
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'signature_key' => 'foo_key',
                    'signature_algorithm' => 'foo_algorithm',
                    'encryption' => [
                        'enabled' => true,
                        'encryption_key' => 'foo_key',
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
                    'signature_key' => 'foo_key',
                    'signature_algorithm' => 'foo_algorithm',
                    'encryption' => [
                        'enabled' => true,
                        'encryption_key' => 'foo_key',
                        'key_encryption_algorithm' => 'foo_key_encryption_algorithm',
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
                    'signature_key' => 'foo_key',
                    'signature_algorithm' => 'foo_algorithm',
                    'encryption' => [
                        'enabled' => true,
                        'encryption_key' => 'foo_key',
                        'key_encryption_algorithm' => 'foo_key_encryption_algorithm',
                        'content_encryption_algorithm' => 'foo_content_encryption_algorithm',
                    ],
                ],
            ]
        );
    }
}