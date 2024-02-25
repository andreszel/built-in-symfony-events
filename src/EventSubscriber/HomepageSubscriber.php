<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HomepageSubscriber implements EventSubscriberInterface
{
    public function __construct(private RequestStack $requestStack) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        /* $request = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');
        dump($request);
        dump($userAgent); */
        //dd($event);
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // the controller can be changed to any PHP callable
        //$event->setController($myCustomController);
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        // get controller and request arguments
        $namedArguments = $event->getRequest()->attributes->all();
        $controllerArguments = $event->getArguments();

        // set the controller arguments to modify the original arguments or add new ones
        //$event->setArguments($newArguments);
    }

    public function onKernelView(ViewEvent $event): void
    {
        $value = $event->getControllerResult();
        $response = new Response();

        //$event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        // ... modify the response object
    }

    public function onKernelFinishRequest(FinishRequestEvent $event): void
    {
        if (null === $parentRequest = $this->requestStack->getParentRequest()) {
            return;
        }
        
        $request = $event->getRequest();

    
        // reset the locale of the subrequest to the locale of the parent request
        //$this->setLocale($parentRequest);
        $request->setLocale($parentRequest);
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = new Response();
        // setup the Response object based on the caught exception
        //$event->setResponse($response);

        // you can alternatively set a new Exception
        // $exception = new \Exception('Some special exception');
        // $event->setThrowable($exception);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                'onKernelRequest', 1
            ],
            KernelEvents::CONTROLLER => [
                'onKernelController', 1
            ],
            KernelEvents::CONTROLLER_ARGUMENTS => [
                'onKernelControllerArguments', 1
            ],
            KernelEvents::VIEW => [
                'onKernelView', 1
            ],
            KernelEvents::RESPONSE => [
                'onKernelResponse', 1
            ],
            KernelEvents::FINISH_REQUEST => [
                'onKernelFinishRequest', 1
            ],
            KernelEvents::TERMINATE => [
                'onKernelTerminate', 1
            ],
            KernelEvents::EXCEPTION => [
                'onKernelException', 1
            ]
        ];
    }
}
