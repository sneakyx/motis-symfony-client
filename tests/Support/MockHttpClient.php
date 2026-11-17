<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\Tests\Support;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Minimal PSR-18 mock for unit tests: replays pre-configured responses
 * (or exceptions) and records all sent requests.
 */
final class MockHttpClient implements ClientInterface
{
    /** @var list<ResponseInterface|ClientExceptionInterface> */
    private array $responses;

    private int $next = 0;

    /** @var list<RequestInterface> */
    private array $requests = [];

    public function __construct(ResponseInterface|ClientExceptionInterface ...$responses)
    {
        $this->responses = $responses;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if (!isset($this->responses[$this->next])) {
            throw new \LogicException('No more responses configured for MockHttpClient');
        }

        $response = $this->responses[$this->next++];

        if ($response instanceof ClientExceptionInterface) {
            throw $response;
        }

        return $response;
    }

    /**
     * @return list<RequestInterface>
     */
    public function requests(): array
    {
        return $this->requests;
    }
}
