<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvents;

class AddEmailFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSubmit(PreSubmitEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        if(!$data) {
            return;
        }

        $randomNumber = rand(1000,20000);

        if(isset($data['addEmail']) && $data['addEmail']) {
            $form->add('email', EmailType::class, [
                'empty_data' => 'test' . $randomNumber . '@wp.pl'
            ]);
        } else {
            unset($data['email']);
            $event->setData($data);
        }
    }
}