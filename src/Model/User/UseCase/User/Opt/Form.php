<?php

namespace App\Model\User\UseCase\User\Opt;

use App\Form\Type\AutocompleteType;
use App\Form\Type\PhoneMobileType;
use App\Model\User\UseCase\User\Phonemob;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\User\OptFetcher;
use App\ReadModel\User\ShopPayTypeFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private OptFetcher $optFetcher;
    private ShopPayTypeFetcher $shopPayTypeFetcher;

    public function __construct(OptFetcher $optFetcher, ShopPayTypeFetcher $shopPayTypeFetcher)
    {

        $this->optFetcher = $optFetcher;
        $this->shopPayTypeFetcher = $shopPayTypeFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('optID', Type\ChoiceType::class, [
                'label' => 'Оптовый пакет',
                'choices' => array_flip($this->optFetcher->assoc($options['data']->optID)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
            ])
            ->add('shopPayTypeID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Метод оплаты',
                'choices' => array_flip($this->shopPayTypeFetcher->assoc($options['data']->shopPayTypeID)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
