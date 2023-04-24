<?php

namespace App\Model\User\UseCase\User\CashierFirmContr;

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
            ->add('firmcontrID', Type\ChoiceType::class, [
                'label' => 'Контрагент',
                'choices' => array_flip($this->firmContrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
                'help' => 'При указании контрагента клиент обнулится'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
