<?php

namespace SpomkyLabs\LexikJoseBundle\Encoder;

use Base64Url\Base64Url;
use Jose\JWTCreator;
use Jose\JWTLoader;
use Jose\Object\JWKInterface;
use Jose\Object\JWKSetInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailure\JWTEncodeFailureException;

/**
 * Json Web Token encoder/decoder.
 *
 * This encoder uses the Spomky-Labs/JoseBundle to create and load the assertions
 */
class LexikJoseEncoder implements JWTEncoderInterface
{
    /**
     * @var \Jose\JWTCreator
     */
    private $jwt_creator;

    /**
     * @var \Jose\JWTLoader
     */
    private $jwt_loader;

    /**
     * @var \Jose\Object\JWKInterface
     */
    private $signature_key;

    /**
     * @var \Jose\Object\JWKInterface
     */
    private $encryption_key;

    /**
     * @var \Jose\Object\JWKSetInterface
     */
    private $keyset;

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
     * @param \Jose\Object\JWKInterface    $signature_key
     * @param \Jose\Object\JWKSetInterface $keyset
     * @param string                       $signature_algorithm
     */
    public function __construct(JWTCreator $jwt_creator,
                                JWTLoader $jwt_loader,
                                JWKInterface $signature_key,
                                JWKSetInterface $keyset,
                                $signature_algorithm
    ) {
        $this->jwt_creator = $jwt_creator;
        $this->jwt_loader = $jwt_loader;
        $this->signature_key = $signature_key;
        $this->keyset = $keyset;
        $this->signature_algorithm = $signature_algorithm;
    }

    /**
     * @param \Jose\Object\JWKInterface $encryption_key
     * @param string                    $key_encryption_algorithm
     * @param string                    $content_encryption_algorithm
     */
    public function enableEncryptionSupport(JWKInterface $encryption_key, $key_encryption_algorithm, $content_encryption_algorithm)
    {
        $this->encryption_key = $encryption_key;
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

            if (null !== $this->encryption_key) {
                $jwt = $this->encrypt($jwt);
            }

            return $jwt;
        } catch (\InvalidArgumentException $e) {
            throw new JWTEncodeFailureException('An error occurred while trying to encode the JWT token.', $e);
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
            [
                'jti' => Base64Url::encode(random_bytes(64)),
                'nbf' => time(),
                'iat' => time(),
            ]
        );
        $headers = [
            'typ'  => 'JWT',
            'cty'  => 'JWT',
            'alg'  => $this->signature_algorithm,
            'crit' => ['exp', 'nbf', 'iat'],
        ];
        if ($this->signature_key->has('kid')) {
            $headers['kid'] = $this->signature_key->get('kid');
        }

        return $this->jwt_creator->sign(
            $payload,
            $headers,
            $this->signature_key
        );
    }

    /**
     * @param string $jwt
     *
     * @return string
     */
    public function encrypt($jwt)
    {
        return $this->jwt_creator->encrypt(
            $jwt,
            [
                'typ'  => 'JWT',
                'cty'  => 'JWT',
                'alg' => $this->key_encryption_algorithm,
                'enc' => $this->content_encryption_algorithm,
            ],
            $this->encryption_key
        );
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        try {
            $jws = $this->jwt_loader->load($token, $this->keyset, null !== $this->encryption_key);
            $this->jwt_loader->verify($jws, $this->keyset);

            return $jws->getPayload();
        } catch (\InvalidArgumentException $e) {
            throw new JWTDecodeFailureException('Invalid JWT Token', $e);
        }
    }
}
