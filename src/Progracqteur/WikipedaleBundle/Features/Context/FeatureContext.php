<?php

namespace Progracqteur\WikipedaleBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//require_once 'PHPUnit/Autoload.php';
//require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context.
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
   private $kernel;
   private $parameters;

   /**
    * Initializes context with parameters from behat.yml.
    *
    * @param array $parameters
    */
   public function __construct(array $parameters)
   {
      $this->parameters = $parameters;
   }

   /**
    * Sets HttpKernel instance.
    * This method will be automatically called by Symfony2Extension ContextInitializer.
    *
    * @param KernelInterface $kernel
    */
   public function setKernel(KernelInterface $kernel)
   {
      $this->kernel = $kernel;
   }

   /**
    * Clicks on some text
    *
    * @When /^I click on the text "([^"]*)"$/
    */
   public function iClickOnTheText($text)
   {
      $session = $this->getSession();
      $element = $session->getPage()->find(
         'xpath',
         $session->getSelectorsHandler()->selectorToXpath('xpath', '*//*[text()="' . $text . '"]'));
      if (null === $element) {
         throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
      }

      $element->click();
   }
   
   /**
    * Clicks on an xpath selected element
    *
    * @When /^I click on the element with xpath "([^"]*)"$/
    */
   public function iClickOnTheElementWithXpath($xpath)
   {
      $this->getSession()->getDriver()->click($xpath);
   }

   /**
    * Waits for a given number of seconds (integer / float)
    *
    * @When /^I wait for (\d+|(\d+\.\d+)|(\.\d+)) seconds$/
    */
   public function iWaitForSeconds($seconds)
   {
      $this->getSession()->getDriver()->wait(floatval($seconds)*1000, '');
   }

   /**
    * Checks that the xpath selected element is not visible
    *
    * @Then /^Element with xpath "([^"]*)" should be visible$/
    */
   public function shouldBeVisibleXpath($xpath)
   {
      $el = $this->getSession()->getDriver()->find($xpath);

      if (count($el) === 0) {
         throw new \Exception("Element with xpath $xpath not found");
      } elseif (count($el) > 1) {
         throw new \Exception(count($el) . " elements with xpath $xpath were found");
      }

      if (!$this->getSession()->getDriver()->isVisible($xpath)) {
         throw new \Exception("the element with xpath $xpath is not visible");
      }
   }

   /**
    * Checks that the xpath selected element is not visible
    *
    * @Then /^Element with xpath "([^"]*)" should not be visible$/
    */
   public function shouldBeNotVisibleXpath($xpath)
   {
      $el = $this->getSession()->getDriver()->find($xpath);

      if (count($el) === 0) {
         throw new \Exception("Element with xpath $xpath not found");
      } elseif (count($el) > 1) {
         throw new \Exception(count($el) . " elements with xpath $xpath were found");
      }

      if ($this->getSession()->getDriver()->isVisible($xpath)) {
         throw new \Exception("the element with xpath $xpath is visible");
      }
   }
   
   /**
    * Takes a screenshot that is saved with a given prefix
    *
    * @Given /^I take a screenshot with prefix "([^"]*)"$/
    */
   public function iTakeAScreenshot($prefix)
   {
      $screenshot = $this->getSession()->getDriver()->getScreenshot();
      
      $date = new \DateTime();
      $filename = $prefix.'_'.$date->format('Y-m-d-H-i');
      
      //create the dir if not exists :
      if (!file_exists('./tmp/')) {
         mkdir('./tmp');
      }
      
      file_put_contents('./tmp/'.$filename.'.png', $screenshot);
   }


   /**
    * Checks that the css selected element is visible
    *
    * @Then /^element "([^"]*)" should not be visible$/
    */
   public function elementShouldNotBeVisible($cssSelector)
   {
      $session = $this->getSession();
      $element = $session->getPage()->find('css', $cssSelector);

      if(! $element) {
         throw new \Exception("Element $cssSelector not found");
      }

      if($element->isVisible()) {
         throw new \Exception("Element $cssSelector is visible");
      }
   }


   /**
    * Checks that the css selected element is visible
    *
    * @Then /^element "([^"]*)" should be visible$/
    */
   public function elementShouldBeVisible($cssSelector)
   {
      $session = $this->getSession();
      $element = $session->getPage()->find('css', $cssSelector);

      if(! $element) {
         throw new \Exception("Element $cssSelector not found");
      }

      if(!$element->isVisible()) {
         throw new \Exception("Element $cssSelector is not visible");
      }
   }

   /**
    * Clicks on an css selected element
    *
    * @When /^I click on the element "([^"]*)"$/
    */
   public function iClickOnTheElement($cssSelector)
   {
      $session = $this->getSession();
      $element = $session->getPage()->find('css', $cssSelector);

      if(! $element) {
         throw new \Exception("Element with id $id not found");
      }

      $element->click();
   }

   /**
    * Fills in form field with specified id|name|label|value.
    *
    * @When /^(?:|I )randomly fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<type>(?:[^"]|\\")*)"$/
    */
   public function fillField($field, $type)
   {
      $value = md5(uniqid(rand(0,1000), true)); 
      if($type === 'email') {
         $value = $value . '@' . md5(uniqid(rand(0,1000), true)) . '.com';
      }
      $field = $this->fixStepArgument($field);
      $this->getSession()->getPage()->fillField($field, $value);
    }
}
