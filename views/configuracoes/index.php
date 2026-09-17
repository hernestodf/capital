<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/tabs/tabs.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';

$empresa = $empresa ?? [];
$matrix = $matrix ?? [];
$roles = $roles ?? ['administrativo', 'comercial', 'estoquista'];
$roleLabels = [
    'administrativo' => 'Administrativo',
    'comercial' => 'Comercial',
    'estoquista' => 'Estoquista'
];
$roleColors = [
    'administrativo' => 'purple',
    'comercial' => 'green',
    'estoquista' => 'cyan'
];

// Tab Empresa content
$tabEmpresaContent = '
<div class="fg">
  <div class="fl">Nome da Empresa</div>
  <input class="fi" type="text" name="nome" value="' . htmlspecialchars($empresa['nome'] ?? 'SISLOC') . '" placeholder="SISLOC" id="input-nome-empresa"/>
</div>

<div class="col2">
  <div class="fg">
    <div class="fl">Identificador</div>
    <input class="fi" type="text" name="identificador" value="' . htmlspecialchars($empresa['identificador'] ?? 'SISLOC') . '" placeholder="SISLOC"/>
  </div>
  <div class="fg">
    <div class="fl">Tagline</div>
    <input class="fi" type="text" name="tagline" value="' . htmlspecialchars($empresa['tagline'] ?? 'Locação') . '" placeholder="Locação"/>
  </div>
</div>

<div class="fg">
  <div class="fl">Subtítulo</div>
  <input class="fi" type="text" name="subtitulo" value="' . htmlspecialchars($empresa['subtitulo'] ?? '') . '" placeholder=""/>
</div>

<div class="col2">
  <div class="fg">
    <div class="fl">CNPJ</div>
    <input class="fi" type="text" name="cnpj" value="' . htmlspecialchars($empresa['cnpj'] ?? '') . '" placeholder="00.000.000/0000-00" id="input-cnpj"/>
  </div>
  <div class="fg">
    <div class="fl">Inscrição Estadual</div>
    <input class="fi" type="text" name="inscricao_estadual" value="' . htmlspecialchars($empresa['inscricao_estadual'] ?? '') . '" placeholder=""/>
  </div>
</div>

<div class="fg">
  <div class="fl">Endereço</div>
  <input class="fi" type="text" name="endereco" value="' . htmlspecialchars($empresa['endereco'] ?? '') . '" placeholder="Rua, Avenida, etc" id="input-endereco"/>
</div>

<div class="col3">
  <div class="fg">
    <div class="fl">Cidade</div>
    <input class="fi" type="text" name="cidade" value="' . htmlspecialchars($empresa['cidade'] ?? '') . '" placeholder="" id="input-cidade"/>
  </div>
  <div class="fg">
    <div class="fl">Estado</div>
    <input class="fi" type="text" name="estado" value="' . htmlspecialchars($empresa['estado'] ?? '') . '" placeholder="SP" maxlength="2" id="input-estado"/>
  </div>
  <div class="fg">
    <div class="fl">CEP</div>
    <input class="fi" type="text" name="cep" value="' . htmlspecialchars($empresa['cep'] ?? '') . '" placeholder="00000-000" id="input-cep"/>
  </div>
</div>

<div class="col2">
  <div class="fg">
    <div class="fl">Telefone</div>
    <input class="fi" type="text" name="telefone" value="' . htmlspecialchars($empresa['telefone'] ?? '') . '" placeholder="(00) 0000-0000" id="input-telefone"/>
  </div>
  <div class="fg">
    <div class="fl">WhatsApp</div>
    <input class="fi" type="text" name="whatsapp" value="' . htmlspecialchars($empresa['whatsapp'] ?? '') . '" placeholder="(00) 00000-0000" id="input-whatsapp"/>
  </div>
</div>

<div class="col2">
  <div class="fg">
    <div class="fl">Email</div>
    <input class="fi" type="email" name="email" value="' . htmlspecialchars($empresa['email'] ?? '') . '" placeholder="contato@empresa.com"/>
  </div>
  <div class="fg">
    <div class="fl">Site</div>
    <input class="fi" type="text" name="site" value="' . htmlspecialchars($empresa['site'] ?? '') . '" placeholder="www.empresa.com"/>
  </div>
</div>

