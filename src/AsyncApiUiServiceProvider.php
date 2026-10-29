<?php

declare(strict_types=1);

namespace EzPhp\AsyncApiUi;

use EzPhp\Contracts\ConfigInterface;
use EzPhp\Contracts\ContainerInterface;
use EzPhp\Contracts\RouterInterface;
use EzPhp\Contracts\ServiceProvider;

/**
 * Class AsyncApiUiServiceProvider
 *
 * Binds AsyncApiUiController from config and registers the viewer page in
 * boot(). The document itself comes from ez-php/asyncapi — this module only
 * renders it.
 *
 * Configuration keys (config/asyncapi-ui.php or environment):
 *   - asyncapi-ui.endpoint     — path of the viewer page (default '/asyncapi-docs'; '' = no route)
 *   - asyncapi-ui.document_url — URL the page loads the document from
 *                                (default: `asyncapi.endpoint`, else '/asyncapi.json')
 *   - app.name                 — page title (default 'AsyncAPI Documentation')
 *
 * Without a router or config (CLI, minimal test containers) it degrades to
 * the defaults or registers nothing.
 *
 * @package EzPhp\AsyncApiUi
 */
final class AsyncApiUiServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(AsyncApiUiController::class, function (ContainerInterface $app): AsyncApiUiController {
            $config = $app->has(ConfigInterface::class) ? $app->make(ConfigInterface::class) : null;

            $documentUrl = $this->string($config, 'asyncapi-ui.document_url', '');

            if ($documentUrl === '') {
                $documentUrl = $this->string($config, 'asyncapi.endpoint', '') ?: '/asyncapi.json';
            }

            return new AsyncApiUiController($documentUrl, $this->string($config, 'app.name', '') ?: 'AsyncAPI Documentation');
        });
    }

    /**
     * Register the viewer route.
     *
     * @return void
     */
    public function boot(): void
    {
        if (!$this->app->has(RouterInterface::class)) {
            return;
        }

        $config = $this->app->has(ConfigInterface::class) ? $this->app->make(ConfigInterface::class) : null;
        $endpoint = $config !== null ? $config->get('asyncapi-ui.endpoint', '/asyncapi-docs') : '/asyncapi-docs';

        if (!is_string($endpoint) || $endpoint === '') {
            return;
        }

        $this->app->make(RouterInterface::class)->get($endpoint, [AsyncApiUiController::class, '__invoke']);
    }

    /**
     * @param ConfigInterface|null $config
     * @param string               $key
     * @param string               $default
     *
     * @return string
     */
    private function string(?ConfigInterface $config, string $key, string $default): string
    {
        $value = $config?->get($key, $default);

        return is_string($value) ? $value : $default;
    }
}
