<?php

declare(strict_types=1);

namespace Tests;

use EzPhp\Application\Application;
use EzPhp\AsyncApiUi\AsyncApiUiController;
use EzPhp\AsyncApiUi\AsyncApiUiServiceProvider;
use EzPhp\Contracts\RouterInterface;
use EzPhp\Http\Request;
use EzPhp\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\AsyncApiUi\ApplicationTestCase;

/**
 * Class AsyncApiUiDefaultsProviderTest
 *
 * No asyncapi-ui config: default endpoint, document URL taken from
 * ez-php/asyncapi's `asyncapi.endpoint`, default title.
 *
 * @package Tests
 */
#[CoversClass(AsyncApiUiServiceProvider::class)]
#[UsesClass(AsyncApiUiController::class)]
final class AsyncApiUiDefaultsProviderTest extends ApplicationTestCase
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected function configFiles(): array
    {
        return ['asyncapi' => ['endpoint' => '/api/asyncapi.json']];
    }

    /**
     * @param Application $app
     *
     * @return void
     */
    protected function configureApplication(Application $app): void
    {
        $app->register(AsyncApiUiServiceProvider::class);
    }

    public function test_defaults_follow_the_asyncapi_endpoint(): void
    {
        $response = $this->app()->handle(new Request('GET', '/asyncapi-docs'));

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(200, $response->status());
        self::assertStringContainsString('<title>AsyncAPI Documentation</title>', $response->body());
        self::assertStringContainsString('{"schema":{"url":"/api/asyncapi.json"}', $response->body());
    }

    public function test_registers_exactly_one_get_route(): void
    {
        $routes = $this->app()->make(RouterInterface::class)->toCache();

        self::assertCount(1, $routes);
        self::assertSame(['GET', '/asyncapi-docs'], [$routes[0]['method'], $routes[0]['path']]);
    }
}
