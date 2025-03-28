<?php

namespace Box\Spout\Common\Helper;

use Box\Spout\Common\Exception\EncodingConversionException;
use Box\Spout\TestUsingResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EncodingHelperTest extends TestCase
{
    use TestUsingResource;

    public static function dataProviderForTestGetBytesOffsetToSkipBOM(): array
    {
        return [
            ['csv_with_utf8_bom.csv', EncodingHelper::ENCODING_UTF8, 3],
            ['csv_with_utf16be_bom.csv', EncodingHelper::ENCODING_UTF16_BE, 2],
            ['csv_with_utf32le_bom.csv', EncodingHelper::ENCODING_UTF32_LE, 4],
            ['csv_with_encoding_utf16le_no_bom.csv', EncodingHelper::ENCODING_UTF16_LE, 0],
            ['csv_standard.csv', EncodingHelper::ENCODING_UTF8, 0],
        ];
    }

    #[DataProvider("dataProviderForTestGetBytesOffsetToSkipBOM")]
    public function testGetBytesOffsetToSkipBOM(string $fileName, string $encoding, int $expectedBytesOffset): void
    {
        $resourcePath = $this->getResourcePath($fileName);
        $filePointer = fopen($resourcePath, 'r');

        $encodingHelper = new EncodingHelper(new GlobalFunctionsHelper());
        $bytesOffset = $encodingHelper->getBytesOffsetToSkipBOM($filePointer, $encoding);

        $this->assertEquals($expectedBytesOffset, $bytesOffset);
    }

    public static function dataProviderForIconvOrMbstringUsage(): array
    {
        return [
            [$shouldUseIconv = true],
            [$shouldNotUseIconv = false],
        ];
    }

    #[DataProvider("dataProviderForIconvOrMbstringUsage")]
    public function testAttemptConversionToUTF8ShouldThrowIfConversionFailed(bool $shouldUseIconv): void
    {
        $this->expectException(EncodingConversionException::class);

        $helperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\GlobalFunctionsHelper')
                        ->onlyMethods(['iconv', 'mb_convert_encoding'])
                        ->getMock();
        $helperStub->method('iconv')->willReturn(false);
        $helperStub->method('mb_convert_encoding')->willReturn(false);

        /** @var EncodingHelper|\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject $encodingHelperStub */
        $encodingHelperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\EncodingHelper')
                        ->setConstructorArgs([$helperStub])
                        ->onlyMethods(['canUseIconv', 'canUseMbString'])
                        ->getMock();
        $encodingHelperStub->method('canUseIconv')->willReturn($shouldUseIconv);
        $encodingHelperStub->method('canUseMbString')->willReturn(true);

        $encodingHelperStub->attemptConversionToUTF8('input', EncodingHelper::ENCODING_UTF16_LE);
    }

    public function testAttemptConversionToUTF8ShouldThrowIfConversionNotSupported(): void
    {
        $this->expectException(EncodingConversionException::class);

        /** @var EncodingHelper|\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject $encodingHelperStub */
        $encodingHelperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\EncodingHelper')
                        ->disableOriginalConstructor()
                        ->onlyMethods(['canUseIconv', 'canUseMbString'])
                        ->getMock();
        $encodingHelperStub->method('canUseIconv')->willReturn(false);
        $encodingHelperStub->method('canUseMbString')->willReturn(false);

        $encodingHelperStub->attemptConversionToUTF8('input', EncodingHelper::ENCODING_UTF16_LE);
    }

    #[DataProvider("dataProviderForIconvOrMbstringUsage")]
    public function testAttemptConversionToUTF8ShouldReturnReencodedString(bool $shouldUseIconv): void
    {
        /** @var EncodingHelper|\PHPUnit\Framework\MockObject\MockObject $encodingHelperStub */
        $encodingHelperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\EncodingHelper')
                        ->setConstructorArgs([new GlobalFunctionsHelper()])
                        ->onlyMethods(['canUseIconv', 'canUseMbString'])
                        ->getMock();
        $encodingHelperStub->method('canUseIconv')->willReturn($shouldUseIconv);
        $encodingHelperStub->method('canUseMbString')->willReturn(true);

        $encodedString = iconv(EncodingHelper::ENCODING_UTF8, EncodingHelper::ENCODING_UTF16_LE, 'input');
        $decodedString = $encodingHelperStub->attemptConversionToUTF8($encodedString, EncodingHelper::ENCODING_UTF16_LE);

        $this->assertEquals('input', $decodedString);
    }

    public function testAttemptConversionToUTF8ShouldBeNoopWhenTargetIsUTF8(): void
    {
        /** @var EncodingHelper|\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject $encodingHelperStub */
        $encodingHelperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\EncodingHelper')
                        ->disableOriginalConstructor()
                        ->onlyMethods(['canUseIconv'])
                        ->getMock();
        $encodingHelperStub->expects($this->never())->method('canUseIconv');

        $decodedString = $encodingHelperStub->attemptConversionToUTF8('input', EncodingHelper::ENCODING_UTF8);
        $this->assertEquals('input', $decodedString);
    }

    #[DataProvider("dataProviderForIconvOrMbstringUsage")]
    public function testAttemptConversionFromUTF8ShouldThrowIfConversionFailed(bool $shouldUseIconv): void
    {
        $this->expectException(EncodingConversionException::class);

        $helperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\GlobalFunctionsHelper')
                        ->onlyMethods(['iconv', 'mb_convert_encoding'])
                        ->getMock();
        $helperStub->method('iconv')->willReturn(false);
        $helperStub->method('mb_convert_encoding')->willReturn(false);

        /** @var EncodingHelper|\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject $encodingHelperStub */
        $encodingHelperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\EncodingHelper')
                        ->setConstructorArgs([$helperStub])
                        ->onlyMethods(['canUseIconv', 'canUseMbString'])
                        ->getMock();
        $encodingHelperStub->method('canUseIconv')->willReturn($shouldUseIconv);
        $encodingHelperStub->method('canUseMbString')->willReturn(true);

        $encodingHelperStub->attemptConversionFromUTF8('input', EncodingHelper::ENCODING_UTF16_LE);
    }

    public function testAttemptConversionFromUTF8ShouldThrowIfConversionNotSupported(): void
    {
        $this->expectException(EncodingConversionException::class);

        /** @var EncodingHelper|\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject $encodingHelperStub */
        $encodingHelperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\EncodingHelper')
                        ->disableOriginalConstructor()
                        ->onlyMethods(['canUseIconv', 'canUseMbString'])
                        ->getMock();
        $encodingHelperStub->method('canUseIconv')->willReturn(false);
        $encodingHelperStub->method('canUseMbString')->willReturn(false);

        $encodingHelperStub->attemptConversionFromUTF8('input', EncodingHelper::ENCODING_UTF16_LE);
    }

    #[DataProvider("dataProviderForIconvOrMbstringUsage")]
    public function testAttemptConversionFromUTF8ShouldReturnReencodedString(bool $shouldUseIconv): void
    {
        /** @var EncodingHelper|\PHPUnit\Framework\MockObject\MockObject $encodingHelperStub */
        $encodingHelperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\EncodingHelper')
                        ->setConstructorArgs([new GlobalFunctionsHelper()])
                        ->onlyMethods(['canUseIconv', 'canUseMbString'])
                        ->getMock();
        $encodingHelperStub->method('canUseIconv')->willReturn($shouldUseIconv);
        $encodingHelperStub->method('canUseMbString')->willReturn(true);

        $encodedString = $encodingHelperStub->attemptConversionFromUTF8('input', EncodingHelper::ENCODING_UTF16_LE);
        $encodedStringWithIconv = iconv(EncodingHelper::ENCODING_UTF8, EncodingHelper::ENCODING_UTF16_LE, 'input');

        $this->assertEquals($encodedStringWithIconv, $encodedString);
    }

    public function testAttemptConversionFromUTF8ShouldBeNoopWhenTargetIsUTF8(): void
    {
        /** @var EncodingHelper|\PHPUnit\Framework\MockObject\MockObject $encodingHelperStub */
        $encodingHelperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\EncodingHelper')
                        ->disableOriginalConstructor()
                        ->onlyMethods(['canUseIconv'])
                        ->getMock();
        $encodingHelperStub->expects($this->never())->method('canUseIconv');

        $encodedString = $encodingHelperStub->attemptConversionFromUTF8('input', EncodingHelper::ENCODING_UTF8);
        $this->assertEquals('input', $encodedString);
    }
}
