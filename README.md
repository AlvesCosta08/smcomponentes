<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/Vite-5.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/Status-Produção-green?style=for-the-badge" alt="Status">
  <img src="https://img.shields.io/badge/Versão-2.0.0-blue?style=for-the-badge" alt="Versão">
  <img src="https://img.shields.io/badge/Tests-144%20passed-brightgreen?style=for-the-badge" alt="Tests">
</p>

<h1 align="center">⚡ SM Componentes</h1>

<p align="center">
  <strong>Plataforma completa de e-commerce para venda de componentes eletrônicos</strong><br>
  Desenvolvida com Laravel 13, MySQL e arquitetura limpa (Clean Architecture)
</p>

<p align="center">
  <a href="#-sobre-o-projeto">Sobre</a> •
  <a href="#-tecnologias">Tecnologias</a> •
  <a href="#-arquitetura">Arquitetura</a> •
  <a href="#-funcionalidades">Funcionalidades</a> •
  <a href="#-instalação">Instalação</a> •
  <a href="#-testes">Testes</a> •
  <a href="#-roadmap">Roadmap</a>
</p>

---

## 📋 Sobre o Projeto

**SM Componentes** é uma plataforma de e-commerce robusta desenvolvida em **Laravel 13** para a venda de componentes eletrônicos. O sistema oferece uma experiência completa de compra online, com gerenciamento avançado de produtos, carrinho de compras inteligente e controle granular de permissões para diferentes níveis de usuários.

### 🎯 Objetivos
- Oferecer uma experiência de compra moderna e responsiva
- Gerenciar mais de **1.668+ produtos** com categorias
- Controlar estoque e disponibilidade em tempo real
- Prover segurança com múltiplos níveis de acesso (Admin, Funcionário, Cliente)
- Sistema de backup automatizado
- **Arquitetura limpa e testável** (Clean Architecture)

---

## 🛠️ Tecnologias

### Backend
| Tecnologia | Versão | Finalidade |
|------------|--------|-----------|
| 🚀 **Laravel** | 13.25.0 | Framework PHP |
| 🐘 **PHP** | 8.5.4 | Linguagem de programação |
| 🗄️ **MySQL** | 8.0 | Banco de dados relacional |
| 🐳 **Docker** | - | Containerização |
| 🔐 **Spatie Permission** | 8.x | Controle de permissões |
| 🔑 **Laravel Breeze** | Latest | Autenticação |
| 🧪 **PHPUnit** | 12.5.12 | Testes automatizados |
| 📊 **Xdebug** | 3.5.3 | Code coverage |

### Frontend
| Tecnologia | Versão | Finalidade |
|------------|--------|-----------|
| 🎨 **Bootstrap** | 5.3.3 | Framework CSS |
| ⚡ **Alpine.js** | 3.13 | Interatividade leve |
| 🏗️ **Vite** | 8.x | Build tool moderna |
| 🎯 **Bootstrap Icons** | 1.13 | Ícones |
| 🖌️ **Font Awesome** | 6.5.1 | Ícones adicionais |

---

## 🏗️ Arquitetura

O projeto segue os princípios da **Clean Architecture** com separação clara de responsabilidades:

## 🏗️ Arquitetura

O projeto segue os princípios da **Clean Architecture** com separação clara de responsabilidades:
┌─────────────────────────────────────────────────────────────┐
│ CONTROLLERS (20+) │
│ Camada de Apresentação │
└────────────────────────┬────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────┐
│ SERVICES (12+) │
│ Camada de Lógica de Negócio │
│ StockService | ProductService | OrderService │
│ OrderAdminService | DashboardService | UserService │
│ BannerService | PaymentService | CheckoutService │
│ MargemService | WishlistService | ProductService │
└────────────────────────┬────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────┐
│ REPOSITORIES (5+) │
│ Camada de Acesso a Dados │
│ ProdutoRepository | PedidoRepository | UserRepository │
│ PedidoItemRepository | BaseRepository │
└────────────────────────┬────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────┐
│ MODELS (10+) │
│ Camada de Dados / Eloquent │
│ Produto | Pedido | User | Banner | Categoria │
│ Carrinho | Wishlist | PedidoItem | ProdutoImagem │
└─────────────────────────────────────────────────────────────┘


### 📦 Camadas Implementadas

