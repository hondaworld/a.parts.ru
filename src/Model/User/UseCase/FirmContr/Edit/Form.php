<?php

namespace App\Model\User\UseCase\FirmContr\Edit;

use App\Form\Type\AddressType;
use App\Form\Type\AutocompleteType;
use App\Form\Type\IntegerNumberType;
use App\Model\Beznal\UseCase\Beznal\Bank;
use App\Model\Contact\UseCase\Contact\Address;
use App\ReadModel\Finance\NalogFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $managerFetcher;

    private $nalogFetcher;

    public function __construct(ManagerFetcher $managerFetcher, NalogFetcher $nalogFetcher)
    {
        $this->managerFetcher = $managerFetcher;
        $this->nalogFetcher = $nalogFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organization', Type\TextType::class, ['label' => 'Организация'])
            ->add('inn', IntegerNumberType::class, ['required' => false, 'label' => 'ИНН', 'attr' => ['maxLength' => 12]])
            ->add('kpp', IntegerNumberType::class, ['required' => false, 'label' => 'КПП', 'attr' => ['maxLength' => 9]])
            ->add('okpo', IntegerNumberType::class, ['required' => false, 'label' => 'ОКПО', 'attr' => ['maxLength' => 8]])
            ->add('ogrn', IntegerNumberType::class, ['required' => false, 'label' => 'ОГРН', 'attr' => ['maxLength' => 15]])
            ->add('isNDS', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Является плательщиком НДС', 'label_attr' => ['class' => 'switch-custom']])
            ->add('address', AddressType::class, ['label' => false, 'data_class' => Address::class])
            ->add('phone', Type\TextType::class, ['required' => false, 'label' => 'Телефон'])
            ->add('fax', Type\TextType::class, ['required' => false, 'label' => 'Факс'])
            ->add('email', Type\TextType::class, ['required' => false, 'label' => 'E-mail'])
            ->add('bank', AutocompleteType::class, ['label' => 'Банк', 'url' => '/api/banks', 'data_class' => Bank::class])
            ->add('rasschet', Type\TextType::class, ['label' => 'Рассчетный счет'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
