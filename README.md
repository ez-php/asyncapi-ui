# ez-php/asyncapi-ui

An HTML viewer for your AsyncAPI document. It serves a page that renders `GET /asyncapi.json`
(from [`ez-php/asyncapi`](https://github.com/ez-php/asyncapi)) with the official AsyncAPI
React component, loaded from jsDelivr. This is the AsyncAPI counterpart to `ez-php/swagger-ui`.

---

## Installation

```bash
composer require ez-php/asyncapi-ui
```

---

## Setup

```php
// provider/modules.php
EzPhp\AsyncApi\AsyncApiServiceProvider::class,      // serves /asyncapi.json
EzPhp\AsyncApiUi\AsyncApiUiServiceProvider::class,  // serves /asyncapi-docs
```

`config/asyncapi-ui.php` (optional):

```php
return [
    'endpoint' => getenv('ASYNCAPI_UI_ENDPOINT') ?: '/asyncapi-docs',  // '' = no route
    'document_url' => getenv('ASYNCAPI_UI_DOCUMENT_URL') ?: '',        // '' = asyncapi.endpoint
];
```

The page title comes from `app.name`. Open `/asyncapi-docs`.

---

## Notes

- **Assets:** loaded from jsDelivr, pinned to `@asyncapi/react-component@3.2.1` with
  Subresource Integrity hashes. The browser loads exactly those files or nothing. Nothing is vendored.
- **Content-Security-Policy:** if you send a CSP, it must allow `https://cdn.jsdelivr.net` for
  scripts and styles, plus the inline `render(...)` script.
- **Restricting access:** set `endpoint` to `''` and register the route yourself behind your
  middleware:
  `$router->get('/internal/events', [AsyncApiUiController::class, '__invoke'])->middleware('auth');`

---

## License

MIT
