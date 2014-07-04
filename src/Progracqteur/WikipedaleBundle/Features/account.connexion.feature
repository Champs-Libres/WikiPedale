Feature: Account connexion

Background:
   Given I am on "/"

@javascript @account_connexion
Scenario: The connexion form is not displayed by default
   Then element "#login_form_div" should not be visible
   And I should see "Mons"

@javascript @account_connexion
Scenario: See connexion form when click on #menu_connexion
   When I click on the element "#menu_connexion a"
   And I wait for 0.5 seconds
   Then element "#login_form_div" should be visible

@javascript @account_connexion
Scenario: Connexion as admin
   When I click on the element "#menu_connexion a"
   And I wait for 0.5 seconds
   And I fill in "login_input_username" with "admin"
   And I fill in "login_input_password" with "admin"
   And I press "Connexion"
   And I wait for 2.5 seconds
   Then I should see "Robert Delieu" in the "div#menu_user_name a span.username" element
   And I take a screenshot with prefix "connexion_with_password"

@javascript @account_connexion
Scenario: Page profile is reachable
   When I click on the element "#menu_connexion a"
   And I wait for 1 seconds
   And I fill in "login_input_username" with "admin"
   And I fill in "login_input_password" with "admin"
   And I press "Connexion"
   And I wait for 1 seconds
   When I click on the element "#menu_user_name"
   Then I am on "/profile"