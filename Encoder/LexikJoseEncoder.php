<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Encoder;

use Exception;
use function is_string;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\InvalidHeaderException;
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
use ParagonIE\ConstantTime\Base64UrlSafe;

/**
 * Json Web Token encoder/decoder.
 *
 * This encoder uses web-token/jwt-framework to create and load the assertions
 */
final class LexikJoseEncoder implements JWTEncoderInterface
{
    private readonly string $issuer;

    private readonly string $audience;

    private readonly JWSBuilder $jwsBuilder;

    private readonly JWSVerifier $jwsLoader;

    private readonly ClaimCheckerManager $claimCheckerManager;

    private readonly HeaderCheckerManager $signatureHeaderCheckerManager;

    private ?HeaderCheckerManager $encryptionHeaderCheckerManager = null;

    private JWKSet $signatureKeyset;

    private readonly int|string $signatureKeyIndex;

    private readonly string $signatureAlgorithm;

    private ?JWEBuilder $jweBuilder = null;

    private ?JWEDecrypter $jweLoader = null;

    private ?JWKSet $encryptionKeyset = null;

    private int|string|null $encryptionKeyIndex = null;

    private ?string $keyEncryptionAlgorithm = null;

    private ?string $contentEncryptionAlgorithm = null;

    private readonly int $ttl;

    private readonly array $mandatoryClaims;

    /**
     * LexikJoseEncoder constructor.
     *
     * @param int|string $signatureKeyIndex
     */
    public function __construct(
        JWSBuilder $jwsBuilder,
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
     * @param int|string $encryptionKeyIndex
     */
    public function enableEncryptionSupport(
        JWEBuilder $jweBuilder,
        JWEDecrypter $jweLoader,
        HeaderCheckerManager $encryptionHeaderCheckerManager,
        JWKSet $encryptionKeyset,
        $encryptionKeyIndex,
        string $keyEncryptionAlgorithm,
        string $contentEncryptionAlgorithm
    ): void {
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
    public function encode(array $payload): string
    {
        try {
            $jwt = $this->sign($payload);

            if ($this->jweBuilder !== null) {
                $jwt = $this->encrypt($jwt);
            }

            return $jwt;
        } catch (Exception $e) {
            throw new JWTEncodeFailureException(
                'encoding_error',
                'An error occurred while trying to encode the JWT token: ' . $e->getMessage(),
                $e
            );
        }
    }

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
            ->build()
        ;

        $serializer = new JWECompactSerializer();

        return $serializer->serialize($jwe, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token): array
    {
        try {
            if ($this->jweBuilder !== null) {
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

            throw new JWTDecodeFailureException($reason, sprintf(
                'Invalid JWT Token. The following claim was not verified: %s.',
                $e->getClaim()
            ));
        } catch (InvalidHeaderException $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, sprintf(
                'Invalid JWT Token. The following header was not verified: %s.',
                $e->getHeader()
            ));
        } catch (Exception $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, sprintf(
                'Invalid JWT Token: %s',
                $e->getMessage()
            ), $e);
        }
    }

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
            ->build()
        ;

        $serializer = new JWSCompactSerializer();

        return $serializer->serialize($jws, 0);
    }

    private function decrypt(string $token): string
    {
        $serializer = new JWECompactSerializer();
        $jwe = $serializer->unserialize($token);
        $this->encryptionHeaderCheckerManager->check($jwe, 0);
        if ($this->jweLoader->decryptUsingKeySet($jwe, $this->encryptionKeyset, 0) === false) {
            throw new JWTDecodeFailureException(
                'decoding_error',
                'An error occurred while trying to decrypt the JWT token.'
            );
        }

        return $jwe->getPayload();
    }

    private function verify(string $token): array
    {
        $serializer = new JWSCompactSerializer();
        $jws = $serializer->unserialize($token);
        $this->signatureHeaderCheckerManager->check($jws, 0);
        if ($this->jwsLoader->verifyWithKeySet($jws, $this->signatureKeyset, 0) === false) {
            throw new JWTDecodeFailureException(
                'decoding_error',
                'An error occurred while trying to verify the JWT token.'
            );
        }
        $jwt = $jws->getPayload();
        if (! is_string($jwt)) {
            throw new JWTDecodeFailureException(
                'decoding_error',
                'An error occurred while trying to verify the JWT token.'
            );
        }

        $payload = JsonConverter::decode($jwt);
        $this->claimCheckerManager->check($payload, $this->mandatoryClaims);

        return $payload;
    }

    private function getAdditionalPayload(): array
    {
        return [
            'jti' => Base64UrlSafe::encode(random_bytes(64)),
            'exp' => time() + $this->ttl,
            'iat' => time(),
            'iss' => $this->issuer,
            'aud' => $this->audience,
        ];
    }

    private function getSignatureHeader(): array
    {
        return [
            'typ' => 'JWT',
            'alg' => $this->signatureAlgorithm,
            'crit' => ['alg'],
        ];
    }

    private function getEncryptionHeader(): array
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
