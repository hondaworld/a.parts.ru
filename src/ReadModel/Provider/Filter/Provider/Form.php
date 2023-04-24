<?php


namespace App\ReadModel\Provider\Filter\Provider;


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
            ->add('showHide', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Скрытые показываются' => true,
                    'Скрытые не показываются' => false,
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