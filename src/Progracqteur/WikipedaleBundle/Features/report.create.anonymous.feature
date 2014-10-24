Feature: Creation of a report as an anonymous user

@javascript @report_create_anonymous
Scenario: I open the creation form as an anonymous
   Given I am on "/city/mons"
   And I wait that the reports have been received
   Then I click on "#div_add_new_description_button"
   Then element "#div_new_report_form_user_mail" should be visible
   Then element "#put_marker_on_the_map_fieldset" should be visible
   Then element "#add_new_report_form__draw_details_on_map" should be visible
   Then element "#div_add_new_description__draw" should not be visible




