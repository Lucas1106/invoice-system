<?php
declare(strict_types=1);

// MESMO BANCO do site antigo (extraído do seu api/config.php):
const DB_HOST = '10.132.36.3';
const DB_NAME = 'meaclean499ea962_meacleaning';
const DB_USER = 'meaclean499ea962_pwa';
const DB_PASS = 'Lucas963852..@.';
const DB_CHARSET = 'utf8mb4';

// Sessão do projeto novo
const SESSION_NAME    = 'invoice_auth';
const COOKIE_SECURE   = true;   // mantenha true em HTTPS
const COOKIE_SAMESITE = 'Lax';

// Slugs/URLs das empresas (AGORA sem /invoice no caminho)
const COMPANY_MEZ = 'MEA';
const COMPANY_LCR = 'LCR';
const URL_MEZ_HOME = '/MEA/';
const URL_LCR_HOME = '/LCR/';
