-- 048: Adicionar campo custo total ao evento
ALTER TABLE `eventos`
  ADD COLUMN `custo` decimal(10,2) DEFAULT NULL COMMENT 'Custo total manual do evento (nao calculado por itens)' AFTER `os_cliente`;
