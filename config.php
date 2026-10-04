<?php
// A chave fica em config.local.php (ignorado pelo Git).
// Copie config.local.example.php para config.local.php e cole sua chave.
// Chave gratuita em https://www.themoviedb.org/settings/api
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}
defined('TMDB_KEY') || define('TMDB_KEY', getenv('TMDB_KEY') ?: 'COLE_SUA_CHAVE_AQUI');
const TMDB_API   = 'https://api.themoviedb.org/3';
const TMDB_IMG   = 'https://image.tmdb.org/t/p/';
const CACHE_SEGS = 600; // 10 minutos

const TIPOS = [
    'todos'   => 'Tudo',
    'filme'   => 'Filmes',
    'serie'   => 'Séries',
    'anime'   => 'Animes',
    'desenho' => 'Desenhos',
];

function h(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Chama a API do TMDB com cache em arquivo. Retorna [] em caso de erro. */
function tmdb(string $caminho, array $params = []): array {
    $params['api_key']  = TMDB_KEY;
    $params['language'] = $params['language'] ?? 'pt-BR';
    $url = TMDB_API . $caminho . '?' . http_build_query(array_filter($params, fn($v) => $v !== null));

    $dir = __DIR__ . '/cache';
$dir = sys_get_temp_dir() . '/sessaolivre-cache';    $arq = $dir . '/' . md5($url) . '.json';

    if (is_file($arq) && time() - filemtime($arq) < CACHE_SEGS) {
        return json_decode(file_get_contents($arq), true) ?: [];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
    $res    = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($res === false || $status !== 200) return [];
    @file_put_contents($arq, $res);
    return json_decode($res, true) ?: [];
}

function normalizar(array $r, string $media): array {
    $data = $r['release_date'] ?? $r['first_air_date'] ?? '';
    return [
        'id'     => $r['id'],
        'media'  => $media,
        'titulo' => $r['title'] ?? $r['name'] ?? 'Sem título',
        'ano'    => substr($data, 0, 4),
        'poster' => $r['poster_path'] ?? null,
        'nota'   => round($r['vote_average'] ?? 0, 1),
        'generos'=> $r['genre_ids'] ?? [],
        'lang'   => $r['original_language'] ?? '',
        'pop'    => $r['popularity'] ?? 0,
    ];
}

/**
 * Busca por tipo.
 * - anime   = animação (gênero 16) com idioma original japonês
 * - desenho = animação (gênero 16) de outras origens
 * - série   = TV sem animação
 * Retorna [itens, total_de_paginas].
 */
function buscar(string $q, string $tipo, int $pag): array {
    $midias = match ($tipo) {
        'filme' => ['movie'],
        'serie' => ['tv'],
        default => ['movie', 'tv'],
    };
    $animacao = in_array($tipo, ['anime', 'desenho'], true);

    $itens = [];
    $total = 1;
    foreach ($midias as $m) {
        if ($q !== '') {
            $d = tmdb("/search/$m", ['query' => $q, 'page' => $pag, 'include_adult' => 'false']);
        } else {
            $d = tmdb("/discover/$m", [
                'sort_by'                => 'popularity.desc',
                'page'                   => $pag,
                'include_adult'          => 'false',
                'with_genres'            => $animacao ? 16 : null,
                'with_original_language' => $tipo === 'anime' ? 'ja' : null,
            ]);
        }
        $total = max($total, (int)($d['total_pages'] ?? 1));
        foreach ($d['results'] ?? [] as $r) {
            $itens[] = normalizar($r, $m);
        }
    }

    $itens = array_filter($itens, function ($i) use ($tipo) {
        $anim = in_array(16, $i['generos'], true);
        return match ($tipo) {
            'serie'   => !$anim,
            'anime'   => $anim && $i['lang'] === 'ja',
            'desenho' => $anim && $i['lang'] !== 'ja',
            default   => true,
        };
    });

    usort($itens, fn($a, $b) => $b['pop'] <=> $a['pop']);
    return [array_values($itens), min($total, 500)];
}
