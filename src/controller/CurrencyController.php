<?php

namespace controller;

use betterphp\utils\ApiException;
use betterphp\utils\Controller;
use betterphp\utils\HttpErrorCodes;
use Exception;
use model\Currency;

require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';

class CurrencyController extends Controller
{

    public function addCurrency(Currency $currency): array
    {
        $data = $currency->jsonSerialize();
        unset($data['id']);
        $sql = 'INSERT INTO currency (name, code) VALUES (:name, :code)';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($data);
        $currency->setId($this->getConnection()->lastInsertId());

        return $this->getCurrencies();
    }

    public function getCurrencies(): array {
        $sql = 'SELECT * FROM currency';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        $currencies = [];
        while ($row = $stmt->fetch()) {
            $currencies[] = Currency::getFromRow($row);
        }
        return $currencies;
    }

    /**
     * @throws Exception
     */
    public function getById(int $id): Currency
    {
        $sql = 'SELECT * FROM currency WHERE id = :id';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new ApiException(HTTPErrorCodes::HTTP_NOT_FOUND, 'Currency not found');
        }
        return Currency::getFromRow($row);
    }
}