<div class="col2">
  <div class="fg">
    <div class="fl">Chave PIX</div>
    <input class="fi" type="text" name="pix_chave" value="' . htmlspecialchars($empresa['pix_chave'] ?? '') . '" placeholder="CPF, CNPJ, email ou telefone"/>
  </div>
  <div class="fg">
    <div class="fl">Tipo PIX</div>
    <select class="fi" name="pix_tipo">
      <option value="">Selecione...</option>
      <option value="cpf" ' . (($empresa['pix_tipo'] ?? '') === 'cpf' ? 'selected' : '') . '>CPF</option>
      <option value="cnpj" ' . (($empresa['pix_tipo'] ?? '') === 'cnpj' ? 'selected' : '') . '>CNPJ</option>
      <option value="email" ' . (($empresa['pix_tipo'] ?? '') === 'email' ? 'selected' : '') . '>Email</option>
      <option value="telefone" ' . (($empresa['pix_tipo'] ?? '') === 'telefone' ? 'selected' : '') . '>Telefone</option>
      <option value="aleatoria" ' . (($empresa['pix_tipo'] ?? '') === 'aleatoria' ? 'selected' : '') . '>Chave Aleatória</option>
    </select>
  </div>
</div>

<div class="col2">
  <div class="fg">
    <div class="fl">Logo do Sistema</div>
    <input class="fi" type="file" name="logo" accept="image/*" id="input-logo" onchange="previewLogo(this)"/>
    <small style="color:var(--text-4);margin-top:4px;display:block">Formatos: JPG, PNG, WebP. Tamanho maximo: 2MB</small>
    <div id="logo-preview" style="margin-top:8px">
      ' . (!empty($empresa['logo_path']) ? '<img src="' . $baseUrl . '/' . htmlspecialchars($empresa['logo_path']) . '" alt="Logo" style="max-height:60px;border-radius:4px"/>' : '') . '
    </div>
  </div>
  <div class="fg">
    <div class="fl">Rodapé de PDF</div>
    <input class="fi" type="text" name="rodape_pdf" value="' . htmlspecialchars($empresa['rodape_pdf'] ?? '') . '" placeholder="Texto que aparecerá em relatórios PDF"/>
    <small style="color:var(--text-4);margin-top:4px;display:block">Ex: "Documento gerado automaticamente pelo sistema"</small>
  </div>
</div>

<div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end">
  ' . renderButton(['label' => 'Salvar Configurações', 'type' => 'submit', 'variant' => 'cyan', 'size' => 'md']) . '
