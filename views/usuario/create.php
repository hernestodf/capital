<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';
require dirname(__DIR__) . '/layout/header.php';
?>

    <section class="section active" id="sec-usuarios-create">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.236-3.18a3 3 0 104.472 0M15 12a6 6 0 11-12 0 6 6 0 0112 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Novo Usuário</div>
          <div class="section-sub">Cadastro de usuário com controle de acesso</div>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (!empty($error)): ?>
      <?= renderAlert([
          'variant' => 'red',
          'title' => 'Erro',
          'message' => $error,
          'dismissible' => true
      ]) ?>
      <?php endif; ?>

      <form method="POST" action="<?= $baseUrl ?>/usuarios/store" data-ajax data-redirect="<?= $baseUrl ?>/usuarios">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>

        <div class="card">
          <div class="card-body">

            <?= renderInput([
                'type' => 'text',
                'name' => 'name',
                'id' => 'name',
                'label' => 'Nome Completo',
                'placeholder' => 'Nome completo do usuário',
                'value' => $old['name'] ?? '',
                'required' => true
            ]) ?>

            <?= renderInput([
                'type' => 'email',
                'name' => 'email',
                'id' => 'email',
                'label' => 'Email',
                'placeholder' => 'email@exemplo.com',
                'value' => $old['email'] ?? '',
                'required' => true
            ]) ?>

            <?= renderInput([
                'type' => 'password',
                'name' => 'password',
                'id' => 'password',
                'label' => 'Senha',
                'placeholder' => 'Mínimo 6 caracteres (padrão: 123456)',
                'value' => '',
                'required' => false
            ]) ?>

            <div class="col2">
              <div class="fg">
                <div class="fl">Telefone</div>
                <input type="tel" name="telefone" id="telefone" class="fi" data-mask="(XX) XXXX-XXXX" value="<?= htmlspecialchars($old['telefone'] ?? '') ?>" placeholder="(XX) XXXX-XXXX">
              </div>

              <div class="fg">
                <div class="fl">Celular</div>
                <input type="tel" name="celular" id="celular" class="fi" data-mask="(XX) XXXXX-XXXX" value="<?= htmlspecialchars($old['celular'] ?? '') ?>" placeholder="(XX) XXXXX-XXXX">
              </div>
            </div>

            <div class="fg">
              <div class="fl">CEP</div>
              <input type="text" name="cep" id="cep" class="fi" data-mask="XXXXX-XXX" value="<?= htmlspecialchars($old['cep'] ?? '') ?>" placeholder="XXXXX-XXX">
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Função</div>
                <select name="role" id="role" class="fi" required>
                  <?php foreach ($roles as $role): ?>
                  <option value="<?= $role ?>" <?= ($old['role'] ?? 'estoquista') === $role ? 'selected' : '' ?>>
                    <?= ucfirst($role) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="fg">
                <div class="fl">Status</div>
                <select name="status" class="fi">
                  <option value="1" <?= ($old['status'] ?? 1) == 1 ? 'selected' : '' ?>>Ativo</option>
                  <option value="0" <?= ($old['status'] ?? 1) == 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
              </div>
            </div>

            <div id="field-comercial" style="display:none">
              <div class="fg">
                <div class="fl">Comercial Associado</div>
                <select name="id_produtor" id="id_produtor" class="fi">
                  <option value="">-- Nenhum --</option>
                  <?php foreach ($produtores as $p): ?>
                  <option value="<?= $p['id'] ?>" <?= ((int)($old['id_produtor'] ?? 0)) === (int)$p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nome']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <div style="font-size:11px;color:var(--text-4);margin-top:4px">Vincular este usuário a um registro de Comercial para filtrar os eventos que ele pode ver.</div>
              </div>
            </div>

          </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px">
          <button type="submit" class="btn btn-cyan">Salvar</button>
          <a href="<?= $baseUrl ?>/usuarios" class="btn btn-gray">Cancelar</a>
        </div>
      </form>
    </section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var roleSelect = document.getElementById('role');
  var fieldComercial = document.getElementById('field-comercial');

  function toggleComercialField() {
    fieldComercial.style.display = roleSelect.value === 'comercial' ? '' : 'none';
  }

  roleSelect.addEventListener('change', toggleComercialField);
  toggleComercialField();

  // Mascara de data-mask agora e global (scripts.js) -- ver bug-043.

  document.getElementById('cep').addEventListener('blur', function(e) {
    var cep = e.target.value.replace(/\D/g, '');
    if (cep.length === 8) {
      fetch(BASE_URL + '/proxy/cep/' + cep)
        .then(res => res.json())
        .then(data => {
          if (!data.erro) showToast('green', 'CEP Encontrado', data.logradouro + ' - ' + data.bairro);
        })
        .catch(() => {});
    }
  });
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
