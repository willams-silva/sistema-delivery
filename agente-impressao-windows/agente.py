"""Bora Comanda - agente Windows 10/11 para impressoras termicas ESC/POS.
Instale: py -m pip install pywin32
Configure config.json local (NAO envie seu token ao GitHub).
Execute: py agente.py
"""
import json, logging, os, sys, time, urllib.parse, urllib.request
from pathlib import Path
import win32print

BASE = Path(__file__).resolve().parent
logging.basicConfig(filename=str(BASE / "agente.log"), level=logging.INFO,
                    format="%(asctime)s %(levelname)s %(message)s")

def api(cfg, acao, **extra):
    data = dict(empresa=cfg["empresa"], token=cfg["token"], acao=acao, **extra)
    url = cfg["url"].rstrip("/") + "/sistema/painel/paginas/pedidos/api-impressao.php"
    if not url.startswith("https://"):
        raise ValueError("Somente HTTPS")
    req = urllib.request.Request(url, urllib.parse.urlencode(data).encode("utf-8"),
                                 headers={"User-Agent":"BoraComanda-Agente/0.1"})
    with urllib.request.urlopen(req, timeout=25) as res:
        return json.load(res)

def clean(s):
    # Evita comandos ESC/POS injetados por campos do pedido.
    return str(s or "").replace("\x1b", "").replace("\x1d", "").replace("\x00", "").strip()

def cupom(pedido, cfg):
    venda = pedido["venda"]
    width = int(cfg.get("colunas", 32))
    linhas = ["BORA COMANDA", "=" * width,
              "PEDIDO: " + clean(venda.get("pedido") or venda["id"]),
              "CLIENTE: " + clean(venda.get("nome_cliente")),
              "DATA: " + clean(venda.get("data")) + " " + clean(venda.get("hora")),
              "ENTREGA: " + clean(venda.get("entrega")), "-" * width]
    for item in pedido["itens"]:
        linhas.append(clean(item.get("quantidade")) + "x " + clean(item.get("nome_produto")))
        if item.get("obs"):
            linhas.append("  OBS: " + clean(item["obs"]))
    linhas += ["-" * width, "TOTAL: R$ " + clean(venda.get("valor")),
              "OBS: " + clean(venda.get("obs")), "=" * width, "", "", ""]
    texto = "\n".join(linhas)
    # CP850 para acentos em muitas termicas; configuravel para modelos diferentes.
    encoding = cfg.get("encoding", "cp850")
    return b"\x1b@" + texto.encode(encoding, errors="replace") + b"\x1dV\x00"

def imprimir(cfg, data):
    impressora = cfg["impressora"]
    handle = win32print.OpenPrinter(impressora)
    try:
        job = win32print.StartDocPrinter(handle, 1, ("Bora Comanda", None, "RAW"))
        try:
            win32print.StartPagePrinter(handle)
            try:
                payload = cupom(data, cfg)
                enviados = win32print.WritePrinter(handle, payload)
                if enviados != len(payload):
                    raise IOError("Impressao incompleta no spooler")
            finally:
                win32print.EndPagePrinter(handle)
        finally:
            win32print.EndDocPrinter(handle)
        return job
    finally:
        win32print.ClosePrinter(handle)

def main():
    cfg = json.loads((BASE / "config.json").read_text(encoding="utf-8"))
    for key in ("url", "empresa", "token", "impressora"):
        if not cfg.get(key):
            raise ValueError("Configure " + key + " em config.json")
    print("Bora Comanda: aguardando cupons (Ctrl+C encerra).")
    while True:
        try:
            response = api(cfg, "proximo")
            pedido = response.get("pedido")
            if pedido:
                id_ = pedido["venda"]["id"]
                try:
                    job = imprimir(cfg, pedido)
                    # Confirma entrega ao spooler Windows, nao impressao fisica.
                    ok = api(cfg, "confirmar", venda_id=id_, reserva=pedido["reserva"])
                    logging.info("Pedido %s enviado ao spooler (job %s), confirmado=%s", id_, job, ok.get("ok"))
                except Exception:
                    logging.exception("Falha ao imprimir pedido %s", id_)
                    api(cfg, "falhou", venda_id=id_, reserva=pedido["reserva"])
        except Exception:
            logging.exception("Erro ao consultar fila")
        time.sleep(max(5, int(cfg.get("intervalo_segundos", 10))))

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        sys.exit(0)
