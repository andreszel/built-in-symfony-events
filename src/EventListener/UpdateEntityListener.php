<?php

namespace App\EventListener;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, priority: 400, connection: 'default')]
#[AsDoctrineListener(event: Events::postRemove, priority: 300, connection: 'default')]
class UpdateEntityListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    // persist - only new record, first stored
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if(!$entity instanceof Project) {
            return;
        }

        $entity->setSlug($this->slugger->slug($entity->getName()));

        $entityManager = $args->getObjectManager();
        $entityManager->flush($entity);
    }

    // update - every exists stored object
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if(!$entity instanceof Project) {
            return;
        }

        $entity->setSlug($this->slugger->slug($entity->getName()));

        $entityManager = $args->getObjectManager();
        $entityManager->flush($entity);
    }

    // remove - every exists stored object
    public function postRemove(PostRemoveEventArgs $args): void
    {
    }
}