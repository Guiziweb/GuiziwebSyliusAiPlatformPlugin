@managing_ai_providers
Feature: Removing a provider connection
    In order to stop using an AI account
    As an Administrator
    I want to remove its connection

    Background:
        Given I am logged in as an administrator
        And there is a connection named "openai-eu" for the "openai" provider
        And there is a connection named "openai-us" for the "openai" provider

    @ui
    Scenario: Removing one of two connections
        When I delete the connection "openai-eu"
        Then I should be notified that it has been successfully deleted
        And the connection "openai-eu" should no longer exist in the registry
        And the connection "openai-us" should appear in the registry

    @ui
    Scenario: A connection used by an agent cannot be removed
        Given the connection "openai-eu" is used by an agent
        When I delete the connection "openai-eu"
        Then I should be notified that it is in use and cannot be deleted
        And the connection "openai-eu" should appear in the registry
