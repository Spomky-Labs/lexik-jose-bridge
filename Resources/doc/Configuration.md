How to configure this bundle?
=============================

## Without Encryption

```yml
lexik_jose:
    ttl: 3600                                 // The TTL of each token issued by the bundle
    server_name: 'https://my.super.service/'  // This value is used to verify the issuer/audience of the tokens
    key_storage_folder: '%kernel.cache_dir%/' // The folder where keys are stored. Must be writable
    signature_algorithm: "RS512"              // The signature algorithm (default is RS512).
    signature_key_configuration:              // The signature keys configuration.
        kty: 'RSA'                            // In that example we will have 4096 bits RSA keys.
        size: 4096                            // Keys must be suitable for the signature algorithm.
```

For all signature algorithms available, please refer to the [spomky-labs/jose documentation](https://github.com/Spomky-Labs/jose#supported-signature-algorithms).

For key configuration, please refer to the [Random JWKSet documentation](https://github.com/Spomky-Labs/jose/blob/master/doc/object/jwkset.md#create-a-key-set-with-random-keys])

## With Encryption

```yml
lexik_jose:
    ...
    encryption:
        enabled: true                           // We enable the encryption (highly recommended
        key_encryption_algorithm: 'A256GCMKW'   // The key encryption algorithm
        content_encryption_algorithm: 'A256GCM' // The content encryption algorithm
        encryption_key_configuration:           // The encryption keys configuration
            kty: 'oct'                          // In that example we will have 256 bits octect strings.
            size: 256                           // Keys must be suitable for the key encryption algorithm.
```

For all key and content encryption algorithms available, please refer to the [spomky-labs/jose documentation](https://github.com/Spomky-Labs/jose#supported-key-encryption-algorithms).

For key configuration, please refer to the [Random JWKSet documentation](https://github.com/Spomky-Labs/jose/blob/master/doc/object/jwkset.md#create-a-key-set-with-random-keys])

**Note: we highly recommend you to enable the encryption support as the token may contain very sensitive information**

# Lexik JWT Authentication Bundle Configuration

Now you just have to set the `lexik_jose_bridge.encoder` as encoder service for the Lexik JWT Authentication Bundle:

```yml
lexik_jwt_authentication:
    encoder:
        service: "lexik_jose_bridge.encoder"
```

*Note: you can find [a complete example from our application configuration used for the tests](https://github.com/Spomky-Labs/lexik-jose-bridge/blob/master/Tests/app/config/config.yml#L31-L49).*
