<?php

namespace App\Model\Order\UseCase\Good\SmsWarehouse;


use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use App\ReadModel\User\TemplateFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $templateFetcher;

    public function __construct(TemplateFetcher $templateFetcher)
    {
        $this->templateFetcher = $templateFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('templateID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Шаблон',
                'choices' => array_flip($this->templateFetcher->assocByGroup(TemplateGroup::WAREHOUSE)),
                'placeholder' => ''
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}
