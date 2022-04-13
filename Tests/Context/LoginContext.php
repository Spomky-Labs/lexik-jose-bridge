<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use function array_key_exists;
use Behat\Mink\Session;
use Exception;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Serializer\CompactSerializer as JWECompactSerializer;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer as JWSCompactSerializer;
use const JSON_THROW_ON_ERROR;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder;

/**
 * Behat context class.
 */
trait LoginContext
{
    /**
     * @var string|null
     */
    private $token;

    /**
     * @param string|null $name name of the session OR active session will be used
     *
     * @return Session
     */
    abstract public function getSession($name = null);

    abstract public function getJWSBuilder(): JWSBuilder;

    abstract public function getJWEBuilder(): JWEBuilder;

    abstract public function getEncoder(): LexikJoseEncoder;

    abstract public function getJWKSetSignature(): JWKSet;

    abstract public function getJWKSetEncryption(): JWKSet;

    abstract public function getIssuer(): string;

    abstract public function getAudience(): string;

    abstract public function getSignatureAlgorithm(): string;

    abstract public function getKeyEncryptionAlgorithm(): string;

    abstract public function getContentEncryptionAlgorithm(): string;

    abstract public function getEncoderKeyIndex(): string;

    abstract public function getEncoderEncryptionKeyIndex(): string;

    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @Given I have a valid signed token
     */
    public function iHaveAValidSignedToken(): void
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getJWSBuilder();
        $payload = $this->getBasicPayload();
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build()
        ;
        $serialzer = new JWSCompactSerializer();
        $this->token = $serialzer->serialize($jwt, 0);
    }

    /**
     * @Given the token must contain the claim :claim with value :value
     */
    public function theTokenMustContainTheClaimWithValue($claim, $value): void
    {
        $this->theTokenMustContainTheClaim($claim);

        /** @var JWTEncoderInterface $encoder */
        $encoder = $this->getEncoder();
        $token_decoded = $encoder->decode($this->getToken());

        if ($value !== $token_decoded[$claim]) {
            throw new Exception();
        }
    }

    /**
     * @Given the token must contain the claim :claim
     */
    public function theTokenMustContainTheClaim($claim): void
    {
        /** @var JWTEncoderInterface $encoder */
        $encoder = $this->getEncoder();

        $token_decoded = $encoder->decode($this->getToken());
        if (! array_key_exists($claim, $token_decoded)) {
            throw new Exception();
        }
    }

    /**
     * @Given I have a valid signed and encrypted token
     */
    public function iHaveAValidSignedAndEncryptedToken(): void
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getJWSBuilder();
        $payload = $this->getBasicPayload();
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build()
        ;
        $serialzer = new JWSCompactSerializer();
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getJWEBuilder();
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build()
        ;

        $serialzer = new JWECompactSerializer();

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @Given I have a signed and encrypted token but without the :claim claim
     */
    public function iHaveASignedAndEncryptedTokenButWithoutTheClaim($claim): void
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getJWSBuilder();
        $payload = $this->getBasicPayload();
        unset($payload[$claim]);
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build()
        ;
        $serialzer = new JWSCompactSerializer();
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getJWEBuilder();
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build()
        ;

        $serialzer = new JWECompactSerializer();

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @Given I have an expired, signed and encrypted token
     */
    public function iHaveAnExpiredSignedAndEncryptedToken(): void
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getJWSBuilder();
        $payload = array_merge(
            $this->getBasicPayload(),
            [
                'exp' => time() - 1,
                'nbf' => time() - 100,
                'iat' => time() - 100,
            ]
        );
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build()
        ;
        $serialzer = new JWSCompactSerializer();
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getJWEBuilder();
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build()
        ;

        $serialzer = new JWECompactSerializer();

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @Given I have a signed and encrypted token but with wrong issuer
     */
    public function iHaveASignedAndEncryptedTokenButWithWrongIssuer(): void
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getJWSBuilder();
        $payload = array_merge($this->getBasicPayload(), [
            'iss' => 'BAD ISSUER',
        ]);
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build()
        ;
        $serialzer = new JWSCompactSerializer();
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getJWEBuilder();
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build()
        ;

        $serialzer = new JWECompactSerializer();

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @Given I have a signed and encrypted token but with wrong audience
     */
    public function iHaveASignedAndEncryptedTokenButWithWrongAudience(): void
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getJWSBuilder();
        $payload = array_merge($this->getBasicPayload(), [
            'aud' => 'BAD AUDIENCE',
        ]);
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build()
        ;
        $serialzer = new JWSCompactSerializer();
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getJWEBuilder();
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build()
        ;

        $serialzer = new JWECompactSerializer();

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @Then I store the token
     */
    public function iStoreTheToken(): void
    {
        $content = json_decode($this->getSession()->getPage()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->token = $content['token'];
    }

    private function getBasicPayload(): array
    {
        return [
            'username' => 'user1',
            'exp' => time() + 100,
            'iat' => time() - 100,
            'nbf' => time() - 100,
            'jti' => 'w53JxRXaEwGn80Jb4c-EZieTfvWgZDzhBw4C3Gv_0VId4zj4KaY6ujkDv9C3y7LLj5gSi9JCzfuBR2Km4vBsVA',
            'iss' => $this->getIssuer(),
            'aud' => $this->getAudience(),
            'ip' => '127.0.0.1',
        ];
    }

    private function getSignatureHeader(): array
    {
        $header = [
            'typ' => 'JWT',
            'cty' => 'JWT',
            'alg' => $this->getSignatureAlgorithm(),
        ];
        $signatureKey = $this->getSignatureKey();
        if ($signatureKey->has('kid')) {
            $header['kid'] = $signatureKey->get('kid');
        }

        return $header;
    }

    private function getEncryptionHeader(): array
    {
        $header = [
            'typ' => 'JWT',
            'cty' => 'JWT',
            'alg' => $this->getKeyEncryptionAlgorithm(),
            'enc' => $this->getContentEncryptionAlgorithm(),
        ];
        $encryption_key = $this->getEncryptionKey();
        if ($encryption_key->has('kid')) {
            $header['kid'] = $encryption_key->get('kid');
        }

        return $header;
    }

    private function getSignatureKey(): JWK
    {
        $keyIndex = $this->getEncoderKeyIndex();

        return $this->getJWKSetSignature()
            ->get($keyIndex)
        ;
    }

    private function getEncryptionKey(): JWK
    {
        $keyIndex = $this->getEncoderEncryptionKeyIndex();

        return $this->getJWKSetEncryption()
            ->get($keyIndex)
        ;
    }
}
