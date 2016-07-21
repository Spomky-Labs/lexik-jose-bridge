<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use Base64Url\Base64Url;
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
     * @Given I have a valid signed token
     */
    public function iHaveAValidSignedToken()
    {
        $jwt_creator = $this->getContainer()->get('jose.jwt_creator.lexik_jose');
        $payload = $this->getBasicPayload();
        $signature_key = $this->getContainer()->get('lexik_jose_bridge.encoder.signature_key');
        $jwt = $jwt_creator->sign($payload, $this->getSignatureHeader(), $signature_key);
        $this->token = $jwt;
    }

    /**
     * @Given I have a valid signed and encrypted token
     */
    public function iHaveAValidSignedAndEncryptedToken()
    {
        $jwt_creator = $this->getContainer()->get('jose.jwt_creator.lexik_jose');
        $payload = $this->getBasicPayload();
        $signature_key = $this->getContainer()->get('lexik_jose_bridge.encoder.signature_key');
        $encryption_key = $this->getContainer()->get('lexik_jose_bridge.encoder.encryption.encryption_key');
        $jwt = $jwt_creator->sign($payload, $this->getSignatureHeader(), $signature_key);
        $jwt = $jwt_creator->encrypt($jwt, $this->getEncryptionHeader(), $encryption_key);
        $this->token = $jwt;
    }

    /**
     * @Given I have an expired, signed and encrypted token
     */
    public function iHaveAnExpiredSignedAndEncryptedToken()
    {
        $jwt_creator = $this->getContainer()->get('jose.jwt_creator.lexik_jose');
        $payload = array_merge(
            $this->getBasicPayload(),
            [
                'exp' => time()-1,
                'nbf' => time()-100,
                'iat' => time()-100,
            ]
        );
        $signature_key = $this->getContainer()->get('lexik_jose_bridge.encoder.signature_key');
        $encryption_key = $this->getContainer()->get('lexik_jose_bridge.encoder.encryption.encryption_key');
        $jwt = $jwt_creator->sign($payload, $this->getSignatureHeader(), $signature_key);
        $jwt = $jwt_creator->encrypt($jwt, $this->getEncryptionHeader(), $encryption_key);
        $this->token = $jwt;
    }

    /**
     * @Given I have a signed and encrypted token but with wrong issuer
     */
    public function iHaveASignedAndEncryptedTokenButWithWrongIssuer()
    {
        $jwt_creator = $this->getContainer()->get('jose.jwt_creator.lexik_jose');
        $payload = array_merge(
            $this->getBasicPayload(),
            [
                'iss' => 'BAD ISSUER',
            ]
        );
        $signature_key = $this->getContainer()->get('lexik_jose_bridge.encoder.signature_key');
        $encryption_key = $this->getContainer()->get('lexik_jose_bridge.encoder.encryption.encryption_key');
        $jwt = $jwt_creator->sign($payload, $this->getSignatureHeader(), $signature_key);
        $jwt = $jwt_creator->encrypt($jwt, $this->getEncryptionHeader(), $encryption_key);
        $this->token = $jwt;
    }

    /**
     * @Given I have a signed and encrypted token but with wrong audience
     */
    public function iHaveASignedAndEncryptedTokenButWithWrongAudience()
    {
        $jwt_creator = $this->getContainer()->get('jose.jwt_creator.lexik_jose');
        $payload = array_merge(
            $this->getBasicPayload(),
            [
                'aud' => 'BAD AUDIENCE',
            ]
        );
        $signature_key = $this->getContainer()->get('lexik_jose_bridge.encoder.signature_key');
        $encryption_key = $this->getContainer()->get('lexik_jose_bridge.encoder.encryption.encryption_key');
        $jwt = $jwt_creator->sign($payload, $this->getSignatureHeader(), $signature_key);
        $jwt = $jwt_creator->encrypt($jwt, $this->getEncryptionHeader(), $encryption_key);
        $this->token = $jwt;
    }

    /**
     * @Given I have a token with an unsupported algorithm
     */
    public function iHaveATokenWithAnUnsupportedAlgorithm()
    {
        $jwt_creator = $this->getContainer()->get('jose.jwt_creator.lexik_jose');
        $payload = $this->getBasicPayload();
        $signature_key = $this->getContainer()->get('lexik_jose_bridge.encoder.signature_key');
        $encryption_key = $this->getContainer()->get('lexik_jose_bridge.encoder.encryption.encryption_key');
        $jwt = $jwt_creator->sign($payload, $this->getSignatureHeader(), $signature_key);

        $parts = explode('.', $jwt);
        $header = json_decode(Base64Url::decode($parts[0]), true);
        $header['alg'] = 'none';
        $parts[0] = Base64Url::encode(json_encode($header));
        $parts[2] = '';
        $jwt = implode('.', $parts);
        $jwt = $jwt_creator->encrypt($jwt, $this->getEncryptionHeader(), $encryption_key);
        $this->token = $jwt;
    }

    /**
     * @Given I have a modified token
     */
    public function iHaveAModifiedToken()
    {
        $jwt_creator = $this->getContainer()->get('jose.jwt_creator.lexik_jose');
        $payload = $this->getBasicPayload();
        $signature_key = $this->getContainer()->get('lexik_jose_bridge.encoder.signature_key');
        $encryption_key = $this->getContainer()->get('lexik_jose_bridge.encoder.encryption.encryption_key');
        $jwt = $jwt_creator->sign($payload, $this->getSignatureHeader(), $signature_key);

        $parts = explode('.', $jwt);
        $body = json_decode(Base64Url::decode($parts[1]), true);
        $body['username'] = 'admin1';
        $parts[1] = Base64Url::encode(json_encode($body));
        $jwt = implode('.', $parts);
        $jwt = $jwt_creator->encrypt($jwt, $this->getEncryptionHeader(), $encryption_key);
        $this->token = $jwt;
    }

    /**
     * @return array
     */
    private function getBasicPayload()
    {
        return [
            'username' => 'user1',
            'exp' => time()+100,
            'iat' => time()-100,
            'nbf' => time()-100,
            'jti' => 'w53JxRXaEwGn80Jb4c-EZieTfvWgZDzhBw4C3Gv_0VId4zj4KaY6ujkDv9C3y7LLj5gSi9JCzfuBR2Km4vBsVA',
            'iss' => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.issuer'),
            'aud' => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.issuer'),
        ];
    }

    /**
     * @return array
     */
    private function getSignatureHeader()
    {
        $headers = [
            'typ'  => 'JWT',
            'cty'  => 'JWT',
            'alg'  => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.signature_algorithm'),
            'crit' => ['exp', 'nbf', 'iat', 'iss', 'aud'],
        ];
        $signature_key = $this->getContainer()->get('lexik_jose_bridge.encoder.signature_key');
        if ($signature_key->has('kid')) {
            $headers['kid'] = $signature_key->get('kid');
        }

        return $headers;
    }

    /**
     * @return array
     */
    private function getEncryptionHeader()
    {
        $headers = [
            'typ'  => 'JWT',
            'cty'  => 'JWT',
            'alg'  => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm'),
            'enc'  => $this->getContainer()->getParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm'),
        ];
        $encryption_key = $this->getContainer()->get('lexik_jose_bridge.encoder.encryption.encryption_key');
        if ($encryption_key->has('kid')) {
            $headers['kid'] = $encryption_key->get('kid');
        }

        return $headers;
    }

    /**
     * @Then I store the token
     */
    public function iStoreTheToken()
    {
        $content = json_decode($this->getSession()->getPage()->getContent(), true);

        $this->token = $content['token'];
    }
}
