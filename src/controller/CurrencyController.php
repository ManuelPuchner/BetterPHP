<?php

namespace controller;

use betterphp\utils\Controller;
use model\Currency;

require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';

class CurrencyController extends Controller
{

    public function addCurrency(Currency $currency): array
    {
        pg_insert($this->getConnection(), 'currency', array(
            'name' => $currency->getName(),
            'code' => $currency->getCode()
        ));

        return $this->getCurrencies();
    }

    public function getCurrencies(): array {
        $currencies = array();
        $result = pg_query($this->getConnection(), 'SELECT * FROM currency');
        while ($row = pg_fetch_assoc($result)) {
            $currencies[] = new Currency($row['id'], $row['name'], $row['code']);
        }
        return $currencies;
    }
}