<?php

/*
 * AJGL CSV RFC Component
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Csv\Rfc\Tests;

use Ajgl\Csv\Rfc\CsvRfcWriteStreamFilter;
use League\Csv\Reader;
use League\Csv\Writer;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class CsvRfcWriteStreamFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testRegiterWithDefaultFiltername()
    {
        $this->assertTrue(CsvRfcWriteStreamFilter::register());
        $this->assertFalse(stream_filter_register(CsvRfcWriteStreamFilter::FILTERNAME_DEFAULT, 'php_user_filter'));
    }

    public function testRegisterWithCustomName()
    {
        $this->assertTrue(CsvRfcWriteStreamFilter::register('foo.bar'));
        $this->assertFalse(stream_filter_register('foo.bar', 'php_user_filter'));
    }

    public function testFilterWithStandardEnclosure()
    {
        CsvRfcWriteStreamFilter::register();
        $fp = fopen('php://temp', 'w+');
        stream_filter_append($fp, CsvRfcWriteStreamFilter::FILTERNAME_DEFAULT, STREAM_FILTER_WRITE);
        fputcsv($fp, array('"Hello\", World!'), ',', '"');
        rewind($fp);
        $actual = fgets($fp, 4096);
        $expected = '"""Hello\"", World!"'."\n";
        $this->assertEquals($expected, $actual);
    }

    public function testFilterWithCustomEnclosureViaParameters()
    {
        CsvRfcWriteStreamFilter::register();
        $fp = fopen('php://temp', 'w+');
        stream_filter_append($fp, CsvRfcWriteStreamFilter::FILTERNAME_DEFAULT, STREAM_FILTER_WRITE, array('enclosure' => '%'));
        fputcsv($fp, array('%Hello\%, World!'), ',', '%');
        rewind($fp);
        $actual = fgets($fp, 4096);
        $expected = '%%%Hello\%%, World!%'."\n";
        $this->assertEquals($expected, $actual);
    }

    public function testFilterWithCustomEnclosureViaFiltername()
    {
        CsvRfcWriteStreamFilter::register('csv.rfc.write.%');
        $fp = fopen('php://temp', 'w+');
        stream_filter_append($fp, 'csv.rfc.write.%', STREAM_FILTER_WRITE);
        fputcsv($fp, array('%Hello\%, World!'), ',', '%');
        rewind($fp);
        $actual = fgets($fp, 4096);
        $expected = '%%%Hello\%%, World!%'."\n";
        $this->assertEquals($expected, $actual);
    }

    public function testLeagueCsvIntegrationWithPath()
    {
        if (!class_exists('League\Csv\Writer')) {
            $this->markTestSkipped("'league/csv' package not found.");
        }

        $payload = '"Hello\", World!';

        CsvRfcWriteStreamFilter::register();
        $filepath = tempnam(sys_get_temp_dir(), 'ajgl_csv_rfc_test_');
        $writer = Writer::createFromPath($filepath);
        $writer->appendStreamFilter(CsvRfcWriteStreamFilter::FILTERNAME_DEFAULT);
        $writer->insertOne(array($payload));
        unset($writer);

        $fp = fopen($filepath, 'r');
        $actual = fgets($fp, 4096);
        $expected = '"""Hello\"", World!"'."\n";
        $this->assertEquals($expected, $actual);
        fclose($fp);

        $reader = Reader::createFromPath($filepath);
        $reader->setEscape($reader->getEnclosure());
        $data = array(array($payload));
        $this->assertEquals($data, iterator_to_array($reader));
    }
}
