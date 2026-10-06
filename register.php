<?php
/** register.php - Cadastro de usuários. O PRIMEIRO usuário cadastrado vira administrador. */
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/conexao.php';

if (esta_logado()) { header('Location: index.php'); exit; }

$erros = [];
$nome = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $erros[] = 'Sessão expirada. Recarregue a página e tente de novo.';
    }
    $nome   = trim($_POST['nome'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $senha  = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';

    // ----- Validação no servidor -----
    $tamanhoNome = mb_strlen($nome, 'UTF-8');
    if ($tamanhoNome < 3 || $tamanhoNome > 50) {
        $erros[] = 'O nome deve ter entre 3 e 50 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (strlen($senha) < 8) {
        $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
    }
    if ($senha !== $senha2) {
        $erros[] = 'As senhas não conferem.';
    }

    // E-mail já cadastrado? (prepared statement)
    if (!$erros) {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }
    }

    if (!$erros) {
        // Se a tabela está vazia, este usuário será o administrador do sistema.
        $total = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        $tipo  = $total === 0 ? 'admin' : 'usuario';

        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES (:nome, :email, :hash, :tipo)'
        );
        $stmt->execute([
            ':nome'  => $nome,
            ':email' => $email,
            ':hash'  => password_hash($senha, PASSWORD_DEFAULT), // nunca guardamos a senha pura
            ':tipo'  => $tipo,
        ]);
        mensagem($tipo === 'admin'
            ? 'Conta de administrador criada! Faça login para acessar o painel.'
            : 'Cadastro concluído! Faça login para continuar.');
        header('Location: login.php');
        exit;
    }
}

$titulo = 'Cadastrar';
$pagina = 'cadastro';
require __DIR__ . '/includes/header.php';
?>
<section class="painel formulario">
  <h1>Criar conta</h1>
  <?php foreach ($erros as $erro): ?>
    <p class="aviso aviso-erro"><?= e($erro) ?></p>
  <?php endforeach; ?>
  <form method="post" action="register.php" novalidate>
    <?= csrf_campo() ?>
    <label for="nome">Nome</label>
    <input type="text" id="nome" name="nome" value="<?= e($nome) ?>" maxlength="50" required>
    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="120" required>
    <label for="senha">Senha (mínimo 8 caracteres)</label>
    <input type="password" id="senha" name="senha" minlength="8" required>
    <label for="senha2">Repita a senha</label>
    <input type="password" id="senha2" name="senha2" minlength="8" required>
    <button class="botao" type="submit">Criar conta</button>
  </form>
  <p>Já tem conta? <a href="login.php">Entre aqui</a>.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
