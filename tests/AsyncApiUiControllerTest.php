<?php

declare(strict_types=1);

namespace Tests;

use EzPhp\AsyncApiUi\AsyncApiUiController;
use EzPhp\Http\Headers;
use EzPhp\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class AsyncApiUiControllerTest
 *
 * @package Tests
 */
#[CoversClass(AsyncApiUiController::class)]
final class AsyncApiUiControllerTest extends TestCase
{
    public function test_renders_an_html_page_with_pinned_assets_and_sri(): void
    {
        $response = (new AsyncApiUiController('/asyncapi.json', 'Shop Events'))(new Request('GET', '/asyncapi-docs'));
        $html = $response->body();
        $version = AsyncApiUiController::VERSION;

        self::assertSame(200, $response->status());
        self::assertSame('text/html; charset=utf-8', Headers::get($response->headers(), 'Content-Type'));
        self::assertStringContainsString('<title>Shop Events</title>', $html);
        self::assertStringContainsString("@asyncapi/react-component@{$version}/browser/standalone/index.js\" integrity=\"sha384-", $html);
        self::assertStringContainsString("@asyncapi/react-component@{$version}/styles/default.min.css\" integrity=\"sha384-", $html);
        self::assertSame(2, substr_count($html, 'crossorigin="anonymous"'));
        self::assertStringContainsString('AsyncApiStandalone.render({"schema":{"url":"/asyncapi.json"}', $html);
    }

    public function test_escapes_the_title_for_html_and_the_document_url_for_javascript(): void
    {
        $html = (new AsyncApiUiController('/x.json?a=1&b="</script><script>alert(1)</script>', '<b>"Docs"</b>'))(new Request('GET', '/'))->body();

        self::assertStringContainsString('<title>&lt;b&gt;&quot;Docs&quot;&lt;/b&gt;</title>', $html);
        self::assertStringNotContainsString('</script><script>alert(1)', $html);
        self::assertStringContainsString('\\u0026b=\\u0022\\u003C/script\\u003E', $html);
    }
}
