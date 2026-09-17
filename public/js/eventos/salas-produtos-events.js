/**
 * Event Listeners para Salas e Produtos
 * Substituem onclick= handlers inline
 */

(function() {
  'use strict';

  // ===== Modal Actions (simples, sem parâmetros) =====

  // Botão "Fechar" do modal Sala
  document.addEventListener('click', function(e) {
    if (e.target.matches('[data-close-modal="modalSala"]')) {
      closeModal('modalSala');
    }
    if (e.target.matches('[data-close-modal="modalCategoria"]')) {
      closeModal('modalCategoria');
    }
  });

  // Ação "Salvar Sala"
  document.addEventListener('click', function(e) {
    if (e.target.matches('[data-action="salvar-sala"]')) {
      salvarSala();
    }
  });

  // Ação "Cancelar Edição de Sala"
  document.addEventListener('click', function(e) {
    if (e.target.matches('[data-action="cancelar-edicao-sala"]')) {
      cancelarEdicaoSala();
    }
  });

  // Ação "Salvar Categoria"
  document.addEventListener('click', function(e) {
    if (e.target.matches('[data-action="salvar-categoria"]')) {
      salvarCategoria();
    }
  });

  // Ação "Cancelar Edição de Categoria"
  document.addEventListener('click', function(e) {
    if (e.target.matches('[data-action="cancelar-edicao-categoria"]')) {
      cancelarEdicaoCategoria();
    }
  });

  // Ação "Fechar Modal de Obs Item"
  document.addEventListener('click', function(e) {
    if (e.target.matches('[data-action="fechar-obs-item"]')) {
      fecharModalItemObs();
    }
  });

  // Ação "Salvar Obs Item"
  document.addEventListener('click', function(e) {
    if (e.target.matches('[data-action="salvar-obs-item"]')) {
      salvarObsItem();
    }
  });

  // ===== Handlers com Parâmetros =====

  // Botão "Editar Sala" (com parâmetros: salaId, salaNome, salaObs)
  document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-editar-sala')) {
      const btn = e.target.closest('.btn-editar-sala');
      const salaId = parseInt(btn.dataset.salaId);
      const salaNome = btn.dataset.salaNome;
      const salaObs = btn.dataset.salaObs;

      editarSala(salaId, salaNome, salaObs);
    }
  });

  // Span "Editar Obs Item" (com parâmetros: itemId, produto, obs)
  // Nota: pode ser tanto span quanto button
  document.addEventListener('click', function(e) {
    if (e.target.matches('.btn-editar-obs-item') || e.target.closest('.btn-editar-obs-item')) {
      const el = e.target.closest('.btn-editar-obs-item');
      const itemId = parseInt(el.dataset.itemId);
      const itemProduto = el.dataset.itemProduto;
      const itemObs = el.dataset.itemObs;

      editarObsItem(itemId, itemProduto, itemObs);
    }
  });

  // Botão "Editar Sala Modal" (em lista de salas, com parâmetros: salaId, salaNome, salaObs)
  document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-editar-sala-modal')) {
      const btn = e.target.closest('.btn-editar-sala-modal');
      const salaId = parseInt(btn.dataset.salaId);
      const salaNome = btn.dataset.salaNome;
      const salaObs = btn.dataset.salaObs;

      editarSalaModal(salaId, salaNome, salaObs);
    }
  });

  // Botão "Excluir Sala" (com parâmetros: salaId, salaNome)
  document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-excluir-sala')) {
      const btn = e.target.closest('.btn-excluir-sala');
      const salaId = parseInt(btn.dataset.salaId);
      const salaNome = btn.dataset.salaNome;

      excluirSala(salaId, salaNome);
    }
  });

  // Botão "Editar Categoria Modal" (com parâmetros: catId, catNome)
  document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-editar-categoria-modal')) {
      const btn = e.target.closest('.btn-editar-categoria-modal');
      const catId = parseInt(btn.dataset.catId);
      const catNome = btn.dataset.catNome;

      editarCategoriaModal(catId, catNome);
    }
  });

  // Botão "Excluir Categoria" (com parâmetros: catId, catNome)
  document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-excluir-categoria')) {
      const btn = e.target.closest('.btn-excluir-categoria');
      const catId = parseInt(btn.dataset.catId);
      const catNome = btn.dataset.catNome;

      excluirCategoria(catId, catNome);
    }
  });

  console.log('[SALAS-PRODUTOS] Event listeners inicializados');
})();
