<?php
// Carregar tema ativo
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}

// Verificar se é admin
$isAdmin = isset($_SESSION['admin_id']);
?>

<!-- Seletor de Tema Flutuante -->
<div class="tema-selector" id="temaSelector">
    <button class="tema-toggle" onclick="toggleTemaSelector()" aria-label="Alterar tema do site">
        <i class="fas fa-palette"></i>
    </button>
    
    <div class="tema-panel" id="temaPanel">
        <div class="tema-header">
            <h6><i class="fas fa-palette"></i> Personalizar Tema</h6>
            <button class="tema-close" onclick="toggleTemaSelector()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="tema-content">
            <!-- Temas Pré-definidos -->
            <div class="tema-section">
                <h6>Temas Prontos</h6>
                <div class="tema-opcoes">
                    <div class="tema-opcao" data-primaria="#782F9B" data-secundaria="#65A300" data-terciaria="#F7F7F7" onclick="aplicarTema(this)">
                        <div class="tema-preview" style="background: linear-gradient(135deg, #782F9B, #65A300)"></div>
                        <span>Roxo Verde</span>
                    </div>
                    
                    <div class="tema-opcao" data-primaria="#2563EB" data-secundaria="#059669" data-terciaria="#F0F9FF" onclick="aplicarTema(this)">
                        <div class="tema-preview" style="background: linear-gradient(135deg, #2563EB, #059669)"></div>
                        <span>Azul Verde</span>
                    </div>
                    
                    <div class="tema-opcao" data-primaria="#DC2626" data-secundaria="#EA580C" data-terciaria="#FEF2F2" onclick="aplicarTema(this)">
                        <div class="tema-preview" style="background: linear-gradient(135deg, #DC2626, #EA580C)"></div>
                        <span>Vermelho Laranja</span>
                    </div>
                    
                    <div class="tema-opcao" data-primaria="#7C3AED" data-secundaria="#EC4899" data-terciaria="#F3E8FF" onclick="aplicarTema(this)">
                        <div class="tema-preview" style="background: linear-gradient(135deg, #7C3AED, #EC4899)"></div>
                        <span>Roxo Rosa</span>
                    </div>
                    
                    <div class="tema-opcao" data-primaria="#059669" data-secundaria="#0D9488" data-terciaria="#F0FDF4" onclick="aplicarTema(this)">
                        <div class="tema-preview" style="background: linear-gradient(135deg, #059669, #0D9488)"></div>
                        <span>Verde Azul</span>
                    </div>
                    
                    <div class="tema-opcao" data-primaria="#F59E0B" data-secundaria="#EF4444" data-terciaria="#FFFBEB" onclick="aplicarTema(this)">
                        <div class="tema-preview" style="background: linear-gradient(135deg, #F59E0B, #EF4444)"></div>
                        <span>Amarelo Vermelho</span>
                    </div>
                </div>
            </div>
            
            <!-- Personalização Avançada -->
            <div class="tema-section">
                <h6>Personalizar Cores</h6>
                <div class="tema-inputs">
                    <div class="tema-input-group">
                        <label>Cor Primária</label>
                        <input type="color" id="corPrimaria" value="<?= $tema['cor_primaria'] ?>" onchange="aplicarCorPersonalizada()">
                    </div>
                    <div class="tema-input-group">
                        <label>Cor Secundária</label>
                        <input type="color" id="corSecundaria" value="<?= $tema['cor_secundaria'] ?>" onchange="aplicarCorPersonalizada()">
                    </div>
                </div>
            </div>
            
            <!-- Ações -->
            <div class="tema-acoes">
                <button class="btn btn-sm btn-outline-secondary" onclick="resetarTema()">
                    <i class="fas fa-undo"></i> Padrão
                </button>
                <?php if ($isAdmin): ?>
                <button class="btn btn-sm btn-primary" onclick="salvarTemaPadrao()">
                    <i class="fas fa-save"></i> Salvar como Padrão
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.tema-selector {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 9998;
}

.tema-toggle {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    animation: tema-bounce 2s infinite;
}

.tema-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(0,0,0,0.3);
}

@keyframes tema-bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-5px);
    }
    60% {
        transform: translateY(-3px);
    }
}

.tema-panel {
    position: absolute;
    bottom: 60px;
    left: 0;
    width: 280px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.tema-panel.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.tema-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
    color: white;
    border-radius: 15px 15px 0 0;
}

.tema-header h6 {
    margin: 0;
    font-weight: 600;
}

.tema-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 1rem;
}

.tema-content {
    padding: 1rem;
}

.tema-section {
    margin-bottom: 1.5rem;
}

.tema-section h6 {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #495057;
}

.tema-opcoes {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}

.tema-opcao {
    text-align: center;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.tema-opcao:hover {
    background: #f8f9fa;
    border-color: var(--cor-primaria);
}

.tema-opcao.ativo {
    border-color: var(--cor-primaria);
    background: rgba(120, 47, 155, 0.1);
}

.tema-preview {
    width: 100%;
    height: 30px;
    border-radius: 6px;
    margin-bottom: 0.25rem;
}

.tema-opcao span {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
}

.tema-inputs {
    display: flex;
    gap: 0.5rem;
}

.tema-input-group {
    flex: 1;
}

.tema-input-group label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    display: block;
}

