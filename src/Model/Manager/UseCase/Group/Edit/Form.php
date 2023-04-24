<?php

namespace App\Model\Manager\UseCase\Group\Edit;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование']);

        $arr = [];
        $arr1 = [];
        if (count($options['data']->actionsList) > 0) {
            foreach ($options['data']->actionsList as $groupID => $group) {
                $arr[$group['name']] = [];
                foreach ($group['sections'] as $sectionID => $section) {
                    $arr[$group['name']][$section['name']] = [];
                    $arr1[$sectionID] = [];
                    foreach ($section['actions'] as $action) {
                        $arr[$group['name']][$section['name']]['<i class="' . $action['icon'] . '"></i> ' . ($action['label'] ?: $action['name'])] = $action['id'];
                        $arr1[$section['name']]['<i class="' . $action['icon'] . '"></i> ' . ($action['label'] ?: $action['name'])] = $action['id'];
                    }
                }
            }
        }

        $builder
            ->add('actions', Type\ChoiceType::class, [
                'is_advanced' => true,
                'required' => false,
                'label' => 'Операции',
                'label_html' => true,
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline pb-2'],
                'choices' => $arr,
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
