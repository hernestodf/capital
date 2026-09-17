<?php

return [
    // =============================================
    // IDENTIDADE DO SISTEMA
    // =============================================
    'name' => 'SisLoc',
    'title' => 'SisLoc - Sistema de Gestão de Locações',
    'version' => '3.0.0',
    
    // Logo como imagem (PNG) - deixe vazio para usar logo_text
    'logo' => '',  // Ex: '/assets/logo.png'
    
    // Texto do logo se não tiver imagem (fallback)
    'logo_text' => 'SL',
    
    // Nome da empresa (para display)
    'company' => 'SisLoc',
    
    // =============================================
    // TEMA DO SISTEMA
    // =============================================
    'theme' => [
        // Tema ativo: default | pink | blue | green | dark
        'active' => 'green',
        
        // Temas disponíveis
        'themes' => [
            'default' => [
                'name' => 'Cyan (Padrão)',
                'primary' => '#0B6E8C',
                'primary_glow' => 'rgba(11,110,140,0.28)',
                'sidebar_bg' => '#0F172A',
                'sidebar_hover' => 'rgba(255,255,255,0.1)',
                'sidebar_text' => 'rgba(255,255,255,0.7)',
                'surface' => '#E0E8F2',
                'background' => '#EDF1F7',
                'text' => '#1E2E45',
                'text_light' => '#4A6080',
            ],
            
            'pink' => [
                'name' => 'Rosa Chamativo',
                'primary' => '#E11D48',
                'primary_glow' => 'rgba(225,29,72,0.28)',
                'sidebar_bg' => '#1E1B4B',
                'sidebar_hover' => 'rgba(225,29,72,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#FCE7F3',
                'background' => '#FDF2F8',
                'text' => '#1E1B4B',
                'text_light' => '#4C1D4E',
            ],
            
            'blue' => [
                'name' => 'Blue Professional',
                'primary' => '#1D4ED8',
                'primary_glow' => 'rgba(29,78,216,0.28)',
                'sidebar_bg' => '#1E3A5F',
                'sidebar_hover' => 'rgba(29,78,216,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#DBEAFE',
                'background' => '#EFF6FF',
                'text' => '#1E3A8A',
                'text_light' => '#3B82F6',
            ],
            
            'green' => [
                'name' => 'Green Nature',
                'primary' => '#059669',
                'primary_glow' => 'rgba(5,150,105,0.28)',
                'sidebar_bg' => '#064E3B',
                'sidebar_hover' => 'rgba(5,150,105,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#D1FAE5',
                'background' => '#ECFDF5',
                'text' => '#064E3B',
                'text_light' => '#059669',
            ],
            
            'amber' => [
                'name' => 'Âmbar Quente',
                'primary' => '#D97706',
                'primary_glow' => 'rgba(217,119,6,0.28)',
                'sidebar_bg' => '#1C1917',
                'sidebar_hover' => 'rgba(217,119,6,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#FEF3C7',
                'background' => '#FFFBEB',
                'text' => '#451A03',
                'text_light' => '#92400E',
            ],
            
            'red' => [
                'name' => 'Vermelho Intenso',
                'primary' => '#DC2626',
                'primary_glow' => 'rgba(220,38,38,0.28)',
                'sidebar_bg' => '#1C0C0C',
                'sidebar_hover' => 'rgba(220,38,38,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#FEE2E2',
                'background' => '#FEF2F2',
                'text' => '#450A0A',
                'text_light' => '#B91C1C',
            ],
            
            'slate' => [
                'name' => 'Cinza Elegante',
                'primary' => '#475569',
                'primary_glow' => 'rgba(71,85,105,0.28)',
                'sidebar_bg' => '#0F172A',
                'sidebar_hover' => 'rgba(71,85,105,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#F1F5F9',
                'background' => '#F8FAFC',
                'text' => '#1E293B',
                'text_light' => '#64748B',
            ],
            
            'indigo' => [
                'name' => 'Índigo Profundo',
                'primary' => '#4F46E5',
                'primary_glow' => 'rgba(79,70,229,0.28)',
                'sidebar_bg' => '#1E1B4B',
                'sidebar_hover' => 'rgba(79,70,229,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#E0E7FF',
                'background' => '#EEF2FF',
                'text' => '#1E1B4B',
                'text_light' => '#6366F1',
            ],
            
            'teal' => [
                'name' => 'Turquesa Oceano',
                'primary' => '#0D9488',
                'primary_glow' => 'rgba(13,148,136,0.28)',
                'sidebar_bg' => '#042F2E',
                'sidebar_hover' => 'rgba(13,148,136,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#CCFBF1',
                'background' => '#F0FDFA',
                'text' => '#134E4A',
                'text_light' => '#0D9488',
            ],
            
            'rose' => [
                'name' => 'Rosa Suave',
                'primary' => '#E11D48',
                'primary_glow' => 'rgba(244,63,94,0.28)',
                'sidebar_bg' => '#1A0A0E',
                'sidebar_hover' => 'rgba(244,63,94,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#FFE4E6',
                'background' => '#FFF1F2',
                'text' => '#4C0519',
                'text_light' => '#E11D48',
            ],
            
            'emerald' => [
                'name' => 'Esmeralda Vibrante',
                'primary' => '#10B981',
                'primary_glow' => 'rgba(16,185,129,0.28)',
                'sidebar_bg' => '#022C22',
                'sidebar_hover' => 'rgba(16,185,129,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#A7F3D0',
                'background' => '#ECFDF5',
                'text' => '#064E3B',
                'text_light' => '#059669',
            ],
        ],
    ],
    
    // =============================================
    // OUTRAS CONFIGURAÇÕES
    // =============================================
    'timezone' => 'America/Sao_Paulo',
    'session_lifetime' => 120,
];