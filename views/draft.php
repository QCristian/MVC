<?php $dashboardSections = $sections ?? []; ?>
<?php $canEditDashboardCopy = in_array($_SESSION['role'] ?? '', ['admin', 'superadmin'], true); ?>

<?php if ($canEditDashboardCopy): ?>
    <section id="panel-header-editor" class="content-section" style="display:block; margin-bottom: 18px;">
        <div class="section-topbar">
            <span class="section-badge">Texto del panel</span>
        </div>
        <div class="section-panel">
            <h2>Editar texto del panel</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_dashboard_text">
                <div class="editor-grid">
                    <label>Aplicar a:</label>
                    <select name="dashboard_role">
                        <option value="global">Global</option>
                        <option value="user">Rol user</option>
                        <option value="moderator">Rol moderator</option>
                        <option value="admin">Rol admin</option>
                        <option value="superadmin">Rol superadmin</option>
                    </select>
                    <input type="text" name="draft_title" value="<?= htmlspecialchars($data['titulo'] ?? 'Mi Sistema MVC') ?>" required>
                    <input type="text" name="draft_subtitle" value="<?= htmlspecialchars($data['subtitulo'] ?? 'Tus Drafts') ?>" placeholder="Subtítulo" required>
                    <textarea name="draft_content" rows="6" required><?= htmlspecialchars($data['contenido'] ?? 'Notas varias acerca de la construiccion de esta app') ?></textarea>
                    <button type="submit">Guardar texto</button>
                </div>
            </form>
        </div>
    </section>
<?php endif; ?>

