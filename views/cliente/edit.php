<?php require dirname(__DIR__) . '/layout/header.php'; ?>

    <section class="section active" id="sec-clientes-edit">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Editar Cliente</div>
          <div class="section-sub"><?= htmlspecialchars($cliente['nome_fantasia'] ?? $cliente['razao_social'] ?? 'Cliente') ?></div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Dados do Cliente</span>
        </div>
        <div class="card-body">
          <?php if (!empty($error)): ?>
          <div class="alert red">
            <div class="alert-ico">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="alert-body">
              <div class="alert-title">Erro</div>
              <div class="alert-desc"><?= htmlspecialchars($error) ?></div>
            </div>
          </div>
          <?php endif; ?>
          
          <form method="POST" action="<?= $baseUrl ?>/clientes/update/<?= $cliente['id'] ?>" data-ajax data-redirect="<?= $baseUrl ?>/clientes">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>
            
            <div class="fg">
              <div class="fl">CNPJ/CPF</div>
              <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="fi" data-mask="XX.XXX.XXX/XXXX-XX" value="<?= htmlspecialchars($cliente['cpf_cnpj'] ?? '') ?>" placeholder="CNPJ ou CPF">
            </div>
            
            <div class="fg">
              <div class="fl">Nome Fantasia</div>
              <input type="text" name="nome_fantasia" id="nome_fantasia" class="fi" value="<?= htmlspecialchars($cliente['nome_fantasia'] ?? '') ?>" placeholder="Nome fantasia">
            </div>
            
            <div class="fg">
              <div class="fl">Razão Social</div>
              <input type="text" name="razao_social" id="razao_social" class="fi" value="<?= htmlspecialchars($cliente['razao_social'] ?? '') ?>" placeholder="Razão social">
            </div>
            
            <div class="col2">
              <div class="fg">
                <div class="fl">Telefone</div>
                <input type="text" name="telefone" id="telefone" class="fi" data-mask="(XX) XXXX-XXXX" value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>" placeholder="(XX) XXXX-XXXX">
              </div>
              <div class="fg">
                <div class="fl">Contato</div>
                <input type="text" name="contato" id="contato" class="fi" value="<?= htmlspecialchars($cliente['contato'] ?? '') ?>" placeholder="Nome do contato">
              </div>
            </div>
            
            <div class="fg">
              <div class="fl">Email</div>
              <input type="email" name="email" class="fi" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>" placeholder="email@exemplo.com">
            </div>
            
            <div class="divider" style="margin: 20px 0"></div>
            
            <div class="fg">
              <div class="fl">CEP</div>
              <input type="text" name="cep" id="cep" class="fi" data-mask="XXXXX-XXX" value="<?= htmlspecialchars($cliente['cep'] ?? '') ?>" placeholder="XXXXX-XXX">
            </div>
            
            <div class="fg">
              <div class="fl">Endereço</div>
              <input type="text" name="endereco" id="endereco" class="fi" value="<?= htmlspecialchars($cliente['endereco'] ?? '') ?>" placeholder="Rua, avenida, etc">
            </div>
            
            <div class="col2">
              <div class="fg">
                <div class="fl">Número</div>
                <input type="text" name="numero" id="numero" class="fi" value="<?= htmlspecialchars($cliente['numero'] ?? '') ?>" placeholder="S/N">
              </div>
              <div class="fg">
                <div class="fl">Complemento</div>
                <input type="text" name="complemento" id="complemento" class="fi" value="<?= htmlspecialchars($cliente['complemento'] ?? '') ?>" placeholder="Apto, sala, etc">
              </div>
            </div>
            
            <div class="col2">
              <div class="fg">
                <div class="fl">Bairro</div>
                <input type="text" name="bairro" id="bairro" class="fi" value="<?= htmlspecialchars($cliente['bairro'] ?? '') ?>" placeholder="Bairro">
              </div>
              <div class="fg">
                <div class="fl">Cidade</div>
                <input type="text" name="cidade" id="cidade" class="fi" value="<?= htmlspecialchars($cliente['cidade'] ?? '') ?>" placeholder="Cidade">
              </div>
            </div>
            
            <div class="fg">
              <div class="fl">Estado</div>
              <select name="estado" id="estado" class="fi">
                <option value="">Selecione</option>
                <?php
                $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                foreach ($ufs as $uf): ?>
                <option value="<?= $uf ?>" <?= ($cliente['estado'] ?? '') == $uf ? 'selected' : '' ?>><?= $uf ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="fg">
              <div class="fl">Observação</div>
              <textarea name="observacao" id="observacao" class="fi" rows="3" placeholder="Observações sobre o cliente"><?= htmlspecialchars($cliente['observacao'] ?? '') ?></textarea>
            </div>
            
            <div class="btn-row" style="margin-top:16px">
              <a href="<?= $baseUrl ?>/clientes" class="btn btn-gray">Cancelar</a>
              <button type="submit" class="btn btn-cyan" id="btn-save-cliente">Salvar</button>
            </div>
          </form>
        </div>
      </div>
    </section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mascara de data-mask agora e global (scripts.js) -- ver bug-043.

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
        .catch(function(err) { console.error('CEP não encontrado'); });
    }
  });

  // Busca CNPJ (BrasilAPI)
  document.querySelector('[name="cpf_cnpj"]').addEventListener('blur', function(e) {
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