| Camada | Descrição | Status |
|--------|-----------|--------|
| **DTOs** | Data Transfer Objects | ✅ 8 DTOs |
| **Services** | Lógica de negócio | ✅ 12+ Services |
| **Repositories** | Acesso a dados | ✅ 5+ Repositories |
| **Controllers** | Controle de requisições | ✅ 20+ Controllers |
| **Requests** | Validação de dados | ✅ 9+ Requests |
| **Tests** | Testes automatizados | ✅ 144 Testes |

---

## ✨ Funcionalidades

### ✅ Já Implementadas

#### 🔐 Autenticação e Autorização
- [x] Sistema de login/registro (Laravel Breeze)
- [x] 3 níveis de usuário: **Admin**, **Funcionário**, **Cliente**
- [x] Controle de permissões granular (Spatie Permission)
- [x] Middleware de proteção de rotas
- [x] Dashboard personalizado por perfil

#### 📦 Gestão de Produtos
- [x] Importação em massa via CSV (1.668+ produtos)
- [x] CRUD completo de produtos (Admin)
- [x] Catálogo com paginação moderna
- [x] Busca de produtos com debounce
- [x] Filtro por categorias
- [x] Página de detalhes com galeria
- [x] Controle de estoque e alertas de baixo estoque
- [x] Preços múltiplos (atacado, unitário, promocional)
- [x] Exportação para CSV

#### 🛒 Carrinho de Compras
- [x] Adicionar/remover/atualizar itens
- [x] Contador dinâmico via AJAX
- [x] Cálculo automático de subtotais
- [x] Persistência em sessão

#### 📸 Banners Dinâmicos
- [x] CRUD completo de banners
- [x] Upload de imagens
- [x] Ordenação drag-and-drop
- [x] Agendamento de exibição
- [x] Fallback com banners padrão

#### 📊 Dashboard Admin
- [x] Cards de estatísticas
- [x] Gráfico de status dos pedidos
- [x] Últimos pedidos
- [x] Produtos com estoque crítico (paginação)
- [x] Vendas mensais
- [x] Top clientes
- [x] Ações rápidas com Font Awesome

#### 📦 Gestão de Pedidos
- [x] Listagem com filtros
- [x] Detalhes do pedido
- [x] Alteração de status
- [x] Relatório de vendas
- [x] Exportação CSV

#### 👥 Gestão de Usuários
- [x] CRUD completo
- [x] Ativação/desativação
- [x] Restauração de usuários deletados
- [x] Histórico de pedidos

#### 💾 Backup
- [x] Backup automático do banco de dados
- [x] Backup dos arquivos
- [x] Agendamento de backup

#### 🎨 Interface
- [x] Design totalmente responsivo
- [x] Página inicial com carrossel de banners
- [x] Navegação por categorias
- [x] Produtos em destaque, ofertas e novidades
- [x] Botões flutuantes (WhatsApp e Instagram)
- [x] Animações e transições suaves
- [x] Psicologia das cores aplicada
- [x] Ícones Font Awesome

#### 📚 Documentação
- [x] Documentação da API (24 rotas)
- [x] Helpers globais
- [x] README completo

---

## 🚀 Instalação

### Pré-requisitos
- PHP 8.5 ou superior
- Composer
- Node.js e NPM
- MySQL 8.0
- (Opcional) Docker e Docker Compose

### 📥 Passo a Passo

