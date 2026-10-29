<?php

declare(strict_types=1);

namespace Tests;

use EzPhp\Application\Application;
use EzPhp\AsyncApiUi\AsyncApiUiController;
use EzPhp\AsyncApiUi\AsyncApiUiServiceProvider;
use EzPhp\Contracts\RouterInterface;
use EzPhp\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\AsyncApiUi\ApplicationTestCase;

/**
 * Class AsyncApiUiDisabledProviderTest
 *
 * An empty endpoint registers no route; the controller binding still resolves
 * with the hard-coded fallbacks.
 *
 * @package Tests
 */
#[CoversClass(AsyncApiUiServiceProvider::class)]
#[UsesClass(AsyncApiUiController::class)]
final class AsyncApiUiDisabledProviderTest extends ApplicationTestCase
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected function configFiles(): array
    {
        return ['asyncapi-ui' => ['endpoint' => '']];
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

    public function test_empty_endpoint_registers_no_route(): void
    {
        self::assertSame([], $this->app()->make(RouterInterface::class)->toCache());
    }

    public function test_controller_falls_back_to_the_default_document_url(): void
    {
        $html = $this->app()->make(AsyncApiUiController::class)(new Request('GET', '/'))->body();

        self::assertStringContainsString('{"schema":{"url":"/asyncapi.json"}', $html);
    }
}
