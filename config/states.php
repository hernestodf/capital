<?php
/**
 * Configuração de instância do Capital.
 *
 * O arquivo original era o registro multi-instância herdado do template do
 * SisLoc e continha hostnames, usuários de banco e paths reais de outro
 * cliente (ProFox) — o Capital nunca teve relação com esse ecossistema,
 * só herdou o arquivo por ser baseado no mesmo framework. Removido.
 *
 * db_name/db_user/db_pass não são definidos aqui: o Capital já define
 * DB_ONLINE_* diretamente no .env, que tem prioridade sobre este arquivo
 * (ver Env::applyStateConfig()).
 */
return [
    'default' => 'CAPITAL',

    'states' => [
        'CAPITAL' => [
            'name'     => 'Capital',
            'hostname' => 'capital.sisloc.online',
            'base_url' => 'https://capital.sisloc.online/public',
            'timezone' => 'America/Sao_Paulo',
        ],
    ],

    'hostname_map' => [
        'capital.sisloc.online' => 'CAPITAL',
        'localhost'             => null, // Usa configurações locais
    ],
];
