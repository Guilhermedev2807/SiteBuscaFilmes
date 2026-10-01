<?php
require __DIR__ . '/config.php';

$q    = trim($_GET['q'] ?? '');
$tipo = $_GET['tipo'] ?? 'todos';
if (!isset(TIPOS[$tipo])) $tipo = 'todos';
$pag  = max(1, min(500, (int)($_GET['pagina'] ?? 1)));

[$itens, $total] = buscar($q, $tipo, $pag);

function link_para(array $mudar): string {
    global $q, $tipo;
    $p = array_merge(['q' => $q, 'tipo' => $tipo, 'pagina' => 1], $mudar);
    if ($p['q'] === '') unset($p['q']);
    if ($p['tipo'] === 'todos') unset($p['tipo']);
    if ($p['pagina'] <= 1) unset($p['pagina']);
    return '?' . http_build_query($p);
}

$titulo = $q !== '' ? 'Resultados para “' . $q . '”' : 'Mais populares: ' . TIPOS[$tipo];
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sessão Livre – busca de filmes, séries, animes e desenhos</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;800&family=Instrument+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topo">
  <a class="marca" href="./">Sessão Livre</a>
  <form class="busca" method="get" role="search">
    <input type="search" name="q" value="<?= h($q) ?>" placeholder="Busque um título" aria-label="Buscar título" autofocus>
    <?php if ($tipo !== 'todos'): ?><input type="hidden" name="tipo" value="<?= h($tipo) ?>"><?php endif; ?>
    <button type="submit">Buscar</button>
  </form>
  <nav class="tipos" aria-label="Tipo de conteúdo">
    <?php foreach (TIPOS as $k => $nome): ?>
      <a href="<?= h(link_para(['tipo' => $k])) ?>" class="<?= $k === $tipo ? 'ativo' : '' ?>"
         <?= $k === $tipo ? 'aria-current="page"' : '' ?>><?= h($nome) ?></a>
    <?php endforeach; ?>
  </nav>
</header>

<main>
  <?php if (TMDB_KEY === 'COLE_SUA_CHAVE_AQUI'): ?>
    <p class="aviso">Falta configurar a chave da API. Abra <code>config.php</code> e cole sua chave do TMDB em <code>TMDB_KEY</code>.</p>
  <?php endif; ?>

  <h1><?= h($titulo) ?></h1>

  <?php if (!$itens): ?>
    <p class="vazio">Nada encontrado. Tente outro título ou troque o tipo de conteúdo.</p>
  <?php else: ?>
    <ul class="grade">
      <?php foreach ($itens as $i): ?>
        <li>
          <a href="detalhes.php?media=<?= h($i['media']) ?>&id=<?= (int)$i['id'] ?>">
            <div class="capa">
              <?php if ($i['poster']): ?>
                <img src="<?= TMDB_IMG ?>w342<?= h($i['poster']) ?>" alt="Pôster de <?= h($i['titulo']) ?>" loading="lazy">
              <?php else: ?>
                <span class="sem-capa">Sem pôster</span>
              <?php endif; ?>
              <?php if ($i['nota'] > 0): ?><span class="nota"><?= $i['nota'] ?></span><?php endif; ?>
            </div>
            <strong><?= h($i['titulo']) ?></strong>
            <small><?= $i['media'] === 'tv' ? 'Série' : 'Filme' ?><?= $i['ano'] ? ', ' . h($i['ano']) : '' ?></small>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <nav class="paginas" aria-label="Páginas">
      <?php if ($pag > 1): ?><a href="<?= h(link_para(['pagina' => $pag - 1])) ?>">Anterior</a><?php endif; ?>
      <span>Página <?= $pag ?> de <?= $total ?></span>
      <?php if ($pag < $total): ?><a href="<?= h(link_para(['pagina' => $pag + 1])) ?>">Próxima</a><?php endif; ?>
    </nav>
  <?php endif; ?>
</main>

<footer>Dados e imagens fornecidos pelo TMDB. Este produto usa a API do TMDB, mas não é endossado ou certificado por ele.</footer>
</body>
</html>