</div>';
?>

    <section class="section active" id="sec-configuracoes">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.573-1.066z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Configurações do Sistema</div>
          <div class="section-sub">Configure dados da empresa e permissões de acesso</div>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (!empty($error)): ?>
      <?= renderAlert(['type' => 'red', 'title' => 'Erro', 'message' => htmlspecialchars($error)]) ?>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
      <?= renderAlert(['type' => 'green', 'title' => 'Sucesso', 'message' => htmlspecialchars($success)]) ?>
      <?php endif; ?>

      <form method="POST" action="<?= $baseUrl ?>/configuracoes/update" enctype="multipart/form-data" id="form-configuracoes">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>
        
        <?php
        // Build Permissoes tab content
        $tabPermissoesContent = '';
        if (empty($matrix)) {
            $tabPermissoesContent = '<div class="table-empty"><div class="table-empty-flex"><svg class="table-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg><div>Nenhuma permissão cadastrada</div></div></div>';
        } else {
            $permRows = '';
            foreach ($matrix as $moduloNome => $moduloData) {
                foreach ($moduloData['permissoes'] as $permissao) {
                    $roleChecks = '';
                    foreach ($roles as $role) {
                        $checked = in_array($role, $permissao['roles']) ? 'checked' : '';
                        $bgStyle = in_array($role, $permissao['roles']) ? 'background:var(--' . $roleColors[$role] . '-20, rgba(0,0,0,0.1))' : 'background:var(--bg-2)';
                        $borderStyle = in_array($role, $permissao['roles']) ? 'border:1px solid var(--' . $roleColors[$role] . ', #666)' : 'border:1px solid var(--border, #ddd)';
                        $roleChecks .= '<label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;padding:6px 12px;border-radius:6px;' . $bgStyle . ';' . $borderStyle . '">
                            <input type="checkbox" value="' . $role . '" ' . $checked . ' onchange="updatePermissaoRoles(' . $permissao['id'] . ', this)" style="cursor:pointer;width:16px;height:16px"/>
                            <span style="font-size:13px;font-weight:500">' . $roleLabels[$role] . '</span>
                        </label>';
                    }
                    $permRows .= '<tr>
                        <td><strong>' . htmlspecialchars($moduloData['modulo_titulo']) . '</strong></td>
                        <td><code style="font-size:12px;background:var(--bg-2);padding:2px 6px;border-radius:4px">' . htmlspecialchars($permissao['nome']) . '</code></td>
                        <td>' . htmlspecialchars($permissao['titulo']) . '</td>
                        <td><div style="display:flex;gap:8px;justify-content:center">' . $roleChecks . '</div></td>
                    </tr>';
                }
            }
            $tabPermissoesContent = '<div class="card"><div class="card-head"><span class="card-title">Matriz de Permissões de Acesso</span><small style="color:var(--text-4)">Marque/desmarque os perfis para cada permissão</small></div><div class="card-body" style="padding:0"><table class="table-default" id="tbl-permissoes"><thead><tr><th>Módulo</th><th>Permissão</th><th>Descrição</th><th style="text-align:center">Perfis com Acesso</th></tr></thead><tbody>' . $permRows . '</tbody></table></div></div>';
        }

        // Build Aparência tab content
        $themes = [
            'default' => ['name' => 'Cyan Neon', 'primary' => '#0E9AA7', 'sidebar' => '#0C2D3F', 'bg' => '#F0FDFD', 'text' => '#0C4A6E'],
            'pink' => ['name' => 'Rosa Chamativo', 'primary' => '#E11D48', 'sidebar' => '#1E1B4B', 'bg' => '#FDF2F8', 'text' => '#1E1B4B'],
            'blue' => ['name' => 'Blue Steel', 'primary' => '#3B6EBF', 'sidebar' => '#1B2E5E', 'bg' => '#F5F8FF', 'text' => '#1B2E5E'],
            'green' => ['name' => 'Green Nature', 'primary' => '#059669', 'sidebar' => '#064E3B', 'bg' => '#ECFDF5', 'text' => '#064E3B'],
            'amber' => ['name' => 'Ambar Quente', 'primary' => '#D97706', 'sidebar' => '#1C1917', 'bg' => '#FFFBEB', 'text' => '#451A03'],
            'red' => ['name' => 'Vermelho Intenso', 'primary' => '#DC2626', 'sidebar' => '#1C0C0C', 'bg' => '#FEF2F2', 'text' => '#450A0A'],
            'slate' => ['name' => 'Cinza Elegante', 'primary' => '#475569', 'sidebar' => '#0F172A', 'bg' => '#F8FAFC', 'text' => '#1E293B'],
            'indigo' => ['name' => 'Indigo Profundo', 'primary' => '#4F46E5', 'sidebar' => '#1E1B4B', 'bg' => '#EEF2FF', 'text' => '#1E1B4B'],
            'teal' => ['name' => 'Turquesa Oceano', 'primary' => '#0D9488', 'sidebar' => '#042F2E', 'bg' => '#F0FDFA', 'text' => '#134E4A'],
            'rose' => ['name' => 'Rosa Suave', 'primary' => '#F43F5E', 'sidebar' => '#1A0A0E', 'bg' => '#FFF1F2', 'text' => '#4C0519'],
            'emerald' => ['name' => 'Esmeralda Vibrante', 'primary' => '#10B981', 'sidebar' => '#022C22', 'bg' => '#ECFDF5', 'text' => '#064E3B'],
            'papiro' => ['name' => 'Papiro', 'primary' => '#C2773A', 'sidebar' => '#2A1506', 'bg' => '#FDF6EE', 'text' => '#3D1F08'],
            'forest' => ['name' => 'Forest', 'primary' => '#3A7D44', 'sidebar' => '#1A2E1E', 'bg' => '#F4F7F2', 'text' => '#1A3320'],
            'lavanda_real' => ['name' => 'Lavanda Real', 'primary' => '#7C3AED', 'sidebar' => '#1E1040', 'bg' => '#F8F4FF', 'text' => '#2E1065'],
            'warm_sand' => ['name' => 'Warm Sand', 'primary' => '#B08060', 'sidebar' => '#1C1008', 'bg' => '#F5F0EB', 'text' => '#2C1A0E'],
            'arctic' => ['name' => 'Arctic', 'primary' => '#0284C7', 'sidebar' => '#071E2C', 'bg' => '#EFF9FF', 'text' => '#0C2A3D'],
            'dark' => ['name' => 'Dark Violeta', 'primary' => '#8B5CF6', 'sidebar' => '#0F0A1A', 'bg' => '#130E22', 'text' => '#E8E0F5'],
            'midnight_mint' => ['name' => 'Midnight Mint', 'primary' => '#34D399', 'sidebar' => '#0A1F1A', 'bg' => '#0B1F19', 'text' => '#D1FAE5'],
            'neon_orchid' => ['name' => 'Neon Orchid', 'primary' => '#C084FC', 'sidebar' => '#1A0A2E', 'bg' => '#1A0A2E', 'text' => '#F3E8FF'],
            'solar_forge' => ['name' => 'Solar Forge', 'primary' => '#F97316', 'sidebar' => '#1A0F05', 'bg' => '#1A0F05', 'text' => '#FFF7ED'],
            'ocean_deep' => ['name' => 'Ocean Deep', 'primary' => '#06B6D4', 'sidebar' => '#041C2C', 'bg' => '#041C2C', 'text' => '#ECFEFF'],
            'neon_fire' => ['name' => 'Neon Fire', 'primary' => '#FA870E', 'sidebar' => '#1A0A02', 'bg' => '#1A0A02', 'text' => '#FFF7ED'],
            'neon_magenta' => ['name' => 'Neon Magenta', 'primary' => '#D1108D', 'sidebar' => '#1A0520', 'bg' => '#1A0520', 'text' => '#FDF2F8'],
            'neon_red' => ['name' => 'Neon Red', 'primary' => '#EF2922', 'sidebar' => '#1A0505', 'bg' => '#1A0505', 'text' => '#FEF2F2'],
            'neon_green' => ['name' => 'Neon Green', 'primary' => '#11AA61', 'sidebar' => '#051A0D', 'bg' => '#051A0D', 'text' => '#ECFDF5'],
            'neon_purple' => ['name' => 'Neon Purple', 'primary' => '#8537E7', 'sidebar' => '#100A20', 'bg' => '#100A20', 'text' => '#F3E8FF'],
            'neon_hotpink' => ['name' => 'Neon Hot Pink', 'primary' => '#E11572', 'sidebar' => '#1A0515', 'bg' => '#1A0515', 'text' => '#FDF2F8'],
            'neon_blue' => ['name' => 'Neon Blue', 'primary' => '#1A51F5', 'sidebar' => '#050A1A', 'bg' => '#050A1A', 'text' => '#EFF6FF'],
            'neon_magenta_dark' => ['name' => 'Neon Magenta Dark', 'primary' => '#92063C', 'sidebar' => '#15020A', 'bg' => '#15020A', 'text' => '#FDF2F8'],
            'neon_forest' => ['name' => 'Neon Forest', 'primary' => '#03731C', 'sidebar' => '#021505', 'bg' => '#021505', 'text' => '#ECFDF5'],
        ];
        $currentTheme = $currentTheme ?? 'default';
        
        $themeGrid = '<div class="theme-grid">';
        foreach ($themes as $themeKey => $themeData) {
            $selected = $currentTheme === $themeKey ? 'selected' : '';
            $checked = $currentTheme === $themeKey ? 'checked' : '';
            $activeBadge = $currentTheme === $themeKey ? '<span class="badge green" style="font-size:10px;padding:2px 8px;margin-left:8px">Ativo</span>' : '';
            $themeGrid .= '<label class="theme-card ' . $selected . '">
                <input type="radio" name="tema" value="' . $themeKey . '" ' . $checked . ' class="theme-radio"/>
                <div class="theme-preview">
                    <div class="theme-sidebar" style="background:' . $themeData['sidebar'] . '"></div>
                    <div class="theme-content" style="background:' . $themeData['bg'] . '">
                        <div class="theme-bar" style="background:' . $themeData['primary'] . '"></div>
                        <div class="theme-lines">
                            <div class="theme-line" style="background:' . $themeData['text'] . ';opacity:0.3"></div>
                            <div class="theme-line" style="background:' . $themeData['text'] . ';opacity:0.2"></div>
                            <div class="theme-line" style="background:' . $themeData['text'] . ';opacity:0.15"></div>
                        </div>
                    </div>
                </div>
                <div class="theme-name"><span>' . htmlspecialchars($themeData['name']) . '</span>' . $activeBadge . '</div>
            </label>';
        }
        $themeGrid .= '</div>';
        
        $tabAparenciaContent = '<div class="card"><div class="card-head"><span class="card-title">Selecionar Tema</span><small style="color:var(--text-4)">Escolha o tema visual do sistema</small></div><div class="card-body">' . $themeGrid . '</div></div>
        <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end">
            ' . renderButton(['label' => 'Salvar Tema', 'type' => 'button', 'variant' => 'cyan', 'size' => 'md', 'onclick' => 'salvarTema()']) . '
        </div>';

        // Render tabs using component
        echo renderTabsColor([
            'id' => 'tabs-config',
            'activeIndex' => 0,
            'tabs' => [
                ['label' => 'Empresa', 'color' => 'cyan', 'content' => $tabEmpresaContent],
                ['label' => 'Permissões', 'color' => 'purple', 'content' => $tabPermissoesContent],
                ['label' => 'Aparência', 'color' => 'green', 'content' => $tabAparenciaContent],
            ]
        ]);
        ?>
      </form>
    </section>

