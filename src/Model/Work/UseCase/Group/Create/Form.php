<?php

namespace App\Model\Work\UseCase\Group\Create;


use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use App\Model\Work\Entity\Group\WorkGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('norma', FloatNumberType::class, ['label' => 'Нормо-час'])
            ->add('sort', IntegerNumberType::class, ['required' => false, 'label' => 'Сортировка'])
            ->add('isTO', Type\ChoiceType::class, [
                'label' => 'ТО',
                'choices' => array_flip(WorkGroup::TO),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
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
