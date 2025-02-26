<?php

use PHPUnit\Framework\TestCase;
use App\Service\MktCalculator;
use App\Entity\DataSet;
use App\Entity\TemperatureReading;
ini_set('memory_limit', '512M'); // Increase memory limit

class MktCalculatorTest extends TestCase
{
    protected $calculator;

    protected function setUp(): void
    {
        $this->calculator = new MktCalculator();
    }

    private function createDataSet(array $temperatures): DataSet
    {
        $readings = array_map(function ($temp) {
            $reading = new TemperatureReading();
            $reading->setTemperature($temp);
            $reading->setTimestamp(new \DateTime());  // Use current timestamp, or set a fixed one
            return $reading;
        }, $temperatures);

        return new DataSet($readings);
    }

    public function testSingleTemperature()
    {
        $tempData = $this->createDataSet([25]);
        $result = $this->calculator->calculate($tempData);
        $this->assertEquals(0, $result);
    }

    public function testMultipleTemperatures()
    {
        $tempData = $this->createDataSet([25, 30, 35, 40]);
        $result = $this->calculator->calculate($tempData);
        $expectedResult = 0;
        $this->assertEquals($expectedResult, round($result));
    }

    public function testNegativeTemperatures()
    {
        $tempData = $this->createDataSet([-10, -5, 0, 5, 10]);
        $result = $this->calculator->calculate($tempData);
        $expectedResult = 0;
        $this->assertEqualsWithDelta($expectedResult, $result, 0.01);
    }

    public function testZeroTemperatures()
    {
        $tempData = $this->createDataSet([0, 0, 0, 0, 0]);
        $result = $this->calculator->calculate($tempData);
        $this->assertEquals(0, $result);
    }

    public function testPerformance()
    {
        $tempData = $this->createDataSet(array_fill(0, 1000000, 25));
        $startTime = microtime(true);
        $result = $this->calculator->calculate($tempData);
        $endTime = microtime(true);
        $this->assertLessThan(1, $endTime - $startTime);
    }
}