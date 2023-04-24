<?php

namespace App\Model\Auto\UseCase\Engine\Create;


use App\Service\Converter\CharsConverter;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CharsConverter $charsConverter;

    public function __construct(CharsConverter $charsConverter)
    {

        $this->charsConverter = $charsConverter;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('url', Type\TextType::class, ['label' => 'Адрес'])
            ->add('description_tuning', CKEditorType::class, ['required' => false, 'label' => 'Текст чип-тюнинг'])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $event->setData([
                    'name' => $event->getData()['name'],
                    'url' => $this->charsConverter->urlConvert($event->getData()['url']),
                    'description_tuning' => $event->getData()['description_tuning']
                ]);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
