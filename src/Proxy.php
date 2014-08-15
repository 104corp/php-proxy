<?php namespace Proxy;

use Proxy\Adapter\Adapter;
use Proxy\Request\Filter\RequestFilter;
use Proxy\Response\Filter\ResponseFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Proxy {

    /**
     * The Request instance.
     *
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * The adapter instance.
     *
     * @var Proxy\Adapter
     */
    protected $adapter;

    /**
     * The registered request filters.
     *
     * @var array
     */
    protected $requestFilters = array();

    /**
     * The registered response filters.
     *
     * @var array
     */
    protected $responseFilters = array();

    /**
     * Construct a Proxy instance.
     *
     * @param Proxy\Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Prepare the proxy to forward a request instance.
     *
     * @param  Symfony\Component\HttpFoundation\Request $request
     * @return $this
     */
    public function forward(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Forward the request to the target url and return the response.
     *
     * @param  string $target
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function to($target)
    {
        if (is_null($this->request))
        {
            throw new \UnexpectedValueException('Missing request instance.');
        }

        $this->applyRequestFilter($this->request);

        $response = $this->adapter->send($this->request, $target);

        $this->applyResponseFilter($response);

        return $response;
    }

    /**
     * Overwrite the request filters array.
     *
     * @param array $filters
     */
    public function setRequestFilter(array $filters)
    {
        $this->requestFilters = $filters;
    }

    /**
     * Register a request filter.
     *
     * @param Proxy\Request\Filter\RequestFilter $filter
     */
    public function addRequestFilter(RequestFilter $filter)
    {
        array_push($this->requestFilters, $filter);
    }

    /**
     * Overwrite the response filters array.
     *
     * @param array $filters
     */
    public function setResponseFilter(array $filters)
    {
        $this->responseFilters = $filters;
    }

    /**
     * Register a response filter.
     *
     * @param Proxy\Request\Filter\ResponseFilter $filter
     */
    public function addResponseFilter(ResponseFilter $filter)
    {
        array_push($this->responseFilters, $filter);
    }

    /**
     * Apply request filters to the request instance.
     *
     * @param  Symfony\Component\HttpFoundation\Request $request
     * @return Symfony\Component\HttpFoundation\Request
     */
    protected function applyRequestFilter(Request $request)
    {
        $callback = function(RequestFilter $filter) use ($request)
        {
            $filter->filter($request);
        };

        array_map($callback, $this->requestFilters);

        return $request;
    }

    /**
     * Apply response filters to the response instance.
     *
     * @param  Symfony\Component\HttpFoundation\Response $response
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function applyResponseFilter(Response $response)
    {
        $callback = function(ResponseFilter $filter) use ($response)
        {
            $filter->filter($response);
        };

        array_map($callback, $this->responseFilters);

        return $response;
    }

}