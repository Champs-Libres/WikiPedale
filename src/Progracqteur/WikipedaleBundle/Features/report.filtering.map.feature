Feature: Filtering markers by category

@javascript @filtering_map
Scenario: Open filtering option menu and closing it
   Given I am on "/city/mons"
   Then element "#filter_and_export_menu" should not be visible
   Then element "#stop_filter_and_export_button" should not be visible
   Then element "#filter_and_export_button" should be visible
   When I click on the element "#filter_and_export_button"
   And I wait for 1 seconds
   Then element "#filter_and_export_menu" should be visible
   Then element "#stop_filter_and_export_button" should be visible
   Then element "#filter_and_export_button" should not be visible
   When I click on the element "#stop_filter_and_export_button"
   And I wait for 1 seconds
   Then element "#filter_and_export_menu" should not be visible
   Then element "#stop_filter_and_export_button" should not be visible
   Then element "#filter_and_export_button" should be visible
   When I click on the element "#filter_and_export_button"