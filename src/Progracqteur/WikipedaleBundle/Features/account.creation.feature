Feature: Creating an account

Background:
   Given I am on "/"

@javascript @account_creation
Scenario: Show the creation form
   When I click on the element "#menu_register a"
   Then I am on "/register"
   And element "#fos_user_registration_form_username" should be visible
   And element "#fos_user_registration_form_email" should be visible
   And element "#fos_user_registration_form_plainPassword_first" should be visible
   And element "#fos_user_registration_form_plainPassword_second" should be visible
   And element "#fos_user_registration_form_label" should be visible
   And element "#fos_user_registration_form_phonenumber" should be visible

@javascript @account_creation
Scenario: Good submission
   Given I am on "/register"
   And I randomly fill in "fos_user_registration_form_username" with "string"
   And I randomly fill in "fos_user_registration_form_email" with "email"
   And I fill in "fos_user_registration_form_plainPassword_first" with "password"
   And I fill in "fos_user_registration_form_plainPassword_second" with "password"
   And I randomly fill in "fos_user_registration_form_label" with "string"
   And I randomly fill in "fos_user_registration_form_phonenumber" with "string"
   And I press "Enregistrer"
   Then I am on "/register/check-email"

@javascript @account_creation
Scenario: Empty form is not submitted
   Given I am on "/register"
   And I press "Enregistrer"
   Then I am on "/register"

@javascript @account_creation
Scenario: Form with different password is not submitted
   Given I am on "/register"
   And I randomly fill in "fos_user_registration_form_username" with "string"
   And I randomly fill in "fos_user_registration_form_email" with "email"
   And I fill in "fos_user_registration_form_plainPassword_first" with "password"
   And I fill in "fos_user_registration_form_plainPassword_second" with "anotherpwd"
   And I randomly fill in "fos_user_registration_form_label" with "string"
   And I randomly fill in "fos_user_registration_form_phonenumber" with "string"
   And I press "Enregistrer"
   Then I am on "/register"

@javascript @account_creation
Scenario: Form with invalid email is not submitted
   Given I am on "/register"
   And I randomly fill in "fos_user_registration_form_username" with "string"
   And I randomly fill in "fos_user_registration_form_email" with "string"
   And I fill in "fos_user_registration_form_plainPassword_first" with "password"
   And I fill in "fos_user_registration_form_plainPassword_second" with "password"
   And I randomly fill in "fos_user_registration_form_label" with "string"
   And I randomly fill in "fos_user_registration_form_phonenumber" with "string"
   And I press "Enregistrer"
   Then I am on "/register"

@javascript @account_creation
Scenario: Form with empty username is not submitted
   Given I am on "/register"
   And I randomly fill in "fos_user_registration_form_email" with "email"
   And I fill in "fos_user_registration_form_plainPassword_first" with "password"
   And I fill in "fos_user_registration_form_plainPassword_second" with "password"
   And I randomly fill in "fos_user_registration_form_label" with "string"
   And I randomly fill in "fos_user_registration_form_phonenumber" with "string"
   And I press "Enregistrer"
   Then I am on "/register"

@javascript @account_creation
Scenario: Form with empty label is not submitted
   Given I am on "/register"
   And I randomly fill in "fos_user_registration_form_username" with "string"
   And I randomly fill in "fos_user_registration_form_email" with "email"
   And I fill in "fos_user_registration_form_plainPassword_first" with "password"
   And I fill in "fos_user_registration_form_plainPassword_second" with "password"
   And I randomly fill in "fos_user_registration_form_phonenumber" with "string"
   And I press "Enregistrer"
   Then I am on "/register"


@javascript @account_creation
Scenario: Form with empty phonenumber is not submitted
   Given I am on "/register"
   And I randomly fill in "fos_user_registration_form_username" with "string"
   And I randomly fill in "fos_user_registration_form_email" with "email"
   And I fill in "fos_user_registration_form_plainPassword_first" with "password"
   And I fill in "fos_user_registration_form_plainPassword_second" with "password"
   And I randomly fill in "fos_user_registration_form_label" with "string"
   Then I am on "/register"