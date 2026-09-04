<div class="users-crud-panel">
    <?php
        $canManageUsers = $canManageUsers ?? false;
        $canDeleteUsers = $canDeleteUsers ?? false;
        $canViewUsers = $canViewUsers ?? false;
        $newUsers = $newUsers ?? [];

        if (!$canViewUsers) {
            echo '<p>Rol actual: ' . htmlspecialchars($_SESSION['role'] ?? 'user') . '</p>';
            return;
        }
    ?>

    <div class="section-topbar">
        <span class="section-badge">Usuarios</span>
        <nav class="section-nav">
            <a href="#users-new-panel">Nuevos</a>
            <a href="#users-list-panel">Ver usuarios</a>
            <?php if ($canManageUsers): ?>
                <a href="#users-create-panel">Crear usuario</a>
            <?php endif; ?>
        </nav>
    </div>

    <?php if (!empty($newUsers)): ?>
        <div id="users-new-panel" class="section-panel new-users-notice">
            <h3>Usuarios nuevos</h3>
            <div class="new-users-list">
                <?php foreach ($newUsers as $nu): ?>
                    <div class="new-user-item">
                        <span class="new-user-badge">NUEVO</span>
                        <strong><?= htmlspecialchars($nu['username']) ?></strong>
                        <span><?= htmlspecialchars($nu['email'] ?? 'sin email') ?></span>
                        <span class="role-tag role-<?= htmlspecialchars($nu['role']) ?>"><?= htmlspecialchars($nu['role']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div id="users-list-panel" class="section-panel" style="display:none;">
        <?php if (!$canManageUsers): ?>
            <p style="color:#8a6d3b; font-weight:bold;">Estás viendo la lista de usuarios en modo consulta. Solo los administradores pueden editar o eliminar usuarios.</p>
            <p>Rol actual: <?= htmlspecialchars($_SESSION['role'] ?? 'moderator') ?></p>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <p style="color:#b00020; font-weight:bold;">
                    <?= htmlspecialchars($error) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p style="color:#0a6b2f; font-weight:bold;">
                    <?= htmlspecialchars($success) ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <h2>Usuarios registrados</h2>
        <table border="1" cellpadding="8" style="width:100%; border-collapse:collapse;">
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                <?php if ($canManageUsers): ?><th>Contraseña</th><?php endif; ?>
                <?php if ($canManageUsers): ?><th>Acciones</th><?php endif; ?>
            </tr>
            <?php foreach ($users as $u): ?>
            <tr class="user-row <?= in_array($u['role'], ['moderator', 'admin', 'superadmin'], true) ? 'is-privileged' : '' ?> <?= (strtotime($u['created_at']) >= strtotime('-3 days')) ? 'is-new-user' : '' ?>">
                <td><?= (int)$u['id'] ?></td>
                <td>
                    <?php if ($canManageUsers): ?>
                        <form method="POST" style="display:inline-block; min-width:180px;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                        <input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" required>
                    <?php else: ?>
                        <?= htmlspecialchars($u['username']) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($canManageUsers): ?>
                        <input type="email" name="email" value="<?= htmlspecialchars($u['email'] ?? '') ?>">
                    <?php else: ?>
                        <?= htmlspecialchars($u['email'] ?? '-') ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                        $roleClass = 'role-user';
                        if ($u['role'] === 'moderator') $roleClass = 'role-moderator';
                        if ($u['role'] === 'admin') $roleClass = 'role-admin';
                        if ($u['role'] === 'superadmin') $roleClass = 'role-superadmin';
                    ?>
                    <span class="role-tag <?= $roleClass ?>"><?= htmlspecialchars($u['role']) ?></span>
                    <?php if ((strtotime($u['created_at']) >= strtotime('-3 days'))): ?>
                        <span class="new-user-badge small">3d</span>
                    <?php endif; ?>
                    <?php if ($canManageUsers): ?>
                        <select name="role">
                            <option value="user" <?= $u['role']==='user' ? 'selected' : '' ?>>user</option>
                            <option value="moderator" <?= $u['role']==='moderator' ? 'selected' : '' ?>>moderator</option>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                                <option value="admin" <?= $u['role']==='admin' ? 'selected' : '' ?>>admin</option>
                                <option value="superadmin" <?= $u['role']==='superadmin' ? 'selected' : '' ?>>superadmin</option>
                            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <option value="admin" <?= $u['role']==='admin' ? 'selected' : '' ?>>admin</option>
                            <?php endif; ?>
                        </select>
                    <?php endif; ?>
                </td>
                <?php if ($canManageUsers): ?>
                <td>
                        <input type="password" name="password" placeholder="Nueva contraseña" style="width:150px;">
                </td>
                <td>
                        <button type="submit">Guardar</button>
                    </form>
                    <?php if ($canDeleteUsers && (int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                        <form method="POST" style="display:inline; margin-top:8px;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <button type="submit" onclick="return confirm('¿Seguro que quieres eliminar este usuario?');">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php if ($canManageUsers): ?>
        <div id="users-create-panel" class="section-panel" style="display:none;">
            <h2>Crear usuario</h2>
            <form method="POST" class="user-form">
                <input type="hidden" name="action" value="create">
                <div style="display:grid; gap:10px; max-width:480px;">
                    <input type="text" name="username" placeholder="Nombre de usuario" required>
                    <input type="email" name="email" placeholder="Email (opcional)">
                    <select name="role">
                        <option value="user">user</option>
                        <option value="moderator">moderator</option>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                            <option value="admin">admin</option>
                            <option value="superadmin">superadmin</option>
                        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <option value="admin">admin</option>
                        <?php endif; ?>
                    </select>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit">Crear usuario</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const usersSection = document.getElementById('users-section');
        if (!usersSection) return;

        const userPanels = usersSection.querySelectorAll('.section-panel');
        const userNav = usersSection.querySelectorAll('.section-nav a');

        function showUserPanel(targetId) {
            userPanels.forEach(panel => {
                const match = panel.id === targetId;
                panel.style.display = match ? 'block' : 'none';
            });
        }

        userNav.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').replace('#', '');
                showUserPanel(targetId);
            });
        });

        const firstUserPanel = usersSection.querySelector('#users-new-panel') || usersSection.querySelector('.section-panel');
        if (firstUserPanel) {
            showUserPanel(firstUserPanel.id);
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const newRows = document.querySelectorAll('.is-new-user');
        if (newRows.length) {
            const audioCtx = window.AudioContext || window.webkitAudioContext;
            if (audioCtx) {
                const ctx = new audioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.value = 880;
                gain.gain.value = 0.02;
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.10);
            }
            newRows.forEach(row => {
                row.animate([
                    { transform: 'scale(1)', boxShadow: '0 0 0 rgba(0,0,0,0)' },
                    { transform: 'scale(1.01)', boxShadow: '0 0 18px rgba(132, 204, 22, 0.5)' },
                    { transform: 'scale(1)', boxShadow: '0 0 0 rgba(0,0,0,0)' }
                ], { duration: 1200, iterations: 1 });
            });
        }
    });
</script>