<script>
// Máscara para CNPJ
document.getElementById('input-cnpj')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 14) value = value.substring(0, 14);
    if (value.length > 12) {
        value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, '$1.$2.$3/$4-$5');
    } else if (value.length > 8) {
        value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1,4})/, '$1.$2.$3/$4');
    } else if (value.length > 5) {
        value = value.replace(/(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})(\d{1,3})/, '$1.$2');
    }
    e.target.value = value;
});

// Máscara para CEP
document.getElementById('input-cep')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 8) value = value.substring(0, 8);
    if (value.length > 5) {
        value = value.replace(/(\d{5})(\d{1,3})/, '$1-$2');
    }
    e.target.value = value;
});

// Máscara para Telefone
function applyPhoneMask(inputId, isCellular = false) {
    document.getElementById(inputId)?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        const maxLen = isCellular ? 11 : 10;
        if (value.length > maxLen) value = value.substring(0, maxLen);
        
        if (value.length > 6) {
            value = value.replace(/(\d{2})(\d{4,5})(\d{4})/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/(\d{2})(\d{1,5})/, '($1) $2');
        }
        e.target.value = value;
    });
}

applyPhoneMask('input-telefone', false);
applyPhoneMask('input-whatsapp', true);

// Preview imediato da logo ao selecionar arquivo
function previewLogo(input) {
    var preview = document.getElementById('logo-preview');
    if (!input.files || !input.files[0]) return;
    
    var reader = new FileReader();
    reader.onload = function(e) {
        preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-height:80px;border-radius:4px;border:2px solid var(--neon-cyan)"/>';
    };
    reader.readAsDataURL(input.files[0]);
}

