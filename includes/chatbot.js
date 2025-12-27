// Abrir e fechar chatbot
document.getElementById("chatbot-toggle").onclick = () =>
  document.getElementById("chatbot").classList.toggle("show");

document.getElementById("chatbot-close").onclick = () =>
  document.getElementById("chatbot").classList.remove("show");

// Elementos
const chatbotInput = document.getElementById("chatbot-input");
const messages = document.getElementById("chatbot-messages");
const sendBtn = document.getElementById("chatbot-send");

// Enviar mensagem
sendBtn.onclick = () => {
  const msg = chatbotInput.value.trim();
  if (!msg) return;

  appendMessage("Você", msg, "user");
  chatbotInput.value = "";

  fetch("includes/chatbot_helper.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "mensagem=" + encodeURIComponent(msg)
  })
  .then(res => res.text())
  .then(resposta => {
    appendMessage("Atendente", resposta, "bot");
    speakText(stripHTML(resposta)); // fala só o texto
  });
};

// Exibir mensagem no chat (com suporte a HTML na resposta)
function appendMessage(sender, text, type) {
  const div = document.createElement("div");
  div.className = "chat-message " + type;

  const senderEl = document.createElement("strong");
  senderEl.textContent = sender + ": ";
  div.appendChild(senderEl);

  const contentEl = document.createElement("span");
  if (type === "bot") {
    contentEl.innerHTML = text; // permite links
  } else {
    contentEl.textContent = text; // texto do usuário, sem HTML
  }
  div.appendChild(contentEl);

  messages.appendChild(div);
  messages.scrollTop = messages.scrollHeight;
}

// Texto para fala
document.getElementById("chatbot-speak").disabled = false;
function speakText(text) {
  const utter = new SpeechSynthesisUtterance(text);
  utter.lang = "pt-BR";
  speechSynthesis.speak(utter);
}

// Remove HTML para leitura por voz
function stripHTML(html) {
  const tmp = document.createElement("div");
  tmp.innerHTML = html;
  return tmp.textContent || tmp.innerText || "";
}


  