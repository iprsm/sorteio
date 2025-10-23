// script.js (versão atualizada para PagBank, mantendo fluxo de popups e contador)

function iniciarContador() {
  const destino = new Date("Dec 24, 2025 20:00:00").getTime();
  
  // Adiciona uma função para executar a atualização
  function updateTimer() {
    const agora = new Date().getTime();
    const distancia = destino - agora;
    
    const timerEl = document.getElementById("timer");
    if (!timerEl) return; // Para se o elemento não existir

    if (distancia <= 0) {
      timerEl.innerHTML = "🎉 O sorteio já começou!";
      clearInterval(intervalo); // Para o intervalo
      return;
    }
    const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
    const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
    const segundos = Math.floor((distancia % (1000 * 60)) / 1000);
    
    // Atualiza os spans individuais
    if (document.getElementById("dias")) document.getElementById("dias").textContent = String(dias).padStart(2, '0');
    if (document.getElementById("horas")) document.getElementById("horas").textContent = String(horas).padStart(2, '0');
    if (document.getElementById("minutos")) document.getElementById("minutos").textContent = String(minutos).padStart(2, '0');
    if (document.getElementById("segundos")) document.getElementById("segundos").textContent = String(segundos).padStart(2, '0');
  }
  
  updateTimer(); // Executa imediatamente para evitar o "00" inicial
  const intervalo = setInterval(updateTimer, 1000); // Inicia o loop
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
  const nomeInput = document.getElementById("nome");
  const telefoneInput = document.getElementById("telefone");

  // Restaura dados do localStorage se existentes
  if(nomeInput) nomeInput.value = localStorage.getItem("compradorNome") || '';
  if(telefoneInput) telefoneInput.value = localStorage.getItem("compradorTelefone") || '';


  if (cancelarBtn) {
    cancelarBtn.addEventListener("click", () => {
      popup.style.display = "none";
    });
  }

  if (confirmarBtn) {
    confirmarBtn.addEventListener("click", () => {
      const nome = nomeInput.value.trim();
      // Formata o telefone enquanto digita (apenas números)
      const telefone = telefoneInput.value.trim().replace(/\D/g, ''); 
      
      if (!nome || telefone.length < 10) { // Validação básica
        alert("Por favor, preencha seu nome e telefone (com DDD).");
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
      aceitarTermosBtn.textContent = "Gerando pagamento PagBank...";

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
          alert("Erro ao criar pagamento: " + data.error);
          aceitarTermosBtn.disabled = false;
          aceitarTermosBtn.textContent = "Prosseguir";
          return;
        }

        // ===== LÓGICA ATUALIZADA PARA PAGBANK =====
        
        // O PHP do PagBank (criar_pagamento.php) retorna 'init_point' (URL do checkout) 
        // e 'order_id' (que é a nossa 'external_reference'/'reference_id' interna)
        const init_point = data.init_point;
        
        // O script antigo esperava 'preference_id' para passar na URL de polling.
        // Vamos usar o 'order_id' do PagBank no lugar, pois o 'verificar_pref.php'
        // já está configurado para buscar em 'external_reference' (que é o 'order_id').
        const preference_id = data.order_id; // Renomeando a variável para reusar a lógica.

        if (init_point && preference_id) {
          // Mantém a lógica original do usuário:
          // 1. Abre o checkout (PagBank) em uma nova aba.
          window.open(init_point, "_blank");
          
          // 2. Navega a aba atual para a página de aguardo, passando
          //    o 'order_id' (renomeado para 'preference_id') como 'pref'.
          //    O 'pagamento_aguardando.html' vai usar 'pref' para consultar 'verificar_pref.php'.
          window.location.href = `pagamento_aguardando.html?pref=${encodeURIComponent(preference_id)}`;
          
        } else if (init_point) {
          // Fallback (caso o 'order_id' não venha), redireciona a página atual.
          // Este também é um fluxo válido (e até mais limpo).
          window.location.href = init_point;
          
        } else {
          alert("Resposta inválida do servidor de pagamento.");
          aceitarTermosBtn.disabled = false;
          aceitarTermosBtn.textContent = "Prosseguir";
        }
        // ===== FIM DA ATUALIZAÇÃO =====

      } catch (err) {
        console.error(err);
        alert("Erro ao conectar com o servidor de pagamento.");
        aceitarTermosBtn.disabled = false;
        aceitarTermosBtn.textContent = "Prosseguir";
      }
    });
  }
});
