Feature: The firewall must be able to detect bad tokens
  The tokens may have been modified or may have expired
  The firewall MUST reject all request with a bad token

  Scenario: The token expired
    Given I have an expired, signed and encrypted token
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And the response should contain "The JWT has expired."

  Scenario: The token is signed but not encrypted
    Given I have a valid signed token
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And the response should contain "The assertion must be encrypted."

  Scenario: The token has a wrong issuer
    Given I have a signed and encrypted token but with wrong issuer
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And the response should contain "The issuer \u0022BAD ISSUER\u0022 is not allowed."

  Scenario: The token has a wrong audience
    Given I have a signed and encrypted token but with wrong audience
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And the response should contain "The audience \u0022BAD AUDIENCE\u0022 is not known."

  Scenario: The token algorithm is not supported
    Given I have a token with an unsupported algorithm
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And the response should contain "The signature algorithm \u0022none\u0022 is not supported or not allowed."

  Scenario: The token signature is not valid (body modified)
    Given I have a modified token
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And the response should contain "Unable to verify the JWS."