.tema-input-group input[type="color"] {
    width: 100%;
    height: 40px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tema-input-group input[type="color"]:hover {
    border-color: var(--cor-primaria);
}

.tema-acoes {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.tema-acoes .btn {
    flex: 1;
    font-size: 0.8rem;
    padding: 0.5rem;
}

@media (max-width: 768px) {
    .tema-selector {
        bottom: 15px;
        left: 15px;
    }
    
    .tema-panel {
        width: 260px;
        left: -10px;
    }
    
    .tema-opcoes {
        grid-template-columns: 1fr;
    }
    
    .tema-inputs {
        flex-direction: column;
    }
}

/* Reduzir animação para usuários que preferem menos movimento */
@media (prefers-reduced-motion: reduce) {
    .tema-toggle {
        animation: none;
    }
    
    .tema-panel {
        transition: none;
    }
}
</style>

<script>
// Temas pré-definidos
const temasPredefinidos = {
    'roxo-verde': { primaria: '#782F9B', secundaria: '#65A300', terciaria: '#F7F7F7' },
    'azul-verde': { primaria: '#2563EB', secundaria: '#059669', terciaria: '#F0F9FF' },
    'vermelho-laranja': { primaria: '#DC2626', secundaria: '#EA580C', terciaria: '#FEF2F2' },
    'roxo-rosa': { primaria: '#7C3AED', secundaria: '#EC4899', terciaria: '#F3E8FF' },
    'verde-azul': { primaria: '#059669', secundaria: '#0D9488', terciaria: '#F0FDF4' },
    'amarelo-vermelho': { primaria: '#F59E0B', secundaria: '#EF4444', terciaria: '#FFFBEB' }
};

// Toggle do painel
function toggleTemaSelector() {
    const panel = document.getElementById('temaPanel');
    panel.classList.toggle('show');
}

// Aplicar tema pré-definido
function aplicarTema(elemento) {
    const primaria = elemento.dataset.primaria;
    const secundaria = elemento.dataset.secundaria;
    const terciaria = elemento.dataset.terciaria;
    
    // Remover classe ativo de todos
    document.querySelectorAll('.tema-opcao').forEach(el => el.classList.remove('ativo'));
    
    // Adicionar classe ativo ao selecionado
    elemento.classList.add('ativo');
    
    // Aplicar cores
    aplicarCores(primaria, secundaria, terciaria);
    
    // Salvar no localStorage
    salvarTemaUsuario(primaria, secundaria, terciaria);
}

// Aplicar cor personalizada
function aplicarCorPersonalizada() {
    const primaria = document.getElementById('corPrimaria').value;
    const secundaria = document.getElementById('corSecundaria').value;
    const terciaria = '#F7F7F7'; // Cor terciária padrão
    
    aplicarCores(primaria, secundaria, terciaria);
    salvarTemaUsuario(primaria, secundaria, terciaria);
}

// Aplicar cores ao CSS
function aplicarCores(primaria, secundaria, terciaria) {
    document.documentElement.style.setProperty('--cor-primaria', primaria);
    document.documentElement.style.setProperty('--cor-secundaria', secundaria);
    document.documentElement.style.setProperty('--cor-terciaria', terciaria);
    
    // Atualizar cores do seletor
    document.querySelector('.tema-toggle').style.background = `linear-gradient(135deg, ${primaria}, ${secundaria})`;
    document.querySelector('.tema-header').style.background = `linear-gradient(135deg, ${primaria}, ${secundaria})`;
}

// Salvar tema do usuário
function salvarTemaUsuario(primaria, secundaria, terciaria) {
    const tema = { primaria, secundaria, terciaria, timestamp: Date.now() };
    localStorage.setItem('temaUsuario', JSON.stringify(tema));
}

// Carregar tema do usuário
function carregarTemaUsuario() {
    const temaSalvo = localStorage.getItem('temaUsuario');
    if (temaSalvo) {
        const tema = JSON.parse(temaSalvo);
        aplicarCores(tema.primaria, tema.secundaria, tema.terciaria);
        
        // Atualizar inputs
        document.getElementById('corPrimaria').value = tema.primaria;
        document.getElementById('corSecundaria').value = tema.secundaria;
        
        // Marcar tema ativo
        document.querySelectorAll('.tema-opcao').forEach(el => {
            if (el.dataset.primaria === tema.primaria && el.dataset.secundaria === tema.secundaria) {
                el.classList.add('ativo');
            }
        });
    }
}

// Resetar para tema padrão
function resetarTema() {
    const temaPadrao = temasPredefinidos['roxo-verde'];
    aplicarCores(temaPadrao.primaria, temaPadrao.secundaria, temaPadrao.terciaria);
    localStorage.removeItem('temaUsuario');
    
    // Resetar inputs
    document.getElementById('corPrimaria').value = temaPadrao.primaria;
    document.getElementById('corSecundaria').value = temaPadrao.secundaria;
    
    // Marcar tema padrão como ativo
    document.querySelectorAll('.tema-opcao').forEach(el => el.classList.remove('ativo'));
    document.querySelector('.tema-opcao[data-primaria="' + temaPadrao.primaria + '"]').classList.add('ativo');
}

// Salvar como tema padrão (apenas admin)
function salvarTemaPadrao() {
    const primaria = document.getElementById('corPrimaria').value;
    const secundaria = document.getElementById('corSecundaria').value;
    const terciaria = '#F7F7F7';
    
    // Enviar para o servidor
    fetch('api/salvar_tema_padrao.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cor_primaria: primaria,
            cor_secundaria: secundaria,
            cor_terciaria: terciaria
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tema salvo como padrão com sucesso!');
        } else {
            alert('Erro ao salvar tema: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar tema padrão');
    });
}

// Carregar tema ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    carregarTemaUsuario();
});

// Fechar painel ao clicar fora
document.addEventListener('click', function(event) {
    const selector = document.getElementById('temaSelector');
    const panel = document.getElementById('temaPanel');
    
    if (!selector.contains(event.target) && panel.classList.contains('show')) {
        panel.classList.remove('show');
    }
});
</script> 