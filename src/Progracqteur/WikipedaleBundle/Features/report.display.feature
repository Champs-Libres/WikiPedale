Feature: Filtering markers by category

@javascript @report_display
Scenario: In click on a report on the map
   Given I am on "/city/mons"
   And I wait that the reports have been received
   Then I randomly choose a current report
   Then I click on the current report
   Then element "#div__report_description_display" should be visible
   Then element "#div_report_description_cat_edit" should not be visible
   Then the current report is well displayed