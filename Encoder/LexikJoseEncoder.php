<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
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
 * This encoder uses web-token/jwt-framework to create and load the assertions
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
    private $signatureHeaderCheckerManager;

    /**
     * @var HeaderCheckerManager
     */
    private $encryptionHeaderCheckerManager;

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
     * @param HeaderCheckerManager $signatureHeaderCheckerManager
     * @param JWKSet               $signatureKeyset
     * @param int|string           $signatureKeyIndex
     * @param string               $signatureAlgorithm
     * @param string               $issuer
     * @param int                  $ttl
     */
    public function __construct(JWSBuilder $jwsBuilder,
                                JWSVerifier $jwsLoader,
                                ClaimCheckerManager $claimCheckerManager,
                                HeaderCheckerManager $signatureHeaderCheckerManager,
                                JWKSet $signatureKeyset,
                                $signatureKeyIndex,
                                string $signatureAlgorithm,
                                string $issuer,
                                int $ttl
    ) {
        $this->jwsBuilder = $jwsBuilder;
        $this->jwsLoader = $jwsLoader;
        $this->claimCheckerManager = $claimCheckerManager;
        $this->signatureHeaderCheckerManager = $signatureHeaderCheckerManager;
        $this->signatureKeyset = $signatureKeyset;
        $this->signatureKeyIndex = $signatureKeyIndex;
        $this->signatureAlgorithm = $signatureAlgorithm;
        $this->issuer = $issuer;
        $this->ttl = $ttl;
    }

    /**
     * @param JWEBuilder           $jweBuilder
     * @param JWEDecrypter         $jweLoader
     * @param HeaderCheckerManager $encryptionHeaderCheckerManager
     * @param JWKSet               $encryptionKeyset
     * @param int|string           $encryptionKeyIndex
     * @param string               $keyEncryptionAlgorithm
     * @param string               $contentEncryptionAlgorithm
     */
    public function enableEncryptionSupport(JWEBuilder $jweBuilder, JWEDecrypter $jweLoader, HeaderCheckerManager $encryptionHeaderCheckerManager, JWKSet $encryptionKeyset, $encryptionKeyIndex, string $keyEncryptionAlgorithm, string $contentEncryptionAlgorithm)
    {
        $this->jweBuilder = $jweBuilder;
        $this->jweLoader = $jweLoader;
        $this->encryptionKeyset = $encryptionKeyset;
        $this->encryptionKeyIndex = $encryptionKeyIndex;
        $this->keyEncryptionAlgorithm = $keyEncryptionAlgorithm;
        $this->contentEncryptionAlgorithm = $contentEncryptionAlgorithm;
        $this->encryptionHeaderCheckerManager = $encryptionHeaderCheckerManager;
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
        $headers = $this->getSignatureHeader();
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
        $headers = $this->getEncryptionHeader();
        $encryptionKey = $this->encryptionKeyset->get($this->encryptionKeyIndex);

        if ($encryptionKey->has('kid')) {
            $headers['kid'] = $encryptionKey->get('kid');
        }

        $jwe = $this->jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader($headers)
            ->addRecipient($encryptionKey)
            ->build();

        $serializer = new JWECompactSerializer(new StandardConverter());

        return $serializer->serialize($jwe, 0);
    }

    /**
     * @param string $token
     *
     * @throws JWTDecodeFailureException
     *
     * @return string
     */
    private function decrypt(string $token): string
    {
        $serializer = new JWECompactSerializer(new StandardConverter());
        $jwe = $serializer->unserialize($token);
        $this->encryptionHeaderCheckerManager->check($jwe, 0);
        if (false === $this->jweLoader->decryptUsingKeySet($jwe, $this->encryptionKeyset, 0)) {
            throw new JWTDecodeFailureException('decoding_error', 'An error occurred while trying to decrypt the JWT token.');
        }

        return $jwe->getPayload();
    }

    /**
     * @param string $token
     *
     * @throws JWTDecodeFailureException
     *
     * @return array
     */
    private function verify(string $token): array
    {
        $jsonConverter = new StandardConverter();
        $serializer = new JWSCompactSerializer($jsonConverter);
        $jws = $serializer->unserialize($token);
        $this->signatureHeaderCheckerManager->check($jws, 0);
        if (false === $this->jwsLoader->verifyWithKeySet($jws, $this->signatureKeyset, 0)) {
            throw new JWTDecodeFailureException('decoding_error', 'An error occurred while trying to verify the JWT token.');
        }
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
            'iat' => time(),
            'iss' => $this->issuer,
            'aud' => $this->issuer,
        ];
    }

    /**
     * @return array
     */
    private function getSignatureHeader()
    {
        return [
            'typ'  => 'JWT',
            'alg'  => $this->signatureAlgorithm,
            'crit' => ['alg'],
        ];
    }

    /**
     * @return array
     */
    private function getEncryptionHeader()
    {
        return [
            'typ'  => 'JWT',
            'cty'  => 'JWT',
            'alg'  => $this->keyEncryptionAlgorithm,
            'enc'  => $this->contentEncryptionAlgorithm,
            'iss'  => $this->issuer,
            'aud'  => $this->issuer,
            'crit' => ['iss', 'aud', 'alg', 'enc'],
        ];
    }
}
