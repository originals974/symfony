<?php

namespace SL\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SL\CoreBundle\Entity\FieldCategory;
use SL\CoreBundle\Entity\FieldType;

class LoadFieldTypeData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /**********TEXT********/ 
        $textCategory = new FieldCategory(); 
        $textCategory->setDisplayName('Texte');

        //Text
        $textType = new FieldType(); 
        $textType->setDisplayName('Texte');
        $textType->setDataType('string'); 
        $textType->setLength(255);
        $textType->setFieldCategory($textCategory); 
        $textType->setFormType('text'); 

        //TextArea
        $textAreaType = new FieldType(); 
        $textAreaType->setDisplayName('Texte long');
        $textAreaType->setDataType('text'); 
        $textAreaType->setLength(0);
        $textAreaType->setFieldCategory($textCategory); 
        $textAreaType->setFormType('textarea'); 

        //Email
        $emailType = new FieldType(); 
        $emailType->setDisplayName('Email');
        $emailType->setDataType('string'); 
        $emailType->setLength(255);
        $emailType->setFieldCategory($textCategory); 
        $emailType->setFormType('email'); 

        //Money
        $moneyType = new FieldType(); 
        $moneyType->setDisplayName('Monétaire');
        $moneyType->setDataType('string'); 
        $moneyType->setLength(255);
        $moneyType->setFieldCategory($textCategory); 
        $moneyType->setFormType('money'); 

        //Number
        $numberType = new FieldType(); 
        $numberType->setDisplayName('Nombre');
        $numberType->setDataType('decimal'); 
        $numberType->setFieldCategory($textCategory); 
        $numberType->setFormType('number'); 

        //Percent
        $percentType = new FieldType(); 
        $percentType->setDisplayName('Pourcentage');
        $percentType->setDataType('decimal'); 
        $percentType->setFieldCategory($textCategory); 
        $percentType->setFormType('percent'); 

        //Url
        $urlType = new FieldType();  
        $urlType->setDisplayName('Url');
        $urlType->setDataType('string');
        $urlType->setLength(255); 
        $urlType->setFieldCategory($textCategory); 
        $urlType->setFormType('url'); 


        /**********CHOICE********/ 
        $choiceCategory = new FieldCategory(); 
        $choiceCategory->setDisplayName('Liste de choix');

        //Entity
        $entityType = new FieldType(); 
        $entityType->setDisplayName('Objet');
        $entityType->setDataType('string'); 
        $entityType->setLength(255);
        $entityType->setEnabled(false);
        $entityType->setFieldCategory($choiceCategory); 
        $entityType->setFormType('entity'); 

        //Data_List
        $dataList = new FieldType(); 
        $dataList->setDisplayName('Liste');
        $dataList->setDataType('string'); 
        $dataList->setLength(255);
        $dataList->setEnabled(false);
        $dataList->setFieldCategory($choiceCategory); 
        $dataList->setFormType('choice');


         /**********DATETIME********/ 
        $dateTimeCategory = new FieldCategory(); 
        $dateTimeCategory->setDisplayName('Date et heure'); 

        //JQueryDate
        $jqueryDateType = new FieldType();  
        $jqueryDateType->setDisplayName('Date');
        $jqueryDateType->setDataType('date'); 
        $jqueryDateType->setFieldCategory($dateTimeCategory); 
        $jqueryDateType->setFormType('genemu_jquerydate');

        $manager->persist($textCategory);
        $manager->persist($textType);
        $manager->persist($textAreaType);
        $manager->persist($emailType);
        $manager->persist($moneyType);
        $manager->persist($numberType);
        $manager->persist($percentType);
        $manager->persist($urlType);
        $manager->persist($choiceCategory);
        $manager->persist($entityType);
        $manager->persist($dataList);
        $manager->persist($dateTimeCategory);
        $manager->persist($jqueryDateType);
        $manager->flush();
    }
}