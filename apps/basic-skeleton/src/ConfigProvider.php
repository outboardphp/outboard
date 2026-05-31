<?php

declare(strict_types=1);

namespace App;

use Laminas\Diactoros\Response\TextResponse;
use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\ValueObject\Definition;
use Outboard\Framework\Contract\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConfigProvider implements DefinitionProvider
{
    /**
     * @return array<string, Definition>
     */
    public function getDefinitions(): array
    {
        return [
            RouterInterface::class => new Definition(
                shared: true,
                substitute: new class implements RouterInterface {
                    public function dispatch(ServerRequestInterface $request): ResponseInterface
                    {
                        return new TextResponse('Basic skeleton app booted.');
                    }
                },
            ),
        ];
    }
}
