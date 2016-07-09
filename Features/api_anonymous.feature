Feature: An anonymous user can access on unprotected routes

  Scenario: The user is not authenticated and access on an unprotected route
    When I am on the page "https://www.example.test/api/anonymous"
    Then I should see "Hello anonymous!"

  Scenario: The user is not authenticated and access on a protected route
    When I am on the page "https://www.example.test/api/hello"
    Then the response status code should be 401

  Scenario: The user is not authenticated and access on a protected route
    When I am on the page "https://www.example.test/api/admin"
    Then the response status code should be 401
