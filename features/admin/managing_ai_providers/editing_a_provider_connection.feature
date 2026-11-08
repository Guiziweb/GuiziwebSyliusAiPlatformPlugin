@managing_ai_providers
Feature: Editing a provider connection
    In order to keep my AI accounts up to date
    As an Administrator
    I want to change the settings of a connection

    Background:
        Given I am logged in as an administrator
        And there is a connection named "openai-eu" for the "openai" provider with the "region" setting set to "EU"

    @ui
    Scenario: Changing a setting of a connection
        When I want to modify the connection "openai-eu"
        And I change its "region" setting to "US"
        And I save my changes
        Then I should be notified that it has been successfully edited
        And I want to modify the connection "openai-eu"
        And its "region" setting should be "US"

    @ui
    Scenario: Not being able to rename a connection or change its provider
        When I want to modify the connection "openai-eu"
        Then its name should not be editable
        And its provider type should be "openai"
