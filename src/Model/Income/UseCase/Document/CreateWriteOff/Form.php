<?php

namespace App\Model\Income\UseCase\Document\CreateWriteOff;


use App\Form\Type\IntegerNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('document_prefix', Type\TextType::class, ['required' => false, 'label' => 'Префикс', 'attr' => ['maxLength' => 15]])
            ->add('document_sufix', Type\TextType::class, ['required' => false, 'label' => 'Суфикс', 'attr' => ['maxLength' => 15]])
            ->add('returning_reason', Type\TextType::class, ['required' => false, 'label' => 'Комментарий'])
        ;

        foreach (array_keys($options['data']->incomeSklads) AS $incomeSkladID) {
            $builder->add('incomeSklad_' .$incomeSkladID, IntegerNumberType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
