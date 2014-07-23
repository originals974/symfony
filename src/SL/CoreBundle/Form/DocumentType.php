<?php

namespace SL\CoreBundle\Form;

//Symfony classes
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

//Custom classes
use SL\CoreBundle\Entity\Object;

class DocumentType extends ObjectType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'document';
    }
}
