<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Encoder;

use Base64Url\Base64Url;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\InvalidHeaderException;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Core\JWKSet;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer as JWECompactSerializer;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer as JWSCompactSerializer;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;

/**
 * Json Web Token encoder/decoder.
 *
 * This encoder uses the spomky-labs/jose components to create and load the assertions
 */
final class LexikJoseEncoder implements JWTEncoderInterface
{
    /**
     * @var string
     */
    private $issuer;

    /**
     * @var JWSBuilder
     */
    private $jwsBuilder;

    /**
     * @var JWSVerifier
     */
    private $jwsLoader;

    /**
     * @var ClaimCheckerManager
     */
    private $claimCheckerManager;

    /**
     * @var HeaderCheckerManager
     */
    private $headerCheckerManager;

    /**
     * @var JWKSet
     */
    private $signatureKeyset;

    /**
     * @var int
     */
    private $signatureKeyIndex;

    /**
     * @var string
     */
    private $signatureAlgorithm;

    /**
     * @var JWEBuilder
     */
    private $jweBuilder;

    /**
     * @var JWEDecrypter
     */
    private $jweLoader;

    /**
     * @var JWKSet
     */
    private $encryptionKeyset;

    /**
     * @var int
     */
    private $encryptionKeyIndex;

    /**
     * @var string|null
     */
    private $keyEncryptionAlgorithm;

    /**
     * @var string|null
     */
    private $contentEncryptionAlgorithm;

    /**
     * @var int
     */
    private $ttl;

    /**
     * LexikJoseEncoder constructor.
     *
     * @param JWSBuilder           $jwsBuilder
     * @param JWSVerifier          $jwsLoader
     * @param ClaimCheckerManager  $claimCheckerManager
     * @param HeaderCheckerManager $headerCheckerManager
     * @param JWKSet               $signatureKeyset
     * @param int                  $signatureKeyIndex
     * @param string               $signatureAlgorithm
     * @param string               $issuer
     * @param int                  $ttl
     */
    public function __construct(JWSBuilder $jwsBuilder,
                                JWSVerifier $jwsLoader,
                                ClaimCheckerManager $claimCheckerManager,
                                HeaderCheckerManager $headerCheckerManager,
                                JWKSet $signatureKeyset,
                                int $signatureKeyIndex,
                                string $signatureAlgorithm,
                                string $issuer,
                                int $ttl
    ) {
        $this->jwsBuilder = $jwsBuilder;
        $this->jwsLoader = $jwsLoader;
        $this->claimCheckerManager = $claimCheckerManager;
        $this->headerCheckerManager = $headerCheckerManager;
        $this->signatureKeyset = $signatureKeyset;
        $this->signatureKeyIndex = $signatureKeyIndex;
        $this->signatureAlgorithm = $signatureAlgorithm;
        $this->issuer = $issuer;
        $this->ttl = $ttl;
    }

    /**
     * @param JWEBuilder $jweBuilder
     * @param JWEDecrypter  $jweLoader
     * @param JWKSet     $encryptionKeyset
     * @param int        $encryptionKeyIndex
     * @param string     $keyEncryptionAlgorithm
     * @param string     $contentEncryptionAlgorithm
     */
    public function enableEncryptionSupport(JWEBuilder $jweBuilder, JWEDecrypter $jweLoader, JWKSet $encryptionKeyset, int $encryptionKeyIndex, string $keyEncryptionAlgorithm, string $contentEncryptionAlgorithm)
    {
        $this->jweBuilder = $jweBuilder;
        $this->jweLoader = $jweLoader;
        $this->encryptionKeyset = $encryptionKeyset;
        $this->encryptionKeyIndex = $encryptionKeyIndex;
        $this->keyEncryptionAlgorithm = $keyEncryptionAlgorithm;
        $this->contentEncryptionAlgorithm = $contentEncryptionAlgorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $payload)
    {
        try {
            $jwt = $this->sign($payload);

            if (null !== $this->jweBuilder) {
                $jwt = $this->encrypt($jwt);
            }

            return $jwt;
        } catch (\Exception $e) {
            throw new JWTEncodeFailureException('encoding_error', 'An error occurred while trying to encode the JWT token: '.$e->getMessage(), $e);
        }
    }

