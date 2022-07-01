<?php

namespace App\Model;

use Exception;

class CurrencyConversion {

    private static $URL = "https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/denni_kurz.txt";

    private array $conversionRates;

    public function __construct() {

        $this->conversionRates = array();
        $this->loadConversionRates();

    }

    /**
     * The function retrieves the currency rates and stores them in an array
     * @return void
     */
    private function loadConversionRates() {
        try {
            $pageContent = @file_get_contents(CurrencyConversion::$URL);

            $rows = explode(PHP_EOL, $pageContent);

            for($i = 2; $i < count($rows); $i++) {

                $values = explode('|', $rows[$i]);
                if(count($values) == 5) {
                    $this->conversionRates[$values[3]] = floatval(str_replace(',', '.', $values[4]));
                }
            }

        } catch(Exception $exception) {
            return;
        }
    }

    /**
     * Function to get the exchange rate of a specific currency
     * @param string $currency
     * @return float exchange rate (0 if unspecified)
     */
    public function getConversionRate(string $currency):float {
        if(isset($this->conversionRates[$currency])) {
            return $this->conversionRates[$currency];
        }

        return 0;
    }

    /**
     * Function for converting the amount in CZK to the specified currency
     * @param string $currency
     * @param float $value
     * @return float the result of the conversion
     */
    public function convertToCurrency(string $currency, float $value):float {
        return $value / $this->getConversionRate($currency);
    }

    /**
     * Function for converting an amount in CZK to EUR
     * @param float $value
     * @return float
     */
    public function convertCzkToEur(float $value):float {
        return $this->convertToCurrency('EUR', $value);
    }

}