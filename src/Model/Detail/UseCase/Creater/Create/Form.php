<?php

namespace App\Model\Detail\UseCase\Creater\Create;

use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $createrFetcher;

    public function __construct(CreaterFetcher $createrFetcher)
    {

        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('name_rus', Type\TextType::class, ['required' => false, 'label' => 'Наименование русское'])
            ->add('isOriginal', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Оригинал', 'label_attr' => ['class' => 'switch-custom']])
            ->add('tableName', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Таблица',
                'choices' => array_flip($this->createrFetcher->assocTableNames()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
            ])
            ->add('creater_weightID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Альтернативный производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('description', Type\TextareaType::class, ['required' => false, 'label' => 'Описание'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
