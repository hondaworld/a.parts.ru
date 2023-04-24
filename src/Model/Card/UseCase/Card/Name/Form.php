<?php

namespace App\Model\Card\UseCase\Card\Name;


use App\ReadModel\Card\ZapGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapGroupFetcher $zapGroupFetcher;

    public function __construct(ZapGroupFetcher $zapGroupFetcher)
    {
        $this->zapGroupFetcher = $zapGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['required' => false, 'label' => 'Наименование'])
            ->add('description', Type\TextType::class, ['required' => false, 'label' => 'Описание'])
            ->add('name_big', Type\TextType::class, ['required' => false, 'label' => 'Альтернативное наименование'])
            ->add('nameEng', Type\TextType::class, ['required' => false, 'label' => 'Английское наименование'])
            ->add('zapGroupID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Группа товаров',
                'choices' => array_flip($this->zapGroupFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
