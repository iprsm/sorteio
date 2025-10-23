# Sorteio Beneficente - IPR São Miguel Paulista

Este projeto é uma aplicação web para gerenciar um sorteio beneficente promovido pela IPR São Miguel Paulista. A aplicação permite que os usuários comprem cotas para participar do sorteio e fornece informações sobre os prêmios, regras e detalhes do evento.

## Estrutura do Projeto

- **index.html**: Documento HTML principal da aplicação. Contém a estrutura da página web, incluindo cabeçalhos, seções para o sorteio, prêmios e botões para a compra de bilhetes.
  
- **style.css**: Arquivo de estilos da aplicação. Define o layout, cores, fontes e animações utilizadas em toda a página web.

- **script.js**: Contém funções JavaScript para gerenciar interações do usuário, como iniciar um cronômetro de contagem regressiva, gerenciar a compra de bilhetes de sorteio e lidar com pop-ups para entrada de dados do usuário.

- **criar_pagamento.php**: Responsável por criar uma solicitação de pagamento para a API do PagBank. Manipula dados recebidos do frontend, constrói a solicitação de pagamento e retorna o link de pagamento ou ID da preferência.

- **verificar_pagamento.php**: Verifica o status de um pagamento usando a API do PagBank. Recupera o ID do pagamento da solicitação, consulta a API do PagBank e retorna o status do pagamento em formato JSON.

- **verificar_pref.php**: Recupera o status do sorteio com base em um ID de preferência. Consulta o banco de dados para o status do sorteio e retorna o resultado em formato JSON.

- **notificacao.php**: Manipula notificações da API do PagBank sobre atualizações de status de pagamento. Processa notificações recebidas, atualiza o banco de dados conforme necessário e registra os dados da notificação.

- **config.php**: Contém a configuração de conexão com o banco de dados, incluindo o nome do servidor, nome de usuário, senha e nome do banco de dados.

- **conexao.php**: Estabelece uma conexão com o banco de dados usando variáveis de ambiente ou credenciais de fallback do `config.php`. Garante que a conexão esteja configurada para usar codificação UTF-8.

## Instruções de Configuração

1. **Clone o repositório**: 
   ```bash
   git clone <URL_DO_REPOSITORIO>
   cd sorteio-iprsm
   ```

2. **Configuração do Banco de Dados**: 
   - Edite o arquivo `config.php` para incluir suas credenciais de banco de dados.

3. **Configuração do Servidor**: 
   - Certifique-se de que seu servidor web esteja configurado para executar arquivos PHP e que o banco de dados esteja acessível.

4. **Acesso à Aplicação**: 
   - Abra `index.html` em um navegador para acessar a aplicação.

## Uso

- Os usuários podem visualizar os prêmios disponíveis e comprar cotas para participar do sorteio.
- Após a compra, os usuários receberão um número da sorte e informações sobre o sorteio.

## Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir um problema ou enviar um pull request.

## Licença

Este projeto é de uso livre.