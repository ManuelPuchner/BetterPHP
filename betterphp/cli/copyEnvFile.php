<?php
$fromPath = dirname(__DIR__) . '/../src/.env';
$toPath = dirname(__DIR__) . '/../dist/.env';
copy($fromPath, $toPath);