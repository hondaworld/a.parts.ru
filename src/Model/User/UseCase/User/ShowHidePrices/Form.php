<?php

namespace App\Model\User\UseCase\User\ShowHidePrices;

use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prices', Type\ChoiceType::class, [
                'label' => 'Прайсы',
                'is_cols' => true,
                'cols' => 3,
                'choices' => array_flip($options['data']->pricesList),
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'checkbox-custom']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
