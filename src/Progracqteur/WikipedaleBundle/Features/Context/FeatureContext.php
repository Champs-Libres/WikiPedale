<?php

namespace Progracqteur\WikipedaleBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;


require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';


/**
 * Feature context.
 */
class FeatureContext extends MinkContext
                  implements KernelAwareInterface
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
        'xpath',
        $session->getSelectorsHandler()->selectorToXpath('xpath', '*//*[text()="'. $text .'"]')
    );
    if (null === $element) {
        throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
    }

    $element->click();

}

/**
 * @Then /^"([^"]*)" should be visible$/
 */
public function shouldBeVisible($selector) {
    // Semble ne pas marcher
    $el = $this->getSession()->getPage()->find('css', $selector);
    $style = '';
    if(!empty($el)){
        $style = preg_replace('/\s/', '', $el->getAttribute('style'));
    } else {
        throw new Exception("Element ({$selector}) not found");
    }

    assertFalse(false !== strstr($style, 'display:none'));
}


/**
 * @Then /^"([^"]*)" should be not visible$/
 */
public function shouldBeNotVisible($selector) {
    // Semble ne pas marcher
    $el = $this->getSession()->getPage()->find('css', $selector);
    $style = '';
    if(!empty($el)){
        $style = preg_replace('/\s/', '', $el->getAttribute('style'));
    } else {
        throw new Exception("Element ({$selector}) not found");
    }

    assertTrue(false !== strstr($style, 'display:none'));
}

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument)
//    {
//        $container = $this->kernel->getContainer();
//        $container->get('some_service')->doSomethingWith($argument);
//    }
//
}
