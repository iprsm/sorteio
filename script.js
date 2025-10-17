document.addEventListener("DOMContentLoaded", () => {
  let cotaSelecionada = {};

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

    const mensagem = `Olá! Meu nome é ${nome}, telefone: ${telefone}. Quero participar do sorteio beneficente com a cota de ${cotaSelecionada.qtd} números por R$ ${cotaSelecionada.valor},00.`;
    const link = `https://wa.me/5511979654420?text=${encodeURIComponent(mensagem)}`;

    window.open(link, "_blank");
    popup.style.display = "none";

    document.getElementById("nome").value = "";
    document.getElementById("telefone").value = "";
  });
});

