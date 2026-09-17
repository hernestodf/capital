<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';
require dirname(__DIR__) . '/layout/header.php';
?>

    <section class="section active" id="sec-clientes-create">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.236-3.18a3 3 0 104.472 0M15 12a6 6 0 11-12 0 6 6 0 0112 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Novo Cliente</div>
          <div class="section-sub">Cadastrar cliente com busca automatica de CEP/CNPJ</div>
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

      <form method="POST" action="<?= $baseUrl ?>/clientes/store" data-ajax data-redirect="<?= $baseUrl ?>/clientes">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>

        <div class="card">
          <div class="card-body">

            <div class="fg">
              <div class="fl">CNPJ/CPF</div>
              <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="fi" data-mask="XX.XXX.XXX/XXXX-XX" value="<?= htmlspecialchars($data['cpf_cnpj'] ?? '') ?>" placeholder="CNPJ ou CPF">
            </div>

            <?= renderInput([
                'type' => 'text',
                'name' => 'nome_fantasia',
                'id' => 'nome_fantasia',
                'label' => 'Nome Fantasia',
                'placeholder' => 'Nome fantasia da empresa',
                'value' => $data['nome_fantasia'] ?? ''
            ]) ?>

            <?= renderInput([
                'type' => 'text',
                'name' => 'razao_social',
                'id' => 'razao_social',
                'label' => 'Razão Social',
                'placeholder' => 'Razão social da empresa',
                'value' => $data['razao_social'] ?? ''
            ]) ?>

            <div class="col2">
              <div class="fg">
                <div class="fl">Telefone</div>
                <input type="tel" name="telefone" id="telefone" class="fi" data-mask="(XX) XXXX-XXXX" value="<?= htmlspecialchars($data['telefone'] ?? '') ?>" placeholder="(XX) XXXX-XXXX">
              </div>

              <div class="fg">
                <div class="fl">Contato</div>
                <input type="text" name="contato" id="contato" class="fi" value="<?= htmlspecialchars($data['contato'] ?? '') ?>" placeholder="Nome do contato">
              </div>
            </div>

            <?= renderInput([
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email',
                'placeholder' => 'email@exemplo.com',
                'value' => $data['email'] ?? ''
            ]) ?>

          </div>
        </div>

        <div class="card" style="margin-top:20px">
          <div class="card-body">

            <div class="fg">
              <div class="fl">CEP</div>
              <input type="text" name="cep" id="cep" class="fi" data-mask="XXXXX-XXX" value="<?= htmlspecialchars($data['cep'] ?? '') ?>" placeholder="XXXXX-XXX">
            </div>

            <div class="fg">
              <div class="fl">Endereço</div>
              <input type="text" name="endereco" id="endereco" class="fi" value="<?= htmlspecialchars($data['endereco'] ?? '') ?>" placeholder="Rua, avenida, etc">
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Numero</div>
                <input type="text" name="numero" id="numero" class="fi" value="<?= htmlspecialchars($data['numero'] ?? '') ?>" placeholder="S/N">
              </div>

              <div class="fg">
                <div class="fl">Complemento</div>
                <input type="text" name="complemento" id="complemento" class="fi" value="<?= htmlspecialchars($data['complemento'] ?? '') ?>" placeholder="Apto, sala, etc">
              </div>
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Bairro</div>
                <input type="text" name="bairro" id="bairro" class="fi" value="<?= htmlspecialchars($data['bairro'] ?? '') ?>" placeholder="Bairro">
              </div>

              <div class="fg">
                <div class="fl">Cidade</div>
                <input type="text" name="cidade" id="cidade" class="fi" value="<?= htmlspecialchars($data['cidade'] ?? '') ?>" placeholder="Cidade">
              </div>
            </div>

            <div class="fg">
              <div class="fl">Estado</div>
              <select name="estado" id="estado" class="fi">
                <option value="">Selecione</option>
                <?php
                $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                foreach ($ufs as $uf): ?>
                <option value="<?= $uf ?>" <?= ($data['estado'] ?? '') == $uf ? 'selected' : '' ?>><?= $uf ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="fg">
              <div class="fl">Observação</div>
              <textarea name="observacao" id="observacao" class="fi" rows="3" placeholder="Observações sobre o cliente"><?= htmlspecialchars($data['observacao'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;gap:12px;margin-top:20px">
              <?= renderButton([
                  'label' => 'Cancelar',
                  'variant' => 'ghost',
                  'onclick' => "window.location.href='{$baseUrl}/clientes'"
              ]) ?>

              <?= renderButton([
                  'label' => 'Salvar',
                  'variant' => 'cyan',
                  'type' => 'submit',
                  'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
                  'iconPosition' => 'left'
              ]) ?>
            </div>

          </div>
        </div>
      </form>
    </section>

<script>
// Mascara de data-mask agora e global (scripts.js) -- ver bug-043.
document.addEventListener('DOMContentLoaded', function() {
  // Busca CEP (ViaCEP)
  document.querySelector('[name="cep"]').addEventListener('blur', function(e) {
    var cep = e.target.value.replace(/\D/g, '');
    if (cep.length === 8) {
      fetch(BASE_URL + '/proxy/cep/' + cep)
        .then(function(res) { return res.json(); })
        .then(function(data) {
          if (!data.erro) {
            var elEnd = document.querySelector('[name="endereco"]');
            if (elEnd) elEnd.value = data.logradouro || '';
            var elBairro = document.querySelector('[name="bairro"]');
            if (elBairro) elBairro.value = data.bairro || '';
            var elCidade = document.querySelector('[name="cidade"]');
            if (elCidade) elCidade.value = data.localidade || '';
            var elEstado = document.querySelector('[name="estado"]');
            if (elEstado) elEstado.value = data.uf || '';
          }
        })
        .catch(function(err) { console.error('CEP nao encontrado'); });
    }
  });

  // Busca CNPJ (BrasilAPI)
  document.getElementById('cpf_cnpj').addEventListener('blur', function(e) {
    var cpfCnpj = e.target.value.replace(/\D/g, '');
    
    
    // Aceita 14 dígitos (CNPJ) ou 11 dígitos (CPF)
    if (cpfCnpj.length === 14) {
      showToast('cyan', 'Buscando', 'Consultando CNPJ na API...');
      
      fetch(BASE_URL + '/proxy/cnpj/' + cpfCnpj)
        .then(function(res) {
          
          if (res.status === 404 || res.status === 502) {
            throw new Error('CNPJ não encontrado na base da Receita Federal');
          }
          if (res.status === 429) {
            throw new Error('Muitas requisições. Aguarde um momento e tente novamente.');
          }
          if (!res.ok) {
            throw new Error('Erro na API: HTTP ' + res.status);
          }
          return res.json();
        })
        .then(function(data) {
          
          if (data.razao_social || data.nome_fantasia) {
            // Formata CNPJ (vem como "00000000000191", precisa "00.000.000/0001-91")
            var cnpj = data.cnpj || cpfCnpj;
            if (cnpj.length === 14) {
              cnpj = cnpj.substring(0,2) + '.' + cnpj.substring(2,5) + '.' + cnpj.substring(5,8) + '/' + cnpj.substring(8,12) + '-' + cnpj.substring(12);
            }
            var elCnpj = document.querySelector('[name="cpf_cnpj"]');
            if (elCnpj) elCnpj.value = cnpj;

            var elRazao = document.querySelector('[name="razao_social"]');
            if (elRazao) elRazao.value = data.razao_social || '';

            var elFantasia = document.querySelector('[name="nome_fantasia"]');
            if (elFantasia) elFantasia.value = data.nome_fantasia || data.razao_social || '';

            // Formata telefone (vem como "6134939002", precisa "(61) 3493-9002")
            var tel1 = data.ddd_telefone_1 || '';
            var tel2 = data.ddd_telefone_2 || '';
            if (tel1.length === 10) {
              tel1 = '(' + tel1.substring(0,2) + ') ' + tel1.substring(2,6) + '-' + tel1.substring(6);
            } else if (tel1.length === 11) {
              tel1 = '(' + tel1.substring(0,2) + ') ' + tel1.substring(2,7) + '-' + tel1.substring(7);
            }
            var elTel = document.querySelector('[name="telefone"]');
            if (elTel) elTel.value = tel1;

            // Formata CEP (vem como "70040912", precisa "70040-912")
            var cep = data.cep || '';
            if (cep.length === 8) {
              cep = cep.substring(0, 5) + '-' + cep.substring(5);
            }
            var elCep = document.querySelector('[name="cep"]');
            if (elCep) elCep.value = cep;

            var elEnd = document.querySelector('[name="endereco"]');
            if (elEnd) elEnd.value = data.logradouro || '';

            var elNum = document.querySelector('[name="numero"]');
            if (elNum) elNum.value = data.numero || '';

            var elComp = document.querySelector('[name="complemento"]');
            if (elComp) elComp.value = data.complemento || '';

            var elBairro = document.querySelector('[name="bairro"]');
            if (elBairro) elBairro.value = data.bairro || '';

            var elCidade = document.querySelector('[name="cidade"]');
            if (elCidade) elCidade.value = data.municipio || '';

            var elEstado = document.querySelector('[name="estado"]');
            if (elEstado) elEstado.value = data.uf || '';

            showToast('green', 'Sucesso', 'CNPJ encontrado! Dados preenchidos automaticamente.');
          } else {
            showToast('yellow', 'Atenção', 'CNPJ encontrado mas sem dados. Preencha manualmente.');
          }
        })
        .catch(function(err) {
          console.error('Erro na busca de CNPJ:', err.message);
          console.error('Detalhes:', err);
          showToast('yellow', 'Atenção', err.message || 'CNPJ não encontrado. Preencha os dados manualmente.');
        });
    } else if (cpfCnpj.length === 11) {
      // É um CPF, não precisa buscar na API de CNPJ
    } else if (cpfCnpj.length > 0 && cpfCnpj.length !== 11 && cpfCnpj.length !== 14) {
      showToast('yellow', 'Atenção', 'CPF/CNPJ inválido. Deve ter 11 (CPF) ou 14 (CNPJ) dígitos.');
    }
  });
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
