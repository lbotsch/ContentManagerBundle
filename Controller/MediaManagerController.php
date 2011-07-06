<?php

namespace Lubo\ContentManagerBundle\Controller;


class MediaManagerController extends BaseController
{
    /**
     * Return the main MediaManager script
     */
    public function getMainScriptAction()
    {
        $response = $this->container->get('templating')
            ->renderResponse('LuboContentManagerBundle:MediaManager:main_script.js.twig', array(
                
            ));
        $response->headers->set('content-type', 'text/javascript');
        return $response;
    }
}