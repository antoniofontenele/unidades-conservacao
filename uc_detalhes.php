<?php
// uc_detalhes.php
require_once 'config/db.php';

// Valida e sanitiza o ID recebido via GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

// Busca dados completos da UC + instituição
$stmt = $pdo->prepare("
    SELECT
        uc.id,
        uc.nome_uc,
        uc.data_criacao,
        uc.descricao,
        uc.imagem_url,
        i.nome_instituicao,
        i.email_instituicao
    FROM unidade_conservacao uc
    INNER JOIN instituicao i ON i.id = uc.instituicao_id
    WHERE uc.id = :id
");
$stmt->execute([':id' => $id]);
$uc = $stmt->fetch();

// Redireciona se UC não existir
if (!$uc) {
    header('Location: index.php');
    exit;
}

// Busca municípios relacionados à UC via tabela associativa
$stmtMun = $pdo->prepare("
    SELECT
        m.nome_municipio,
        m.uf
    FROM municipio m
    INNER JOIN unidade_conservacao_municipio ucm
        ON ucm.municipio_id = m.id
    WHERE ucm.unidade_conservacao_id = :id
    ORDER BY m.nome_municipio ASC
");
$stmtMun->execute([':id' => $id]);
$municipios = $stmtMun->fetchAll();

// Busca comunicações ordenadas da mais recente para a mais antiga
$stmtCom = $pdo->prepare("
    SELECT
        id,
        titulo,
        descricao,
        email_comunicante,
        data_hora_envio,
        status
    FROM comunicacao
    WHERE unidade_conservacao_id = :id
    ORDER BY data_hora_envio DESC
");
$stmtCom->execute([':id' => $id]);
$comunicacoes = $stmtCom->fetchAll();

// Mapa de status conforme modelo da Etapa 1 (0 = em análise, 1 = analisada)
$statusLabels = [
    0 => ['label' => 'Em Análise',  'class' => 'status-analise'],
    1 => ['label' => 'Analisada',   'class' => 'status-analisada'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($uc['nome_uc']) ?> — Detalhes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="container">
        <a href="index.php" class="btn-voltar">← Voltar para listagem</a>
        <h1>🌿 Unidades de Conservação</h1>
    </div>
</header>

<main class="container">

    <!-- ========== DETALHES DA UC ========== -->
    <section class="uc-detalhe">

        <!-- AJUSTE 2: Placeholder quando imagem_url estiver vazia -->
        <img
            src="<?= !empty($uc['imagem_url'])
                ? htmlspecialchars($uc['imagem_url'])
                : 'assets/placeholder-uc.jpg' ?>"
            alt="<?= htmlspecialchars($uc['nome_uc']) ?>"
            class="uc-imagem"
            onerror="this.src='assets/placeholder-uc.jpg'"
        >

        <h2><?= htmlspecialchars($uc['nome_uc']) ?></h2>

        <div class="info-grid">
            <div class="info-item">
                <strong>🏛️ Instituição Responsável</strong>
                <p><?= htmlspecialchars($uc['nome_instituicao']) ?></p>
                <p class="info-email">
                    <?= htmlspecialchars($uc['email_instituicao']) ?>
                </p>
            </div>

            <div class="info-item">
                <strong>📅 Data de Criação</strong>
                <p><?= date('d/m/Y', strtotime($uc['data_criacao'])) ?></p>
            </div>

            <div class="info-item">
                <strong>📍 Municípios Abrangidos</strong>
                <?php if (!empty($municipios)): ?>
                    <ul class="lista-municipios">
                        <?php foreach ($municipios as $m): ?>
                            <li>
                                <?= htmlspecialchars($m['nome_municipio']) ?>
                                — <?= htmlspecialchars($m['uf']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="aviso">Nenhum município cadastrado.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="descricao-completa">
            <strong>📝 Descrição</strong>
            <p><?= nl2br(htmlspecialchars($uc['descricao'])) ?></p>
        </div>

    </section>

    <!-- ========== BOTÃO NOVA COMUNICAÇÃO ========== -->
    <div class="acao-comunicacao">
        <a href="nova_comunicacao.php?id=<?= (int)$uc['id'] ?>"
           class="btn-comunicar">
            📢 Enviar Comunicação para esta Unidade
        </a>
    </div>

    <!-- ========== LISTAGEM DE COMUNICAÇÕES ========== -->
    <section class="comunicacoes">
        <h3>
            💬 Comunicações Recebidas
            <span class="badge-count"><?= count($comunicacoes) ?></span>
        </h3>

        <?php if (empty($comunicacoes)): ?>
            <p class="aviso">
                Nenhuma comunicação enviada para esta unidade ainda.
                Seja o primeiro a enviar!
            </p>
        <?php else: ?>
            <?php foreach ($comunicacoes as $com): ?>
                <?php
                    $statusInfo = $statusLabels[$com['status']]
                        ?? ['label' => 'Desconhecido', 'class' => ''];
                ?>
                <div class="card-comunicacao">
                    <div class="com-header">
                        <h4><?= htmlspecialchars($com['titulo']) ?></h4>
                        <span class="badge <?= $statusInfo['class'] ?>">
                            <?= $statusInfo['label'] ?>
                        </span>
                    </div>

                    <p class="com-descricao">
                        <?= nl2br(htmlspecialchars($com['descricao'])) ?>
                    </p>

                    <div class="com-footer">
                        <span>
                            📧 <?= htmlspecialchars($com['email_comunicante']) ?>
                        </span>
                        <span>
                            🕐 <?= date(
                                'd/m/Y \à\s H:i',
                                strtotime($com['data_hora_envio'])
                            ) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

</main>

<footer>
    <div class="container">
        <p>© 2026 — Projeto Unidades de Conservação É Preciso | UNIVALI</p>
    </div>
</footer>

</body>
</html>
