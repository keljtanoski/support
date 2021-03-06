<?php

namespace Tests\Helpers;

use Helldar\Support\Facades\Helpers\Str;
use Helldar\Support\Helpers\Arr;
use Tests\Fixtures\Instances\Arrayable;
use Tests\Fixtures\Instances\Bar;
use Tests\Fixtures\Instances\Baz;
use Tests\TestCase;

final class ArrTest extends TestCase
{
    public function testExcept()
    {
        $array = [
            'foo' => 123,
            'bar' => 456,
            'baz' => 789,
        ];

        $this->assertSame(['bar' => 456, 'baz' => 789], $this->arr()->except($array, 'foo'));
        $this->assertSame(['bar' => 456, 'baz' => 789], $this->arr()->except($array, ['foo']));

        $this->assertSame(['bar' => 456], $this->arr()->except($array, ['foo', 'baz']));

        $this->assertSame(['foo' => 123, 'bar' => 456, 'baz' => 789], $this->arr()->except($array, []));
        $this->assertSame(['foo' => 123, 'bar' => 456, 'baz' => 789], $this->arr()->except($array, null));
        $this->assertSame(['foo' => 123, 'bar' => 456, 'baz' => 789], $this->arr()->except($array, 123));

        $this->assertSame([], $this->arr()->except([], []));
        $this->assertSame([], $this->arr()->except([], ''));
        $this->assertSame([], $this->arr()->except([], ['foo', 'bar']));
    }

    public function testRenameKeys()
    {
        $source = [
            'foo' => 123,
            'BaR' => 456,
            'BAZ' => 789,
        ];

        $expected_renamed = [
            'FOO' => 123,
            'BAR' => 456,
            'BAZ' => 789,
        ];

        $expected_modified = [
            'foo_123' => 123,
            'bar_456' => 456,
            'baz_789' => 789,
        ];

        $renamed = $this->arr()->renameKeys($source, static function ($key) {
            return mb_strtoupper($key);
        });

        $modified = $this->arr()->renameKeys($source, static function ($key, $value) {
            return mb_strtolower($key) . '_' . $value;
        });

        $this->assertSame($expected_renamed, $renamed);
        $this->assertSame($expected_modified, $modified);
    }

    public function testRenameKeysMap()
    {
        $source = [
            'foo' => 123,
            'BaR' => 456,
            'BAZ' => 789,
        ];

        $expected = [
            'FOOX' => 123,
            'BARX' => 456,
            'BAZ'  => 789,
        ];

        $map = [
            'foo' => 'FOOX',
            'BaR' => 'BARX',
        ];

        $renamed = $this->arr()->renameKeysMap($source, $map);

        $this->assertSame($expected, $renamed);
    }

    public function testGet()
    {
        $this->assertEquals('bar', $this->arr()->get(['foo' => 'bar'], 'foo'));
        $this->assertEquals('bar', $this->arr()->get(['foo' => 'bar'], 'foo', 'bar'));
        $this->assertEquals('baz', $this->arr()->get(['foo' => 'bar'], 'bar', 'baz'));

        $this->assertNull($this->arr()->get(['foo' => 'bar'], 'bar'));

        $this->assertSame('Foo', $this->arr()->get(new Arrayable(), 'foo'));
        $this->assertSame('Bar', $this->arr()->get(new Arrayable(), 'bar'));
        $this->assertSame('Baz', $this->arr()->get(new Arrayable(), 'baz'));

        $this->assertNull($this->arr()->get(new Arrayable(), 'qwerty'));
    }

    public function testMerge()
    {
        $arr1 = [
            'foo' => 'Bar',
            '0'   => 'Foo',
            '2'   => 'Bar',
            '400' => 'Baz',
            600   => ['foo' => 'Foo', 'bar' => 'Bar'],
        ];

        $arr2 = [
            '2'   => 'Bar bar',
            '500' => 'Foo bar',
            '600' => ['baz' => 'Baz'],
            '700' => ['aaa' => 'AAA'],
        ];

        $expected = [
            'foo' => 'Bar',
            0     => 'Foo',
            2     => 'Bar bar',
            400   => 'Baz',
            500   => 'Foo bar',
            600   => ['bar' => 'Bar', 'baz' => 'Baz', 'foo' => 'Foo'],
            700   => ['aaa' => 'AAA'],
        ];

        $result = $this->arr()->merge($arr1, $arr2);

        $this->assertSame($expected, $result);
    }

    public function testLongestStringLength()
    {
        $array = ['foo', 'bar', 'foobar', 'baz'];

        $this->assertSame(6, $this->arr()->longestStringLength($array));
    }

    public function testOnly()
    {
        $arr = [
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz',
            200   => 'Num 200',
            400   => 'Num 400',
        ];

        $this->assertSame(['foo' => 'Foo', 'bar' => 'Bar'], $this->arr()->only($arr, ['foo', 'bar']));
        $this->assertSame(['bar' => 'Bar', 200 => 'Num 200'], $this->arr()->only($arr, ['bar', 200]));

        $this->assertSame([], $this->arr()->only($arr, []));
        $this->assertSame([], $this->arr()->only($arr, null));
    }

