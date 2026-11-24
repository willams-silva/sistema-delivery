<?php
$tabela = 'videos';
require_once("../../../conexao.php");

// Consulta para obter os vídeos tutoriais
$query = $pdo->query("SELECT * from $tabela order by ordem asc, id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
?>

<!-- Container principal com classes responsivas -->
<div class="container-fluid px-2 px-md-4">
    <div class="row">
        <div class="col-12">
            <!-- Card para conter a tabela -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0" style="color:#63351e">
                        <i class="fas fa-video me-2"></i>Tutoriais em Vídeo
                    </h5>
                </div>
                <div class="card-body px-2 px-md-3">
                    <?php if ($linhas > 0): ?>
                        <!-- Tabela responsiva simplificada -->
                        <div class="table-responsive">
                            <table class="table border-bottom w-100" id="tabela">
                                <thead class="table-light"> 
                                    <tr> 
                                        <th>Título do Tutorial</th>
                                    </tr> 
                                </thead> 
                                <tbody>
                                    <?php foreach ($res as $row): ?>
                                        <tr class="tutorial-row" 
                                            style="cursor: pointer;" tabindex="0" role="button" onkeydown="if(event.key==='Enter' || event.key===' '){event.preventDefault();this.click();}" 
                                            data-link="<?php echo htmlspecialchars((string)$row['link'], ENT_QUOTES, 'UTF-8'); ?>" 
                                            data-title="<?php echo htmlspecialchars((string)$row['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                            onclick="visualizarVideo(this.dataset.link, this.dataset.title)">
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="tutorial-icon-wrapper me-3">
                                                        <i class="fas fa-play-circle tutorial-icon"></i>
                                                    </div>
                                                    <span class="tutorial-title"><?php echo htmlspecialchars((string)$row['titulo'], ENT_QUOTES, 'UTF-8'); ?><span class="bc-watch">Assistir tutorial</span></span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <!-- Mensagem quando não há registros -->
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>Não há tutoriais cadastrados no momento.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
html body #listar #tabela tbody td:last-child a.btn{display:inline-flex!important;align-items:center;justify-content:center;width:38px;height:38px;padding:0!important;margin:3px;border:1px solid currentColor!important;border-radius:9px!important;color:var(--bc-action,#795038)!important;background:var(--bc-bg,#fff0df)!important;transition:background .18s,color .18s,transform .18s}
html body #listar #tabela tbody td:last-child a.btn.ocultar{display:none!important}
html body #listar #tabela tbody td:last-child a.btn.btn-info-light,html body #listar #tabela tbody td:last-child a.btn.btn-info{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child a.btn.btn-danger-light{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child a.btn.btn-success-light{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar #tabela tbody td:last-child a.btn.btn-dark-light{--bc-action:#41434a;--bc-bg:#e9eaed}
html body #listar #tabela tbody td:last-child a.btn i{color:inherit!important;font-size:16px}
html body #listar #tabela tbody td:last-child a.btn:hover{background:var(--bc-action,#795038)!important;color:#fff!important;transform:translateY(-2px)}
html body #listar #tabela tbody td:last-child a.btn:focus-visible{outline:2px solid #914a29;outline-offset:2px}
html body #listar.bc-tutorial-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-tutorial-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-tutorial-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-tutorial-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-tutorial-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-tutorial-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-tutorial-cards #tabela thead{display:none!important}
html body #listar.bc-tutorial-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-tutorial-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-tutorial-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-tutorial-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-tutorial-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-tutorial-cards #tabela tbody tr:hover,html body #listar.bc-tutorial-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-tutorial-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-tutorial-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-tutorial-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-tutorial-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-tutorial-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-tutorial-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
html body #listar.bc-tutorial-cards #tabela tbody td:last-child::before{content:none!important}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela thead th{background:#f4e9df!important;color:#63351e!important}
html body #listar #tabela .bc-no-attachment{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #e5d4c6;border-radius:8px;background:#f8efe6;color:#795038;font-size:12px}
html body #listar #tabela tbody td:last-child a.btn i{color:inherit!important}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child a.btn-primary{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child a.btn-danger{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child a.btn-success{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar.bc-tutorial-cards #tabela tbody tr,html body #listar.bc-tutorial-cards #tabela tbody td{overflow:visible!important}
html body #listar.bc-tutorial-cards #tabela tbody td .badge{width:auto!important;padding:6px 10px}
html body #listar #tabela tbody td:last-child a.btn-info:not(.btn-info-light){--bc-action:#795038;--bc-bg:#fff0df}
html body #listar.bc-tutorial-cards #tabela tbody td:last-child{border:0!important;text-align:left;margin:0}
html body #listar .tutorial-icon-wrapper{background:#f4e9df!important;color:#914a29!important;flex-shrink:0}
html body #listar .tutorial-icon{color:#914a29!important}
html body #listar .tutorial-title{color:#4e2d20!important;font-weight:600}
html body #listar.bc-tutorial-cards .tutorial-row:hover{transform:none!important;background:#fff!important}
html body #listar.bc-tutorial-cards .tutorial-row:hover .tutorial-icon-wrapper{background:#f4e9df!important}
html body #listar.bc-tutorial-cards .tutorial-row td>div{flex-direction:column;align-items:flex-start!important;gap:12px}
html body #listar.bc-tutorial-cards .tutorial-icon-wrapper{width:100%;height:90px;border-radius:10px}
html body #listar.bc-tutorial-cards .tutorial-icon{font-size:32px}
html body #listar .bc-watch{display:block;font-size:12px;color:#914a29;margin-top:6px}
html body #listar #tabela_filter input{border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#4e2d20!important}
html body #modalVideo .modal-header{background:#914a29;color:#fff;border:0}
html body #modalVideo .modal-title{color:#fff}
</style>
<div id="bc-tutorial-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-tutorial-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.tutorialView=mode;root.classList.toggle('bc-tutorial-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.tutorialView||'cards');
})();
</script>
<!-- Modal para visualizar vídeo -->
<div class="modal fade video-modal" id="modalVideo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="bc-tutorial-video-title">Tutorial</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <!-- Conteúdo do vídeo -->
            <div class="video-container">
                <div class="ratio ratio-16x9">
                    <iframe id="videoFrame" src="" allowfullscreen></iframe>
                </div>
                
                <!-- Overlay de carregamento -->
                <div class="video-loading-overlay" id="videoLoadingOverlay">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos adicionais para responsividade, interatividade e aparência profissional -->
