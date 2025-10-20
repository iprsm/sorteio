// script.js (versão Mercado Pago checkout pro)
function iniciarContador() {
  const destino = new Date("Dec 24, 2025 20:00:00").getTime();
  setInterval(() => {
    const agora = new Date().getTime();
    const distancia = destino - agora;
    if (distancia <= 0) {
      document.getElementById("timer").innerHTML = "🎉 O sorteio já começou!";
      return;
    }
    const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
    const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
    const segundos = Math.floor((distancia % (1000 * 60)) / 1000);
    document.getElementById("dias").textContent = dias;
    document.getElementById("horas").textContent = horas;
    document.getElementById("minutos").textContent = minutos;
    document.getElementById("segundos").textContent = segundos;
  }, 1000);
}

function comprarCota(qtd, valor) {
  localStorage.setItem("cotaQtd", qtd);
  localStorage.setItem("cotaValor", valor);
  document.getElementById("popup").style.display = "flex";
}

window.addEventListener("DOMContentLoaded", () => {
  iniciarContador();

  const popup = document.getElementById("popup");
  const confirmarBtn = document.getElementById("confirmarBtn");
  const cancelarBtn = document.getElementById("cancelarBtn");
  const popupTermos = document.getElementById("popup-termos");
  const aceitarTermosBtn = document.getElementById("aceitarTermosBtn");
  const checkbox = document.getElementById("aceito-termos");

  if (cancelarBtn) {
    cancelarBtn.addEventListener("click", () => {
      popup.style.display = "none";
    });
  }

  if (confirmarBtn) {
    confirmarBtn.addEventListener("click", () => {
      const nome = document.getElementById("nome").value.trim();
      const telefone = document.getElementById("telefone").value.trim();
      if (!nome || !telefone) {
        alert("Por favor, preencha seu nome e telefone.");
        return;
      }
      localStorage.setItem("compradorNome", nome);
      localStorage.setItem("compradorTelefone", telefone);
      popup.style.display = "none";
      popupTermos.style.display = "flex";
    });
  }

  if (checkbox) {
    checkbox.addEventListener("change", () => {
      aceitarTermosBtn.disabled = !checkbox.checked;
    });
  }

  if (aceitarTermosBtn) {
    aceitarTermosBtn.addEventListener("click", async () => {
      aceitarTermosBtn.disabled = true;
      aceitarTermosBtn.textContent = "Gerando pagamento...";

      try {
        const nome = localStorage.getItem("compradorNome");
        const telefone = localStorage.getItem("compradorTelefone");
        const qtd = localStorage.getItem("cotaQtd");
        const valor = localStorage.getItem("cotaValor");

        const resp = await fetch("criar_pagamento.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ nome, telefone, qtd, valor })
        });

        const data = await resp.json();

        if (data.error) {
          alert("Erro: " + data.error);
          aceitarTermosBtn.disabled = false;
          aceitarTermosBtn.textContent = "Prosseguir";
          return;
        }

        // redireciona para Mercado Pago Checkout (init_point)
        if (data.init_point) {
          window.location.href = data.init_point;
        } else {
          alert("Resposta inválida do servidor de pagamento.");
          aceitarTermosBtn.disabled = false;
          aceitarTermosBtn.textContent = "Prosseguir";
        }
      } catch (err) {
        console.error(err);
        alert("Erro ao conectar com o servidor de pagamento.");
        aceitarTermosBtn.disabled = false;
        aceitarTermosBtn.textContent = "Prosseguir";
      }
    });
  }
});

