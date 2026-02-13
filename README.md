Aqui está o conteúdo do seu README.md pronto para copiar e colar. Ele utiliza formatação avançada para destacar o profissionalismo do seu sistema.

Markdown
# 🛍️ Sistema PDV - Moda Mais Barata


![Status](https://img.shields.io/badge/Status-Ativo-ff69b4)
![PHP](https://img.shields.io/badge/PHP-5.6%20%7C%207.x-blue)
![License](https://img.shields.io/badge/License-Proprietário-lightgrey)
video demostrativo:
https://www.youtube.com/watch?v=dk8Geeddn_Q

O **Moda Mais Barata** é um sistema de Ponto de Venda (PDV) desenvolvido para otimizar o fluxo de caixa, controle de vendas e emissão de comprovantes. Focado em simplicidade e eficiência, o sistema atende tanto vendas presenciais quanto operações online.

---

## 🚀 Funcionalidades

### 🔐 Gestão de Acessos e Permissões
O sistema possui uma lógica de hierarquia inteligente baseada no banco de dados:
* **Perfil Administrador (Raquel)**: Acesso global. Visualiza todos os caixas cadastrados e possui acesso exclusivo ao módulo de **Relatórios**.
* **Perfil Operador**: Acesso restrito. Visualiza apenas o caixa vinculado ao seu perfil (`caixa_permitido`).

### 💰 Operação de Caixa
* **Abertura de Turno**: Registro de valor inicial (troco) com verificação de senha de segurança.
* **Vendas Online**: Atalho rápido para processamento de pedidos externos.
* **Fechamento**: Controle de status (Aberto/Fechado) atualizado em tempo real.

### 🖨️ Módulo de Impressão
* **Cupom de Venda**: Geração de cupom térmico (80mm) com detalhamento de itens, quantidades e formas de pagamento.
* **Automação**: Fechamento automático da janela de impressão após a conclusão da tarefa para agilizar o atendimento.
* **Taxas de Cartão**: Cálculo automático de taxas fixas para pagamentos em crédito/débito no comprovante.

---

## 🛠️ Tecnologias Utilizadas

* **Backend**: PHP (utilizando PDO para conexão segura com banco de dados).
* **Frontend**: Bootstrap 5, CSS3 Customizado e JavaScript.
* **Banco de Dados**: MySQL (MyISAM) com criptografia de senhas.

---

## 📂 Estrutura de Arquivos Principais

| Arquivo | Função |
| :--- | :--- |
| `abrir_caixa.php` | Interface principal de gestão e seleção de caixas. |
| `imprimir_cupom_10.php` | Layout e lógica de impressão de tickets de venda. |
| `auth.php` | Sistema de autenticação e proteção de rotas. |
| `config.php` | Arquivo de conexão com o banco de dados. |

---

## ⚙️ Instalação e Configuração

1.  **Requisitos**: Servidor Apache com PHP 5.6 ou superior e MySQL.
2.  **Banco de Dados**: Importe o esquema fornecido no arquivo `usuarios.sql`.
3.  **Configuração**: Ajuste os dados de conexão em `config.php`.
4.  **Acesso**:
    * **Admin**: Login `Raquel` (Acesso total).
    * **Operadores**: Aline, Bianca, Katia (Acesso por caixa).

---

> [!IMPORTANT]
> **Endereço da Loja**: Avenida Afonso Pena, 749, Centro - Belo Horizonte/MG.  
> **Política de Troca**: 7 dias mediante apresentação do cupom.
