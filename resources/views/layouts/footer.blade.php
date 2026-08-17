<footer class="footer-custom" role="contentinfo">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="footer-brand">
                    <i class="bi bi-plug" aria-hidden="true"></i> SM Componentes
                </div>
                <p class="footer-text mt-3">
                    Sua loja de componentes eletrônicos desde 2020. Qualidade e confiança em cada produto.
                </p>
                <div class="footer-social">
                    <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i>
                    </a>
                    <a href="https://instagram.com/smcomponentes" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <i class="bi bi-instagram" aria-hidden="true"></i>
                    </a>
                    <a href="#" aria-label="Facebook">
                        <i class="bi bi-facebook" aria-hidden="true"></i>
                    </a>
                    <a href="#" aria-label="YouTube">
                        <i class="bi bi-youtube" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <h5>Links Rápidos</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ route('produtos.index') }}" class="footer-link">Todos os Produtos</a></li>
                    <li><a href="#" class="footer-link">Sobre Nós</a></li>
                    <li><a href="#" class="footer-link">Política de Privacidade</a></li>
                    <li><a href="#" class="footer-link">Termos de Uso</a></li>
                    <li><a href="#" class="footer-link">Central de Ajuda</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contato</h5>
                <ul class="list-unstyled footer-text">
                    <li class="mb-2">
                        <i class="bi bi-envelope text-primary" aria-hidden="true"></i> 
                        <a href="mailto:contato@smcomponentes.com" class="footer-link">contato@smcomponentes.com</a>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-telephone text-primary" aria-hidden="true"></i> 
                        <a href="tel:+5511999999999" class="footer-link">(11) 99999-9999</a>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-whatsapp text-success" aria-hidden="true"></i> 
                        <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer" class="footer-link">(11) 98888-8888</a>
                    </li>
                    <li>
                        <i class="bi bi-geo-alt text-primary" aria-hidden="true"></i> 
                        <span>São Paulo, SP - Brasil</span>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="footer-bottom text-center">
            &copy; {{ date('Y') }} SM Componentes. Todos os direitos reservados.
            <span class="d-block d-sm-inline mt-1 mt-sm-0">
                Desenvolvido com <span class="heart" aria-hidden="true">❤</span> para você
            </span>
        </div>
    </div>
</footer>