    public function testStoreAsArray()
    {
        $array = ['q' => 1, 'r' => 2, 's' => 5, 'w' => 123];

        $path = $this->tempDirectory('array.php');

        $this->arr()->store($array, $path);

        $loaded = require $path;

        $this->assertFileExists($path);
        $this->assertIsArray($loaded);
        $this->assertEquals($array, $loaded);
    }

    public function testStoreAsJson()
    {
        $array = ['q' => 1, 'r' => 2, 's' => 5, 'w' => 123];

        $path = $this->tempDirectory('array.json');

        $this->arr()->store($array, $path, true);

        $this->assertJsonStringEqualsJsonFile($path, json_encode($array));
    }

    public function testStoreAsSortedArray()
    {
        $array = ['w' => 123, 'q' => 1, 's' => 5, 'r' => 2];

        $path = $this->tempDirectory('sorted.php');

        $this->arr()->storeAsArray($path, $array, true);

        $loaded = require $path;

        $this->assertIsArray($loaded);
        $this->assertEquals($array, $loaded);
    }

    public function testStoreAsSortedJson()
    {
        $array = ['w' => 123, 'q' => 1, 's' => 5, 'r' => 2];

        $path = $this->tempDirectory('sorted.json');

        $this->arr()->storeAsJson($path, $array, true);

        $this->assertJsonStringEqualsJsonFile($path, json_encode($array));
    }

    public function testSortByKeys()
    {
        $source = ['q' => 1, 'r' => 2, 's' => 5, 'w' => 123];

        $sorter = ['q', 'w', 'e'];

        $expected = ['q' => 1, 'w' => 123, 'r' => 2, 's' => 5];

        $actual = $this->arr()->sortByKeys($source, $sorter);

        $this->assertSame($expected, $actual);
    }

    public function testExists()
    {
        $this->assertTrue($this->arr()->exists(['foo' => 'bar'], 'foo'));
        $this->assertFalse($this->arr()->exists(['foo' => 'bar'], 'bar'));

        $this->assertTrue($this->arr()->exists(new Arrayable(), 'foo'));
        $this->assertTrue($this->arr()->exists(new Arrayable(), 'bar'));
        $this->assertFalse($this->arr()->exists(new Arrayable(), 'qwe'));
        $this->assertFalse($this->arr()->exists(new Arrayable(), 'rty'));
    }

    public function testWrap()
    {
        $this->assertEquals(['data'], $this->arr()->wrap('data'));
        $this->assertEquals(['data'], $this->arr()->wrap(['data']));
        $this->assertEquals([1], $this->arr()->wrap(1));
    }

    public function testToArray()
    {
        $this->assertEquals(['foo', 'bar'], $this->arr()->toArray(['foo', 'bar']));
        $this->assertEquals(['foo' => 'Foo', 'bar' => 'Bar'], $this->arr()->toArray(['foo' => 'Foo', 'bar' => 'Bar']));
        $this->assertEquals(['foo' => 'Foo', 'bar' => 'Bar'], $this->arr()->toArray((object) ['foo' => 'Foo', 'bar' => 'Bar']));
        $this->assertEquals(['foo'], $this->arr()->toArray('foo'));

        $this->assertEquals(['first' => 'Foo', 'second' => 'Bar'], $this->arr()->toArray(new Bar()));
        $this->assertEquals(['qwerty' => 'Qwerty'], $this->arr()->toArray(new Baz()));
    }

    public function testIsArrayable()
    {
        $this->assertTrue($this->arr()->isArrayable([]));
        $this->assertTrue($this->arr()->isArrayable(['foo']));
        $this->assertTrue($this->arr()->isArrayable(new Arrayable()));
    }

    public function testAddUnique()
    {
        $array   = ['foo'];
        $values1 = ['foo', 'bar', 'baz'];
        $values2 = 'foobar';

        $expected = ['foo', 'bar', 'baz', 'foobar'];

        $array = $this->arr()->addUnique($array, $values1);
        $array = $this->arr()->addUnique($array, $values2);

        $this->assertSame($expected, $array);
    }

    public function testGetKeyIfExist()
    {
        $this->assertEquals('foo', $this->arr()->getKey(['foo' => 'bar'], 'foo'));
        $this->assertEquals('foo', $this->arr()->getKey(['foo' => 'bar'], 'foo', 'bar'));
        $this->assertEquals('baz', $this->arr()->getKey(['foo' => 'bar'], 'bar', 'baz'));

        $this->assertNull($this->arr()->get(['foo' => 'bar'], 'bar'));
    }

    public function testMap()
    {
        $source = [
            'foo' => 11,
            'bar' => 22,
            'baz' => 33,
        ];

        $expected = [
            'foo' => 'Foo_22',
            'bar' => 'Bar_44',
            'baz' => 'Baz_66',
        ];

        $this->assertSame($expected, $this->arr()->map($source, static function ($value, $key) {
            return Str::studly($key) . '_' . ($value * 2);
        }));
    }

    protected function arr(): Arr
    {
        return new Arr();
    }
}
