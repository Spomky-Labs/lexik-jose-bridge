<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use function count;
use Symfony\Component\HttpFoundation\Request;

final class RequestBuilder
{
    private array $query = [];

    private array $fragment = [];

    private array $server = [];

    private array $header = [];

    private array $request_parameter = [];

    private ?string $content = null;

    private string $method = 'GET';

    private string $uri = '/';

    /**
     * @return string
     */
    public function getUri()
    {
        $parse_url = parse_url($this->uri);
        $parse_url['query'] = array_merge($parse_url['query'] ?? [], $this->query);
        $parse_url['fragment'] = array_merge($parse_url['fragment'] ?? [], $this->fragment);
        if (count($parse_url['query']) === 0) {
            unset($parse_url['query']);
        }
        if (count($parse_url['fragment']) === 0) {
            unset($parse_url['fragment']);
        }

        return
            ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
            . ((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') . '@' : '')
            . ((isset($parse_url['host'])) ? $parse_url['host'] : '')
            . ((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
            . ((isset($parse_url['path'])) ? $parse_url['path'] : '')
            . ((isset($parse_url['query'])) ? '?' . http_build_query($parse_url['query']) : '')
            . ((isset($parse_url['fragment'])) ? '#' . http_build_query($parse_url['fragment']) : '');
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return self
     */
    public function addFragmentParameter($key, $value)
    {
        $this->fragment[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function removeFragmentParameter($key)
    {
        unset($this->fragment[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return self
     */
    public function addQueryParameter($key, $value)
    {
        $this->query[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function removeQueryParameter($key)
    {
        unset($this->query[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return self
     */
    public function addServer($key, $value)
    {
        $this->server[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     *
     * @return self
     */
    public function removeServer($key)
    {
        unset($this->server[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return self
     */
    public function addHeader($key, $value)
    {
        $this->header[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function removeHeader($key)
    {
        unset($this->header[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return self
     */
    public function addRequestParameter($key, $value)
    {
        $this->request_parameter[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function removeRequestParameter($key)
    {
        unset($this->request_parameter[$key]);

        return $this;
    }

    /**
     * @return array
     */
    public function getRequestParameters()
    {
        return $this->request_parameter;
    }

    /**
     * @param $content
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return self
     */
    public function unsetContent()
    {
        $this->content = null;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return self
     */
    public function unsetMethod()
    {
        $this->method = 'GET';

        return $this;
    }

    /**
     * @param $uri
     *
     * @return self
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return self
     */
    public function unsetUri()
    {
        $this->uri = '/';

        return $this;
    }

    /**
     * @return array
     */
    public function getServer()
    {
        $data = $this->server;
        foreach ($this->header as $key => $value) {
            $data[mb_strtoupper('HTTP_' . $key)] = $value;
        }

        return $data;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return Request::create(
            $this->getUri(),
            $this->method,
            $this->getRequestParameters(),
            [],
            [],
            $this->getServer(),
            $this->getContent()
        );
    }
}
