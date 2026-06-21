<?php
// index.php
require_once 'config/db.php';

// Busca todas as UCs com nome da instituição responsável
//
// AJUSTE 4: os nomes reais das colunas em unidade_conservacao têm sufixo
// "_uc" (data_criacao_uc, descricao_uc, imagem_url_uc), conforme o schema
// da Etapa 1 (sql/etapa1_item_b.sql). Usamos "AS" para expor essas colunas
// com os mesmos apelidos que o restante do template já espera
// ($uc['data_criacao'], $uc['descricao'], $uc['imagem_url']).
$stmt = $pdo->query("
    SELECT 
        uc.id,
        uc.nome_uc,
        uc.data_criacao_uc AS data_criacao,
        uc.descricao_uc    AS descricao,
        uc.imagem_url_uc   AS imagem_url,
        i.nome_instituicao
    FROM unidade_conservacao uc
    INNER JOIN instituicao i ON i.id = uc.instituicao_id
    ORDER BY uc.nome_uc ASC
");
$unidades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unidades de Conservação — SC</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="container">
        <h1>🌿 Unidades de Conservação</h1>
        <p>Zona Costeira Catarinense — IMA/SC</p>
    </div>
</header>

<main class="container">
    <h2>Unidades Cadastradas</h2>

    <?php if (empty($unidades)): ?>
        <p class="aviso">Nenhuma unidade de conservação cadastrada.</p>
    <?php else: ?>
        <div class="grid-ucs">
            <?php foreach ($unidades as $uc): ?>
                <div class="card-uc">

                    <!-- AJUSTE 2: Placeholder quando imagem_url estiver vazia -->
                    <img
                        src="<?= !empty($uc['imagem_url'])
                            ? htmlspecialchars($uc['imagem_url'])
                            : 'assets/placeholder-uc.jpg' ?>"
                        alt="Imagem de <?= htmlspecialchars($uc['nome_uc']) ?>"
                        class="card-img"
                        onerror="this.src='assets/placeholder-uc.jpg'"
                    >

                    <div class="card-body">
                        <h3><?= htmlspecialchars($uc['nome_uc']) ?></h3>

                        <p class="instituicao">
                            🏛️ <?= htmlspecialchars($uc['nome_instituicao']) ?>
                        </p>

                        <p class="data">
                            📅 Criada em:
                            <?= date('d/m/Y', strtotime($uc['data_criacao'])) ?>
                        </p>

                        <p class="descricao">
                            <?= htmlspecialchars(
                                mb_substr($uc['descricao'], 0, 150, 'UTF-8') . '...'
                            ) ?>
                        </p>

                        <a href="uc_detalhes.php?id=<?= (int)$uc['id'] ?>"
                           class="btn-ver">
                            Ver Detalhes e Comunicações
                        </a>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer>
    <div class="container">
        <p>© 2026 — Projeto Unidades de Conservação É Preciso | UNIVALI</p>
    </div>
</footer>

</body>
</html>