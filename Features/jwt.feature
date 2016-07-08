Feature: A user can authenticate against a website
  In order to authenticate a user
  A JWT is issued after the user credentials are verified by the website

  Background: I am logged in as user1
    Given I am on "https://www.example.test/login"
    And I fill in "username" with "user1"
    And I fill in "password" with "user1"
    And I press "login"
    And the response status code should be 200
    And the response content-type should be "application/json"
    And the response should contain a token
    And I store the token

  Scenario: The user authenticate against a website
    Given I am on "https://www.example.test/api/foo"
