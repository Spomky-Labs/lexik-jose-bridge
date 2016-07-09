Feature: A user can authenticate against a website
  In order to authenticate a user
  A JWT is issued after the user credentials are verified by the website

  Background: I am logged in as admin1 and I have a token
    Given I am on "https://www.example.test/login"
    And I fill in "username" with "admin1"
    And I fill in "password" with "admin1"
    And I press "login"
    And the response status code should be 200
    And the response content-type should be "application/json"
    And the response should contain a token
    And I store the token

  Scenario: The user is authenticated and send a valid request
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/anonymous"
    Then I should see "Hello admin1!"

  Scenario: The user is authenticated and send a valid request
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/hello"
    Then I should see "Hello admin1!"

  Scenario: The user is authenticated and send a valid request
    Given I add the token in the authorization header
    When I am on the page "https://www.example.test/api/admin"
    Then I should see "Hello admin1!"
