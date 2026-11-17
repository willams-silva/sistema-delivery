<?php
/**
 * Identidade visual compartilhada dos relatorios PDF do SaaS.
 * A logo e enviada pelo painel para sistema/img/logo.jpg.
 * Retorna data URI local, sem dependencia de acesso HTTP do Dompdf.
 */
if (!function_exists('sasRelatorioLogo')) {
    function sasRelatorioLogo()
    {
        $arquivo = __DIR__ . '/../../img/logo.jpg';
        if (!is_file($arquivo) || !is_readable($arquivo)) {
            return '';
        }
        $dados = @file_get_contents($arquivo);
        if ($dados === false || $dados === '') {
            return '';
        }
        $info = @getimagesize($arquivo);
        $mime = isset($info['mime']) ? $info['mime'] : '';
        if (!in_array($mime, array('image/jpeg', 'image/png', 'image/gif'), true)) {
            return '';
        }
        return 'data:' . $mime . ';base64,' . base64_encode($dados);
    }
}
if (!function_exists('sasRelatorioCss')) {
    function sasRelatorioCss()
    {
        return '.report-head{border-bottom:3px solid #ffc400;border-collapse:collapse;width:100%}' .
            '.report-title{color:#061b3a;font-weight:bold}' .
            '.report-table thead{display:table-header-group}' .
            '.report-table tbody tr{page-break-inside:avoid}' .
            '#footer .page:after{content:counter(page)}';
    }
}