<?php foreach ($dashboardSections as $index => $section): ?>
<?php $sectionViews = $sectionModel->getSectionViews((int)$section['id']); ?>
<?php $visibleSectionViews = $sectionModel->getVisibleSectionViews((int)$section['id']); ?>
<section id="section-<?= (int)$section['id'] ?>" class="content-section" style="display: <?= $index === 0 ? 'block' : 'none'; ?>">
    <div class="section-topbar">
        <span class="section-badge"><?= htmlspecialchars($section['title']) ?></span>
        <nav class="section-nav" data-navbarcontenido="true">
            <?php foreach ($visibleSectionViews as $view): ?>
                <a href="#section-<?= (int)$section['id'] ?>-view-<?= (int)$view['id'] ?>"><?= htmlspecialchars($view['title']) ?></a>
            <?php endforeach; ?>
            <?php if ($canManageContentViews): ?>
                <form method="POST" style="display:inline-block; margin-left:8px;">
                    <input type="hidden" name="action" value="create_section_view">
                    <input type="hidden" name="section_id" value="<?= (int)$section['id'] ?>">
                    <input type="text" name="view_title" placeholder="Nueva vista" required style="width:120px;">
                    <button type="submit">Añadir vista</button>
                </form>
            <?php endif; ?>
        </nav>
    </div>

            <?php /* Añadir vista moved into nav; show inline section edit for admins below */ ?>

            <?php if ($canManageSidebarSections): ?>
                <div style="margin-top:12px;">
                    <form method="POST" style="display:flex; gap:8px; align-items:center;">
                        <input type="hidden" name="action" value="update_section">
                        <input type="hidden" name="section_id" value="<?= (int)$section['id'] ?>">
                        <input type="text" name="title" value="<?= htmlspecialchars($section['title']) ?>" required style="flex:1;">
                        <button type="submit">Guardar sección</button>
                    </form>
                    <?php foreach ($sectionViews as $view): ?>
                            <?php
                                $products = $sectionModel->getProductsByView((int)$view['id']);
                                $blocks = $sectionModel->getBlocksByView((int)$view['id']);
                            ?>
                            <div id="section-<?= (int)$section['id'] ?>-view-<?= (int)$view['id'] ?>" class="section-panel" style="display:none;">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px; flex-wrap:wrap;">
                                    <h3><?= htmlspecialchars($view['title']) ?></h3>
                                    <?php if ($canManageContentViews): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="action" value="delete_section_view">
                                            <input type="hidden" name="view_id" value="<?= (int)$view['id'] ?>">
                                            <button type="submit" onclick="return confirm('¿Seguro que quieres eliminar esta vista?');">Eliminar vista</button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <p><strong>Tipo:</strong> <?= htmlspecialchars(ucfirst((string)($view['kind'] ?? 'custom'))) ?></p>
                                <p><strong>Visible para:</strong> <?= htmlspecialchars((string)($view['visible_to'] ?? 'all')) ?: 'all' ?></p>
                                <p><?= nl2br(htmlspecialchars($view['content'])) ?></p>

                                <div class="view-blocks" style="margin-top:18px;">
                                    <h4>Bloques internos</h4>
                                    <?php if (!empty($blocks)): ?>
                                        <?php foreach ($blocks as $block): ?>
                                            <div class="view-block" style="border:1px solid #e3e3e3; padding:10px; margin-bottom:8px; border-radius:8px; background: rgba(0,0,0,0.02);">
                                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                                                    <div style="flex:1;"><?= nl2br(htmlspecialchars($block['content'])) ?></div>
                                                    <?php if ($canManageContentViews): ?>
                                                        <div style="display:flex; flex-direction:column; gap:6px; margin-left:8px;">
                                                            <form method="POST">
                                                                <input type="hidden" name="action" value="update_view_block">
                                                                <input type="hidden" name="block_id" value="<?= (int)$block['id'] ?>">
                                                                <textarea name="block_content" rows="3" required style="min-width:220px;"><?= htmlspecialchars($block['content']) ?></textarea>
                                                                <button type="submit">Guardar bloque</button>
                                                            </form>
                                                            <div style="display:flex; gap:6px;">
                                                                <form method="POST">
                                                                    <input type="hidden" name="action" value="move_view_block">
                                                                    <input type="hidden" name="block_id" value="<?= (int)$block['id'] ?>">
                                                                    <input type="hidden" name="direction" value="up">
                                                                    <button type="submit">▲</button>
                                                                </form>
                                                                <form method="POST">
                                                                    <input type="hidden" name="action" value="move_view_block">
                                                                    <input type="hidden" name="block_id" value="<?= (int)$block['id'] ?>">
                                                                    <input type="hidden" name="direction" value="down">
                                                                    <button type="submit">▼</button>
                                                                </form>
                                                                <form method="POST">
                                                                    <input type="hidden" name="action" value="delete_view_block">
                                                                    <input type="hidden" name="block_id" value="<?= (int)$block['id'] ?>">
                                                                    <button type="submit" onclick="return confirm('Eliminar este bloque?');">Eliminar</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No hay bloques internos.</p>
                                    <?php endif; ?>

                                    <?php if ($canManageContentViews): ?>
                                        <div style="margin-top:12px;">
                                            <h5>Añadir bloque</h5>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="create_view_block">
                                                <input type="hidden" name="view_id" value="<?= (int)$view['id'] ?>">
                                                <select name="block_type">
                                                    <option value="div">Div</option>
                                                    <option value="section">Section</option>
                                                </select>
                                                <textarea name="block_content" rows="4" placeholder="Contenido HTML o texto" required style="display:block; width:100%; margin-top:8px;"></textarea>
                                                <button type="submit" style="margin-top:8px;">Añadir bloque</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (($view['kind'] ?? 'custom') === 'table' || ($view['kind'] ?? 'custom') === 'custom'): ?>
                                    <div class="product-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-top:18px;">
                                        <?php foreach ($products as $product): ?>
                                            <article class="product-card" style="border:1px solid #d9d9d9; border-radius:12px; padding:14px; background: rgba(255,255,255,0.04);">
                                                <?php if (!empty($product['photo_path']) || !empty($product['photo_url'])): ?>
                                                    <img src="<?= htmlspecialchars(!empty($product['photo_path']) ? $product['photo_path'] : $product['photo_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; max-height:160px; object-fit:cover; border-radius:8px; margin-bottom:12px;">
                                                <?php endif; ?>
                                                <h4><?= htmlspecialchars($product['name']) ?></h4>
                                                <p><strong>Valor:</strong> <?= htmlspecialchars(number_format((float)$product['value'], 2, ',', '.')) ?> €</p>
                                                <p><strong>Peso:</strong> <?= htmlspecialchars(number_format((float)$product['weight'], 2, ',', '.')) ?> kg</p>
                                                <?php if ($canManageContentViews): ?>
                                                    <div style="display:flex; flex-direction:column; gap:8px; margin-top:12px;">
                                                        <form method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="action" value="save_product">
                                                            <input type="hidden" name="view_id" value="<?= (int)$view['id'] ?>">
                                                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                                            <input type="text" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                                            <input type="number" step="0.01" min="0" name="product_value" value="<?= htmlspecialchars((float)$product['value']) ?>" required>
                                                            <input type="number" step="0.01" min="0" name="product_weight" value="<?= htmlspecialchars((float)$product['weight']) ?>" required>
                                                            <input type="url" name="product_photo" value="<?= htmlspecialchars($product['photo_url'] ?? '') ?>" placeholder="URL de la imagen">
                                                            <input type="file" name="product_photo_file" accept="image/*">
                                                            <button type="submit">Guardar</button>
                                                        </form>
                                                        <form method="POST">
                                                            <input type="hidden" name="action" value="delete_product">
                                                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                                            <button type="submit" onclick="return confirm('¿Quitar este producto?');">Eliminar</button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
