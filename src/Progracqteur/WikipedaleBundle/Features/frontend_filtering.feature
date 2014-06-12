Feature: filtering markers by category

@javascript @ajd
Scenario: Test filtering options
        Given I am on "/city/mons"
        Then element "#div_options_affichage" should not be visible
        When I click on the element "#buttonOptionsAffichage"
        And I wait for 2 seconds
        Then I should see "Filtrer par catégories"