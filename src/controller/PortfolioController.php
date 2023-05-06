<?php

namespace controller;

use betterphp\utils\Controller;

class PortfolioController extends Controller
{
    public function getPortfolios(): array
    {
        $sql = 'SELECT * FROM portfolio';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        $portfolio = [];
        while ($row = $stmt->fetch()) {
            $portfolio[] = Portfolio::getFromRow($row);
        }
        return $portfolio;
    }
}