# Agente de impressão Bora Comanda — Windows 10/11 (prévia técnica)

O agente consulta a fila HTTPS e envia cupons ESC/POS RAW à fila de impressão do Windows. **Não está homologado em impressora real.** Exige impressora compatível com ESC/POS RAW e driver instalado. O retorno do spooler **não comprova a saída física do papel**. Não execute em produção sem validar o modelo.

## Preparação do servidor
1. No phpMyAdmin execute `sistema/painel/paginas/pedidos/impressao_schema.sql` (as tabelas de automação anteriores devem existir).
2. Gere **um token aleatório de 64 caracteres hexadecimais** (por exemplo `py -c "import secrets;print(secrets.token_hex(32))"` no Windows).
3. Registre **somente o hash** no banco: `INSERT INTO automacao_impressoras (empresa,token_hash,nome) VALUES (7,SHA2('COLE_TOKEN_AQUI',256),'Caixa 1') ON DUPLICATE KEY UPDATE token_hash=VALUES(token_hash),nome=VALUES(nome);` Troque 7 pelo ID real. Não compartilhe o token.
4. Os pedidos aceitos automaticamente entram na fila quando a opção de impressão estiver habilitada. Não imprime aceites manuais nesta versão.

## Computador Windows
1. Instale Python 3.11+ e o driver da impressora. Instale `py -m pip install pywin32`.
2. Copie `config.exemplo.json` para `config.json` (arquivo **local**, nunca envie ao GitHub). Informe o mesmo token, empresa e nome exato da impressora em Configurações do Windows.
3. Rode `py agente.py`. Consulte `agente.log` em caso de erro.
4. Para criar um executável futuramente: `py -m pip install pyinstaller` e `py -m PyInstaller --onefile --name BoraComandaAgente agente.py`. Coloque `config.json` ao lado do .exe.
5. O agente deve estar aberto no computador e a impressora precisa estar ligada. Inicialização automática e instalador ainda não foram implementados.

## Limites
- A fila usa reserva de 3 minutos e confirmação após o spooler aceitar o documento. Falhas após envio e antes da confirmação podem causar reimpressão; falhas físicas podem ocorrer mesmo após confirmação.
- Não há garantia de entrega exatamente uma vez. Para produção, validar política de reimpressão e testes de falha.
- Não envie credenciais ao GitHub. Use HTTPS e um token exclusivo por estabelecimento.
- Os tempos do pedido continuam configurados no painel Pedidos e executados pelo cron da Hostinger.
