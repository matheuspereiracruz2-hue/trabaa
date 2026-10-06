<?php
/**
 * admin.php - Painel administrativo (CRUD de itens_wiki). Só administradores logados entram.
 * Create: formulário sem ?editar | Read: tabela | Update: ?editar=ID | Delete: botão com POST + CSRF.
 */
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/conexao.php';
exigir_admin(); // se não for admin, redireciona antes de qualquer saída

/** Valida e limpa os dados do formulário. Retorna [dados_limpos, lista_de_erros]. */
function validar_item(array $post): array
{
    $erros = [];
    $nome      = trim($post['nome'] ?? '');
    $categoria = $post['categoria'] ?? '';
    $estacao   = $post['estacao'] ?? '';
    $preco     = trim($post['preco_venda'] ?? '');
    $descricao = trim($post['descricao'] ?? '');

    $tam = mb_strlen($nome, 'UTF-8');
    if ($tam < 2 || $tam > 80) {
        $erros[] = 'O nome deve ter entre 2 e 80 caracteres.';
    }
    if (!array_key_exists($categoria, CATEGORIAS)) {
        $erros[] = 'Escolha uma categoria válida.';
    }
    if (!in_array($estacao, ESTACOES, true)) {
        $erros[] = 'Escolha uma estação válida.';
    }
    // Preço é opcional (moradores não têm). Se vier preenchido, precisa ser inteiro >= 0.
    if ($preco === '') {
        $preco = null;
    } elseif (!ctype_digit($preco) || (int) $preco > 1000000) {
        $erros[] = 'O preço deve ser um número inteiro entre 0 e 1.000.000.';
    } else {
        $preco = (int) $preco;
    }
    $tamDesc = mb_strlen($descricao, 'UTF-8');
    if ($tamDesc < 10 || $tamDesc > 1000) {
        $erros[] = 'A descrição deve ter entre 10 e 1000 caracteres.';
    }
    return [compact('nome', 'categoria', 'estacao', 'preco', 'descricao'), $erros];
}

