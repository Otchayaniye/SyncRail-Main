<?php
include("../lay/menu.php");
require_once('../connections/db.php');
session_start();
$error = "";

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT user_adm FROM usuario WHERE pk_user = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$admin = $resultado->fetch_assoc();
$_SESSION['admin'] = $admin['user_adm'];

// Buscar chamados - usuários comuns só veem seus próprios chamados
if ($_SESSION['admin'] == 1) {
    $stmt = $conn->prepare("SELECT c.*, u.user_name as usuario_nome FROM chamados c LEFT JOIN usuario u ON c.user_id = u.pk_user ORDER BY c.data_criacao DESC");
} else {
    $stmt = $conn->prepare("SELECT c.*, u.user_name as usuario_nome FROM chamados c LEFT JOIN usuario u ON c.user_id = u.pk_user WHERE c.user_id = ? ORDER BY c.data_criacao DESC");
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$chamados = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/repair.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Dashboard</title>
    <style>

    </style>
</head>

<body class="backgroundf min-vh-100 d-flex flex-column justify-content-center">
    <div class="container d-flex justify-content-evenly align-items-stretch bgcont rounded p-3">
        <div class="p-2 lbox w-100">
            <div class="w-100 d-flex justify-content-between">
                <h2>Chamados para Manutenção</h2>
                <button class="btn p-0 iconplus ps-3 pe-3" data-bs-toggle="modal" data-bs-target="#criarChamadoModal">
                    <i class="bi bi-plus-circle"></i>
                </button>
            </div>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="mt-4 table-container">
                <?php if ($chamados->num_rows > 0): ?>
                    <div class="table-responsive table-header">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Título</th>
                                    <th>Descrição</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <?php if ($_SESSION['admin'] == 1): ?>
                                        <th>Usuário</th>
                                    <?php endif; ?>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($chamado = $chamados->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($chamado['titulo']); ?></td>
                                        <td><?php echo htmlspecialchars($chamado['descricao']); ?></td>
                                        <td>
                                            <span
                                                class="badge 
                                                <?php echo $chamado['prioridade'] == 'alta' ? 'bg-danger' : ($chamado['prioridade'] == 'media' ? 'bg-warning' : 'bg-info'); ?>">
                                                <?php echo ucfirst($chamado['prioridade']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge 
                                                <?php echo $chamado['status'] == 'aberto' ? 'bg-success' : ($chamado['status'] == 'em_andamento' ? 'bg-warning' : 'bg-secondary'); ?>">
                                                <?php echo ucfirst($chamado['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($chamado['data_criacao'])); ?></td>
                                        <?php if ($_SESSION['admin'] == 1): ?>
                                            <td><?php echo htmlspecialchars($chamado['usuario_nome']); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($_SESSION['admin'] == 1 || $chamado['user_id'] == $_SESSION['user_id']): ?>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                        data-bs-target="#editarChamadoModal"
                                                        onclick="carregarChamado(<?php echo $chamado['id']; ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <a href="../connections/deleterequisition.php?id=<?php echo $chamado['id']; ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Tem certeza que deseja excluir este chamado?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        Nenhum chamado encontrado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Criar Chamado -->
    <div class="modal fade" id="criarChamadoModal" tabindex="-1" aria-labelledby="criarChamadoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="criarChamadoModalLabel">Criar Novo Chamado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../connections/createrequisition.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="prioridade" class="form-label">Prioridade</label>
                            <select class="form-select" id="prioridade" name="prioridade" required>
                                <option value="baixa">Baixa</option>
                                <option value="media">Média</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Chamado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Chamado -->
    <div class="modal fade" id="editarChamadoModal" tabindex="-1" aria-labelledby="editarChamadoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarChamadoModalLabel">Editar Chamado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../connections/editrequisition.php" method="POST">
                    <input type="hidden" id="editar_chamado_id" name="chamado_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editar_titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="editar_titulo" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="editar_descricao" name="descricao" rows="3"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editar_prioridade" class="form-label">Prioridade</label>
                            <select class="form-select" id="editar_prioridade" name="prioridade" required>
                                <option value="baixa">Baixa</option>
                                <option value="media">Média</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                        <?php if ($_SESSION['admin'] == 1): ?>
                            <div class="mb-3">
                                <label for="editar_status" class="form-label">Status</label>
                                <select class="form-select" id="editar_status" name="status" required>
                                    <option value="aberto">Aberto</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="fechado">Fechado</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script src="../js/repair.js"></script>
    <script>
        // Função para carregar dados do chamado no modal de edição
        function carregarChamado(id) {
            fetch(`../connections/searchrequisition.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editar_chamado_id').value = data.id;
                    document.getElementById('editar_titulo').value = data.titulo;
                    document.getElementById('editar_descricao').value = data.descricao;
                    document.getElementById('editar_prioridade').value = data.prioridade;
                    <?php if ($_SESSION['admin'] == 1): ?>
                        document.getElementById('editar_status').value = data.status;
                    <?php endif; ?>
                })
                .catch(error => console.error('Erro:', error));
        }
    </script>
</body>

</html>