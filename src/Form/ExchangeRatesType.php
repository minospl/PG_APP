<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExchangeRatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                    'placeholder' =>
                        [
                            'year' => 'Rok',
                            'month' => 'Miesiąc',
                            'day' => 'Dzień'
                        ]
                ])
            ->add('submit', SubmitType::class, [
                'attr' =>
                    [
                        'class' => 'btn btn-primary',
                        'style' => 'margin-top: 20px;',
                    ],
                'label' => 'Wyszukaj',
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExchangeRatesModel::class
        ]);
    }
}
