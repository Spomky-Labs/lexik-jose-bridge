How to configure this bundle?
=============================

## Without Encryption

```yml
lexik_jose:
    ttl: 3600                                     // The TTL of each token issued by the bundle
    server_name: 'https://my.super.service/'      // This value is used to verify the issuer/audience of the tokens
    key_set: '%env(LEXIK_JOSE_SIGNATURE_KEYSET)%' // The signature key set (loaded through an env variable
    key_index: 0                                  // The index of the signature key in the key set
    signature_algorithm: "RS512"                  // The signature algorithm (default is RS512).
    claim_checked:                                // A list of claim checker aliases.
        - 'my_claim_checker_alias'                // See https://web-token.spomky-labs.com for more information
```

For all signature algorithms available, please refer to the [spomky-labs/jose documentation](https://github.com/Spomky-Labs/jose#supported-signature-algorithms).

For key configuration, please refer to the [Random JWKSet documentation](https://github.com/Spomky-Labs/jose/blob/master/doc/object/jwkset.md#create-a-key-set-with-random-keys)

## With Encryption

```yml
lexik_jose:
    ...
    encryption:
        enabled: true                                  // We enable the encryption (highly recommended)
        key_set: '%env(LEXIK_JOSE_ENCRYPTION_KEYSET)%' // The encryption key set (loaded through an env variable
        key_index: 0                                   // The index of the encryption key in the key set
        key_encryption_algorithm: 'A256GCMKW'          // The key encryption algorithm
        content_encryption_algorithm: 'A256GCM'        // The content encryption algorithm
```

The `key_set` parameters must contain valid key sets as per the [RFC7517](https://tools.ietf.org/html/rfc7517).

**Note: we highly recommend you to enable the encryption support as the token may contain very sensitive information**

# Lexik JWT Authentication Bundle Configuration

Now you just have to set the `SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder` as encoder service for the Lexik JWT Authentication Bundle:

```yml
lexik_jwt_authentication:
    encoder:
        service: "SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder"
```

*Note: you can find [a complete example from our application configuration used for the tests](https://github.com/Spomky-Labs/lexik-jose-bridge/blob/master/Tests/app/config/config.yml).*
