<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class FloatNumberNegativeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [new Regex([
                'pattern' => "/^([\-])?\d+([.|,]\d+)?$/",
                'message' => "Значение должно быть дробным числом"
            ])],
            'attr' => ['class' => 'js-convert-float-negative']
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($view->vars['attr']) {
            $view->vars['attr']['class'] = isset($view->vars['attr']['class']) ? $view->vars['attr']['class'] . ' js-convert-float-negative' : 'js-convert-float-negative';
        }
    }

    public function getParent(): string
    {
        return Type\TextType::class;
    }
}