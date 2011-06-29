<?php

namespace Lubo\ContentManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseController extends ContainerAware
{
    /**
     * Forwards the request to another controller.
     *
     * @param  string  $controller The controller name (a string like BlogBundle:Post:index)
     * @param  array   $path       An array of path parameters
     * @param  array   $query      An array of query parameters
     *
     * @return Response A Response instance
     */
    public function forward($controller, array $path = array(), array $query = array())
    {
        return $this->container->get('http_kernel')->forward($controller, $path, $query);
    }
    
    /**
     * Renders a view.
     *
     * @param string   $view The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }
    
    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @return NotFoundHttpException
     */
    public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }
    
    /**
     * Returns a Response with Json encoded data
     * 
     * @param array $data The data to encode
     * @return Response
     */
    public function getJsonResponse($data)
    {
        return new Response(json_encode($data), 200,
            array('content-type' => 'application/json; charset=utf-8'));
    }
    
    /**
     * Shortcut to return the Doctrine Registry class
     *
     * @return Symfony\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        if (!$this->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not installed in your application.');
        }

        return $this->get('doctrine');
    }
    
    /**
     * Returns true if the service id is defined.
     *
     * @param  string  $id The service id
     *
     * @return Boolean true if the service id is defined, false otherwise
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * Gets a service by id.
     *
     * @param  string $id The service id
     *
     * @return object  The service
     */
    public function get($id)
    {
        return $this->container->get($id);
    }
}
    