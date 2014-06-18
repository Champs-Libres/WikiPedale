Feature: filtering markers by category

@javascript
Scenario: Test filtering options
        Given I am on "/city/mons"
        Then element "#filter_and_export_menu" should not be visible
        When I click on the element "#buttonOptionsAffichage"
        And I wait for 2 seconds
        Then I should see "Filtrer par catégories"
