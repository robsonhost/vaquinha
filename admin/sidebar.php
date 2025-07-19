<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">15 Notificações</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-envelope mr-2"></i> 4 novas mensagens
                    <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-users mr-2"></i> 8 novos usuários
                    <span class="float-right text-muted text-sm">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-file mr-2"></i> 3 novos relatórios
                    <span class="float-right text-muted text-sm">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="notificacoes.php" class="dropdown-item dropdown-footer">Ver Todas as Notificações</a>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin']) ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="perfil.php" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> Meu Perfil
                </a>
                <a href="configuracoes.php" class="dropdown-item">
                    <i class="fas fa-cog mr-2"></i> Configurações
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt mr-2"></i> Sair
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
        <img src="../images/logo-default.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Vaquinha Admin</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Campanhas -->
                <li class="nav-item has-treeview <?= in_array(basename($_SERVER['PHP_SELF']), ['campanhas.php', 'nova_campanha.php', 'editar_campanha.php', 'campanhas_pendentes.php']) ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-hand-holding-heart"></i>
                        <p>
                            Campanhas
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="campanhas.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'campanhas.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Todas as Campanhas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="campanhas_pendentes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'campanhas_pendentes.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Campanhas Pendentes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="nova_campanha.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'nova_campanha.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nova Campanha</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="categorias.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'categorias.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Categorias</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Doações -->
                <li class="nav-item has-treeview <?= in_array(basename($_SERVER['PHP_SELF']), ['doacoes.php', 'editar_doacao.php']) ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-heart"></i>
                        <p>
                            Doações
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="doacoes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'doacoes.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Todas as Doações</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="doacoes.php?status=pendente" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pendentes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="doacoes.php?status=confirmada" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Confirmadas</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Usuários -->
                <li class="nav-item has-treeview <?= in_array(basename($_SERVER['PHP_SELF']), ['usuarios.php', 'cadastro_usuario.php', 'editar_usuario.php']) ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Usuários
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="usuarios.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Todos os Usuários</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="cadastro_usuario.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'cadastro_usuario.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Novo Usuário</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Notificações -->
                <li class="nav-item">
                    <a href="notificacoes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'notificacoes.php' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>
                            Notificações
                            <span class="badge badge-warning right">15</span>
                        </p>
                    </a>
                </li>

                <!-- Relatórios -->
                <li class="nav-item has-treeview <?= in_array(basename($_SERVER['PHP_SELF']), ['relatorios.php', 'exportar_relatorio.php']) ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Relatórios
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="relatorios.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'relatorios.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Relatórios Gerais</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="relatorios.php?tipo=campanhas" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Relatório Campanhas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="relatorios.php?tipo=doacoes" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Relatório Doações</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Configurações -->
                <li class="nav-item has-treeview <?= in_array(basename($_SERVER['PHP_SELF']), ['configuracoes.php', 'temas.php', 'textos.php']) ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Configurações
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="configuracoes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'configuracoes.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Configurações Gerais</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="temas.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'temas.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Temas e Cores</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="textos.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'textos.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Textos do Site</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logo.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'logo.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Logo e Imagens</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="integracoes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'integracoes.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Integrações/API</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Sistema -->
                <li class="nav-item has-treeview <?= in_array(basename($_SERVER['PHP_SELF']), ['logs.php', 'backup.php', 'editar_perfil.php']) ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>
                            Sistema
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="editar_perfil.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'editar_perfil.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Meu Perfil</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="backup.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'backup.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Backup</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logs.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'logs.php' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Logs do Sistema</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Separador -->
                <li class="nav-header">ACESSO RÁPIDO</li>

                <!-- Links rápidos -->
                <li class="nav-item">
                    <a href="../index.php" class="nav-link" target="_blank">
                        <i class="nav-icon fas fa-external-link-alt"></i>
                        <p>Ver Site</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="campanhas_pendentes.php" class="nav-link">
                        <i class="nav-icon fas fa-clock"></i>
                        <p>Campanhas Pendentes</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="doacoes.php?status=pendente" class="nav-link">
                        <i class="nav-icon fas fa-hourglass-half"></i>
                        <p>Doações Pendentes</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside> 