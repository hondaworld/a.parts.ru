<?php

namespace App\Model\Provider\UseCase\Provider\Opt;

use App\Form\Type\AutocompleteType;
use App\Form\Type\TimePickerType;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\UseCase\Provider\User;
use App\ReadModel\Sklad\ZapSkladFetcher;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['data']->opts as $optID => $opt) {
            $builder->add($options['data']->getProfit(0, $optID), Type\TextType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm js-convert-float']]);
            foreach ($options['data']->providerPrices as $providerPriceID => $providerPrice) {
                $builder->add($options['data']->getProfit($providerPriceID, $optID), Type\TextType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm js-convert-float']]);
            }
        }
        foreach ($options['data']->providerPrices as $providerPriceID => $providerPrice) {
            $builder->add($options['data']->getProfit($providerPriceID, 0), Type\TextType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm js-convert-float']]);
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
