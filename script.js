document.addEventListener("DOMContentLoaded", () => {
  let cotaSelecionada = {};

  // 🔗 Substitua depois pelos links reais do Mercado Pago
  const linksMercadoPago = {
    10: "https://mpago.la/13uiCtK",
    20: "https://mpago.la/2LCEREu",
    30: "https://mpago.la/2sEFA1C",
    70: "https://mpago.la/1Jze6VV"
  };

  window.comprarCota = function (qtd, valor) {
    cotaSelecionada = { qtd, valor };
    document.getElementById("popup").style.display = "flex";
  };

  const popup = document.getElementById("popup");
  const confirmarBtn = document.getElementById("confirmarBtn");
  const cancelarBtn = document.getElementById("cancelarBtn");

  cancelarBtn.addEventListener("click", () => {
    popup.style.display = "none";
  });

  confirmarBtn.addEventListener("click", () => {
    const nome = document.getElementById("nome").value.trim();
    const telefone = document.getElementById("telefone").value.trim();

    if (!nome || !telefone) {
      alert("Por favor, preencha todos os campos.");
      return;
    }

    // Salva dados localmente para usar na página de retorno
    localStorage.setItem("compradorNome", nome);
    localStorage.setItem("compradorTelefone", telefone);
    localStorage.setItem("cotaQtd", cotaSelecionada.qtd);
    localStorage.setItem("cotaValor", cotaSelecionada.valor);

    const link = linksMercadoPago[cotaSelecionada.qtd];
    if (!link) {
      alert("Link de pagamento não encontrado.");
      return;
    }

    // Redireciona para o link do Mercado Pago
    window.location.href = link;
  });
});

