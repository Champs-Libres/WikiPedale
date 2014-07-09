<?php

namespace Progracqteur\WikipedaleBundle\Tests\Entity\Model;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Model\Category;

class CategoryTest extends WebTestCase
{
    /**
     * Test the isParent method over a parent and a children
     */
    public function testIsParentMethod() {
        $parent = $this->newRandomCategory();
        $children = $this->newRandomCategory();
        $children->setParent($parent);
        $this->assertTrue($parent->isParent());
        $this->assertFalse($children->isParent());
    }

    /**
     * Test the getParent method over a parent and a children
     */
    public function testgetParentMethod() {
        $parent = $this->newRandomCategory();
        $children = $this->newRandomCategory();
        $children->setParent($parent);
        $this->assertNull($parent->getParent());
        $this->assertEquals($children->getParent(),$parent);
    }

    /**
     * Test the getChildren method over a parent and a children
     */
    public function testgetChildrenMethod() {
        $parent = $this->newRandomCategory();
        $children1 = $this->newRandomCategory();
        $children2 = $this->newRandomCategory();
        $children1->setParent($parent);
        $children2->setParent($parent);
        $this->assertEquals(0,count($children1->getChildren()));
        $this->assertEquals(2,count($parent->getChildren()));
    }

    /**
     * Generate a random string
     *
     * @return string
     */
    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * Generate a random Category (label and order is randomly generated)
     * 
     * @return  Progracqteur\WikipedaleBundle\Entity\Model\Category
     */
    private function newRandomCategory()
    {
        $c = new Category();
        $c->setLabel($this->generateRandomString(rand(10,20)));
        $c->setOrder(rand(0,20));
        return $c;
    }
}