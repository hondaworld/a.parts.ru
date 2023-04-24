<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints\Regex;

class IntegerNumberType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [new Regex([
                'pattern' => "/^\d+$/",
                'message' => "Значение должно быть целым числом"
            ])]
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($view->vars['attr']) {
            $view->vars['attr']['class'] = isset($view->vars['attr']['class']) ? $view->vars['attr']['class'] . ' js-convert-number' : 'js-convert-number';
        } else {
            $view->vars['attr']['class'] = 'js-convert-number';
        }
    }

    public function getParent(): string
    {
        return Type\TextType::class;
    }
}