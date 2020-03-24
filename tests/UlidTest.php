<?php

namespace Ulid;

use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ulid\Ulid;

class UlidTest extends TestCase
{
    public function testUlidCanBeInstanciated()
    {
        $ulid = new Ulid();
        $this->assertInstanceOf(Ulid::class, $ulid);
    }

    /**
     * @covers Ulid\Ulid::isValidFormat
     */
    public function testIsValidFormat()
    {
        $ulid = new Ulid();
        $result = $ulid->isValidFormat('01E475VQGHNW990PVHXFDT4C6W');
        $this->assertTrue($result);
    }

    /**
     * @covers Ulid\Ulid::isValidFormat
     */
    public function testIsInvalidFormat()
    {
        $ulid = new Ulid();
        $result = $ulid->isValidFormat('1585083964945');
        $this->assertFalse($result);
    }

    /**
     * @covers Ulid\Ulid::getTimeFromUlid
     */
    public function testGetTimeFromUlid()
    {
        $ulid = new Ulid();
        $result = $ulid->getTimeFromUlid('01E475VQGHNW990PVHXFDT4C6W');
        $this->assertEquals('1585083964945', $result);
    }

    /**
     * @covers Ulid\Ulid::getTimeFromUlid
     */
    public function testGetTimeFromUlidInvalid()
    {
        $ulid = new Ulid();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid format ULID string.');
        $ulid->getTimeFromUlid('1585083964945');
    }

    /**
     * @covers Ulid\Ulid::getRandomnessFromString
     */
    public function testGetRandomnessFromString()
    {
        $ulid = new Ulid();
        $result = $ulid->getRandomnessFromString('01E475VQGHNW990PVHXFDT4C6W');
        $this->assertEquals('NW990PVHXFDT4C6W', $result);
    }

    /**
     * @covers Ulid\Ulid::getRandomnessFromString
     */
    public function testGetRandomnessFromStringInvalid()
    {
        $ulid = new Ulid();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid format ULID string.');
        $ulid->getRandomnessFromString('1585083964945');
    }

    /**
     * @covers Ulid\Ulid::isDuplicatedTime
     */
    public function testIsDuplicatedTime()
    {
        $ulid = new Ulid();
        $result = $ulid->isDuplicatedTime(0);
        $this->assertTrue($result);
    }

    /**
     * @covers Ulid\Ulid::isDuplicatedTime
     */
    public function testIsNotDuplicatedTime()
    {
        $ulid = new Ulid();
        $result = $ulid->isDuplicatedTime(1);
        $this->assertFalse($result);
    }

    /**
     * @covers Ulid\Ulid::hasIncrementLastRandChars
     */
    public function testHasNotIncrementLastRandChars()
    {
        $ulid = new Ulid();
        $result = $ulid->hasIncrementLastRandChars(false);
        $this->assertFalse($result);
    }

    /**
     * @covers Ulid\Ulid::hasIncrementLastRandChars
     */
    public function testHasIncrementLastRandChars()
    {
        $ulid = new Ulid();
        $ulid->lastRandChars[15] = 31;
        $result = $ulid->hasIncrementLastRandChars(true);
        $this->assertTrue($result);
    }

    /**
     * @covers Ulid\Ulid::generate
     */
    public function testGenerate()
    {
        $ulid = new Ulid();
        $result = $ulid->generate();
        $this->assertInternalType('string', $result);
        $this->assertEquals(26, strlen($result));
    }

    /**
     * @covers Ulid\Ulid::generate
     */
    public function testGenerateWithTime()
    {
        $ulid = new Ulid();
        $result = $ulid->generate(1585083964945);
        $this->assertInternalType('string', $result);
        $timePart = substr(
            $result,
            0,
            UlidConstants::TIME_LENGTH
        );
        $this->assertEquals('01E475VQGH', $timePart);
    }

    /**
     * @covers Ulid\Ulid::decodeTime
     */
    public function testDecodeTime()
    {
        $ulid = new Ulid();
        $result = $ulid->decodeTime('01E475VQGH');
        $this->assertInternalType('int', $result);
        $this->assertEquals('1585083964945', $result);
    }

    /**
     * @covers Ulid\Ulid::decodeTime
     */
    public function testDecodeTimeInvalid()
    {
        $ulid = new Ulid();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid ULID character: %');
        $ulid->decodeTime('01E%75VQGH');
    }

    /**
     * @covers Ulid\Ulid::decodeTime
     */
    public function testDecodeTimeInvalidTime()
    {
        $ulid = new Ulid();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid ULID string. Timestamp too large');
        $ulid->decodeTime('QS234SAD23RADSWRA3FADQ3RS2');
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
