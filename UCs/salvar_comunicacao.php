<?php
// salvar_comunicacao.php
session_start();
require_once 'config/db.php';

// Aceita apenas requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Coleta e sanitiza os dados recebidos
$ucId    = filter_input(INPUT_POST, 'unidade_conservacao_id', FILTER_VALIDATE_INT);
$titulo  = trim($_POST['titulo']           ?? '');
$descr   = trim($_POST['descricao']        ?? '');
$email   = trim($_POST['email_comunicante'] ?? '');

// ==========================================
// AJUSTE 3: Validações com mensagens claras
// ==========================================
$erros = [];

if (!$ucId) {
    $erros[] = "Unidade de conservação inválida ou não informada.";
}
if (empty($titulo)) {
    $erros[] = "O título da comunicação é obrigatório.";
} elseif (strlen($titulo) > 150) {
    $erros[] = "O título deve ter no máximo 150 caracteres.";
}
if (empty($descr)) {
    $erros[] = "A descrição detalhada é obrigatória.";
} elseif (strlen($descr) < 10) {
    $erros[] = "A descrição deve ter ao menos 10 caracteres.";
}
if (empty($email)) {
    $erros[] = "O e-mail é obrigatório.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "O e-mail informado não é válido.";
}

// Se houver erros, redireciona com mensagens e repopula o formulário
if (!empty($erros)) {
    $_SESSION['erros']      = $erros;
    $_SESSION['dados_form'] = [
        'titulo'    => $titulo,
        'descricao' => $descr,
        'email'     => $email,
    ];
    header("Location: nova_comunicacao.php?id=" . (int)$ucId);
    exit;
}

// ==========================================
// Persiste a comunicação no banco de dados
// ==========================================
try {
    $stmt = $pdo->prepare("
        INSERT INTO comunicacao
            (titulo_comunicacao, descricao_comunicacao, email_comunicante_comunicacao,
             unidade_conservacao_id, data_hora_envio_comunicacao, status_comunicacao)
        VALUES
            (:titulo, :descricao, :email, :uc_id, NOW(), 0)
    ");

    $stmt->execute([
        ':titulo'    => $titulo,
        ':descricao' => $descr,
        ':email'     => $email,
        ':uc_id'     => $ucId,
    ]);

    // Sucesso — redireciona para a página de detalhes da UC
    $_SESSION['sucesso'] =
        "Comunicação enviada com sucesso! " .
        "Em breve será analisada pelos responsáveis.";

    header("Location: uc_detalhes.php?id=" . (int)$ucId);
    exit;

} catch (PDOException $e) {
    $_SESSION['erros']      = [
        "Erro interno ao salvar a comunicação. Tente novamente."
    ];
    $_SESSION['dados_form'] = [
        'titulo'    => $titulo,
        'descricao' => $descr,
        'email'     => $email,
    ];
    header("Location: nova_comunicacao.php?id=" . (int)$ucId);
    exit;
}
