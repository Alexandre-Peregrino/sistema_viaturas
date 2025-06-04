<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<h1 align="center">🚔 Sistema de Gestão de Viaturas</h1>

<p align="center">
  Sistema web desenvolvido com Laravel para controle e gerenciamento de viaturas de uma OPM (Organização Policial Militar).
</p>

<p align="center">
  <a href="https://github.com/Alexandre-Peregrino/sistema_viaturas/actions"><img src="https://github.com/Alexandre-Peregrino/sistema_viaturas/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Laravel"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"></a>
</p>

---

## 📋 Sobre o Projeto

Este sistema tem como objetivo registrar, editar e consultar dados de viaturas policiais de acordo com a unidade da qual pertencem, com funcionalidades específicas para dois perfis:

- **Admin:** gerenciamento completo de usuários e viaturas.
- **P4:** acesso restrito às viaturas da própria unidade.

---

## 🚀 Tecnologias Utilizadas

- Laravel 10
- PHP 8.3.11
- PostgreSQL
- Bootstrap 5
- pgAdmin
- Nginx (ambiente local)

---

## ⚙️ Instalação Local

### Pré-requisitos

- PHP >= 8.1
- Composer
- PostgreSQL
- Node.js e npm

### Passos

```bash
git clone https://github.com/Alexandre-Peregrino/sistema_viaturas.git
cd sistema_viaturas

composer install
cp .env.example .env
php artisan key:generate

# Configure o banco no .env
php artisan migrate

npm install && npm run dev

php artisan serve