// Busca automática de CNPJ via BrasilAPI
document.getElementById('input-cnpj')?.addEventListener('blur', function(e) {
    let cnpj = e.target.value.replace(/\D/g, '');
    if (cnpj.length !== 14) return;
    
    fetch(BASE_URL + '/proxy/cnpj/' + cnpj)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && !data.error) {
                // Preencher Nome Fantasia (preferência) ou Razão Social
                var nomeField = document.getElementById('input-nome-empresa');
                if (nomeField) {
                    var valorAtual = nomeField.value.trim();
                    if (!valorAtual || valorAtual === 'SISLOC') {
                        nomeField.value = data.nome_fantasia || data.razao_social || '';
                    }
                }
                
                // Preencher Endereço
                var enderecoField = document.getElementById('input-endereco');
                if (enderecoField && !enderecoField.value) {
                    var logradouro = data.logradouro || '';
                    var numero = data.numero || '';
                    var complemento = data.complemento || '';
                    var bairro = data.bairro || '';
                    enderecoField.value = logradouro + (numero ? ', ' + numero : '') + (complemento ? ' - ' + complemento : '') + (bairro ? ' - ' + bairro : '');
                }
                
                // Preencher Cidade
                var cidadeField = document.getElementById('input-cidade');
                if (cidadeField && !cidadeField.value) {
                    cidadeField.value = data.municipio || '';
                }
                
                // Preencher Estado
                var estadoField = document.getElementById('input-estado');
                if (estadoField && !estadoField.value) {
                    estadoField.value = data.uf || '';
                }
                
                // Preencher Telefone
                var telefoneField = document.getElementById('input-telefone');
                if (telefoneField && !telefoneField.value) {
                    var ddd = data.ddd || '';
                    var telefone = data.telefone || '';
                    if (ddd && telefone) {
                        telefoneField.value = '(' + ddd + ') ' + telefone;
                    }
                }
                
                showToast('green', 'CNPJ Encontrado', 'Dados da empresa carregados automaticamente');
            }
        })
        .catch(function(err) { console.error('Erro ao buscar CNPJ:', err); });
});

