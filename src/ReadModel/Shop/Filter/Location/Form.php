<?php


namespace App\ReadModel\Shop\Filter\Location;


use App\Form\Type\InPageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('name', Type\TextType::class, ['filter' => true])
            ->add('name_short', Type\TextType::class, ['filter' => true])
            ->add('number', Type\TextType::class, ['filter' => true, 'attr' => ['placeholder' => 'Номер детали']])
            ->add('showHidden', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Покзывать скрытые' => true,
                    'Не показывать скрытые' => false,
                ],
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
            ])
            ->add('isEmpty', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Все ячейки' => false,
                    'Только пустые' => true,
                ],
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
            ])
        ;
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