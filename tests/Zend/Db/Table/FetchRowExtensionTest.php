<?php

declare(strict_types=1);

namespace Tests\Zend\Db\Table;

use PHPStan\Testing\TypeInferenceTestCase;

class FetchRowExtensionTest extends TypeInferenceTestCase
{
    public static function getAdditionalConfigFiles(): array
    {
        return [ROOT . '/extension.neon'];
    }

    /**
     * @return iterable<mixed>
     */
    public function dataFileAsserts(): iterable
    {
        // path to a file with actual asserts of expected types:
        yield from $this->gatherAssertTypes(ROOT . '/tests/fixtures/TestClass.php');
    }

    /**
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(
        string $assertType,
        string $file,
        ...$args
    ): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }
}
