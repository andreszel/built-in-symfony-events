<?php

namespace App\EventListener;

use App\Entity\Project;
use App\Service\Slugger;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Project::class)]
class ProjectChangeSlugListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function postUpdate(Project $project, PostUpdateEventArgs $event):void
    {
        $entity = $event->getObject();

        if(!$entity instanceof Project) {
            return;
        }

        $entity->setSlug($this->slugger->slug($entity->getName()));

        $entityManager = $event->getObjectManager();
        $entityManager->flush();
    }
}