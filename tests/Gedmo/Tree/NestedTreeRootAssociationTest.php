<?php

namespace Gedmo\Tree;

use Doctrine\Common\EventManager;
use Tool\BaseTestCaseORM;
use Tree\Fixture\RootAssociationCategory;

/**
 * These are tests for Tree behavior
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class NestedTreeRootAssociationTest extends BaseTestCaseORM
{
    const CATEGORY = "Tree\\Fixture\\RootAssociationCategory";

    protected function setUp()
    {
        parent::setUp();

        $evm = new EventManager();
        $evm->addEventSubscriber(new TreeListener());

        $this->getMockSqliteEntityManager($evm);
        $this->populate();
    }

    public function testRootEntity()
    {
        $repo = $this->em->getRepository(self::CATEGORY);

        // Foods
        $food = $repo->findOneByTitle('Food');
        $this->assertEquals($food->getId(), $food->getRoot()->getId());

        $fruits = $repo->findOneByTitle('Fruits');
        $this->assertEquals($food->getId(), $fruits->getRoot()->getId());

        $vegetables = $repo->findOneByTitle('Vegetables');
        $this->assertEquals($food->getId(), $vegetables->getRoot()->getId());

        $carrots = $repo->findOneByTitle('Carrots');
        $this->assertEquals($food->getId(), $carrots->getRoot()->getId());

        $potatoes = $repo->findOneByTitle('Potatoes');
        $this->assertEquals($food->getId(), $potatoes->getRoot()->getId());

        // Sports
        $sports = $repo->findOneByTitle('Sports');
        $this->assertEquals($sports->getId(), $sports->getRoot()->getId());
    }

    public function testRemoveParentForNode()
    {
        $repo = $this->em->getRepository(self::CATEGORY);

        /** @var RootAssociationCategory $food */
        $food = $repo->findOneByTitle('Food');
        $this->assertEquals($food->getId(), $food->getRoot()->getId());
        $this->assertEquals(0, $food->getLevel());
        $this->assertEquals(1, $food->getLeft());
        $this->assertEquals(10, $food->getRight());

        /** @var RootAssociationCategory $fruits */
        $fruits = $repo->findOneByTitle('Fruits');
        $this->assertEquals($food->getId(), $fruits->getRoot()->getId());
        $this->assertEquals(1, $fruits->getLevel());
        $this->assertEquals(2, $fruits->getLeft());
        $this->assertEquals(3, $fruits->getRight());

        // Remove node's parent, which should move out the node into a new tree
        $fruits->setParent(null);
        $this->em->flush();

        $food = $repo->findOneByTitle('Food');
        $this->assertEquals($food->getId(), $food->getRoot()->getId());
        $this->assertEquals(0, $food->getLevel());
        $this->assertEquals(1, $food->getLeft());
        $this->assertEquals(8, $food->getRight());

        $fruits = $repo->findOneByTitle('Fruits');
        $this->assertEquals($fruits->getId(), $fruits->getRoot()->getId());
        $this->assertEquals(0, $fruits->getLevel());
        $this->assertEquals(1, $fruits->getLeft());
        $this->assertEquals(2, $fruits->getRight());
    }

    protected function getUsedEntityFixtures()
    {
        return array(
            self::CATEGORY,
        );
    }

    private function populate()
    {
        $root = new RootAssociationCategory();
        $root->setTitle("Food");

        $root2 = new RootAssociationCategory();
        $root2->setTitle("Sports");

        $child = new RootAssociationCategory();
        $child->setTitle("Fruits");
        $child->setParent($root);

        $child2 = new RootAssociationCategory();
        $child2->setTitle("Vegetables");
        $child2->setParent($root);

        $childsChild = new RootAssociationCategory();
        $childsChild->setTitle("Carrots");
        $childsChild->setParent($child2);

        $potatoes = new RootAssociationCategory();
        $potatoes->setTitle("Potatoes");
        $potatoes->setParent($child2);

        $this->em->persist($root);
        $this->em->persist($root2);
        $this->em->persist($child);
        $this->em->persist($child2);
        $this->em->persist($childsChild);
        $this->em->persist($potatoes);
        $this->em->flush();
        $this->em->clear();
    }
}
