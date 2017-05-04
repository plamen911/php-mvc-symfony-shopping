<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 5/4/17
 * Time: 21:12
 */

namespace AppBundle\EventListener;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpNotFoundListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        $response = new Response();
        $response->setContent('Object is not found.');
        $event->setResponse($response);
    }
}