<style>
    /* Estilo para as linhas clicáveis */
    .tutorial-row {
        transition: all 0.2s ease;
    }
    
    .tutorial-row:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transform: translateX(3px);
    }
    
    .tutorial-row:active {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    /* Estilo para o ícone do tutorial */
    .tutorial-icon-wrapper {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: rgba(13, 110, 253, 0.1);
        transition: all 0.2s ease;
    }
    
    .tutorial-row:hover .tutorial-icon-wrapper {
        background-color: rgba(13, 110, 253, 0.2);
    }
    
    .tutorial-icon {
        font-size: 18px;
        color: #0d6efd;
    }
    
    /* Estilo para o título do tutorial */
    .tutorial-title {
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .tutorial-row:hover .tutorial-title {
        color: #0d6efd;
    }
    
    /* Estilos para o modal de vídeo */
    .video-modal .modal-content {
        border: none;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        background-color: #000;
        padding: 0;
    }
    
    .video-modal .modal-dialog {
        max-width: 800px;
        margin: 1.75rem auto;
    }
    
    .video-container {
        position: relative;
        background-color: #000;
    }
    
    .video-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 10;
        transition: opacity 0.3s ease;
    }
    
    /* Animação para o modal */
    .modal.fade .modal-dialog {
        transform: scale(0.95);
        opacity: 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }
    
    .modal.show .modal-dialog {
        transform: scale(1);
        opacity: 1;
    }
    
    /* Melhorias para DataTables em dispositivos móveis */
    @media (max-width: 767.98px) {
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            text-align: left !important;
            margin-bottom: 0.75rem !important;
            width: 100% !important;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            width: calc(100% - 70px) !important;
            margin-left: 5px !important;
            display: inline-block !important;
        }
        
        .dataTables_wrapper .dataTables_filter label {
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
        }
        
        .dataTables_wrapper .dataTables_filter label::before {
            content: "Buscar: ";
            white-space: nowrap;
        }
        
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            width: 100% !important;
            text-align: center !important;
            margin-top: 0.5rem !important;
        }
        
        .dataTables_wrapper .dataTables_paginate {
            margin-bottom: 0.5rem !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.5rem !important;
        }
        
        .video-modal .modal-dialog {
            margin: 0.5rem;
        }
        
        /* Ajuste para o campo de busca */
        div.dataTables_wrapper div.dataTables_filter input {
            width: calc(100% - 60px) !important;
            margin-left: 5px !important;
        }
    }
    
    /* Ajustes para telas muito pequenas */
    @media (max-width: 575.98px) {
        /* Melhorar espaçamento em telas pequenas */
        .card-body {
            padding: 0.75rem;
        }
        
        .tutorial-icon-wrapper {
            width: 32px;
            height: 32px;
        }
        
        .tutorial-icon {
            font-size: 16px;
        }
        
        /* Ajustes específicos para o DataTables em telas muito pequenas */
        .dataTables_wrapper .dataTables_length select {
            width: 80px !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.2rem 0.4rem !important;
            font-size: 0.875rem !important;
        }
    }
    
    /* Estilos adicionais para o DataTables */
    .dataTables_wrapper {
        width: 100%;
    }
    
    .dataTables_filter {
        margin-bottom: 1rem;
    }
    
    .dataTables_filter input {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .dataTables_filter input:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>

<script>
    // Inicialização do DataTables com configurações responsivas
    $(document).ready(function() {
        if ($('#tabela').length) $('#tabela').DataTable({
            "language": {
                "url": '//cdn.datatables.net/plug-ins/1.13.2/i18n/pt-BR.json'
            },
            "ordering": false,
            "stateSave": true,
            "responsive": false,
            "pageLength": 10,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
            // Configuração personalizada para melhor responsividade
            "dom": '<"row mb-3"<"col-12 col-md-6"l><"col-12 col-md-6"f>>rt<"row"<"col-12 col-md-6"i><"col-12 col-md-6"p>>',
            // Ajustar para dispositivos móveis
            "initComplete": function(settings, json) {
                if (window.innerWidth < 768) {
                    this.api().page.len(5).draw();
                    
                    // Ajustes adicionais para o campo de busca em dispositivos móveis
                    $('.dataTables_filter input').attr('placeholder', 'Digite para buscar...');
                    
                    // Remover o texto "Search:" do label
                    $('.dataTables_filter label').contents().filter(function() {
                        return this.nodeType === 3; // Nó de texto
                    }).remove();
                }
            }
        });
        
        // Ajustar DataTables quando a janela for redimensionada
        $(window).off('resize.bcTutorial').on('resize.bcTutorial',function() {
            if (!$('#tabela').length || !$.fn.dataTable.isDataTable('#tabela')) return;
            if (window.innerWidth < 768) {
                $('#tabela').DataTable().page.len(5).draw();
                
                // Ajustes adicionais para o campo de busca em dispositivos móveis
                $('.dataTables_filter input').attr('placeholder', 'Digite para buscar...');
                
                // Remover o texto "Search:" do label
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3; // Nó de texto
                }).remove();
            } else {
                $('#tabela').DataTable().page.len(10).draw();
            }
        });
        
        // Evento para quando o iframe terminar de carregar
        $('#videoFrame').on('load', function() {
            // Esconder o overlay de carregamento
            $('#videoLoadingOverlay').fadeOut(300);
        });
        
        // Ajuste inicial para o campo de busca
        $('.dataTables_filter input').attr('placeholder', 'Digite para buscar...');
        
        // Remover o texto "Search:" do label
        $('.dataTables_filter label').contents().filter(function() {
            return this.nodeType === 3; // Nó de texto
        }).remove();
    });

    // Função para visualizar vídeo
    function visualizarVideo(link, titulo) {
        // Mostrar o overlay de carregamento
        $('#videoLoadingOverlay').show();
        
        // Tenta extrair o ID do vídeo do YouTube
        let videoId = '';
        let embedUrl = link;
        
        if (link.includes('youtube.com') || link.includes('youtu.be')) {
            // Para links do YouTube
            if (link.includes('youtube.com/watch?v=')) {
                videoId = link.split('v=')[1];
                const ampersandPosition = videoId.indexOf('&');
                if (ampersandPosition !== -1) {
                    videoId = videoId.substring(0, ampersandPosition);
                }
                embedUrl = `https://www.youtube.com/embed/${videoId}?rel=0&showinfo=0&autoplay=1`;
            } else if (link.includes('youtu.be/')) {
                videoId = link.split('youtu.be/')[1];
                embedUrl = `https://www.youtube.com/embed/${videoId}?rel=0&showinfo=0&autoplay=1`;
            }
        } else if (link.includes('vimeo.com')) {
            // Para links do Vimeo
            const vimeoId = link.split('vimeo.com/')[1];
            if (vimeoId) {
                embedUrl = `https://player.vimeo.com/video/${vimeoId}?autoplay=1`;
            }
        }
        
        // Definir a URL do iframe
        $('#bc-tutorial-video-title').text(titulo);
        $('#videoFrame').attr('src', embedUrl);
        
        // Mostrar o modal
        $('#modalVideo').modal('show');
    }

    // Limpar o iframe quando o modal de vídeo for fechado
    $('#modalVideo').on('hidden.bs.modal', function () {
        $('#videoFrame').attr('src', '');
    });
    
    // Detectar orientação do dispositivo e ajustar a visualização
    window.addEventListener('orientationchange', function() {
        setTimeout(function() {
            if ($('#tabela').length && $.fn.dataTable.isDataTable('#tabela')) $('#tabela').DataTable().columns.adjust();
            
            // Reajustar o campo de busca após a mudança de orientação
            $('.dataTables_filter input').attr('placeholder', 'Digite para buscar...');
            
            // Remover o texto "Search:" do label
            $('.dataTables_filter label').contents().filter(function() {
                return this.nodeType === 3; // Nó de texto
            }).remove();
        }, 500);
    });
</script>

