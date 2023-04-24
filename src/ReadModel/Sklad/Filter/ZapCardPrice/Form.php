<?php


namespace App\ReadModel\Sklad\Filter\ZapCardPrice;


use App\Form\Type\AutocompleteSimpleType;
use App\Form\Type\InPageType;
use App\Form\Type\IntegerNumberType;
use App\ReadModel\Auto\AutoModelFetcher;
use App\ReadModel\Card\ZapGroupFetcher;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Shop\ShopTypeFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapGroupFetcher $zapGroupFetcher;
    private ShopTypeFetcher $shopTypeFetcher;
    private CreaterFetcher $createrFetcher;
    private AutoModelFetcher $autoModelFetcher;

    public function __construct(ZapGroupFetcher $zapGroupFetcher, ShopTypeFetcher $shopTypeFetcher, CreaterFetcher $createrFetcher, AutoModelFetcher $autoModelFetcher)
    {
        $this->zapGroupFetcher = $zapGroupFetcher;
        $this->shopTypeFetcher = $shopTypeFetcher;
        $this->createrFetcher = $createrFetcher;
        $this->autoModelFetcher = $autoModelFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('abc', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'width: 40px;']])
            ->add('number', AutocompleteSimpleType::class, ['filter' => true, 'url' => '/api/skladZapCardNumbers/' . $options['data']->zapSkladID])
            ->add('year', IntegerNumberType::class, ['filter' => true, 'attr' => ['placeholder' => 'Год']])
            ->add('quantity1_from', IntegerNumberType::class, ['filter' => true, 'attr' => ['placeholder' => 'с', 'style' => 'width: 40px;']])
            ->add('quantity1_till', IntegerNumberType::class, ['filter' => true, 'attr' => ['placeholder' => 'по', 'style' => 'width: 40px;']])
            ->add('quantity5_from', IntegerNumberType::class, ['filter' => true, 'attr' => ['placeholder' => 'с', 'style' => 'width: 40px;']])
            ->add('quantity5_till', IntegerNumberType::class, ['filter' => true, 'attr' => ['placeholder' => 'по', 'style' => 'width: 40px;']])
            ->add('quantity6_from', IntegerNumberType::class, ['filter' => true, 'attr' => ['placeholder' => 'с', 'style' => 'width: 40px;']])
            ->add('quantity6_till', IntegerNumberType::class, ['filter' => true, 'attr' => ['placeholder' => 'по', 'style' => 'width: 40px;']])
            ->add('zapGroupID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->zapGroupFetcher->assoc()),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ])
            ->add('shop_typeID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->shopTypeFetcher->assoc()),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ])
            ->add('createrID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->createrFetcher->assoc()),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ])
            ->add('auto_modelID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->autoModelFetcher->assoc()),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt'
                ],
                'placeholder' => 'Модель автомобиля'
            ])
            ->add('showDeleted', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Удаленные не показываются' => false,
                    'Удаленные показываются' => true,
                ],
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ]
            ])
            ->add('quantity', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Нужно' => 'necessary',
                    'Лишнее' => 'unnecessary',
                ],
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => 'Количество'
            ])
            ->add('searchWholeNumber', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'По целому номеру' => true,
                    'По части номера' => false,
                ],
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ]
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