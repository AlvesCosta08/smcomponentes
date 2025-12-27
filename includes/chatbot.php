<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="includes/chatbot.css">

<!-- Botão de toggle -->
<button id="chatbot-toggle" class="btn btn-primary rounded-circle shadow-lg">
  <i class="fas fa-comments"></i>
</button>

<!-- Container do Chatbot -->
<div id="chatbot" class="d-none">
  <div class="chatbot-header">
    <span>Atendente Virtual</span>
    <button class="close-btn"><i class="fas fa-times"></i></button>
  </div>
  <div class="chatbot-body" id="chatbot-messages">
    <div class="chat-message bot">Olá! Como posso ajudar?</div>
  </div>
  <div class="chatbot-footer">
    <input type="text" id="chatbot-input" placeholder="Digite sua mensagem...">
    <button id="chatbot-send"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>

<script src="includes/chatbot.js" defer></script>



