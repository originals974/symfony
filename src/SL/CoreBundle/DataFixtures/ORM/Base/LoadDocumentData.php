<?php

namespace SL\CoreBundle\DataFixtures\ORM\Base;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;

class LoadDocumentData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $documentEntityClass = new EntityClass(); 
        $documentEntityClass->setDisplayName('Document');
        $documentEntityClass->setDocument(true);
        $documentEntityClass->setIcon('fa-file-text-o');

        $manager->persist($documentEntityClass);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10;
    }
}