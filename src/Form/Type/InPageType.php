<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InPageType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'required' => false,
            'choices' => ['5' => '5', '10' => '10', '20' => '20', '50' => '50', '100' => '100', '500' => '500'],
            'expanded' => false,
            'multiple' => false,
            'attr' => ['class' => 'custom-select custom-select-sm form-control-alt', 'onchange' => 'this.form.submit()'],
            'placeholder' => false,
        ]);
    }

    public function getParent(): string
    {
        return Type\ChoiceType::class;
    }
}