</section>
<?php endforeach; ?>

<?php if ($canManageSidebarSections): ?>
    <section id="section-create" class="content-section" style="display:none">
        <div class="section-topbar">
            <span class="section-badge">Nueva sección</span>
        </div>
        <div class="section-panel">
            <h2>Crear nueva sección del sidebar</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create_section">
                <div class="editor-grid">
                    <input type="text" name="title" placeholder="Título de la sección" required>
                    <textarea name="content" rows="8" placeholder="Contenido de la sección" required></textarea>
                    <button type="submit">Crear sección</button>
                </div>
            </form>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($canViewUsers)): ?>
<section id="users-section" class="content-section" style="display:none">
    <?php
        $users = $users ?? [];
        $error = $error ?? '';
        $success = $success ?? '';
        $canManageUsers = $canManageUsers ?? false;
        $canViewUsers = $canViewUsers ?? false;
        $newUsers = $newUsers ?? [];
        require __DIR__ . '/user_management.php';
    ?>
</section>
<?php endif; ?>

<?php /* Change requests / review UI removed per new requirement */ ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const links = document.querySelectorAll('.sidebar a');
        const sections = document.querySelectorAll('.content-section');

        function showSection(targetId) {
            sections.forEach(section => {
                section.style.display = (section.id === targetId) ? 'block' : 'none';
            });
        }

        links.forEach(link => {
            const href = link.getAttribute('href');
            if (!href || !href.startsWith('#')) return;

            link.addEventListener('click', function (e) {
                e.preventDefault();
                showSection(href.replace('#', ''));
            });
        });

        document.querySelectorAll('.section-nav a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const currentSection = this.closest('section');
                if (!currentSection) return;
                const targetId = this.getAttribute('href').replace('#', '');
                currentSection.querySelectorAll('.section-panel').forEach(panel => {
                    panel.style.display = (panel.id === targetId) ? 'block' : 'none';
                });
            });
        });

        const defaultSection = document.getElementById('panel-header-editor') || document.querySelector('.content-section') || null;
        if (defaultSection) {
            showSection(defaultSection.id);
        }
    });
</script>

<?php $extraJs = "/mvc/public/js/script.js"; ?>