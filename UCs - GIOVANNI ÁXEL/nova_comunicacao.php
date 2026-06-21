<?php
// nova_comunicacao.php
session_start();
require_once 'config/db.php';

// Valida o ID da UC recebido via GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

// Busca nome da UC para exibição no formulário
$stmt = $pdo->prepare(
    "SELECT id, nome_uc FROM unidade_conservacao WHERE id = :id"
);
$stmt->execute([':id' => $id]);
$uc = $stmt->fetch();

if (!$uc) {
    header('Location: index.php');
    exit;
}

// AJUSTE 3: Captura mensagens de sucesso e erros da sessão
$sucesso = $_SESSION['sucesso'] ?? null;
$erros   = $_SESSION['erros']   ?? [];
unset($_SESSION['sucesso'], $_SESSION['erros']);

// Recupera dados preenchidos anteriormente (para repopular o form após erro)
$dadosAnteriores = $_SESSION['dados_form'] ?? [];
unset($_SESSION['dados_form']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Comunicação — <?= htmlspecialchars($uc['nome_uc']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="container">
        <a href="uc_detalhes.php?id=<?= (int)$uc['id'] ?>"
           class="btn-voltar">
            ← Voltar para a unidade
        </a>
        <h1>📢 Nova Comunicação</h1>
    </div>
</header>

<main class="container">

    <h2>Enviar Comunicação para:</h2>
    <h3 class="uc-nome-form">
        🌿 <?= htmlspecialchars($uc['nome_uc']) ?>
    </h3>

    <!-- AJUSTE 3: Exibe mensagem de sucesso -->
    <?php if ($sucesso): ?>
        <div class="alerta-sucesso">
            ✅ <?= htmlspecialchars($sucesso) ?>
        </div>
    <?php endif; ?>

    <!-- AJUSTE 3: Exibe lista de erros de validação -->
    <?php if (!empty($erros)): ?>
        <div class="alerta-erro">
            <strong>⚠️ Por favor, corrija os erros abaixo:</strong>
            <ul>
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form
        action="salvar_comunicacao.php"
        method="POST"
        class="form-comunicacao"
        novalidate
    >
        <!-- Campo oculto com ID da UC -->
        <input type="hidden"
               name="unidade_conservacao_id"
               value="<?= (int)$uc['id'] ?>">

        <div class="form-group">
            <label for="titulo">Título da Comunicação *</label>
            <input
                type="text"
                id="titulo"
                name="titulo"
                placeholder="Ex: Problema de desmatamento identificado"
                maxlength="150"
                value="<?= htmlspecialchars($dadosAnteriores['titulo'] ?? '') ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="descricao">Descrição Detalhada *</label>
            <textarea
                id="descricao"
                name="descricao"
                rows="6"
                placeholder="Descreva detalhadamente a situação observada..."
                required
            ><?= htmlspecialchars($dadosAnteriores['descricao'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="email">Seu E-mail *</label>
            <input
                type="email"
                id="email"
                name="email_comunicante"
                placeholder="seuemail@exemplo.com"
                value="<?= htmlspecialchars($dadosAnteriores['email'] ?? '') ?>"
                required
            >
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-enviar">
                📤 Enviar Comunicação
            </button>
            <a href="uc_detalhes.php?id=<?= (int)$uc['id'] ?>"
               class="btn-cancelar">
                Cancelar
            </a>
        </div>

    </form>

</main>

<footer>
    <div class="container">
        <p>© 2026 — Projeto Unidades de Conservação É Preciso | UNIVALI</p>
    </div>
</footer>

</body>
</html>
