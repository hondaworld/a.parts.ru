<?php


namespace App\Form\Type;


use App\ReadModel\Firm\FirmFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutocompleteFirmType extends AbstractType
{
    private FirmFetcher $firmFetcher;

    public function __construct(FirmFetcher $firmFetcher)
    {
        $this->firmFetcher = $firmFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', Type\ChoiceType::class, [
                'required' => false,
                'label' => false,
                'choices' => array_flip($this->firmFetcher->assocNotHide()),
                'attr' => [
                    'class' => 'js-autocomplete-firm'
                ],
            ])
            ->add('contactID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Адрес',
                'choices' => array_keys($options['contacts']),
                'choice_value' => function ($value) {
                    return $value;
                },
                'choice_label' => function ($choice, $key, $value) use ($options) {
                    return $options['contacts'][$value];
                },
            ])
            ->add('beznalID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Реквизит',
                'choices' => array_keys($options['beznals']),
                'choice_value' => function ($value) {
                    return $value;
                },
                'choice_label' => function ($choice, $key, $value) use ($options) {
                    return $options['beznals'][$value];
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'url' => '',
            'contacts' => '',
            'beznals' => '',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parentData = $form->getParent()->getData();
    }

    public function getParent(): string
    {
        return Type\FormType::class;
    }
}