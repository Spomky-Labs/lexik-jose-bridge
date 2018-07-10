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

  Scenario: The token must contain all claims and custom claims
    Given the token must contain the claim "exp"
    And the token must contain the claim "jti"
    And the token must contain the claim "iat"
    And the token must contain the claim "username" with value "admin1"
    And the token must contain the claim "ip" with value "127.0.0.1"
    And the token must contain the claim "iss" with value "https://my.super-service.org/"
    And the token must contain the claim "aud" with value "MyProject1"
