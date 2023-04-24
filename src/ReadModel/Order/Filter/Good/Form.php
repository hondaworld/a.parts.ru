<?php


namespace App\ReadModel\Order\Filter\Good;


use App\Form\Type\InPageType;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private IncomeStatusFetcher $incomeStatusFetcher;
    private ProviderFetcher $providerFetcher;
    private ZapSkladFetcher $zapSkladFetcher;

    public function __construct(CreaterFetcher $createrFetcher, IncomeStatusFetcher $incomeStatusFetcher, ProviderFetcher $providerFetcher, ZapSkladFetcher $zapSkladFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->incomeStatusFetcher = $incomeStatusFetcher;
        $this->providerFetcher = $providerFetcher;
        $this->zapSkladFetcher = $zapSkladFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('orderID', Type\TextType::class, [
                'filter' => true,
                'attr' => ['placeholder' => false, 'class' => 'js-convert-number', 'style' => 'width: 60px;']
            ])
            ->add('number', Type\TextType::class, [
                'filter' => true,
                'attr' => ['placeholder' => 'Номер', 'style' => 'width: 120px;']
            ])
            ->add('expenseDocumentNumber', Type\TextType::class, [
                'filter' => true,
                'attr' => ['placeholder' => false, 'style' => 'width: 50px;']
            ])
            ->add('schetNumber', Type\TextType::class, [
                'filter' => true,
                'attr' => ['placeholder' => false, 'style' => 'width: 50px;']
            ])
            ->add('incomeStatus', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => ['Не заказано' => -1] + array_flip($this->incomeStatusFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 120px;']
            ])
            ->add('createrID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->createrFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 120px;']
            ])
            ->add('zapSkladID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->zapSkladFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 120px;']
            ])
            ->add('providerID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->providerFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 120px;']
            ])
            ->add('isShowAllGoods', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Только неотгруженные' => false,
                    'Все детали' => true
                ], 'placeholder' => false,
                'label' => false,
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('reserve', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'В резерве' => 'reserve',
                    'Не в резерве' => 'not_reserve',
                    'В отгрузке' => 'shipping',
                ], 'placeholder' => '',
                'label' => false,
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 80px;']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}