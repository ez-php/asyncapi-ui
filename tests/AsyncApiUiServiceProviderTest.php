<?php

declare(strict_types=1);

namespace Tests;

use EzPhp\Application\Application;
use EzPhp\AsyncApiUi\AsyncApiUiController;
use EzPhp\AsyncApiUi\AsyncApiUiServiceProvider;
use EzPhp\Http\Request;
use EzPhp\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\AsyncApiUi\ApplicationTestCase;

/**
 * Class AsyncApiUiServiceProviderTest
 *
 * Configured endpoint and document URL, served end to end.
 *
 * @package Tests
 */
#[CoversClass(AsyncApiUiServiceProvider::class)]
#[UsesClass(AsyncApiUiController::class)]
final class AsyncApiUiServiceProviderTest extends ApplicationTestCase
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected function configFiles(): array
    {
        return [
            'app' => ['name' => 'Shop'],
            'asyncapi-ui' => ['endpoint' => '/events/docs', 'document_url' => '/events/asyncapi.json'],
        ];
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

    public function test_serves_the_viewer_on_the_configured_endpoint(): void
    {
        $response = $this->app()->handle(new Request('GET', '/events/docs'));

        self::assertSame(200, $response->status());
        self::assertInstanceOf(Response::class, $response);
        self::assertStringContainsString('<title>Shop</title>', $response->body());
        self::assertStringContainsString('{"schema":{"url":"/events/asyncapi.json"}', $response->body());
    }
}
