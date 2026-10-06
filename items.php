<?php
/** items.php - Listagem pública dos registros, com busca por nome e filtro por categoria. */
$titulo = 'Itens';
$pagina = 'itens';
require __DIR__ . '/includes/header.php';

// Lê os filtros da URL (GET) e valida: a categoria só vale se existir na lista fixa.
$busca     = trim($_GET['busca'] ?? '');
$categoria = $_GET['categoria'] ?? '';
if (!array_key_exists($categoria, CATEGORIAS)) {
    $categoria = '';
}

// Monta a consulta de forma dinâmica, mas SEMPRE com parâmetros (prepared statement).
$sql    = 'SELECT nome, categoria, estacao, preco_venda, descricao FROM itens_wiki WHERE 1=1';
$params = [];
if ($busca !== '') {
    $sql .= ' AND nome LIKE :busca';
    $params[':busca'] = '%' . $busca . '%';
}
if ($categoria !== '') {
    $sql .= ' AND categoria = :categoria';
    $params[':categoria'] = $categoria;
}
$sql .= ' ORDER BY nome ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$itens = $stmt->fetchAll();
?>
<h1>Guia de itens</h1>

<form class="painel filtro" method="get" action="items.php">
  <label for="busca">Nome</label>
  <input type="search" id="busca" name="busca" value="<?= e($busca) ?>" maxlength="80" placeholder="Ex.: Abóbora">
  <label for="categoria">Categoria</label>
  <select id="categoria" name="categoria">
    <option value="">Todas</option>
    <?php foreach (CATEGORIAS as $chave => $rotulo): ?>
      <option value="<?= e($chave) ?>" <?= $categoria === $chave ? 'selected' : '' ?>><?= e($rotulo) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="botao" type="submit">Filtrar</button>
</form>

<?php if (!$itens): ?>
  <p class="vazio">Nenhum registro encontrado. Tente outro nome ou categoria.</p>
<?php else: ?>
  <p><?= count($itens) ?> registro(s) encontrado(s).</p>
  <div class="grade">
  <?php foreach ($itens as $i): ?>
    <article class="painel cartao">
      <span class="etiqueta etiqueta-<?= e($i['categoria']) ?>"><?= e(CATEGORIAS[$i['categoria']] ?? $i['categoria']) ?></span>
      <h3><?= e($i['nome']) ?></h3>
      <p><?= nl2br(e($i['descricao'])) ?></p>
      <p class="meta">Estação: <?= e($i['estacao']) ?>
        <?php if ($i['preco_venda'] !== null): ?> - Venda: <strong><?= (int) $i['preco_venda'] ?> g</strong><?php endif; ?>
      </p>
    </article>
  <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
