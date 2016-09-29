<?php

namespace SpomkyLabs\LexikJoseBundle\Encoder;

use Base64Url\Base64Url;
use Jose\JWTCreator;
use Jose\JWTLoader;
use Jose\Object\JWKSetInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;

/**
 * Json Web Token encoder/decoder.
 *
 * This encoder uses the Spomky-Labs/JoseBundle to create and load the assertions
 */
class LexikJoseEncoder implements JWTEncoderInterface
{
    /**
     * @var string
     */
    private $issuer;

    /**
     * @var \Jose\JWTCreator
     */
    private $jwt_creator;

    /**
     * @var \Jose\JWTLoader
     */
    private $jwt_loader;

    /**
     * @var \Jose\Object\JWKSetInterface
     */
    private $signature_jwkset;

    /**
     * @var \Jose\Object\JWKSetInterface
     */
    private $encryption_jwkset;

    /**
     * @var string
     */
    private $signature_algorithm;

    /**
     * @var string|null
     */
    private $key_encryption_algorithm;

    /**
     * @var string|null
     */
    private $content_encryption_algorithm;

    /**
     * LexikJoseEncoder constructor.
     *
     * @param \Jose\JWTCreator             $jwt_creator
     * @param \Jose\JWTLoader              $jwt_loader
     * @param \Jose\Object\JWKSetInterface $signature_jwkset
     * @param string                       $signature_algorithm
     * @param string                       $issuer
     */
    public function __construct(JWTCreator $jwt_creator,
                                JWTLoader $jwt_loader,
                                JWKSetInterface $signature_jwkset,
                                $signature_algorithm,
                                $issuer
    ) {
        $this->jwt_creator = $jwt_creator;
        $this->jwt_loader = $jwt_loader;
        $this->signature_jwkset = $signature_jwkset;
        $this->signature_algorithm = $signature_algorithm;
        $this->issuer = $issuer;
    }

    /**
     * @param \Jose\Object\JWKSetInterface $encryption_jwkset
     * @param string                       $key_encryption_algorithm
     * @param string                       $content_encryption_algorithm
     */
    public function enableEncryptionSupport(JWKSetInterface $encryption_jwkset, $key_encryption_algorithm, $content_encryption_algorithm)
    {
        $this->encryption_jwkset = $encryption_jwkset;
        $this->key_encryption_algorithm = $key_encryption_algorithm;
        $this->content_encryption_algorithm = $content_encryption_algorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $payload)
    {
        try {
            $jwt = $this->sign($payload);

            if (null !== $this->encryption_jwkset) {
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
    private function sign(array $payload)
    {
        $payload = array_merge(
            $payload,
            $this->getAdditionalPayload()
        );
        $headers = $this->getSignatureHeaders();
        $signature_key = $this->signature_jwkset->getKey(0);
        if ($signature_key->has('kid')) {
            $headers['kid'] = $signature_key->get('kid');
        }

        return $this->jwt_creator->sign($payload, $headers, $signature_key);
    }

    /**
     * @param string $jwt
     *
     * @return string
     */
    public function encrypt($jwt)
    {
        $headers = $this->getEncryptionHeaders();
        $encryption_key = $this->encryption_jwkset[0];

        if ($encryption_key->has('kid')) {
            $headers['kid'] = $encryption_key->get('kid');
        }

        return $this->jwt_creator->encrypt($jwt, $headers, $encryption_key);
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        try {
            $jws = $this->jwt_loader->load($token, $this->encryption_jwkset, null !== $this->encryption_jwkset);
            $this->jwt_loader->verify($jws, $this->signature_jwkset);

            return $jws->getClaims();
        } catch (\Exception $e) {
            $reason = $this->getDecodeErrorReason($e->getMessage());
            throw new JWTDecodeFailureException($reason, sprintf('Invalid JWT Token: %s', $e->getMessage()), $e);
        }
    }

    /**
     * @param string $error
     *
     * @return string
     */
    private function getDecodeErrorReason($error)
    {
        $maps = $this->getDecodeErrorMapping();
        if (array_key_exists($error, $maps)) {
            return $maps[$error];
        }

        return JWTDecodeFailureException::INVALID_TOKEN;
    }

    /**
     * @return array
     */
    private function getDecodeErrorMapping()
    {
        return [
            'The JWT has expired.'      => JWTDecodeFailureException::EXPIRED_TOKEN,
            'Unable to verify the JWS.' => JWTDecodeFailureException::UNVERIFIED_TOKEN,
        ];
    }

    /**
     * @return array
     */
    private function getAdditionalPayload()
    {
        return [
            'jti' => Base64Url::encode(random_bytes(64)),
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
            'alg'  => $this->signature_algorithm,
            'crit' => ['exp', 'nbf', 'iat', 'iss', 'aud'],
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
            'alg'  => $this->key_encryption_algorithm,
            'enc'  => $this->content_encryption_algorithm,
        ];
    }
}
