# API de Cadastro de Clientes com Validação de CEP

## 📌 Descrição do Projeto

**Backend (API em Laravel)** para cadastro de clientes, com funcionalidades de listagem, criação, edição e exclusão, além de validação automática de endereço via **CEP**.

## ✅ Arquitetura

- A aplicação é executada **totalmente em containers Docker**.
- O Laravel é executado em um container próprio (`laravel-app`).
- O banco de dados MySQL roda em um container separado (`mysql`).

---

## 🚀 Tecnologias Utilizadas

- PHP ^7.3 ou ^8.0  
- Laravel 8.x  
- MySQL 8.x  
- [BrasilAPI](https://brasilapi.com.br) – validação de endereço via CEP  
- [LaravelLegends/pt-br-validator](https://github.com/LaravelLegends/pt-br-validator) – validação de CPF

---

## 📡 Rotas da API

- `GET    /api/customers` – Listar clientes  
- `POST   /api/customers` – Criar cliente  
- `GET    /api/customers/{id}` – Buscar cliente específico  
- `PUT    /api/customers/{id}` – Atualizar cliente  
- `DELETE /api/customers/{id}` – Deletar cliente (soft delete)

---

## 🛠️ Requisitos

- Docker 20.10+  
- Docker Compose v1.29+ ou Docker Compose V2  

---

## ⚙️ Instalação

```bash
# 1. Clone o repositório
git clone https://github.com/seuusuario/seurepositorio.git
cd seurepositorio

# 2. Copie o arquivo .env
cp .env.example .env

# 3. Suba os containers da aplicação e do banco
docker-compose up --build -d

# 4. Instale as dependências do Laravel dentro do container
docker exec -it laravel-app composer install

# 5. Gere a chave da aplicação
docker exec -it laravel-app php artisan key:generate

# 6. Configure o .env (se necessário)
# Exemplo de variáveis de conexão com o banco:
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=api_customer
DB_USERNAME=admin
DB_PASSWORD=admin

# 7. Rode as migrations e seeders
docker exec -it laravel-app php artisan migrate --seed
