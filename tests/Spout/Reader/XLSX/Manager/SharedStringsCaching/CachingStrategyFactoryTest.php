<?php

namespace Box\Spout\Reader\XLSX\Manager\SharedStringsCaching;

use Box\Spout\Reader\XLSX\Creator\HelperFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CachingStrategyFactoryTest extends TestCase
{
    public static function dataProviderForTestCreateBestCachingStrategy(): array
    {
        return [
            [null, -1, 'FileBasedStrategy'],
            [CachingStrategyFactory::MAX_NUM_STRINGS_PER_TEMP_FILE, -1, 'FileBasedStrategy'],
            [CachingStrategyFactory::MAX_NUM_STRINGS_PER_TEMP_FILE + 10, -1, 'FileBasedStrategy'],
            [CachingStrategyFactory::MAX_NUM_STRINGS_PER_TEMP_FILE - 10, -1, 'InMemoryStrategy'],
            [10, CachingStrategyFactory::AMOUNT_MEMORY_NEEDED_PER_STRING_IN_KB * 10, 'FileBasedStrategy'],
            [15, CachingStrategyFactory::AMOUNT_MEMORY_NEEDED_PER_STRING_IN_KB * 10, 'FileBasedStrategy'],
            [5, CachingStrategyFactory::AMOUNT_MEMORY_NEEDED_PER_STRING_IN_KB * 10, 'InMemoryStrategy'],
        ];
    }

    #[DataProvider("dataProviderForTestCreateBestCachingStrategy")]
    public function testCreateBestCachingStrategy(?int $sharedStringsUniqueCount, int $memoryLimitInKB, string $expectedStrategyClassName): void
    {
        /** @var CachingStrategyFactory|\PHPUnit\Framework\MockObject\MockObject $factoryStub */
        $factoryStub = $this
            ->getMockBuilder('\Box\Spout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory')
            ->disableOriginalConstructor()
            ->onlyMethods(['getMemoryLimitInKB'])
            ->getMock();

        $factoryStub->method('getMemoryLimitInKB')->willReturn($memoryLimitInKB);

        $tempFolder = sys_get_temp_dir();
        $helperFactory = new HelperFactory();
        $strategy = $factoryStub->createBestCachingStrategy($sharedStringsUniqueCount, $tempFolder, $helperFactory);

        $fullExpectedStrategyClassName = 'Box\Spout\Reader\XLSX\Manager\SharedStringsCaching\\' . $expectedStrategyClassName;
        $this->assertEquals($fullExpectedStrategyClassName, get_class($strategy));

        $strategy->clearCache();
    }

    public static function dataProviderForTestGetMemoryLimitInKB(): array
    {
        return [
            ['-1', -1],
            ['invalid', -1],
            ['1024B', 1],
            ['128K', 128],
            ['256KB', 256],
            ['512M', 512 * 1024],
            ['2MB', 2 * 1024],
            ['1G', 1 * 1024 * 1024],
            ['10GB', 10 * 1024 * 1024],
            ['2T', 2 * 1024 * 1024 * 1024],
            ['5TB', 5 * 1024 * 1024 * 1024],
        ];
    }

    #[DataProvider("dataProviderForTestGetMemoryLimitInKB")]
    public function testGetMemoryLimitInKB(string $memoryLimitFormatted, float $expectedMemoryLimitInKB): void
    {
        /** @var CachingStrategyFactory|\PHPUnit\Framework\MockObject\MockObject $factoryStub */
        $factoryStub = $this
            ->getMockBuilder('\Box\Spout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory')
            ->disableOriginalConstructor()
            ->onlyMethods(['getMemoryLimitFromIni'])
            ->getMock();

        $factoryStub->method('getMemoryLimitFromIni')->willReturn($memoryLimitFormatted);

        $memoryLimitInKB = \ReflectionHelper::callMethodOnObject($factoryStub, 'getMemoryLimitInKB');

        $this->assertEquals($expectedMemoryLimitInKB, $memoryLimitInKB);
    }
}
