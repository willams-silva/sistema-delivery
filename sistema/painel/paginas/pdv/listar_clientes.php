<?php
@session_start();
if (empty($_SESSION['empresa'])) { http_response_code(401); exit('Sessão expirada'); }
?>
<div class="w-100" id="pdv-cliente-identificacao">
  <select id="cliente" class="form-select" aria-label="Identificação do cliente" onchange="trocarCliente()">
    <option value="0" selected>Consumidor Final</option>
    <option value="cpf">Buscar por CPF</option>
  </select>
  <div id="pdv-busca-cpf" class="mt-2" style="display:none">
    <div class="d-flex gap-2">
      <input id="pdv-cpf" type="text" inputmode="numeric" maxlength="14" autocomplete="off" class="form-control" placeholder="Digite o CPF do cliente" aria-label="CPF do cliente">
      <button type="button" class="btn btn-primary" onclick="buscarClienteCpf()">Buscar</button>
    </div>
    <small id="pdv-cliente-resultado" class="d-block mt-2" role="status" aria-live="polite"></small>
  </div>
</div>
<script>
function trocarCliente() {
  var busca = document.getElementById('cliente').value === 'cpf';
  document.getElementById('pdv-busca-cpf').style.display = busca ? 'block' : 'none';
  document.getElementById('cliente_input').value = busca ? '' : '0';
  document.getElementById('pdv-cliente-resultado').textContent = '';
  if (busca) document.getElementById('pdv-cpf').focus();
}
function buscarClienteCpf() {
  var cpf = (document.getElementById('pdv-cpf').value || '').replace(/\D/g, '');
  var resultado = document.getElementById('pdv-cliente-resultado');
  document.getElementById('cliente_input').value = '';
  if (cpf.length !== 11) {
    resultado.textContent = 'Digite um CPF com 11 números.';
    return;
  }
  resultado.textContent = 'Consultando cliente...';
  $.ajax({
    url: 'paginas/pdv/buscar_cliente_cpf.php',
    type: 'POST', dataType: 'json', data: {cpf: cpf}
  }).done(function(d) {
    if (d.encontrado) {
      document.getElementById('cliente_input').value = String(d.id);
      resultado.textContent = 'Cliente identificado: ' + d.nome;
    } else {
      resultado.textContent = d.mensagem || 'CPF não cadastrado. Use o botão + para cadastrar.';
    }
  }).fail(function() {
    resultado.textContent = 'Não foi possível consultar o CPF. Tente novamente.';
  });
}
$(document).off('keydown.pdvCpf', '#pdv-cpf').on('keydown.pdvCpf', '#pdv-cpf', function(e) {
  if (e.key === 'Enter') { e.preventDefault(); buscarClienteCpf(); }
});
</script>
