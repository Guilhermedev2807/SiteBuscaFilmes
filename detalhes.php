<?php
require __DIR__ . '/config.php';

$media = ($_GET['media'] ?? '') === 'tv' ? 'tv' : 'movie';
$id    = (int)($_GET['id'] ?? 0);

$d = $id > 0 ? tmdb("/$media/$id", [
    'append_to_response'     => 'credits,videos',
    'include_video_language' => 'pt,en,null',
]) : [];

if (!$d) {
    http_response_code(404);
    echo '<p style="font-family:sans-serif;padding:2rem">Título não encontrado. <a href="./">Voltar à busca</a></p>';
    exit;
}

$titulo = $d['title'] ?? $d['name'] ?? '';
$original = $d['original_title'] ?? $d['original_name'] ?? '';
$data   = $d['release_date'] ?? $d['first_air_date'] ?? '';
$ano    = substr($data, 0, 4);
$generos = array_column($d['genres'] ?? [], 'name');

if ($media === 'tv') {
    $duracao = ($d['number_of_seasons'] ?? 0) . ' temporada(s), ' . ($d['number_of_episodes'] ?? 0) . ' episódios';
} else {
    $min = (int)($d['runtime'] ?? 0);
    $duracao = $min ? intdiv($min, 60) . 'h ' . ($min % 60) . 'min' : '';
}

$trailer = null;
foreach ($d['videos']['results'] ?? [] as $v) {
    if (($v['site'] ?? '') === 'YouTube' && ($v['type'] ?? '') === 'Trailer') { $trailer = $v['key']; break; }
}
$elenco = array_slice($d['credits']['cast'] ?? [], 0, 8);
$fundo  = $d['backdrop_path'] ?? null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($titulo) ?> – Sessão Livre</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;800&family=Instrument+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topo"><a class="marca" href="./">Sessão Livre</a><a class="voltar" href="javascript:history.back()">Voltar aos resultados</a></header>

<main class="detalhe">
  <?php if ($fundo): ?>
    <div class="fundo" style="background-image:url('<?= TMDB_IMG ?>w1280<?= h($fundo) ?>')"></div>
  <?php endif; ?>

  <div class="ficha">
    <?php if (!empty($d['poster_path'])): ?>
      <img class="poster" src="<?= TMDB_IMG ?>w500<?= h($d['poster_path']) ?>" alt="Pôster de <?= h($titulo) ?>">
    <?php endif; ?>

    <div class="texto">
      <h1><?= h($titulo) ?><?= $ano ? ' <span>(' . h($ano) . ')</span>' : '' ?></h1>
      <?php if ($original && $original !== $titulo): ?><p class="original"><?= h($original) ?></p><?php endif; ?>
      <p class="meta">
        <?= ($d['vote_average'] ?? 0) > 0 ? 'Nota ' . round($d['vote_average'], 1) . ' de 10' : 'Sem nota' ?>
        <?= $duracao ? ' &nbsp;|&nbsp; ' . h($duracao) : '' ?>
        <?= $generos ? ' &nbsp;|&nbsp; ' . h(implode(', ', $generos)) : '' ?>
      </p>
      <?php if (!empty($d['tagline'])): ?><p class="tagline"><?= h($d['tagline']) ?></p><?php endif; ?>
      <h2>Sinopse</h2>
      <p><?= h($d['overview'] ?: 'Sinopse ainda não disponível em português.') ?></p>

      <?php if ($elenco): ?>
        <h2>Elenco</h2>
        <p><?= h(implode(', ', array_column($elenco, 'name'))) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($trailer): ?>
    <section class="trailer">
      <h2>Trailer</h2>
      <div class="video">
        <iframe src="https://www.youtube-nocookie.com/embed/<?= h($trailer) ?>" title="Trailer de <?= h($titulo) ?>" allowfullscreen loading="lazy"></iframe>
      </div>
    </section>
  <?php endif; ?>
</main>

<footer>Dados e imagens fornecidos pelo TMDB.</footer>
</body>
</html>
