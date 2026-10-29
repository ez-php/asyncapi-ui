<?php

declare(strict_types=1);

namespace EzPhp\AsyncApiUi;

use EzPhp\Http\Request;
use EzPhp\Http\Response;

/**
 * Class AsyncApiUiController
 *
 * Invokable controller rendering the AsyncAPI React standalone component over
 * an AsyncAPI document URL (typically `GET /asyncapi.json` from
 * `ez-php/asyncapi`). The component fetches and renders the document in the
 * browser; the assets come from the jsDelivr CDN — nothing is vendored.
 *
 * Registered by AsyncApiUiServiceProvider::boot().
 *
 * @package EzPhp\AsyncApiUi
 */
final readonly class AsyncApiUiController
{
    /**
     * Pinned CDN assets with Subresource Integrity hashes — the browser refuses
     * a tampered or changed file. Bump version and both hashes together
     * (sha384 of the exact files); see CLAUDE.md for the command.
     */
    public const string VERSION = '3.2.1';

    private const string CSS = 'https://cdn.jsdelivr.net/npm/@asyncapi/react-component@' . self::VERSION . '/styles/default.min.css';

    private const string CSS_SRI = 'sha384-oo9RoQcacP++XdMX6CjTucTvASEORHX3chFik0/V2kHcsHiVboGyWZztGeq/0bum';

    private const string JS = 'https://cdn.jsdelivr.net/npm/@asyncapi/react-component@' . self::VERSION . '/browser/standalone/index.js';

    private const string JS_SRI = 'sha384-wy5bSOazlkSKMGH7XMW6+pK8ho8+rCPr7mTqKdAb/dvfwXAPWCFivgr3C7wJMFAh';

    /**
     * @param string $documentUrl URL of the AsyncAPI document the viewer fetches, e.g. '/asyncapi.json'.
     * @param string $title       HTML `<title>` of the page.
     */
    public function __construct(
        private string $documentUrl,
        private string $title,
    ) {
    }

    /**
     * Render the viewer page.
     *
     * @param Request $request Unused; required by the router signature.
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        return (new Response($this->html(), 200))->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * @return string
     */
    private function html(): string
    {
        $title = htmlspecialchars($this->title, ENT_QUOTES);
        $css = self::CSS;
        $cssSri = self::CSS_SRI;
        $js = self::JS;
        $jsSri = self::JS_SRI;
        // Inside <script>, HTML escaping does not apply: encode as a JS string
        // literal, with <, >, &, ' and " hex-escaped so it cannot close the tag.
        $props = json_encode(
            ['schema' => ['url' => $this->documentUrl], 'config' => ['show' => ['sidebar' => true]]],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return <<<HTML
            <!doctype html>
            <html>
            <head>
                <title>{$title}</title>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link rel="stylesheet" href="{$css}" integrity="{$cssSri}" crossorigin="anonymous">
            </head>
            <body>
                <div id="asyncapi"></div>
                <script src="{$js}" integrity="{$jsSri}" crossorigin="anonymous"></script>
                <script>
                    AsyncApiStandalone.render({$props}, document.getElementById("asyncapi"));
                </script>
            </body>
            </html>
            HTML;
    }
}
