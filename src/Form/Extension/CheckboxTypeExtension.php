<?php


namespace App\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckboxTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [CheckboxType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['type']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['type'])) {
            $view->vars['custom_control_type'] = 'custom-control-' . $options['type'];
        }
    }

}