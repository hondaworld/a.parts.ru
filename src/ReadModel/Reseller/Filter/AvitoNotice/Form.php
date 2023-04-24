<?php


namespace App\ReadModel\Reseller\Filter\AvitoNotice;


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
            ->add('oem', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'width: 40px;']])
            ->add('brand', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'width: 40px;']]);
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