**1. Clone o repositório**
```bash
git clone https://github.com/seu-usuario/smcomponentes.git
cd smcomponentes

smcomponentes/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── ApiDocs.php          # Comando para documentação
│   ├── Domain/
│   │   ├── Pedidos/
│   │   │   ├── Enums/
│   │   │   │   ├── StatusPagamentoEnum.php
│   │   │   │   └── StatusPedidoEnum.php
│   │   │   └── Services/
│   │   ├── Produtos/
│   │   │   ├── Services/
│   │   │   │   └── PricingCalculator.php
│   │   │   └── ValueObjects/
│   │   │       └── Stock.php
│   │   └── Usuarios/
│   │       └── ValueObjects/
│   │           ├── Cnpj.php
│   │           └── Cpf.php
│   ├── DTOs/
│   │   ├── Requests/
│   │   ├── Responses/
│   │   ├── OrderDTO.php
│   │   ├── ProductDTO.php
│   │   └── WishlistDTO.php
│   ├── Helpers/
│   │   └── helpers.php               # Funções globais
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Api/
│   │   │   ├── Auth/
│   │   │   ├── CarrinhoController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── HomeController.php
│   │   │   ├── PedidoController.php
│   │   │   ├── ProdutoController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── WebhookController.php
│   │   │   └── WishlistController.php
│   │   └── Requests/
│   │       ├── Admin/
│   │       ├── Auth/
│   │       ├── Carrinho/
│   │       ├── Checkout/
│   │       ├── Produto/
│   │       ├── ProdutoRequest.php
│   │       └── ProfileUpdateRequest.php
│   ├── Infrastructure/
│   │   └── Repositories/
│   │       └── EloquentPedidoRepository.php
│   ├── Listeners/
│   │   └── AssignUserRole.php
│   ├── Models/
│   │   ├── Banner.php
│   │   ├── Carrinho.php
│   │   ├── Categoria.php
│   │   ├── Pedido.php
│   │   ├── PedidoItem.php
│   │   ├── Produto.php
│   │   ├── ProdutoImagem.php
│   │   ├── User.php
│   │   ├── Wishlist.php
│   │   └── WishlistItem.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   ├── BaseRepository.php
│   │   ├── PedidoItemRepository.php
│   │   ├── PedidoRepository.php
│   │   ├── ProdutoRepository.php
│   │   └── UserRepository.php
│   └── Services/
│       ├── Contracts/
│       ├── Traits/
│       ├── BannerService.php
│       ├── CheckoutService.php
│       ├── DashboardService.php
│       ├── MargemService.php
│       ├── OrderAdminService.php
│       ├── OrderService.php
│       ├── PaymentService.php
│       ├── ProductService.php
│       ├── StockService.php
│       ├── UserService.php
│       └── WishlistService.php
├── config/
├── database/
│   ├── factories/
│   ├── migrations/              # 29 migrações
│   └── seeders/                 # 9 seeders
├── public/
├── resources/
│   └── views/
│       ├── admin/
│       ├── auth/
│       ├── carrinho/
│       ├── checkout/
│       ├── cliente/
│       ├── components/
│       ├── layouts/
│       ├── produtos/
│       ├── profile/
│       └── wishlist/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── scripts/                     # Scripts de automação
├── storage/
│   └── api-docs/
│       └── api-documentation.html
├── tests/
│   ├── Feature/                 # 14+ testes
│   │   ├── Admin/
│   │   ├── Carrinho/
│   │   ├── Checkout/
│   │   ├── ApiTest.php
│   │   ├── AuthTest.php
│   │   ├── CarrinhoTest.php
│   │   ├── CategoriaControllerTest.php
│   │   ├── CheckoutTest.php
│   │   ├── PedidoControllerTest.php
│   │   ├── PerfilTest.php
│   │   ├── ProdutoControllerTest.php
│   │   ├── SetupTest.php
│   │   └── WishlistTest.php
│   └── Unit/                    # 20+ testes
│       ├── Unit/
│       ├── BannerTest.php
│       ├── CategoriaTest.php
│       ├── PedidoItemTest.php
│       ├── PedidoTest.php
│       ├── ProdutoImagemTest.php
│       ├── ProdutoTest.php
│       ├── UserTest.php
│       ├── WishlistItemTest.php
│       └── WishlistTest.php
├── artisan
├── composer.json
├── phpunit.xml
└── README.md

🗺️ Roadmap
📅 Q3 2026
□ Checkout com integração de pagamento
□ Avaliações e comentários
□ Lista de desejos (wishlist)
□ API RESTful completa (Laravel Sanctum)
📅 Q4 2026
□ Sistema de pedidos completo
□ Área do cliente avançada
□ Relatórios gerenciais
□ Otimização de performance (caching)
📅 2027
□ Aplicativo mobile
□ Integração com marketplaces
□ Sistema de avaliações
□ Programa de fidelidade
🤝 Contribuição
Contribuições são sempre bem-vindas! Para contribuir:

Faça um Fork do projeto

Crie uma Branch para sua Feature (git checkout -b feature/NovaFeature)

Faça commit das suas alterações (git commit -m 'Adiciona NovaFeature')

Faça Push para a Branch (git push origin feature/NovaFeature)

Abra um Pull Request

📊 Status do Projeto
Métrica	Valor
Versão	2.0.0
Status	Produção
Testes	144 ✅
Asserções	278
Cobertura	25%
Services	12+
Repositórios	5+
DTOs	8
Controllers	20+
Models	10
Migrations	29
Seeders	9
Rotas API	24
👨‍💻 Autor
Alves - Desenvolvedor e Arquiteto de Soluções

📄 Licença
Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

