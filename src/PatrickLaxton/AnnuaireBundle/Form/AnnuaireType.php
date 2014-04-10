<?php

namespace PatrickLaxton\AnnuaireBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnnuaireType extends AbstractType {

    /**
     * @var string
     */
    const INPUT_NAME = 'filename';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add(self::INPUT_NAME)
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'PatrickLaxton\AnnuaireBundle\Entity\File'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'patricklaxton_annuairebundle_import';
    }

}