// Busca automática de CEP via ViaCEP
document.getElementById('input-cep')?.addEventListener('blur', function(e) {
    let cep = e.target.value.replace(/\D/g, '');
    if (cep.length !== 8) return;
    
    fetch(BASE_URL + '/proxy/cep/' + cep)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && !data.erro) {
                // Preencher Endereço
                var enderecoField = document.getElementById('input-endereco');
                if (enderecoField && !enderecoField.value) {
                    var logradouro = data.logradouro || '';
                    var complemento = data.complemento || '';
                    var bairro = data.bairro || '';
                    enderecoField.value = logradouro + (complemento ? ', ' + complemento : '') + (bairro ? ' - ' + bairro : '');
                }
                
                // Preencher Cidade
                var cidadeField = document.getElementById('input-cidade');
                if (cidadeField && !cidadeField.value) {
                    cidadeField.value = data.localidade || '';
                }
                
                // Preencher Estado
                var estadoField = document.getElementById('input-estado');
                if (estadoField && !estadoField.value) {
                    estadoField.value = data.uf || '';
                }
                
                showToast('green', 'CEP Encontrado', 'Endereço carregado automaticamente');
            }
        })
        .catch(function(err) { console.error('Erro ao buscar CEP:', err); });
});

// Selecionar tema ao clicar no card
document.querySelectorAll('.theme-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('.theme-radio').checked = true;
        // Aplicar preview do tema imediatamente
        aplicarTema(this.querySelector('.theme-radio').value);
    });
});

