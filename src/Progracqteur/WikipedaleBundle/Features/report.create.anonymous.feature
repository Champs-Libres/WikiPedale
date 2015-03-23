Feature: Creation of a report as an anonymous user

@javascript @create_report_for_anonymous
Scenario: I open the creation form as an anonymous
   Given I am on "/city/mons"
   And I wait that the reports have been received
   Then I click on "#div_add_new_description_button"
   Then element "#div_new_report_form_user_mail" should be visible
   Then element "#put_marker_on_the_map_fieldset" should be visible
   Then element "#add_new_report_form__draw_details_on_map" should be visible
   Then element "#div_add_new_description__draw" should not be visible
   And I randomly fill in "add_new_report_form__user_label" with "string"
   And I randomly fill in "add_new_report_form__email" with "email"
   Then I randomly select a point on the map
   And I randomly fill in "add_new_report_form__lieu" with "string"
   And I randomly fill in "add_new_report_form__description" with "string"
   And I doubleclick on "#new_report_form_submit_button"
   And I wait for 2.5 seconds
   Then I should see "Traitement en cours" in the "div#add_new_report_form__message" element
   And I wait for 7 seconds
   Then I should see "Le point noir que vous avez soumis a bien été enregistré. Avant d'afficher le point noir, nous allons vérifier votre adresse mail. Veuillez suivre les instructions qui vous ont été envoyées par email." in the "div#add_new_report_form__message" element