<?php

namespace App\EventListener;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Project::class)]
class ProjectChangeSlugListener
{
    public function postUpdate(Project $project, PostUpdateEventArgs $event):void
    {
        $entity = $event->getObject();

        if(!$entity instanceof Project) {
            return;
        }

        $entityManager = $event->getObjectManager();

        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($project->getName())->lower();

        $project->setSlug($slug);

        $entityManager->persist($project);
        $entityManager->flush();
    }
}