// Aplicar tema dinamicamente sem reload
function aplicarTema(tema) {
    const temas = {
        default: { primary: '#0E9AA7', glow: 'rgba(14,154,167,0.28)', sidebar: '#0C2D3F', hover: 'rgba(14,154,167,0.35)', surface: '#CCFBF1', bg: '#F0FDFD', text: '#0C4A6E', textLight: '#0E9AA7' },
        pink: { primary: '#E11D48', glow: 'rgba(225,29,72,0.28)', sidebar: '#1E1B4B', hover: 'rgba(225,29,72,0.4)', surface: '#FCE7F3', bg: '#FDF2F8', text: '#1E1B4B', textLight: '#4C1D4E' },
        blue: { primary: '#3B6EBF', glow: 'rgba(59,110,191,0.28)', sidebar: '#1B2E5E', hover: 'rgba(107,155,210,0.35)', surface: '#E2EBFA', bg: '#F5F8FF', text: '#1B2E5E', textLight: '#6B9BD2' },
        green: { primary: '#059669', glow: 'rgba(5,150,105,0.28)', sidebar: '#064E3B', hover: 'rgba(5,150,105,0.4)', surface: '#D1FAE5', bg: '#ECFDF5', text: '#064E3B', textLight: '#059669' },
        amber: { primary: '#D97706', glow: 'rgba(217,119,6,0.28)', sidebar: '#1C1917', hover: 'rgba(217,119,6,0.4)', surface: '#FEF3C7', bg: '#FFFBEB', text: '#451A03', textLight: '#92400E' },
        red: { primary: '#DC2626', glow: 'rgba(220,38,38,0.28)', sidebar: '#1C0C0C', hover: 'rgba(220,38,38,0.4)', surface: '#FEE2E2', bg: '#FEF2F2', text: '#450A0A', textLight: '#B91C1C' },
        slate: { primary: '#475569', glow: 'rgba(71,85,105,0.28)', sidebar: '#0F172A', hover: 'rgba(71,85,105,0.4)', surface: '#F1F5F9', bg: '#F8FAFC', text: '#1E293B', textLight: '#64748B' },
        indigo: { primary: '#4F46E5', glow: 'rgba(79,70,229,0.28)', sidebar: '#1E1B4B', hover: 'rgba(79,70,229,0.4)', surface: '#E0E7FF', bg: '#EEF2FF', text: '#1E1B4B', textLight: '#6366F1' },
        teal: { primary: '#0D9488', glow: 'rgba(13,148,136,0.28)', sidebar: '#042F2E', hover: 'rgba(13,148,136,0.4)', surface: '#CCFBF1', bg: '#F0FDFA', text: '#134E4A', textLight: '#0D9488' },
        rose: { primary: '#F43F5E', glow: 'rgba(244,63,94,0.28)', sidebar: '#1A0A0E', hover: 'rgba(244,63,94,0.4)', surface: '#FFE4E6', bg: '#FFF1F2', text: '#4C0519', textLight: '#E11D48' },
        emerald: { primary: '#10B981', glow: 'rgba(16,185,129,0.28)', sidebar: '#022C22', hover: 'rgba(16,185,129,0.4)', surface: '#A7F3D0', bg: '#ECFDF5', text: '#064E3B', textLight: '#059669' },
        papiro: { primary: '#C2773A', glow: 'rgba(194,119,58,0.28)', sidebar: '#2A1506', hover: 'rgba(194,119,58,0.35)', surface: '#F5E6D0', bg: '#FDF6EE', text: '#3D1F08', textLight: '#8B4C1A' },
        forest: { primary: '#3A7D44', glow: 'rgba(58,125,68,0.28)', sidebar: '#1A2E1E', hover: 'rgba(58,125,68,0.35)', surface: '#D8EDD8', bg: '#F4F7F2', text: '#1A3320', textLight: '#255C2E' },
        lavanda_real: { primary: '#7C3AED', glow: 'rgba(124,58,237,0.28)', sidebar: '#1E1040', hover: 'rgba(124,58,237,0.35)', surface: '#EDE0FF', bg: '#F8F4FF', text: '#2E1065', textLight: '#5B21B6' },
        warm_sand: { primary: '#B08060', glow: 'rgba(176,128,96,0.28)', sidebar: '#1C1008', hover: 'rgba(176,128,96,0.35)', surface: '#E8DCCF', bg: '#F5F0EB', text: '#2C1A0E', textLight: '#7A4E2A' },
        arctic: { primary: '#0284C7', glow: 'rgba(2,132,199,0.28)', sidebar: '#071E2C', hover: 'rgba(2,132,199,0.35)', surface: '#D4EEFA', bg: '#EFF9FF', text: '#0C2A3D', textLight: '#015A8A' },
        dark: { primary: '#8B5CF6', glow: 'rgba(139,92,246,0.28)', sidebar: '#0F0A1A', hover: 'rgba(139,92,246,0.35)', surface: '#1E1533', bg: '#130E22', text: '#E8E0F5', textLight: '#A78BFA' },
        midnight_mint: { primary: '#34D399', glow: 'rgba(52,211,153,0.28)', sidebar: '#0A1F1A', hover: 'rgba(52,211,153,0.35)', surface: '#132E28', bg: '#0B1F19', text: '#D1FAE5', textLight: '#6EE7B7' },
        neon_orchid: { primary: '#C084FC', glow: 'rgba(192,132,252,0.28)', sidebar: '#1A0A2E', hover: 'rgba(192,132,252,0.35)', surface: '#2E1545', bg: '#1A0A2E', text: '#F3E8FF', textLight: '#D8B4FE' },
        solar_forge: { primary: '#F97316', glow: 'rgba(249,115,22,0.28)', sidebar: '#1A0F05', hover: 'rgba(249,115,22,0.35)', surface: '#2E1A0A', bg: '#1A0F05', text: '#FFF7ED', textLight: '#FB923C' },
        ocean_deep: { primary: '#06B6D4', glow: 'rgba(6,182,212,0.28)', sidebar: '#041C2C', hover: 'rgba(6,182,212,0.35)', surface: '#0A2A3F', bg: '#041C2C', text: '#ECFEFF', textLight: '#22D3EE' },
        neon_fire: { primary: '#FA870E', glow: 'rgba(250,135,14,0.35)', sidebar: '#1A0A02', hover: 'rgba(250,135,14,0.45)', surface: '#2A1505', bg: '#1A0A02', text: '#FFF7ED', textLight: '#FB923C' },
        neon_magenta: { primary: '#D1108D', glow: 'rgba(209,16,141,0.35)', sidebar: '#1A0520', hover: 'rgba(209,16,141,0.45)', surface: '#2A0A35', bg: '#1A0520', text: '#FDF2F8', textLight: '#F472B6' },
        neon_red: { primary: '#EF2922', glow: 'rgba(239,41,34,0.35)', sidebar: '#1A0505', hover: 'rgba(239,41,34,0.45)', surface: '#2A0808', bg: '#1A0505', text: '#FEF2F2', textLight: '#F87171' },
        neon_green: { primary: '#11AA61', glow: 'rgba(17,170,97,0.35)', sidebar: '#051A0D', hover: 'rgba(17,170,97,0.45)', surface: '#0A2A15', bg: '#051A0D', text: '#ECFDF5', textLight: '#34D399' },
        neon_purple: { primary: '#8537E7', glow: 'rgba(133,55,231,0.35)', sidebar: '#100A20', hover: 'rgba(133,55,231,0.45)', surface: '#1A0F30', bg: '#100A20', text: '#F3E8FF', textLight: '#A78BFA' },
        neon_hotpink: { primary: '#E11572', glow: 'rgba(225,21,114,0.35)', sidebar: '#1A0515', hover: 'rgba(225,21,114,0.45)', surface: '#2A0A20', bg: '#1A0515', text: '#FDF2F8', textLight: '#F472B6' },
        neon_blue: { primary: '#1A51F5', glow: 'rgba(26,81,245,0.35)', sidebar: '#050A1A', hover: 'rgba(26,81,245,0.45)', surface: '#0A1530', bg: '#050A1A', text: '#EFF6FF', textLight: '#60A5FA' },
        neon_magenta_dark: { primary: '#92063C', glow: 'rgba(146,6,60,0.35)', sidebar: '#15020A', hover: 'rgba(146,6,60,0.45)', surface: '#200510', bg: '#15020A', text: '#FDF2F8', textLight: '#F43F5E' },
        neon_forest: { primary: '#03731C', glow: 'rgba(3,115,28,0.35)', sidebar: '#021505', hover: 'rgba(3,115,28,0.45)', surface: '#052010', bg: '#021505', text: '#ECFDF5', textLight: '#22C55E' },
    };
    
    const t = temas[tema];
    if (!t) return;
    
    const root = document.documentElement.style;
    root.setProperty('--neon-cyan', t.primary);
    root.setProperty('--neon-cyan-glow', t.glow);
    root.setProperty('--bg-dark', t.sidebar);
    root.setProperty('--bg-surface', t.surface);
    root.setProperty('--bg-darkest', t.bg);
    root.setProperty('--text-1', t.text);
    root.setProperty('--text-2', t.text);
    root.setProperty('--text-3', t.textLight);
    root.setProperty('--bg-hover', t.hover);
    
    document.querySelectorAll('.ni:hover, .nav-item:hover, .si:hover').forEach(el => {
        el.style.background = t.hover;
    });
}

