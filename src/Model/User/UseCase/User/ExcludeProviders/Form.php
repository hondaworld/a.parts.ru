<?php

namespace App\Model\User\UseCase\User\ExcludeProviders;

use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $arr = [];
        foreach ($options['data']->providersList as $providerID => $provider) {
            $arr[$provider['name']] = $providerID;
        }

        $builder
            ->add('providers', Type\ChoiceType::class, [
                'label' => 'Поставщики',
                'is_cols' => true,
                'cols' => 3,
                'choice_data' => $options['data']->providersList,
                'choices' => $arr,
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
