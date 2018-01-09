<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Serializer\CompactSerializer as JWECompactSerializer;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer as JWSCompactSerializer;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Behat context class.
 */
trait LoginContext
{
    /**
     * @var null|string
     */
    private $token = null;

    /**
     * @param string|null $name name of the session OR active session will be used
     *
     * @return \Behat\Mink\Session
     */
    abstract public function getSession($name = null);

    /**
     * @return ContainerInterface
     */
    abstract public function getContainer();

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return StandardConverter
     */
    private function getJsonConverter(): StandardConverter
    {
        return new StandardConverter();
    }

    /**
     * @Given I have a valid signed token
     */
    public function iHaveAValidSignedToken()
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getContainer()->get('jose.jws_builder.lexik_jose');
        $payload = $this->getBasicPayload();
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload($this->getJsonConverter()->encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build();
        $serialzer = new JWSCompactSerializer($this->getJsonConverter());
        $this->token = $serialzer->serialize($jwt, 0);
    }

    /**
     * @Given the token must contain the claim :claim with value :value
     */
    public function theTokenMustContainTheClaimWithValue($claim, $value)
    {
        $this->theTokenMustContainTheClaim($claim);
        /** @var JWTEncoderInterface $encoder */
        $encoder = $this->getContainer()->get('lexik_jwt_authentication.encoder');
        $token_decoded = $encoder->decode($this->getToken());

        if ($value !== $token_decoded[$claim]) {
            throw new \Exception();
        }
    }

    /**
     * @Given the token must contain the claim :claim
     */
    public function theTokenMustContainTheClaim($claim)
    {
        /** @var JWTEncoderInterface $encoder */
        $encoder = $this->getContainer()->get('lexik_jwt_authentication.encoder');

        $token_decoded = $encoder->decode($this->getToken());
        if (!array_key_exists($claim, $token_decoded)) {
            throw new \Exception();
        }
    }

    /**
     * @Given I have a valid signed and encrypted token
     */
    public function iHaveAValidSignedAndEncryptedToken()
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getContainer()->get('jose.jws_builder.lexik_jose');
        $payload = $this->getBasicPayload();
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload($this->getJsonConverter()->encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build();
        $serialzer = new JWSCompactSerializer(new StandardConverter());
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getContainer()->get('jose.jwe_builder.lexik_jose');
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build();

        $serialzer = new JWECompactSerializer(new StandardConverter());

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @Given I have an expired, signed and encrypted token
     */
    public function iHaveAnExpiredSignedAndEncryptedToken()
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getContainer()->get('jose.jws_builder.lexik_jose');
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
            ->withPayload($this->getJsonConverter()->encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build();
        $serialzer = new JWSCompactSerializer(new StandardConverter());
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getContainer()->get('jose.jwe_builder.lexik_jose');
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build();

        $serialzer = new JWECompactSerializer(new StandardConverter());

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @Given I have a signed and encrypted token but with wrong issuer
     */
    public function iHaveASignedAndEncryptedTokenButWithWrongIssuer()
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getContainer()->get('jose.jws_builder.lexik_jose');
        $payload = array_merge(
            $this->getBasicPayload(),
            [
                'iss' => 'BAD ISSUER',
            ]
        );
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload($this->getJsonConverter()->encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build();
        $serialzer = new JWSCompactSerializer(new StandardConverter());
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getContainer()->get('jose.jwe_builder.lexik_jose');
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build();

        $serialzer = new JWECompactSerializer(new StandardConverter());

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @Given I have a signed and encrypted token but with wrong audience
     */
    public function iHaveASignedAndEncryptedTokenButWithWrongAudience()
    {
        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = $this->getContainer()->get('jose.jws_builder.lexik_jose');
        $payload = array_merge(
            $this->getBasicPayload(),
            [
                'aud' => 'BAD AUDIENCE',
            ]
        );
        $signatureKey = $this->getSignatureKey();
        $jwt = $jwsBuilder
            ->create()
            ->withPayload($this->getJsonConverter()->encode($payload))
            ->addSignature($signatureKey, $this->getSignatureHeader())
            ->build();
        $serialzer = new JWSCompactSerializer(new StandardConverter());
        $jws = $serialzer->serialize($jwt);

        /** @var JWEBuilder $jweBuilder */
        $jweBuilder = $this->getContainer()->get('jose.jwe_builder.lexik_jose');
        $encryptionKey = $this->getEncryptionKey();
        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($this->getEncryptionHeader())
            ->addRecipient($encryptionKey)
            ->build();

        $serialzer = new JWECompactSerializer(new StandardConverter());

        $this->token = $serialzer->serialize($jwe, 0);
    }

    /**
     * @return array
     */
    private function getBasicPayload()
    {
        return [
            'username' => 'user1',
            'exp'      => time() + 100,
            'iat'      => time() - 100,
            'nbf'      => time() - 100,
            'jti'      => 'w53JxRXaEwGn80Jb4c-EZieTfvWgZDzhBw4C3Gv_0VId4zj4KaY6ujkDv9C3y7LLj5gSi9JCzfuBR2Km4vBsVA',
            'iss'      => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.issuer'),
            'aud'      => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.issuer'),
        ];
    }

    /**
     * @return array
     */
    private function getSignatureHeader()
    {
        $header = [
            'typ'  => 'JWT',
            'cty'  => 'JWT',
            'alg'  => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.signature_algorithm'),
        ];
        $signatureKey = $this->getSignatureKey();
        if ($signatureKey->has('kid')) {
            $header['kid'] = $signatureKey->get('kid');
        }

        return $header;
    }

    /**
     * @return array
     */
    private function getEncryptionHeader()
    {
        $header = [
            'typ'  => 'JWT',
            'cty'  => 'JWT',
            'alg'  => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm'),
            'enc'  => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm'),
        ];
        $encryption_key = $this->getEncryptionKey();
        if ($encryption_key->has('kid')) {
            $header['kid'] = $encryption_key->get('kid');
        }

        return $header;
    }

    /**
     * @Then I store the token
     */
    public function iStoreTheToken()
    {
        $content = json_decode($this->getSession()->getPage()->getContent(), true);

        $this->token = $content['token'];
    }

    /**
     * @return JWK
     */
    private function getSignatureKey(): JWK
    {
        $keyIndex = $this->getContainer()->getParameter('lexik_jose_bridge.encoder.key_index');

        return $this->getContainer()->get('jose.key_set.lexik_jose_bridge.signature')->get($keyIndex);
    }

    /**
     * @return JWK
     */
    private function getEncryptionKey(): JWK
    {
        $keyIndex = $this->getContainer()->getParameter('lexik_jose_bridge.encoder.encryption.key_index');

        return $this->getContainer()->get('jose.key_set.lexik_jose_bridge.encryption')->get($keyIndex);
    }
}
