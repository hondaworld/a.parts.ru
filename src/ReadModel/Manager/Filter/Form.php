<?php


namespace App\ReadModel\Manager\Filter;


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
            ->add('login', Type\TextType::class, ['filter' => true])
            ->add('name', Type\TextType::class, ['filter' => true])
            ->add('user_name', Type\TextType::class, ['filter' => true])
            ->add('email', Type\TextType::class, ['filter' => true]);
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