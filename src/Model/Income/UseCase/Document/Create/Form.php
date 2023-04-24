<?php

namespace App\Model\Income\UseCase\Document\Create;


use App\Form\Type\FloatNumberType;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmFetcher $firmFetcher;
    private ProviderFetcher $providerFetcher;

    public function __construct(FirmFetcher $firmFetcher, ProviderFetcher $providerFetcher)
    {
        $this->firmFetcher = $firmFetcher;
        $this->providerFetcher = $providerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('document_prefix', Type\TextType::class, ['required' => false, 'label' => 'Префикс', 'attr' => ['maxLength' => 15]])
            ->add('document_sufix', Type\TextType::class, ['required' => false, 'label' => 'Суфикс', 'attr' => ['maxLength' => 15]])
            ->add('firmID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Организация',
                'choices' => array_flip($this->firmFetcher->assocNotHide()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('providerID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Поставщик',
                'choices' => array_flip($this->providerFetcher->assocIncomeInWarehouse()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('user_contactID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Адрес поставщика',
                'choices' => [],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('osn_nakladnaya', Type\TextType::class, ['required' => false, 'label' => 'Накладная', 'attr' => ['maxLength' => 50]])
            ->add('osn_schet', Type\TextType::class, ['required' => false, 'label' => 'Счет', 'attr' => ['maxLength' => 50]])
            ->add('balance', FloatNumberType::class, ['required' => false, 'label' => 'Оплата поставщику'])
            ->add('balance_nds', FloatNumberType::class, ['required' => false, 'label' => 'НДС оплаты поставщику'])
            ->add('description', Type\TextType::class, ['required' => false, 'label' => 'Комментарий к оплате'])
            ->add('is_priceZak', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Заменить цены', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
