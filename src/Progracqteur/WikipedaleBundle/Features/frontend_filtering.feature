Feature: filtering markers by category

Scenario: Test filtering options
        Given I am on "/city/mons"
        Then "#div_options_affichage" should be not visible
        And I should see "Filtrer par catégories"
        When I click on the text "Filtrer les signalements et export"
        Then I should see "Filtrer par catégories :"