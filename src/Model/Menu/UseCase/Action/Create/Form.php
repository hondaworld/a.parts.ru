<?php

namespace App\Model\Menu\UseCase\Action\Create;

use App\ReadModel\Menu\MenuActionFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Операция'])
            ->add('label', Type\TextType::class, ['label' => 'Наименование'])
            ->add('icon', Type\TextType::class, ['required' => false, 'label' => 'Иконка', 'attr' => ['maxLength' => 100]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
