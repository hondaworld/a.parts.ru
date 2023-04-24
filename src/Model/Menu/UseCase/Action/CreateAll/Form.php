<?php

namespace App\Model\Menu\UseCase\Action\CreateAll;

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
            ->add('actions', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Операции',
                'label_html' => true,
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline', 'label_html' => true],
                'choices' => array_flip(array_map(function ($a) use ($options) {
                    return '<i class="' . $a['icon'] . '"></i> ' . $a['label'];
                }, $options['data']->newActions)),
                'expanded' => true,
                'multiple' => true,
                'placeholder' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
