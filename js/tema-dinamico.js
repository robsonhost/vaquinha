/**
 * Sistema de Tema Dinâmico para Vaquinha Online
 * Carrega configurações do servidor e aplica automaticamente
 */

class TemaDinamico {
    constructor() {
        this.config = null;
        this.tema = null;
        this.init();
    }

    async init() {
        try {
            await this.carregarConfiguracoes();
            this.aplicarTema();
            this.aplicarConfiguracoes();
        } catch (error) {
            console.error('Erro ao carregar tema dinâmico:', error);
            this.aplicarTemaPadrao();
        }
    }

    async carregarConfiguracoes() {
        const response = await fetch('admin/tema_dinamico.php');
        if (!response.ok) {
            throw new Error('Erro ao carregar configurações');
        }
        
        const data = await response.json();
        this.config = data.configuracoes;
        this.tema = data.tema;
        
        // Salvar no localStorage para cache
        localStorage.setItem('vaquinha_config', JSON.stringify(data));
        localStorage.setItem('vaquinha_config_timestamp', data.timestamp);
    }

    aplicarTema() {
        if (!this.tema) return;

        const root = document.documentElement;
        
        // Aplicar cores do tema
        root.style.setProperty('--cor-primaria', this.tema.cor_primaria);
        root.style.setProperty('--cor-secundaria', this.tema.cor_secundaria);
        root.style.setProperty('--cor-terciaria', this.tema.cor_terciaria);
        
        // Aplicar gradientes
        root.style.setProperty('--gradiente-primario', `linear-gradient(135deg, ${this.tema.cor_primaria}, ${this.tema.cor_secundaria})`);
        root.style.setProperty('--gradiente-secundario', `linear-gradient(45deg, ${this.tema.cor_secundaria}, ${this.tema.cor_primaria})`);
        
        // Adicionar classe ao body para identificação
        document.body.classList.add('tema-aplicado');
        document.body.setAttribute('data-tema', this.tema.nome.toLowerCase().replace(/\s+/g, '-'));
        
        console.log(`Tema "${this.tema.nome}" aplicado com sucesso`);
    }

    aplicarConfiguracoes() {
        if (!this.config) return;

        // Atualizar título da página
        if (this.config.nome_site) {
            document.title = this.config.nome_site;
        }

        // Atualizar meta description
        const metaDesc = document.querySelector('meta[name="description"]');
        if (metaDesc && this.config.descricao_site) {
            metaDesc.setAttribute('content', this.config.descricao_site);
        }

        // Aplicar modo manutenção se ativo
        if (this.config.manutencao) {
            this.aplicarModoManutencao();
        }

        // Atualizar logo se disponível
        if (this.config.logo) {
            this.atualizarLogo();
        }

        // Atualizar informações de contato
        this.atualizarInformacoesContato();
    }

    aplicarModoManutencao() {
        // Criar overlay de manutenção
        const overlay = document.createElement('div');
        overlay.id = 'manutencao-overlay';
        overlay.innerHTML = `
            <div class="manutencao-content">
                <i class="fas fa-tools fa-3x mb-3"></i>
                <h2>Site em Manutenção</h2>
                <p>Estamos trabalhando para melhorar sua experiência. Volte em breve!</p>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        `;
        
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        `;
        
        document.body.appendChild(overlay);
    }

    atualizarLogo() {
        const logoElements = document.querySelectorAll('.navbar-brand img, .logo-img');
        logoElements.forEach(img => {
            img.src = this.config.logo;
            img.alt = this.config.nome_site;
        });
    }

    atualizarInformacoesContato() {
        // Atualizar WhatsApp
        if (this.config.whatsapp) {
            const whatsappLinks = document.querySelectorAll('[data-contact="whatsapp"]');
            whatsappLinks.forEach(link => {
                link.href = `https://wa.me/${this.config.whatsapp.replace(/\D/g, '')}`;
                link.textContent = this.config.whatsapp;
            });
        }

        // Atualizar email
        if (this.config.email) {
            const emailLinks = document.querySelectorAll('[data-contact="email"]');
            emailLinks.forEach(link => {
                link.href = `mailto:${this.config.email}`;
                link.textContent = this.config.email;
            });
        }

        // Atualizar "Quem Somos"
        if (this.config.quem_somos) {
            const quemSomosElements = document.querySelectorAll('[data-content="quem-somos"]');
            quemSomosElements.forEach(element => {
                element.textContent = this.config.quem_somos;
            });
        }
    }

    aplicarTemaPadrao() {
        const temaPadrao = {
            cor_primaria: '#782F9B',
            cor_secundaria: '#65A300',
            cor_terciaria: '#F7F7F7'
        };

        const root = document.documentElement;
        root.style.setProperty('--cor-primaria', temaPadrao.cor_primaria);
        root.style.setProperty('--cor-secundaria', temaPadrao.cor_secundaria);
        root.style.setProperty('--cor-terciaria', temaPadrao.cor_terciaria);
        
        console.log('Tema padrão aplicado');
    }

    // Método para recarregar configurações
    async recarregar() {
        localStorage.removeItem('vaquinha_config');
        localStorage.removeItem('vaquinha_config_timestamp');
        await this.init();
    }

    // Método para obter configuração específica
    getConfig(chave) {
        return this.config ? this.config[chave] : null;
    }

    // Método para obter cor específica
    getCor(tipo) {
        return this.tema ? this.tema[`cor_${tipo}`] : null;
    }
}

// Função utilitária para determinar cor de contraste
function getContrastColor(hexcolor) {
    const r = parseInt(hexcolor.substr(1,2), 16);
    const g = parseInt(hexcolor.substr(3,2), 16);
    const b = parseInt(hexcolor.substr(5,2), 16);
    const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return (yiq >= 128) ? '#000000' : '#ffffff';
}

// Função para aplicar cores de contraste automaticamente
function aplicarCoresContraste() {
    const tema = window.temaDinamico;
    if (!tema || !tema.tema) return;

    const corPrimaria = tema.getCor('primaria');
    const corSecundaria = tema.getCor('secundaria');
    
    if (corPrimaria) {
        const contrastePrimaria = getContrastColor(corPrimaria);
        document.documentElement.style.setProperty('--texto-primario', contrastePrimaria);
    }
    
    if (corSecundaria) {
        const contrasteSecundaria = getContrastColor(corSecundaria);
        document.documentElement.style.setProperty('--texto-secundario', contrasteSecundaria);
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Verificar cache primeiro
    const cachedConfig = localStorage.getItem('vaquinha_config');
    const cachedTimestamp = localStorage.getItem('vaquinha_config_timestamp');
    const cacheAge = Date.now() - (cachedTimestamp * 1000);
    
    // Cache válido por 5 minutos
    if (cachedConfig && cacheAge < 300000) {
        try {
            const data = JSON.parse(cachedConfig);
            window.temaDinamico = new TemaDinamico();
            window.temaDinamico.config = data.configuracoes;
            window.temaDinamico.tema = data.tema;
            window.temaDinamico.aplicarTema();
            window.temaDinamico.aplicarConfiguracoes();
            aplicarCoresContraste();
        } catch (error) {
            console.error('Erro ao carregar cache:', error);
            window.temaDinamico = new TemaDinamico();
        }
    } else {
        window.temaDinamico = new TemaDinamico();
    }
});

// Expor para uso global
window.TemaDinamico = TemaDinamico;
window.getContrastColor = getContrastColor; 