Feature: The firewall must be able to detect bad tokens
  The tokens may be missing claims
  The firewall MUST reject all request with a bad token

  Scenario: The token is missing the Expiration Time claim
    Given I have a signed and encrypted token but without the "exp" claim
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And print last response
    And the response should contain "Invalid JWT Token"
    And the error listener should receive an invalid token event containing an exception with message "The following claims are mandatory: exp."

  Scenario: The token is missing the Issued At claim
    Given I have a signed and encrypted token but without the "iat" claim
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And print last response
    And the response should contain "Invalid JWT Token"
    And the error listener should receive an invalid token event containing an exception with message "The following claims are mandatory: iat."

  Scenario: The token is missing the JWT ID claim
    Given I have a signed and encrypted token but without the "jti" claim
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And print last response
    And the response should contain "Invalid JWT Token"
    And the error listener should receive an invalid token event containing an exception with message "The following claims are mandatory: jti."

  Scenario: The token is missing the Issuer claim
    Given I have a signed and encrypted token but without the "iss" claim
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And print last response
    And the response should contain "Invalid JWT Token"
    And the error listener should receive an invalid token event containing an exception with message "The following claims are mandatory: iss."

  Scenario: The token is missing the Audience claim
    Given I have a signed and encrypted token but without the "aud" claim
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401
    And print last response
    And the response should contain "Invalid JWT Token"
    And the error listener should receive an invalid token event containing an exception with message "The following claims are mandatory: aud."
