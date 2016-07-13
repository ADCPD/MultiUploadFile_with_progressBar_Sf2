<?php
/**
 * Created by PhpStorm.
 * User: dhaouadi_a
 * Date: 04/05/2016
 * Time: 16:05
 */

namespace ProgressAssetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditFileType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text', array(
                'attr' => array(
                    'class' => 'form-control',
                    'id' => 'name'
                )
            ))
            ->add('file', 'file', array(
                'data_class' => null,

                'attr' => array(
                    'name' => 'upl',
                    'multiple ' => 'multiple',
                    'class' => "addFile"
                )
            ));;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ProgressAssetBundle\Entity\Files'
        ));
    }
}