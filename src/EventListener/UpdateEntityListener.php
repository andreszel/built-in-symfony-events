<?php

namespace App\EventListener;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
class UpdateEntityListener
{
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if(!$entity instanceof Project) {
            return;
        }

        $entityManager = $args->getObjectManager();

        dump('Doctrine Lifecycle Listener');
    }
}