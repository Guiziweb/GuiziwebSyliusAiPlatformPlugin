@managing_ai_providers
Feature: Checking the platform of a connection
    In order to know whether a connection can be used
    As an Administrator
    I want to check that the platform of a connection can be built

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: Being told how to install the bridge of a provider
        Given there is a connection named "gemini-main" for the "gemini" provider
        When I check the platform of the connection "gemini-main"
        Then I should be notified that the bridge of the "symfony/ai-gemini-platform" package is missing

    @ui
    Scenario: Checking a connection whose bridge is installed
        Given there is a connection named "openai-eu" for the "openai" provider
        When I check the platform of the connection "openai-eu"
        Then I should be notified that the platform can be built
