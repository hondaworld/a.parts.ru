<?php

namespace App\Model\Work\UseCase\Group\Auto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('linkMarka', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => false, 'label_attr' => ['class' => 'checkbox-custom']])
            ->add('normaMarka', Type\TextType::class, ['required' => false, 'help' => 'нормо-час', 'attr' => ['class' => 'form-control-sm js-convert-float']])
            ->add('partsMarka', Type\TextareaType::class, ['required' => false, 'help' => 'номер запчасти;кол', 'attr' => ['class' => 'form-control-sm']])
        ;

        foreach (($options['data']->autoMarka)->getModels() as $autoModel) {
            $builder
                ->add('linkModel_' . $autoModel->getId(), Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => false, 'label_attr' => ['class' => 'checkbox-custom']])
                ->add('normaModel_' . $autoModel->getId(), Type\TextType::class, ['required' => false, 'help' => 'нормо-час', 'attr' => ['class' => 'form-control-sm js-convert-float']])
                ->add('partsModel_' . $autoModel->getId(), Type\TextareaType::class, ['required' => false, 'help' => 'номер запчасти;кол', 'attr' => ['class' => 'form-control-sm']])
            ;
            foreach ($autoModel->getGenerations() as $autoGeneration) {
                $builder
                    ->add('linkGeneration_' . $autoGeneration->getId(), Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => false, 'label_attr' => ['class' => 'checkbox-custom']])
                    ->add('normaGeneration_' . $autoGeneration->getId(), Type\TextType::class, ['required' => false, 'help' => 'нормо-час', 'attr' => ['class' => 'form-control-sm js-convert-float']])
                    ->add('partsGeneration_' . $autoGeneration->getId(), Type\TextareaType::class, ['required' => false, 'help' => 'номер запчасти;кол', 'attr' => ['class' => 'form-control-sm']])
                ;
                foreach ($autoGeneration->getModifications() as $autoModification) {
                    $builder
                        ->add('linkModification_' . $autoModification->getId(), Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => false, 'label_attr' => ['class' => 'checkbox-custom']])
                        ->add('normaModification_' . $autoModification->getId(), Type\TextType::class, ['required' => false, 'help' => 'нормо-час', 'attr' => ['class' => 'form-control-sm js-convert-float']])
                        ->add('partsModification_' . $autoModification->getId(), Type\TextareaType::class, ['required' => false, 'help' => 'номер запчасти;кол', 'attr' => ['class' => 'form-control-sm']])
                    ;
                }
            }
        }

//        foreach ($options['data']->opts as $optID => $opt) {
//            $builder->add($options['data']->getProfit($optID), Type\TextType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm js-convert-float']]);
//        }
//        $builder->add($options['data']->getProfit(0), Type\TextType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm js-convert-float']]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