// Salvar tema (sem reload)
function salvarTema() {
    const tema = document.querySelector('input[name="tema"]:checked')?.value;
    if (!tema) {
        showToast('red', 'Erro', 'Selecione um tema');
        return;
    }
    
    const formData = new FormData();
    formData.append('_csrf_token', '<?= \App\Core\Csrf::getToken() ?>');
    formData.append('tema', tema);
    
    fetch('<?= $baseUrl ?>/configuracoes/update-theme', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('green', 'Sucesso', 'Tema salvo com sucesso!');
            // Atualizar badge "Ativo" nos cards
            document.querySelectorAll('.theme-name .badge').forEach(b => b.remove());
            const selectedCard = document.querySelector('.theme-card.selected .theme-name');
            if (selectedCard) {
                const badge = document.createElement('span');
                badge.className = 'badge green';
                badge.style.cssText = 'font-size:10px;padding:2px 8px;margin-left:8px';
                badge.textContent = 'Ativo';
                selectedCard.appendChild(badge);
            }
        } else {
            showToast('red', 'Erro', data.message);
        }
    })
    .catch(() => {
        showToast('red', 'Erro', 'Erro ao salvar tema');
    });
}

// Atualizar roles de uma permissão (apenas checkbox)
function updatePermissaoRoles(permissaoId, checkbox) {
  const row = checkbox.closest('tr');
  const checkboxes = row.querySelectorAll('input[type="checkbox"]');
  const roles = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
  
  fetch('<?= $baseUrl ?>/configuracoes/update-roles', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=<?= \App\Core\Csrf::getToken() ?>&permissao_id=' + permissaoId + (roles.length ? '&roles[]=' + roles.join('&roles[]=') : '')
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Sucesso', data.message);
    } else {
      showToast('red', 'Erro', data.message);
      checkbox.checked = !checkbox.checked; // Revert on error
    }
  })
  .catch(() => {
    showToast('red', 'Erro', 'Erro ao atualizar permissão');
    checkbox.checked = !checkbox.checked;
  });
}

</script>


<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
