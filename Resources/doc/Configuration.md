How to configure this bundle?
=============================

# Pre-requisite

This bundle needs services created by the JoseBundle. They are namely:
* A JWT Creator for token generation.
* A JWT Loader for token loading. This loader MUST check the following claims and headers: `exp`, `iat`, `nbf` and `crit`.
* A signature key for token signing. This key can be a private key (if symmetric) or a shared key (if asymmetric).
* A keyset that contain the signature public key (if symmetric) or signature shared key (if asymmetric).

If you want to encrypt the tokens, you will need the following additional services:
* An encryption key for token encryption. This key can be a public key (if symmetric) or a shared key (if asymmetric).
* The keyset MUST also contain the encryption private key (if symmetric) or encryption shared key (if asymmetric).

*Reminder: the Spomky-Labs/JoseBundle creates services according to the ID you set in the configuration*
*A key with the ID `lexik_private_key` will be available through the `jose.key.lexik_private_key` service.*

Example With Encryption Support
-------------------------------

```yml
jose:
    easy_jwt_creator: # The JWT Creator
        lexik:
            signature_algorithms:
                - "RS512"
            key_encryption_algorithms:
                - "A256GCMKW"
            content_encryption_algorithms:
                - "A256GCM"
    easy_jwt_loader: # The JWT Loader
        lexik:
            signature_algorithms:
                - "RS512"
            key_encryption_algorithms:
                - "A256GCMKW"
            content_encryption_algorithms:
                - "A256GCM"
            claim_checkers: # Very important: the following claim checkers MUST be enabled
                - "exp"
                - "iat"
                - "nbf"
            header_checkers: # Very important: the following header checkers MUST be enabled
                - "crit"
    keys:
        lexik_private_key: # We load public and private keys from a file for the signature
            file:
                path: "%kernel.root_dir%/keys/private.key"
                password: "tests"
                additional_values: # Not mandatory but highly recommended
                    kid: "KEY1"
                    alg: "RS512"
        lexik_public_key:
            file:
                path: "%kernel.root_dir%/keys/public.key"
                additional_values: # Not mandatory but highly recommended
                    kid: "KEY1"
                    alg: "RS512"
        lexik_encryption_key: # The encryption key is a symmetric key
            values:
                values:
                    kty: "oct"
                    kid: "KEY2"
                    use: "enc"
                    alg: "A256GCMKW"
                    k: "qC57l_uxcm7Nm3K-ct4GFjx8tM1U8CZ0NLBvdQstiS8"
    key_sets:
        lexik_keyset: # The keyset contains the signature public key and the encryption shared key
            keys:
                id:
                    - "jose.key.lexik_public_key"
                    - "jose.key.lexik_encryption_key"
```

# The Bundle Configuration

Now that all your services are available, we can configure the bundle.

## Without Encryption

```yml
lexik_jose:
    jwt_creator: "jose.jwt_creator.lexik"
    jwt_loader: "jose.jwt_loader.lexik"
    signature_algorithm: "RS512" # The algorithm MUST be supported by the JWT Creator and the JWT Loader
    signature_key: "jose.key.lexik_private_key"
    keyset: "jose.key_set.lexik_keyset"
```

## With Encryption

```yml
lexik_jose:
    ...
    encryption:
        enabled: true
        encryption_key: 'jose.key.lexik_encryption_key'
        key_encryption_algorithm: 'A256GCMKW' # The algorithm MUST be supported by the JWT Creator and the JWT Loader
        content_encryption_algorithm: 'A256GCM' # The algorithm MUST be supported by the JWT Creator and the JWT Loader
```

# Lexik JWT Authentication Bundle Configuration

Now you just have to set the `lexik_jose_bridge.encoder` as encoder service for the Lexik JWT Authentication Bundle:

```yml
lexik_jwt_authentication:
    encoder:
        service: "lexik_jose_bridge.encoder"
```
