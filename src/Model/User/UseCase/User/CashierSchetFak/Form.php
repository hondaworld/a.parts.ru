<?php

namespace App\Model\User\UseCase\User\CashierSchetFak;

use App\Form\Type\AutocompleteType;
use App\Form\Type\PhoneMobileType;
use App\Model\User\UseCase\User\Phonemob;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\User\FirmContrFetcher;
use App\ReadModel\User\OptFetcher;
use App\ReadModel\User\ShopPayTypeFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmContrFetcher $firmContrFetcher;

    public function __construct(FirmContrFetcher $firmContrFetcher)
    {

        $this->firmContrFetcher = $firmContrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isGruzInnKpp', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'В с/ф в ИНН/КПП показывать данные грузополучателя', 'label_attr' => ['class' => 'switch-custom']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
