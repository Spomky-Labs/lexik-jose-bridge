<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Encoder;

use Base64Url\Base64Url;
use Exception;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\InvalidHeaderException;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Jose\Component\Checker\MissingMandatoryHeaderParameterException;
use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer as JWECompactSerializer;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer as JWSCompactSerializer;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use function Safe\sprintf;

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
     * @var string
     */
    private $audience;

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
     * @var int|string
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
     * @var int|string
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
     * @var array
     */
    private $mandatoryClaims;

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
     * @param array                $mandatoryClaims
     */
    public function __construct(JWSBuilder $jwsBuilder,
                                JWSVerifier $jwsLoader,
                                ClaimCheckerManager $claimCheckerManager,
                                HeaderCheckerManager $signatureHeaderCheckerManager,
                                JWKSet $signatureKeyset,
                                $signatureKeyIndex,
                                string $signatureAlgorithm,
                                string $issuer,
                                string $audience,
                                int $ttl,
                                array $mandatoryClaims = []
    ) {
        $this->jwsBuilder = $jwsBuilder;
        $this->jwsLoader = $jwsLoader;
        $this->claimCheckerManager = $claimCheckerManager;
        $this->signatureHeaderCheckerManager = $signatureHeaderCheckerManager;
        $this->signatureKeyset = $signatureKeyset;
        $this->signatureKeyIndex = $signatureKeyIndex;
        $this->signatureAlgorithm = $signatureAlgorithm;
        $this->issuer = $issuer;
        $this->audience = $audience;
        $this->ttl = $ttl;
        $this->mandatoryClaims = $mandatoryClaims;
    }

    /**
     * @param JWEBuilder           $jweBuilder
     * @param JWEDecrypter         $jweLoader
     * @param HeaderCheckerManager $encryptionHeaderCheckerManager
     * @param JWKSet               $encryptionKeyset
     * @param int|string           $encryptionKeyIndex
     * @param string               $keyEncryptionAlgorithm
     * @param string               $contentEncryptionAlgorithm
     *
     * @return void
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
        } catch (Exception $e) {
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
        $payload += $this->getAdditionalPayload();
        $headers = $this->getSignatureHeader();
        $signatureKey = $this->signatureKeyset->get($this->signatureKeyIndex);
        if ($signatureKey->has('kid')) {
            $headers['kid'] = $signatureKey->get('kid');
        }

        $jws = $this->jwsBuilder
            ->create()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature($signatureKey, $headers)
            ->build();

        $serializer = new JWSCompactSerializer();

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

        $serializer = new JWECompactSerializer();

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
        $serializer = new JWECompactSerializer();
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
     * @throws InvalidClaimException
     * @throws InvalidHeaderException
     * @throws JWTDecodeFailureException
     * @throws MissingMandatoryClaimException
     * @throws MissingMandatoryHeaderParameterException
     *
     * @return array
     */
    private function verify(string $token): array
    {
        $serializer = new JWSCompactSerializer();
        $jws = $serializer->unserialize($token);
        $this->signatureHeaderCheckerManager->check($jws, 0);
        if (false === $this->jwsLoader->verifyWithKeySet($jws, $this->signatureKeyset, 0)) {
            throw new JWTDecodeFailureException('decoding_error', 'An error occurred while trying to verify the JWT token.');
        }
        $jwt = $jws->getPayload();
        if (!\is_string($jwt)) {
            throw new JWTDecodeFailureException('decoding_error', 'An error occurred while trying to verify the JWT token.');
        }

        $payload = JsonConverter::decode($jwt);
        $this->claimCheckerManager->check($payload, $this->mandatoryClaims);

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
        } catch (Exception $e) {
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
            'aud' => $this->audience,
        ];
    }

    /**
     * @return array
     */
    private function getSignatureHeader()
    {
        return [
            'typ' => 'JWT',
            'alg' => $this->signatureAlgorithm,
            'crit' => ['alg'],
        ];
    }

    /**
     * @return array
     */
    private function getEncryptionHeader()
    {
        return [
            'typ' => 'JWT',
            'cty' => 'JWT',
            'alg' => $this->keyEncryptionAlgorithm,
            'enc' => $this->contentEncryptionAlgorithm,
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'crit' => ['iss', 'aud', 'alg', 'enc'],
        ];
    }
}
