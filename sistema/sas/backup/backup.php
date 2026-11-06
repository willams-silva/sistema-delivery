<?php 
require_once("../../conexao.php");
require_once("../verificar.php");

$arquivo = fopen('backup_'.date('d-m-Y').'.sql','wt'); 
        $tables = $pdo->query('SHOW TABLES');
        
        foreach ($tables as $table) {
            $sql = '-- TABLE: '.$table[0].PHP_EOL; 
            $create = $pdo->query('SHOW CREATE TABLE `'.$table[0].'`')->fetch(); 
            $sql.=$create['Create Table'].';'.PHP_EOL;
            fwrite($arquivo, $sql); 
            $linhas = $pdo->query('SELECT * FROM `'.$table[0].'`'); 
            $linhas->setFetchMode(PDO::FETCH_ASSOC); 

            foreach ($linhas as $linha) {
                $linha = array_map(array($pdo,'quote'),$linha); 
                $sql = 'INSERT INTO `'.$table[0].'` (`'.implode('`, `',array_keys($linha)).'`) VALUES ('.utf8_decode(implode(', ',$linha)).');'. PHP_EOL;
                fwrite($arquivo, $sql); 
            }

            $sql = PHP_EOL; 
            $resultado = fwrite($arquivo, $sql); 
            flush();          
        }

        if($resultado !== false){
            $nome_arquivo = 'backup_'.date('d-m-Y').'.sql';
            $tamanho_arquivo = filesize($nome_arquivo);
            $tamanho_formatado = $tamanho_arquivo > 1024 * 1024 ? 
                round($tamanho_arquivo / (1024 * 1024), 2) . ' MB' : 
                round($tamanho_arquivo / 1024, 2) . ' KB';
            
            echo "<style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 0;
                    min-height: 100vh;
                    font-family: Arial, sans-serif;
                }
                .notificacao-backup {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: linear-gradient(135deg, #28a745, #20c997);
                    color: white;
                    padding: 30px;
                    border-radius: 15px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                    text-align: center;
                    font-family: Arial, sans-serif;
                    z-index: 9999;
                    min-width: 400px;
                    animation: aparecerBackup 0.5s ease-out;
                }
                @keyframes aparecerBackup {
                    from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                }
                .notificacao-backup h2 {
                    margin: 0 0 20px 0;
                    font-size: 24px;
                    font-weight: bold;
                }
                .notificacao-backup .info {
                    background: rgba(255,255,255,0.2);
                    padding: 15px;
                    border-radius: 10px;
                    margin: 10px 0;
                    text-align: left;
                }
                .notificacao-backup .info strong {
                    color: #fff;
                }
                .notificacao-backup .btn {
                    background: rgba(255,255,255,0.2);
                    border: 2px solid rgba(255,255,255,0.3);
                    color: white;
                    padding: 12px 25px;
                    border-radius: 25px;
                    cursor: pointer;
                    font-size: 16px;
                    margin-top: 20px;
                    transition: all 0.3s ease;
                }
                .notificacao-backup .btn:hover {
                    background: rgba(255,255,255,0.3);
                    border-color: rgba(255,255,255,0.5);
                }
            </style>";
            
            echo "<div class='notificacao-backup'>
                <h2>✅ Backup Concluído!</h2>
                <div class='info'>
                    <strong>📁 Arquivo:</strong> $nome_arquivo<br>
                    <strong>💾 Tamanho:</strong> $tamanho_formatado<br>
                    <strong>⏰ Data:</strong> " . date('d/m/Y H:i:s') . "
                </div>
                <button class='btn' onclick='window.location=\"../index.php\"'>Ok</button>
            </div>";
            
            echo "<script>
                setTimeout(function() {
                    window.location='../index.php';
                }, 5000);
            </script>";
        }else{
            echo "<style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 0;
                    min-height: 100vh;
                    font-family: Arial, sans-serif;
                }
                .notificacao-erro {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: linear-gradient(135deg, #dc3545, #c82333);
                    color: white;
                    padding: 30px;
                    border-radius: 15px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                    text-align: center;
                    font-family: Arial, sans-serif;
                    z-index: 9999;
                    min-width: 400px;
                    animation: aparecerErro 0.5s ease-out;
                }
                @keyframes aparecerErro {
                    from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                }
                .notificacao-erro h2 {
                    margin: 0 0 20px 0;
                    font-size: 24px;
                    font-weight: bold;
                }
                .notificacao-erro .btn {
                    background: rgba(255,255,255,0.2);
                    border: 2px solid rgba(255,255,255,0.3);
                    color: white;
                    padding: 12px 25px;
                    border-radius: 25px;
                    cursor: pointer;
                    font-size: 16px;
                    margin-top: 20px;
                    transition: all 0.3s ease;
                }
                .notificacao-erro .btn:hover {
                    background: rgba(255,255,255,0.3);
                    border-color: rgba(255,255,255,0.5);
                }
            </style>";
            
            echo "<div class='notificacao-erro'>
                <h2>❌ Erro no Backup</h2>
                <p>Falha ao gerar backup!<br>Verifique as permissões de escrita no diretório.</p>
                <button class='btn' onclick='window.location=\"../index.php\"'>Voltar ao Sistema</button>
            </div>";
            
            echo "<script>
                setTimeout(function() {
                    window.location='../index.php';
                }, 5000);
            </script>";
        }
        fclose($arquivo); 
 
?>

