Feature: A Console Command to rotate keys

  Scenario:  I warm up the cache to generate the keys
    When I run command "spomky-labs:lexik_jose:delete"
    And the file "%kernel.cache_dir%/signature.jwkset" should not exist
    And the file "%kernel.cache_dir%/encryption.jwkset" should not exist
    And I run command "cache:warmup"
    Then The command exit code should be 0
    And the file "%kernel.cache_dir%/signature.jwkset" should exist
    And the file "%kernel.cache_dir%/encryption.jwkset" should exist

  Scenario:  I want to delete the ket sets
    When I run command "spomky-labs:lexik_jose:delete"
    Then The command exit code should be 0
    And I should see
    """
    Done.

    """

  Scenario:  I want to regen the ket sets
    When I run command "spomky-labs:lexik_jose:regen"
    Then The command exit code should be 0
    And I should see
    """
    Done.

    """

  Scenario:  I want to rotate the ket sets
    When I run command "spomky-labs:lexik_jose:rotate" with parameters
    """
    {
        "ttl": "7 days"
    }
    """
    Then The command exit code should be 0
    And I should see
    """
    Done.

    """

  Scenario:  I want to rotate the ket sets
    When I run command "spomky-labs:lexik_jose:rotate" with parameters
    """
    {
        "ttl": "0 second"
    }
    """
    Then The command exit code should be 0
    And I should see
    """
    Done.

    """