    /**
     * @param array $payload
     *
     * @return string
     */
    private function sign(array $payload): string
    {
        $jsonConverter = new StandardConverter();
        $payload = array_merge(
            $payload,
            $this->getAdditionalPayload()
        );
        $headers = $this->getSignatureHeaders();
        $signatureKey = $this->signatureKeyset->get($this->signatureKeyIndex);
        if ($signatureKey->has('kid')) {
            $headers['kid'] = $signatureKey->get('kid');
        }

        $jws = $this->jwsBuilder
            ->create()
            ->withPayload($jsonConverter->encode($payload))
            ->addSignature($signatureKey, $headers)
            ->build();

        $serializer = new JWSCompactSerializer($jsonConverter);

        return $serializer->serialize($jws, 0);
    }

    /**
     * @param string $jws
     *
     * @return string
     */
    public function encrypt(string $jws): string
    {
        $headers = $this->getEncryptionHeaders();
        $encryptionKey = $this->encryptionKeyset->get($this->encryptionKeyIndex);

        if ($encryptionKey->has('kid')) {
            $headers['kid'] = $encryptionKey->get('kid');
        }

        $jwe = $this->jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeaders($headers)
            ->addRecipient($encryptionKey)
            ->build();

        $serializer = new JWECompactSerializer(new StandardConverter());

        return $serializer->serialize($jwe, 0);
    }

    /**
     * @param string $token
     *
     * @return string
     */
    private function decrypt(string $token): string
    {
        $serializer = new JWECompactSerializer(new StandardConverter());
        $jwe = $serializer->unserialize($token);
        $this->headerCheckerManager->check($jwe, 0);
        $jwe = $this->jweLoader->decryptUsingKeySet($jwe, $this->encryptionKeyset);

        return $jwe->getPayload();
    }

    /**
     * @param string $token
     *
     * @return array
     */
    private function verify(string $token): array
    {
        $jsonConverter = new StandardConverter();
        $serializer = new JWSCompactSerializer($jsonConverter);
        $jws = $serializer->unserialize($token);
        $this->jwsLoader->verifyWithKeySet($jws, $this->signatureKeyset);
        $payload = $jsonConverter->decode($jws->getPayload());
        $this->claimCheckerManager->check($payload);

        return $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        try {
            if (null !== $this->jweBuilder) {
                $token = $this->decrypt($token);
            }

            return $this->verify($token);
        } catch (InvalidClaimException $e) {
            switch ($e->getClaim()) {
                case 'exp':
                    $reason = JWTDecodeFailureException::EXPIRED_TOKEN;
                    break;
                default:
                    $reason = JWTDecodeFailureException::INVALID_TOKEN;
            }

            throw new JWTDecodeFailureException($reason, sprintf('Invalid JWT Token. The following claim was not verified: %s.', $e->getClaim()));
        } catch (InvalidHeaderException $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, sprintf('Invalid JWT Token. The following header was not verified: %s.', $e->getHeader()));
        } catch (\Exception $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, sprintf('Invalid JWT Token: %s', $e->getMessage()), $e);
        }
    }

    /**
     * @return array
     */
    private function getAdditionalPayload(): array
    {
        return [
            'jti' => Base64Url::encode(random_bytes(64)),
            'exp' => time() + $this->ttl,
            'nbf' => time(),
            'iat' => time(),
            'iss' => $this->issuer,
            'aud' => $this->issuer,
        ];
    }

    /**
     * @return array
     */
    private function getSignatureHeaders()
    {
        return [
            'typ'  => 'JWT',
            'cty'  => 'JWT',
            'alg'  => $this->signatureAlgorithm,
        ];
    }

    /**
     * @return array
     */
    private function getEncryptionHeaders()
    {
        return [
            'typ'  => 'JWT',
            'cty'  => 'JWT',
            'alg'  => $this->keyEncryptionAlgorithm,
            'enc'  => $this->contentEncryptionAlgorithm,
            'iss' => $this->issuer,
            'aud' => $this->issuer,
            'crit' => ['iss', 'aud'],
        ];
    }
}
