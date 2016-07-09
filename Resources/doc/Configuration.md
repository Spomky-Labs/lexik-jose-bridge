How to configure this bundle?
=============================

# Pre-requisite

This bundle needs services created by the JoseBundle. They are namely:
* A signature key for token signing. This key can be a private key (if symmetric) or a shared key (if asymmetric).

If you want to encrypt the tokens, you will need the following additional services:
* An encryption key for token encryption. This key can be a private key (if symmetric) or a shared key (if asymmetric).

If private keys are provided, public keys are automatically computed.

*Reminder: the Spomky-Labs/JoseBundle creates services according to the ID you set in the configuration*
*A key with the ID `lexik_signature_key` will be available through the `jose.key.lexik_signature_key` service.*

Example With Encryption Support
-------------------------------

```yml
jose:
    keys:
        lexik_signature_key: # We load a private key from a file for the signature
            file:
                path: "%kernel.root_dir%/keys/private.key"
                password: "tests"
                additional_values: # Not mandatory but highly recommended
                    kid: "KEY1"
                    alg: "RS512"
                    use: "sig"
        lexik_encryption_key: # The encryption key is a symmetric key
            values:
                values:
                    kty: "oct"
                    kid: "KEY2"
                    use: "enc"
                    alg: "A256GCMKW"
                    k: "qC57l_uxcm7Nm3K-ct4GFjx8tM1U8CZ0NLBvdQstiS8"
```

# The Bundle Configuration

Now that all your services are available, we can configure the bundle.

## Without Encryption

```yml
lexik_jose:
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

# Lexik JWT Authentication Bundle Configuration

Now you just have to set the `lexik_jose_bridge.encoder` as encoder service for the Lexik JWT Authentication Bundle:

```yml
lexik_jwt_authentication:
    encoder:
        service: "lexik_jose_bridge.encoder"
```
