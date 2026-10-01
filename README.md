# Sessão Livre

Site de busca de filmes, séries, animes e desenhos feito em PHP puro, usando a API do [TMDB](https://www.themoviedb.org/).

## O que faz

- Busca por título em filmes, séries, animes e desenhos
- Filtro por tipo de conteúdo
- Lista dos títulos mais populares quando nenhuma busca é feita
- Página de detalhes com sinopse, nota, duração, gêneros, elenco e trailer
- Paginação dos resultados
- Cache das respostas da API (10 minutos)
- Layout responsivo, que funciona no celular

## Como os tipos são separados

O TMDB não tem categorias próprias para anime e desenho, então o site usa esta regra:

| Tipo    | Regra                                            |
|---------|--------------------------------------------------|
| Filme   | Filmes em geral                                  |
| Série   | Séries de TV que não são animação                |
| Anime   | Animação (gênero 16) com idioma original japonês |
| Desenho | Animação (gênero 16) de outras origens           |

## Requisitos

- PHP 8.0 ou superior
- Extensões `curl` e `openssl` ativadas
- Uma chave gratuita da API do TMDB

## Como rodar

1. Clone o repositório:
```
   git clone https://github.com/SEU-USUARIO/SiteBuscaFilmes.git
   cd SiteBuscaFilmes
```

2. Crie sua chave em [themoviedb.org/settings/api](https://www.themoviedb.org/settings/api) (é grátis). Use a **Chave da API** (v3), não o token de leitura.

3. Crie o arquivo de configuração local:
```
   cp config.local.example.php config.local.php
```
   No Windows (PowerShell): `Copy-Item config.local.example.php config.local.php`

4. Abra o `config.local.php` e cole sua chave:
```php
   const TMDB_KEY = 'sua-chave-aqui';
```

5. Inicie o servidor embutido do PHP:
```
   php -S localhost:8000
```

6. Acesse `http://localhost:8000`.

## Estrutura

```
index.php                  Página principal: busca, filtros e grade de resultados
detalhes.php               Página de detalhes de um filme ou série
config.php                 Chamadas à API, cache e lógica de busca por tipo
config.local.example.php   Modelo para o arquivo com a sua chave
style.css                  Estilos
```

## Problemas comuns

**`Call to undefined function curl_init()`**: a extensão cURL está desativada no PHP. No `php.ini`, ative `extension=curl` e `extension=openssl`.

**Página abre mas não mostra nenhum título**: confira se a chave está correta no `config.local.php`. No Windows, também pode faltar o certificado SSL: baixe o `cacert.pem` em [curl.se/docs/caextract.html](https://curl.se/docs/caextract.html) e aponte `curl.cainfo` e `openssl.cafile` para ele no `php.ini`.

## Créditos

Este produto usa a API do TMDB, mas não é endossado ou certificado por ele. Todos os dados e imagens pertencem ao [TMDB](https://www.themoviedb.org/).