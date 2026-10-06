<?php
/** index.php - Página inicial pública: apresenta o jogo e mostra destaques do banco. */
$titulo = 'Início';
$pagina = 'inicio';
require __DIR__ . '/includes/header.php';

// Os 3 registros mais recentes viram "destaques". Sem parâmetros do usuário, mas usamos prepare por padrão.
$stmt = $pdo->prepare('SELECT id, nome, categoria, descricao FROM itens_wiki ORDER BY criado_em DESC, id DESC LIMIT 3');
$stmt->execute();
$destaques = $stmt->fetchAll();
?>
<section class="painel hero">
  <h1>Bem-vindo ao Vale!</h1>
  <p>Você herdou a velha fazenda do seu avô na Vila dos Pelicanos. Plante, pesque, explore as minas e faça amizade com os moradores. Este guia reúne o que você precisa saber para prosperar a cada estação.</p>
  <p><a class="botao" href="items.php">Ver o guia de itens</a> <a class="botao botao-claro" href="geolocation.php">Quão longe é a vila?</a></p>
</section>

<section>
  <h2>Destaques do guia</h2>
  <?php if (!$destaques): ?>
    <p class="vazio">Ainda não há registros. Um administrador pode cadastrar o primeiro item no painel.</p>
  <?php else: ?>
  <div class="grade">
    <?php foreach ($destaques as $d): ?>
      <article class="painel cartao">
        <span class="etiqueta etiqueta-<?= e($d['categoria']) ?>"><?= e(CATEGORIAS[$d['categoria']] ?? $d['categoria']) ?></span>
        <h3><?= e($d['nome']) ?></h3>
        <p><?= e(mb_strimwidth($d['descricao'], 0, 120, '...', 'UTF-8')) ?></p>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<section class="painel">
  <h2>Como usar este guia</h2>
  <p>Busque cultivos, peixes, moradores e itens na página <a href="items.php">Itens</a>. Cadastre-se para ter uma conta de jogador; o conteúdo é mantido por administradores.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
