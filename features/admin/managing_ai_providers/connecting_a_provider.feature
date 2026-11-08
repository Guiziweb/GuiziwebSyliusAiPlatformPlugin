@managing_ai_providers
Feature: Connecting a provider
    In order to let the assistants of my shop use an AI provider
    As an Administrator
    I want to connect an account of that provider

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: Connecting an account of a provider
        When I want to connect a provider of type "openai"
        And I name it "openai-eu"
        And I set its "api_key" setting to "sk-eu-key"
        And I set its "region" setting to "EU"
        And I add it
        Then I should be notified that it has been successfully created
        And the connection "openai-eu" should appear in the registry

    @ui
    Scenario: Connecting two accounts of the same provider
        Given there is a connection named "openai-eu" for the "openai" provider
        When I want to connect a provider of type "openai"
        And I name it "openai-us"
        And I set its "api_key" setting to "sk-us-key"
        And I add it
        Then I should be notified that it has been successfully created
        And there should be 2 connections in the registry

    @ui
    Scenario: Being refused a name that another connection already uses
        Given there is a connection named "openai-eu" for the "openai" provider
        When I want to connect a provider of type "mistral"
        And I name it "openai-eu"
        And I set its "api_key" setting to "mistral-key"
        And I add it
        Then I should be told that "Another connection already uses this code."
        And there should be 1 connection in the registry

    @ui
    Scenario: Being refused a name with forbidden characters
        When I want to connect a provider of type "openai"
        And I name it "Bad Code!"
        And I set its "api_key" setting to "sk-key"
        And I add it
        Then I should be told that "The code can only contain letters, digits, dashes and underscores."
        And there should be 0 connections in the registry

    @ui
    Scenario: Being refused a required setting left blank
        When I want to connect a provider of type "openai"
        And I name it "openai-eu"
        And I add it
        Then I should be told that "This value should not be blank."
        And there should be 0 connections in the registry

    @ui
    Scenario: Finding what I typed after an error
        When I want to connect a provider of type "openai"
        And I name it "Bad Code!"
        And I set its "api_key" setting to "sk-key"
        And I set its "region" setting to "EU"
        And I add it
        Then I should be told that "The code can only contain letters, digits, dashes and underscores."
        And the form should still show the name "Bad Code!"
        And the form should still show its "api_key" setting as "sk-key"
        And the form should still show its "region" setting as "EU"

    @ui
    Scenario: Getting the settings of the chosen provider
        When I want to connect a provider of type "anthropic"
        Then the form should have a "api_key" setting
        And the form should have a "cache_retention" setting
        And the form should not have a "region" setting
        And the form should not have a "http_client" setting
        And the "cache_retention" setting should offer "none, short, long"
