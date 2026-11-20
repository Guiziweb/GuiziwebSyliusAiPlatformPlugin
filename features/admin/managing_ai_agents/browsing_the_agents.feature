@managing_ai_agents
Feature: Browsing the agents
    In order to see which assistants my plugins bring
    As an Administrator
    I want to list the agents

    Background:
        Given I am logged in as an administrator
        And there is an agent named "Sample assistant"

    @ui
    Scenario: Seeing the agents of the installed plugins
        When I browse the AI agents
        Then I should see the agent "Sample assistant"
        And the agent "Sample assistant" should use the default configuration

    @ui
    Scenario: Seeing the connection and the model an agent uses
        Given there is a connection named "openai-eu" for the "openai" provider
        And the agent "Sample assistant" uses the connection "openai-eu" with the model "gpt-4o-mini"
        When I browse the AI agents
        Then the agent "Sample assistant" should use the connection "openai-eu" with the model "gpt-4o-mini"
