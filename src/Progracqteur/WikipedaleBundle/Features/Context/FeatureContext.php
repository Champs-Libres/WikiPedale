<?php

namespace Progracqteur\WikipedaleBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
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
    * Click some text
    *
    * @When /^I click on the text "([^"]*)"$/
    */
   public function iClickOnTheText($text)
   {
      $session = $this->getSession();
      $element = $session->getPage()->find(
              'xpath', $session->getSelectorsHandler()->selectorToXpath('xpath', '*//*[text()="' . $text . '"]')
      );
      if (null === $element) {
         throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
      }

      $element->click();
   }
   
   /**
    * @When /^I click on the element with xpath "([^"]*)"$/
    */
   public function iClickOnTheElementWithXpath($xpath)
   {
      $this->getSession()->getDriver()->click($xpath);
   }

   /**
    * @When /^I wait for (\d+) seconds$/
    */
   public function iWaitForSeconds($seconds)
   {
      $this->getSession()->getDriver()->wait($seconds*1000, '');
   }

   /**
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

}
