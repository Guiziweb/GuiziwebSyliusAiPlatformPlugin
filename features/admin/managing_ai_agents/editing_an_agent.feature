@managing_ai_agents
Feature: Editing an agent
    In order to adapt an assistant to my shop
    As an Administrator
    I want to change the prompt, the connection and the model of an agent

    Background:
        Given I am logged in as an administrator
        And there is an agent named "Sample assistant"
        And there is a connection named "openai-eu" for the "openai" provider

    @ui
    Scenario: Changing the prompt of an agent
        When I want to modify the agent "Sample assistant"
        And I change its prompt to "You always answer in rhymes"
        And I save my changes
        Then I should be notified that it has been successfully edited
        And I want to modify the agent "Sample assistant"
        And its prompt should be "You always answer in rhymes"

    @ui
    Scenario: Not being able to save an agent without a prompt
        When I want to modify the agent "Sample assistant"
        And I clear its prompt
        And I save my changes
        Then I should be told that "This value should not be blank."

    @ui
    Scenario: Not being able to choose a connection without a model
        When I want to modify the agent "Sample assistant"
        And I choose the connection "openai-eu" for it
        And I save my changes
        Then I should be told that "Choose a model for this provider."

    @ui
    Scenario: Keeping the connection and the model when only the prompt changes
        Given the agent "Sample assistant" uses the connection "openai-eu" with the model "gpt-4o-mini"
        When I want to modify the agent "Sample assistant"
        And I change its prompt to "You always answer in rhymes"
        And I save my changes
        Then I should be notified that it has been successfully edited
        And I want to modify the agent "Sample assistant"
        And its connection should be "openai-eu"
        And its model should be "gpt-4o-mini"

    @ui
    Scenario: Keeping a model the connection no longer lists
        Given the agent "Sample assistant" uses the connection "openai-eu" with the model "retired-model"
        When I want to modify the agent "Sample assistant"
        Then the model "retired-model" should still be offered
        And I change its prompt to "You always answer in rhymes"
        And I save my changes
        And I should be notified that it has been successfully edited

    @ui
    Scenario: Not offering a disabled connection
        Given the connection "openai-eu" is disabled
        When I want to modify the agent "Sample assistant"
        Then the connection "openai-eu" should not be offered

    @ui
    Scenario: Still offering the disabled connection an agent already uses
        Given the agent "Sample assistant" uses the connection "openai-eu" with the model "gpt-4o-mini"
        And the connection "openai-eu" is disabled
        When I want to modify the agent "Sample assistant"
        Then the connection "openai-eu" should be offered
