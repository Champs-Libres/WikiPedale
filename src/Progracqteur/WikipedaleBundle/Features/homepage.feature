Feature: having a homepage

Background:
   Given I am on "/"

@javascript
Scenario: Show the homepage
   Then Element with xpath "//div[@id='login_form_div']" should not be visible
   And I should see "Mons"

@javascript
Scenario: See connexion form
   When I click on the element with xpath "//div[@id='menu_connexion']/a"
   And I wait for 2 seconds
   Then Element with xpath "//form[@id='loginForm']" should be visible

@javascript
Scenario: Connexion with password
   When I click on the element with xpath "//div[@id='menu_connexion']/a"
   And I wait for 2 seconds
   And I fill in "login_input_username" with "admin"
   And I fill in "login_input_password" with "admin"
   And I press "Connexion"
   And I wait for 1 seconds
   Then I should see "Robert Delieu" in the "div#menu_user_name a span.username" element
   And I take a screenshot with prefix "connexion_with_password"

@javascript
Scenario: Page profile is reachable
   When I click on the element with xpath "//div[@id='menu_connexion']/a"
   And I wait for 2 seconds
   And I fill in "login_input_username" with "admin"
   And I fill in "login_input_password" with "admin"
   And I press "Connexion"
   And I wait for 1 seconds
   And I click on the element with xpath "//*[@id='menu_user_name']/a"
   Then I am on "/profile"