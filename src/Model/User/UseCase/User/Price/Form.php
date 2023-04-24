<?php

namespace App\Model\User\UseCase\User\Price;

use App\Form\Type\IntegerNumberType;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private ZapSkladFetcher $zapSkladFetcher;

    public function __construct(CreaterFetcher $createrFetcher, ZapSkladFetcher $zapSkladFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->zapSkladFetcher = $zapSkladFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', Type\TextType::class, ['required' => false, 'label' => 'E-mail'])
            ->add('email_send', Type\TextType::class, ['required' => false, 'label' => 'E-mail для ответа'])
            ->add('filename', Type\TextType::class, ['required' => false, 'label' => 'Наименование файла'])
            ->add('first_line', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Не учитывать первую строчку', 'label_attr' => ['class' => 'switch-custom']])
            ->add('line', IntegerNumberType::class, ['required' => false, 'label' => 'Поле начала', 'attr' => ['maxLength' => 2]])
            ->add('order_num', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с номером заказа', 'attr' => ['maxLength' => 2]])
            ->add('number_num', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с номером', 'attr' => ['maxLength' => 2]])
            ->add('creater_num', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с производителем', 'attr' => ['maxLength' => 2]])
            ->add('quantity_num', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с количеством', 'attr' => ['maxLength' => 2]])
            ->add('price_num', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с ценой', 'attr' => ['maxLength' => 2]])
            ->add('createrID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('zapSkladID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Приоритетный склад отгрузки',
                'choices' => array_flip($this->zapSkladFetcher->assoc($options['data']->zapSkladID)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
