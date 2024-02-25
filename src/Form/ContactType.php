<?php

namespace App\Form;

use App\EventSubscriber\AddEmailFieldSubscriber;
use App\Form\Model\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('phone', TextType::class)
            ->add('subject', TextType::class)
            ->add('message', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
            ])
            ->add('addEmail', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Add test email',
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this, 'onPreSetData']
            )
            ->addEventSubscriber(new AddEmailFieldSubscriber())
            ->add('save', SubmitType::class, ['label' => 'Send message'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }

    public function onPreSetData(PreSetDataEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        $randomNumber = rand(1000,20000);
        $data->setMessage('###' . $randomNumber . ' Default message set in preSetData Event! Write your own message.');

        $form->remove('email');

        $event->setData($data);
    }
}
