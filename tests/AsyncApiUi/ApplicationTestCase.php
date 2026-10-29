<?php

declare(strict_types=1);

namespace Tests\AsyncApiUi;

use EzPhp\Testing\ApplicationTestCase as EzPhpApplicationTestCase;
use RuntimeException;

/**
 * Base class for asyncapi-ui tests that need a bootstrapped Application.
 *
 * Writes one config file per entry of configFiles() before bootstrap — the
 * provider registers its route in boot(), so config must exist by then.
 *
 * @package Tests\AsyncApiUi
 */
abstract class ApplicationTestCase extends EzPhpApplicationTestCase
{
    /**
     * Config file name (without .php) => array it returns.
     *
     * @return array<string, array<string, mixed>>
     */
    abstract protected function configFiles(): array;

    /**
     * @return string
     */
    protected function getBasePath(): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ez-asyncapi-ui-test-' . uniqid('', true);
        $configDir = $path . DIRECTORY_SEPARATOR . 'config';

        mkdir($configDir, 0o777, true);

        foreach ($this->configFiles() as $name => $values) {
            $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($values, true) . ";\n";

            if (file_put_contents($configDir . DIRECTORY_SEPARATOR . $name . '.php', $content) === false) {
                throw new RuntimeException("Failed to write {$name}.php for test at {$configDir}");
            }
        }

        return $path;
    }
}
