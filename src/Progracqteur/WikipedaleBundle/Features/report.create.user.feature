Feature: Creation of a report as a logged user

Background:
   Given I am on "/city/mons"
   And I wait that the reports have been received
   Then I click on "#menu_connexion a"
   And I wait for 0.5 seconds
   And I fill in "login_input_username" with "user"
   And I fill in "login_input_password" with "user"
   And I doubleclick on "#login_input_submit"
   And I wait for 3 seconds
   Then I should see "Arnaud Bobo" in the "div#menu_user_name a span.username" element

@javascript @report_create_user
Scenario: I open the creation form as an user
   Then I click on "#div_add_new_description_button"
   Then element "#div_new_report_form_user_mail" should not be visible
   Then element "#put_marker_on_the_map_fieldset" should be visible
   Then element "#add_new_report_form__draw_details_on_map" should be visible
   Then element "#div_add_new_description__draw" should not be visible