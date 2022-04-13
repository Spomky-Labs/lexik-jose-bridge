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

    public function getUri(): string
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
            . ($parse_url['host'] ?? '')
            . ((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
            . ($parse_url['path'] ?? '')
            . ((isset($parse_url['query'])) ? '?' . http_build_query($parse_url['query']) : '')
            . ((isset($parse_url['fragment'])) ? '#' . http_build_query($parse_url['fragment']) : '');
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addFragmentParameter($key, $value): static
    {
        $this->fragment[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     */
    public function removeFragmentParameter($key): static
    {
        unset($this->fragment[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addQueryParameter($key, $value): static
    {
        $this->query[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     */
    public function removeQueryParameter($key): static
    {
        unset($this->query[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addServer($key, $value): static
    {
        $this->server[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     */
    public function removeServer($key): static
    {
        unset($this->server[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addHeader($key, $value): static
    {
        $this->header[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     */
    public function removeHeader($key): static
    {
        unset($this->header[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addRequestParameter($key, $value): static
    {
        $this->request_parameter[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     */
    public function removeRequestParameter($key): static
    {
        unset($this->request_parameter[$key]);

        return $this;
    }

    public function getRequestParameters(): array
    {
        return $this->request_parameter;
    }

    /**
     * @param $content
     */
    public function setContent($content): static
    {
        $this->content = $content;

        return $this;
    }

    public function unsetContent(): static
    {
        $this->content = null;

        return $this;
    }

    /**
     * @param string $method
     */
    public function setMethod($method): static
    {
        $this->method = $method;

        return $this;
    }

    public function unsetMethod(): static
    {
        $this->method = 'GET';

        return $this;
    }

    /**
     * @param $uri
     */
    public function setUri($uri): static
    {
        $this->uri = $uri;

        return $this;
    }

    public function unsetUri(): static
    {
        $this->uri = '/';

        return $this;
    }

    public function getServer(): array
    {
        $data = $this->server;
        foreach ($this->header as $key => $value) {
            $data[mb_strtoupper('HTTP_' . $key)] = $value;
        }

        return $data;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getRequest(): Request
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
