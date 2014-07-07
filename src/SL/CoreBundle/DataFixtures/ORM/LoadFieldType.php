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
        $textCategory->setTechnicalName('text');
        $textCategory->setDisplayName('Texte');
        $textCategory->setDisplayOrder(1); 

        //Text
        $textType = new FieldType(); 
        $textType->setTechnicalName('text'); 
        $textType->setDisplayName('Texte');
        $textType->setDisplayOrder(0);
        $textType->setDataType('string'); 
        $textType->setLength(255);
        $textType->setFieldCategory($textCategory); 
        $textType->setFormType('text'); 

        //TextArea
        $textAreaType = new FieldType(); 
        $textAreaType->setTechnicalName('textarea'); 
        $textAreaType->setDisplayName('Texte long');
        $textAreaType->setDisplayOrder(10);
        $textAreaType->setDataType('text'); 
        $textAreaType->setLength(0);
        $textAreaType->setFieldCategory($textCategory); 
        $textAreaType->setFormType('textarea'); 

        //Email
        $emailType = new FieldType(); 
        $emailType->setTechnicalName('email'); 
        $emailType->setDisplayName('Email');
        $emailType->setDisplayOrder(20);
        $emailType->setDataType('string'); 
        $emailType->setLength(255);
        $emailType->setFieldCategory($textCategory); 
        $emailType->setFormType('email'); 

        //Money
        $moneyType = new FieldType(); 
        $moneyType->setTechnicalName('money'); 
        $moneyType->setDisplayName('Monétaire');
        $moneyType->setDisplayOrder(30);
        $moneyType->setDataType('string'); 
        $moneyType->setLength(255);
        $moneyType->setFieldCategory($textCategory); 
        $moneyType->setFormType('money'); 

        //Number
        $numberType = new FieldType(); 
        $numberType->setTechnicalName('number'); 
        $numberType->setDisplayName('Nombre');
        $numberType->setDisplayOrder(40);
        $numberType->setDataType('decimal'); 
        $numberType->setFieldCategory($textCategory); 
        $numberType->setFormType('number'); 

        //Percent
        $percentType = new FieldType(); 
        $percentType->setTechnicalName('percent'); 
        $percentType->setDisplayName('Pourcentage');
        $percentType->setDisplayOrder(50);
        $percentType->setDataType('decimal'); 
        $percentType->setFieldCategory($textCategory); 
        $percentType->setFormType('percent'); 

        //Url
        $urlType = new FieldType(); 
        $urlType->setTechnicalName('url'); 
        $urlType->setDisplayName('Url');
        $urlType->setDisplayOrder(60);
        $urlType->setDataType('string');
        $urlType->setLength(255); 
        $urlType->setFieldCategory($textCategory); 
        $urlType->setFormType('url'); 


        /**********CHOICE********/ 
        $choiceCategory = new FieldCategory(); 
        $choiceCategory->setTechnicalName('choice');
        $choiceCategory->setDisplayName('Liste de choix');
        $choiceCategory->setDisplayOrder(2); 

        //Entity
        $entityType = new FieldType(); 
        $entityType->setTechnicalName('entity'); 
        $entityType->setDisplayName('Objet');
        $entityType->setDisplayOrder(0);
        $entityType->setDataType('string'); 
        $entityType->setLength(255);
        $entityType->setEnabled(false);
        $entityType->setFieldCategory($choiceCategory); 
        $entityType->setFormType('collection'); 

        //Data_List
        $dataList = new FieldType(); 
        $dataList->setTechnicalName('data_list'); 
        $dataList->setDisplayName('Liste');
        $dataList->setDisplayOrder(10);
        $dataList->setDataType('string'); 
        $dataList->setLength(255);
        $dataList->setEnabled(false);
        $dataList->setFieldCategory($choiceCategory); 
        $dataList->setFormType('choice'); 


         /**********DATETIME********/ 
        $dateTimeCategory = new FieldCategory(); 
        $dateTimeCategory->setTechnicalName('datetime');
        $dateTimeCategory->setDisplayName('Date et heure');
        $dateTimeCategory->setDisplayOrder(3); 

        //JQueryDate
        $jqueryDateType = new FieldType(); 
        $jqueryDateType->setTechnicalName('genemu_jquerydate'); 
        $jqueryDateType->setDisplayName('Date');
        $jqueryDateType->setDisplayOrder(70);
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