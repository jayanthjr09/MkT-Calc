<?php

namespace App\Service;

use App\Entity\DataSet;

class MktCalculator
{
    private const ACTIVATION_ENERGY = 83.144; //83.144 for the Activation Energy. kJ/mol
    private const GAS_CONSTANT = 8.314; //J/mol

    public function calculate(DataSet $dataSet): float
    {
        $readings = $dataSet->getTemperatureReadings();
        $n = count($readings);
        if ($n === 0) {
            return 0;
        }

        $sum = 0;
        foreach ($readings as $reading) {
            $temperatureKelvin = $reading->getTemperature() + 273.15; // converted to Kelvin by adding 273.15.
            $sum += exp(-self::ACTIVATION_ENERGY * 1000 / (self::GAS_CONSTANT * $temperatureKelvin));
        }

        $mkt = -self::ACTIVATION_ENERGY * 1000 / (self::GAS_CONSTANT * log($sum / $n));
        return $mkt - 273.15; // Convert back to Celsius - 273.15.
    }
}