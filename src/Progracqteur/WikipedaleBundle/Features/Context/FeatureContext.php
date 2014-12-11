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
   private $currentReport;

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
   * @Given /^I submit "([^"]*)" \(ajax\)$/
   */
   public function stepISubmitForm($cssSelector)
   {

      $element = $this->getSession()->getPage()->find('css', $cssSelector);
 
      if (null === $element) {
         throw new Exception("'$cssSelector' button not found");
      }

      $script = '$("#loginForm").submit();';
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
      $element = $this->getSession()->getPage()->find('css', $cssSelector);

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
    * @When /^I doubleclick on "([^"]*)"$/
    */
   public function iDoubleClickOnTheElement($cssSelector)
   {
      $element = $this->getSession()->getPage()->find('css', $cssSelector);

      if(! $element) {
         throw new \Exception("Element with id $id not found");
      }

      $element->doubleClick();
   }

   /**
    * Clicks on an css selected element
    *
    * @When /^I click on "([^"]*)"$/
    */
   public function iClickOnTheElement($cssSelector)
   {
      $element = $this->getSession()->getPage()->find('css', $cssSelector);

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
   public function randomlyfillFieldWith($field, $type)
   {
      $value = md5(uniqid(rand(0,1000), true)); 
      if($type === 'email') {
         $value = $value . '@' . md5(uniqid(rand(0,1000), true)) . '.com';
      }

      $field = $this->fixStepArgument($field);
      $this->getSession()->getPage()->fillField($field, $value);
   }


   /**
    * @Given /^I randomly fill in "([^"]*)"$/
    */
   public function randomlyfillField($field)
   {
      $this->randomlyfillFieldWith($field, "string");
   }


   /**
    * @Then /^the current report is well displayed$/
    */
   public function reportIsWellDisplayed()
   {
      $this->assertElementContains("#div__report_description_display", 
         $this->currentReport->getCreator()->getLabel());
      $this->assertElementContains("#div__report_description_display", 
         $this->currentReport->getDescription());
   }

    /**
     * @Then /^I wait that the reports have been received$/
     */
    public function iWaitReportsReceived()
    {
        usleep(500);
        $reportReceived = false;
        while(! $reportReceived) {
            $reportReceived = (boolean) $this->getSession()->getDriver()
            ->evaluateScript('return require("report").isInitialized();');  
        }
    }

    /**
     * @Given /^I randomly choose a current report$/
     */
    public function randomlyChooseAReport()
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $idCurrentReport = $this->getSession()->getDriver()
            ->evaluateScript('return require("report").getARandomReportId();');

        $this->currentReport = $em->getRepository('ProgracqteurWikipedaleBundle:Model\\Report')->findOneById($idCurrentReport);
    }


    /**
     *  @Then /^I randomly select a point on the map$/
     */
    public function randomlySelectAPointOnTheMap()
    {
        // Mons
        $javascript = "document.getElementById('add_new_report_form__lat').value='" . (rand(504500, 504570)/10000) . "'";
        $this->getSession()->executeScript($javascript);
        $javascript = "document.getElementById('add_new_report_form__lon').value='" . (rand(39400, 39620)/10000) .  "'";
        $this->getSession()->executeScript($javascript);
    }

    /**
     * @Given /^I click on the current report$/
     */
    public function iclickOnTheCurrentReport()
    {
        $this->getSession()->getDriver()
            ->evaluateScript('require("data_map_glue").focusOnReport(' . $this->currentReport->getId() .');');
    }
}
