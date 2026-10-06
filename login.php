<?php
/** login.php - Autenticação com password_verify() e sessão. */
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/conexao.php';

if (esta_logado()) { header('Location: index.php'); exit; }

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!csrf_valido()) {
        $erro = 'Sessão expirada. Recarregue a página e tente de novo.';
    } elseif ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nome, senha_hash, tipo FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            session_regenerate_id(true); // evita fixação de sessão
            $_SESSION['usuario_id']   = (int) $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            header('Location: ' . ($usuario['tipo'] === 'admin' ? 'admin.php' : 'index.php'));
            exit;
        }
        // Mensagem genérica: não revela se foi o e-mail ou a senha que errou.
        $erro = 'E-mail ou senha incorretos.';
    }
}

$titulo = 'Entrar';
$pagina = 'login';
require __DIR__ . '/includes/header.php';
?>
<section class="painel formulario">
  <h1>Entrar</h1>
  <?php if ($erro): ?><p class="aviso aviso-erro"><?= e($erro) ?></p><?php endif; ?>
  <form method="post" action="login.php" novalidate>
    <?= csrf_campo() ?>
    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
    <label for="senha">Senha</label>
    <input type="password" id="senha" name="senha" required>
    <button class="botao" type="submit">Entrar</button>
  </form>
  <p>Ainda não tem conta? <a href="register.php">Cadastre-se</a>.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