$erros = [];
$item  = ['id' => 0, 'nome' => '', 'categoria' => 'cultivo', 'estacao' => 'Todas', 'preco_venda' => '', 'descricao' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        mensagem('Sessão expirada. Tente novamente.', 'erro');
        header('Location: admin.php');
        exit;
    }
    $acao = $_POST['acao'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    if ($acao === 'excluir' && $id > 0) {
        // DELETE
        $stmt = $pdo->prepare('DELETE FROM itens_wiki WHERE id = :id');
        $stmt->execute([':id' => $id]);
        mensagem('Registro excluído.');
        header('Location: admin.php');
        exit;
    }

    if ($acao === 'salvar') {
        [$d, $erros] = validar_item($_POST);
        if (!$erros) {
            if ($id > 0) {
                // UPDATE
                $stmt = $pdo->prepare(
                    'UPDATE itens_wiki SET nome=:nome, categoria=:cat, estacao=:est, preco_venda=:preco, descricao=:desc WHERE id=:id'
                );
                $stmt->execute([':nome' => $d['nome'], ':cat' => $d['categoria'], ':est' => $d['estacao'],
                                ':preco' => $d['preco'], ':desc' => $d['descricao'], ':id' => $id]);
                mensagem('Registro atualizado.');
            } else {
                // CREATE
                $stmt = $pdo->prepare(
                    'INSERT INTO itens_wiki (nome, categoria, estacao, preco_venda, descricao, criado_por)
                     VALUES (:nome, :cat, :est, :preco, :desc, :autor)'
                );
                $stmt->execute([':nome' => $d['nome'], ':cat' => $d['categoria'], ':est' => $d['estacao'],
                                ':preco' => $d['preco'], ':desc' => $d['descricao'], ':autor' => $_SESSION['usuario_id']]);
                mensagem('Registro cadastrado.');
            }
            header('Location: admin.php');
            exit;
        }
        // Com erro: devolve o que o admin digitou para ele corrigir.
        $item = ['id' => $id, 'nome' => $d['nome'], 'categoria' => $d['categoria'], 'estacao' => $d['estacao'],
                 'preco_venda' => $d['preco'] ?? '', 'descricao' => $d['descricao']];
    }
} elseif (isset($_GET['editar'])) {
    // Carrega o registro para o formulário de edição
    $stmt = $pdo->prepare('SELECT * FROM itens_wiki WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['editar']]);
    $encontrado = $stmt->fetch();
    if ($encontrado) {
        $item = $encontrado;
    } else {
        mensagem('Registro não encontrado.', 'erro');
        header('Location: admin.php');
        exit;
    }
}

// READ: lista todos os registros
$lista = $pdo->query('SELECT id, nome, categoria, estacao, preco_venda FROM itens_wiki ORDER BY id DESC')->fetchAll();

$titulo = 'Painel administrativo';
$pagina = 'admin';
require __DIR__ . '/includes/header.php';
?>
<h1>Painel administrativo</h1>

<section class="painel formulario">
  <h2><?= $item['id'] ? 'Editar registro' : 'Novo registro' ?></h2>
  <?php foreach ($erros as $erro): ?>
    <p class="aviso aviso-erro"><?= e($erro) ?></p>
  <?php endforeach; ?>
  <form method="post" action="admin.php" novalidate>
    <?= csrf_campo() ?>
    <input type="hidden" name="acao" value="salvar">
    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">

    <label for="nome">Nome</label>
    <input type="text" id="nome" name="nome" value="<?= e($item['nome']) ?>" maxlength="80" required>

    <label for="categoria">Categoria</label>
    <select id="categoria" name="categoria">
      <?php foreach (CATEGORIAS as $chave => $rotulo): ?>
        <option value="<?= e($chave) ?>" <?= $item['categoria'] === $chave ? 'selected' : '' ?>><?= e($rotulo) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="estacao">Estação</label>
    <select id="estacao" name="estacao">
      <?php foreach (ESTACOES as $est): ?>
        <option value="<?= e($est) ?>" <?= $item['estacao'] === $est ? 'selected' : '' ?>><?= e($est) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="preco_venda">Preço de venda em ouro (deixe vazio para moradores)</label>
    <input type="text" inputmode="numeric" id="preco_venda" name="preco_venda" value="<?= e($item['preco_venda']) ?>">

    <label for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao" rows="4" maxlength="1000" required><?= e($item['descricao']) ?></textarea>

    <button class="botao" type="submit"><?= $item['id'] ? 'Salvar alterações' : 'Cadastrar registro' ?></button>
    <?php if ($item['id']): ?><a class="botao botao-claro" href="admin.php">Cancelar edição</a><?php endif; ?>
  </form>
</section>

<section>
  <h2>Registros (<?= count($lista) ?>)</h2>
  <?php if (!$lista): ?>
    <p class="vazio">Nenhum registro ainda. Use o formulário acima para cadastrar o primeiro.</p>
  <?php else: ?>
  <div class="tabela-rolagem">
  <table class="tabela">
    <thead><tr><th>Nome</th><th>Categoria</th><th>Estação</th><th>Preço</th><th>Ações</th></tr></thead>
    <tbody>
    <?php foreach ($lista as $l): ?>
      <tr>
        <td><?= e($l['nome']) ?></td>
        <td><?= e(CATEGORIAS[$l['categoria']] ?? $l['categoria']) ?></td>
        <td><?= e($l['estacao']) ?></td>
        <td><?= $l['preco_venda'] !== null ? (int) $l['preco_venda'] . ' g' : '-' ?></td>
        <td class="acoes">
          <a class="botao botao-pequeno" href="admin.php?editar=<?= (int) $l['id'] ?>">Editar</a>
          <form method="post" action="admin.php" class="form-excluir">
            <?= csrf_campo() ?>
            <input type="hidden" name="acao" value="excluir">
            <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
            <button class="botao botao-pequeno botao-perigo" type="submit" data-confirmar="Excluir &quot;<?= e($l['nome']) ?>&quot;?">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
