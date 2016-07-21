How to configure this bundle?
=============================

# Pre-requisite

This bundle needs services created by the JoseBundle. They are namely:
* A signature key for token signing.
* An optional encryption key for token encryption.

These keys can be a private key (if symmetric) or a shared key (if asymmetric). When private keys are used, associated public keys are automatically computed.

Example With Encryption Support
-------------------------------

```yml
jose:
    keys:
        lexik_signature_key: # This is the key ID. We load a private key from a file for the signature
            file:
                path: "%kernel.root_dir%/keys/private.key"
                password: "Optional password if the key is encrypted"
                additional_values: # Not mandatory but highly recommended
                    kid: "KEY1"
                    alg: "RS512"
                    use: "sig"
        lexik_encryption_key: # This is the key ID. The encryption key is a symmetric key
            values:
                values:
                    kty: "oct"
                    kid: "KEY2"
                    use: "enc"
                    alg: "A256GCMKW"
                    k: "qC57l_uxcm7Nm3K-ct4GFjx8tM1U8CZ0NLBvdQstiS8"
```

*Reminder: the Spomky-Labs/JoseBundle creates services according to the key ID you set in the configuration: `jose.key.keyID`*
*The keys loaded above will be available through the services `jose.key.lexik_signature_key` and `jose.key.lexik_encryption_key`.*

# The Bundle Configuration

Now that all your keys are available through services, we can configure the bundle.

## Without Encryption

```yml
lexik_jose:
    server_name: "https://my.super.service/"
    signature_algorithm: "RS512"
    signature_key: "jose.key.lexik_signature_key"
```

## With Encryption

```yml
lexik_jose:
    ...
    encryption:
        enabled: true
        encryption_key: 'jose.key.lexik_encryption_key'
        key_encryption_algorithm: 'A256GCMKW'
        content_encryption_algorithm: 'A256GCM'
```

**Note: we highly recommend you to enable the encryption support as the token may contain very sensitive information**

# Lexik JWT Authentication Bundle Configuration

Now you just have to set the `lexik_jose_bridge.encoder` as encoder service for the Lexik JWT Authentication Bundle:

```yml
lexik_jwt_authentication:
    encoder:
        service: "lexik_jose_bridge.encoder"
```

*Note: you can find [a complete example from our application configuration used for the tests](https://github.com/Spomky-Labs/lexik-jose-bridge/blob/master/Tests/app/config/config.yml#L31-L61).*
