<?php
class SectionModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTables();
    }

    private function ensureTables() {
        $sql = [
            "CREATE TABLE IF NOT EXISTS dashboard_sections (
                id INT NOT NULL AUTO_INCREMENT,
                title VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL,
                content TEXT NOT NULL,
                created_by INT DEFAULT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY ux_dashboard_sections_slug (slug),
                KEY idx_dashboard_sections_created_by (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            "CREATE TABLE IF NOT EXISTS section_change_requests (
                id INT NOT NULL AUTO_INCREMENT,
                section_id INT NOT NULL,
                requested_title VARCHAR(150) NOT NULL,
                requested_content TEXT NOT NULL,
                requested_by INT NOT NULL,
                status ENUM('pending','in_progress','approved','rejected','deleted') NOT NULL DEFAULT 'pending',
                reviewed_by INT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                reviewed_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY idx_section_change_requests_section_id (section_id),
                KEY idx_section_change_requests_requested_by (requested_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            "CREATE TABLE IF NOT EXISTS dashboard_section_views (
                id INT NOT NULL AUTO_INCREMENT,
                section_id INT NOT NULL,
                title VARCHAR(150) NOT NULL,
                content TEXT NOT NULL DEFAULT '',
                kind ENUM('summary','details','table','form','custom') NOT NULL DEFAULT 'custom',
                visible_to VARCHAR(255) NOT NULL DEFAULT 'all',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                is_default TINYINT(1) NOT NULL DEFAULT 0,
                sort_order INT NOT NULL DEFAULT 0,
                created_by INT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_dashboard_section_views_section_id (section_id),
                KEY idx_dashboard_section_views_created_by (created_by),
                CONSTRAINT fk_dashboard_section_views_section FOREIGN KEY (section_id) REFERENCES dashboard_sections(id) ON DELETE CASCADE,
                CONSTRAINT fk_dashboard_section_views_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            "CREATE TABLE IF NOT EXISTS dashboard_products (
                id INT NOT NULL AUTO_INCREMENT,
                view_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                weight DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                photo_path VARCHAR(255) DEFAULT NULL,
                photo_url VARCHAR(255) DEFAULT NULL,
                created_by INT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_dashboard_products_view_id (view_id),
                KEY idx_dashboard_products_created_by (created_by),
                CONSTRAINT fk_dashboard_products_view FOREIGN KEY (view_id) REFERENCES dashboard_section_views(id) ON DELETE CASCADE,
                CONSTRAINT fk_dashboard_products_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            "CREATE TABLE IF NOT EXISTS dashboard_view_blocks (
                id INT NOT NULL AUTO_INCREMENT,
                view_id INT NOT NULL,
                block_type VARCHAR(50) NOT NULL DEFAULT 'div',
                content TEXT NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                created_by INT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_dashboard_view_blocks_view_id (view_id),
                CONSTRAINT fk_dashboard_view_blocks_view FOREIGN KEY (view_id) REFERENCES dashboard_section_views(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            "CREATE TABLE IF NOT EXISTS dashboard_settings (
                id INT NOT NULL AUTO_INCREMENT,
                setting_key VARCHAR(100) NOT NULL,
                setting_value TEXT NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY ux_dashboard_settings_key (setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
        ];

        foreach ($sql as $statement) {
            $this->pdo->exec($statement);
        }

        $this->ensureColumn('dashboard_section_views', 'kind', "ALTER TABLE dashboard_section_views ADD COLUMN kind ENUM('summary','details','table','form','custom') NOT NULL DEFAULT 'custom' AFTER content");
        $this->ensureColumn('dashboard_section_views', 'visible_to', "ALTER TABLE dashboard_section_views ADD COLUMN visible_to VARCHAR(255) NOT NULL DEFAULT 'all' AFTER kind");
        $this->ensureColumn('dashboard_section_views', 'is_active', "ALTER TABLE dashboard_section_views ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER visible_to");
        $this->ensureColumn('dashboard_section_views', 'is_default', "ALTER TABLE dashboard_section_views ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
        $this->ensureColumn('dashboard_section_views', 'sort_order', "ALTER TABLE dashboard_section_views ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER is_default");
        $this->ensureColumn('dashboard_products', 'photo_path', "ALTER TABLE dashboard_products ADD COLUMN photo_path VARCHAR(255) DEFAULT NULL AFTER weight");
        $this->ensureColumn('dashboard_products', 'photo_url', "ALTER TABLE dashboard_products ADD COLUMN photo_url VARCHAR(255) DEFAULT NULL AFTER photo_path");
    }

    private function ensureColumn($table, $column, $sql) {
        try {
            $stmt = $this->pdo->prepare('SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1');
            $stmt->execute([$table, $column]);
            if ($stmt->fetchColumn() === false) {
                $this->pdo->exec($sql);
            }
        } catch (Exception $e) {
            // ignore and continue; schema is already valid or table is absent
        }
    }

    public function getAll() {
        $stmt = $this->pdo->query(
            'SELECT * FROM dashboard_sections ORDER BY sort_order ASC, id ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM dashboard_sections WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($title, $content, $createdBy, $sortOrder = 999) {
        $slug = $this->slugify($title);
        $stmt = $this->pdo->prepare(
            'INSERT INTO dashboard_sections (title, slug, content, created_by, sort_order) VALUES (?, ?, ?, ?, ?)'
        );

        return $stmt->execute([$title, $slug, $content, $createdBy, $sortOrder]);
    }

    public function update($id, $title, $content) {
        $slug = $this->slugify($title);
        $stmt = $this->pdo->prepare(
            'UPDATE dashboard_sections SET title = ?, slug = ?, content = ? WHERE id = ?'
        );

        return $stmt->execute([$title, $slug, $content, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM dashboard_sections WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function createPendingChange($sectionId, $title, $content, $requestedBy) {
        $stmt = $this->pdo->prepare(
            'INSERT INTO section_change_requests (section_id, requested_title, requested_content, requested_by, status) VALUES (?, ?, ?, ?, "pending")'
        );

        return $stmt->execute([$sectionId, $title, $content, $requestedBy]);
    }

    public function getSetting($key, $default = '') {
        $stmt = $this->pdo->prepare('SELECT setting_value FROM dashboard_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    public function saveSetting($key, $value) {
        $stmt = $this->pdo->prepare(
            'INSERT INTO dashboard_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP'
        );
        return $stmt->execute([$key, (string)$value]);
    }

    public function getRoleHeader($role, $field, $default = '') {
        $key = 'panel_header_' . $field . '_' . strtolower((string)$role);
        $roleValue = $this->getSetting($key, null);
        if ($roleValue !== null && $roleValue !== '') {
            return $roleValue;
        }

        $globalKey = 'panel_header_' . $field . '_global';
        return $this->getSetting($globalKey, $default);
    }

    public function saveRoleHeader($role, $field, $value) {
        $key = 'panel_header_' . $field . '_' . strtolower((string)$role);
        return $this->saveSetting($key, $value);
    }

    public function saveGlobalHeader($field, $value) {
        $key = 'panel_header_' . $field . '_global';
        return $this->saveSetting($key, $value);
    }

    public function getSectionViews($sectionId) {
        $stmt = $this->pdo->prepare('SELECT * FROM dashboard_section_views WHERE section_id = ? ORDER BY sort_order ASC, created_at ASC, id ASC');
        $stmt->execute([(int)$sectionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createDefaultSectionViews($sectionId, $createdBy = 0) {
        $existing = $this->getSectionViews((int)$sectionId);
        if (!empty($existing)) {
            return true;
        }

        $defaults = [
            ['title' => 'Resumen', 'content' => 'Resumen general de esta sección del panel.', 'kind' => 'summary', 'visible_to' => 'all', 'is_active' => 1, 'is_default' => 1],
            ['title' => 'Detalles', 'content' => 'Detalles técnicos y contexto de esta sección.', 'kind' => 'details', 'visible_to' => 'all', 'is_active' => 1, 'is_default' => 1],
        ];

        foreach ($defaults as $index => $item) {
            $stmt = $this->pdo->prepare('INSERT INTO dashboard_section_views (section_id, title, content, kind, visible_to, is_active, is_default, sort_order, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                (int)$sectionId,
                $item['title'],
                $item['content'],
                $item['kind'],
                $item['visible_to'],
                (int)$item['is_active'],
                (int)$item['is_default'],
                $index,
                (int)$createdBy,
            ]);
        }

        return true;
    }

    public function getSectionViewById($viewId) {
        $stmt = $this->pdo->prepare('SELECT * FROM dashboard_section_views WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$viewId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBlocksByView($viewId) {
        $stmt = $this->pdo->prepare('SELECT * FROM dashboard_view_blocks WHERE view_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([(int)$viewId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createViewBlock($viewId, $blockType, $content, $createdBy = 0) {
        $blockType = trim($blockType) ?: 'div';
        $stmt = $this->pdo->prepare('INSERT INTO dashboard_view_blocks (view_id, block_type, content, sort_order, created_by) VALUES (?, ?, ?, 0, ?)');
        return $stmt->execute([(int)$viewId, $blockType, $content, (int)$createdBy]);
    }

    public function updateViewBlock($blockId, $content) {
        $stmt = $this->pdo->prepare('UPDATE dashboard_view_blocks SET content = ? WHERE id = ?');
        return $stmt->execute([$content, (int)$blockId]);
    }

    public function deleteViewBlock($blockId) {
        $stmt = $this->pdo->prepare('DELETE FROM dashboard_view_blocks WHERE id = ?');
        return $stmt->execute([(int)$blockId]);
    }

    public function moveViewBlock($blockId, $direction = 'up') {
        $stmt = $this->pdo->prepare('SELECT * FROM dashboard_view_blocks WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$blockId]);
        $block = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$block) return false;
        $viewId = (int)$block['view_id'];
        $currentOrder = (int)$block['sort_order'];
        if ($direction === 'up') {
            $neighborStmt = $this->pdo->prepare('SELECT * FROM dashboard_view_blocks WHERE view_id = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1');
            $neighborStmt->execute([$viewId, $currentOrder]);
        } else {
            $neighborStmt = $this->pdo->prepare('SELECT * FROM dashboard_view_blocks WHERE view_id = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1');
            $neighborStmt->execute([$viewId, $currentOrder]);
        }
        $neighbor = $neighborStmt->fetch(PDO::FETCH_ASSOC);
        if (!$neighbor) return false;
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('UPDATE dashboard_view_blocks SET sort_order = ? WHERE id = ?')->execute([(int)$neighbor['sort_order'], (int)$blockId]);
            $this->pdo->prepare('UPDATE dashboard_view_blocks SET sort_order = ? WHERE id = ?')->execute([$currentOrder, (int)$neighbor['id']]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function moveSectionView($viewId, $direction = 'up') {
        $stmt = $this->pdo->prepare('SELECT * FROM dashboard_section_views WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$viewId]);
        $view = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$view) return false;
        $sectionId = (int)$view['section_id'];
        $currentOrder = (int)$view['sort_order'];
        if ($direction === 'up') {
            $neighborStmt = $this->pdo->prepare('SELECT * FROM dashboard_section_views WHERE section_id = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1');
            $neighborStmt->execute([$sectionId, $currentOrder]);
        } else {
            $neighborStmt = $this->pdo->prepare('SELECT * FROM dashboard_section_views WHERE section_id = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1');
            $neighborStmt->execute([$sectionId, $currentOrder]);
        }
        $neighbor = $neighborStmt->fetch(PDO::FETCH_ASSOC);
        if (!$neighbor) return false;
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('UPDATE dashboard_section_views SET sort_order = ? WHERE id = ?')->execute([(int)$neighbor['sort_order'], (int)$viewId]);
            $this->pdo->prepare('UPDATE dashboard_section_views SET sort_order = ? WHERE id = ?')->execute([$currentOrder, (int)$neighbor['id']]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getVisibleSectionViews($sectionId, $role = 'user') {
        $items = $this->getSectionViews((int)$sectionId);
        return array_values(array_filter($items, function ($view) use ($role) {
            return $this->roleCanSeeView($view, $role);
        }));
    }

    public function roleCanSeeView($view, $role) {
        if (empty($view)) {
            return false;
        }

        if ((int)($view['is_active'] ?? 1) !== 1) {
            return false;
        }

        $visibleTo = trim((string)($view['visible_to'] ?? 'all'));
        if ($visibleTo === '' || strtolower($visibleTo) === 'all') {
            return true;
        }

        $roles = array_map('trim', explode(',', strtolower($visibleTo)));
        $roles = array_filter($roles, function ($item) { return $item !== ''; });
        return in_array(strtolower((string)$role), $roles, true);
    }

    public function createSectionView($sectionId, $title, $kind = 'custom', $visibleTo = 'all', $createdBy = 0) {
        $title = trim($title);
        if ($title === '') {
            return false;
        }
        $kind = in_array($kind, ['summary','details','table','form','custom'], true) ? $kind : 'custom';
        $visibleTo = trim((string)$visibleTo);
        if ($visibleTo === '') {
            $visibleTo = 'all';
        }
        $stmt = $this->pdo->prepare('INSERT INTO dashboard_section_views (section_id, title, content, kind, visible_to, is_active, is_default, sort_order, created_by) VALUES (?, ?, ?, ?, ?, 1, 0, 0, ?)');
        return $stmt->execute([(int)$sectionId, $title, 'Vista creada por el moderador.', $kind, $visibleTo, (int)$createdBy]);
    }

    // updateSectionView removed — view editing via editor has been deprecated

    public function createSingleDefaultView($sectionId, $createdBy = 0) {
        $stmt = $this->pdo->prepare('INSERT INTO dashboard_section_views (section_id, title, content, kind, visible_to, is_active, is_default, sort_order, created_by) VALUES (?, ?, ?, ?, ?, 1, 1, 0, ?)');
        return $stmt->execute([(int)$sectionId, 'Vista', 'Vista inicial del panel.', 'custom', 'all', (int)$createdBy]);
    }

    public function toggleSectionView($viewId, $active) {
        $stmt = $this->pdo->prepare('UPDATE dashboard_section_views SET is_active = ? WHERE id = ?');
        return $stmt->execute([(int)$active, (int)$viewId]);
    }

    public function updateSectionViewVisibility($viewId, $visibleTo = 'all') {
        $roles = array_values(array_unique(array_filter(array_map('trim', preg_split('/\s*,\s*/', strtolower((string)$visibleTo)), function ($value) { return $value !== ''; }))));
        if (empty($roles)) {
            $roles = ['all'];
        }
        $stmt = $this->pdo->prepare('UPDATE dashboard_section_views SET visible_to = ? WHERE id = ?');
        return $stmt->execute([implode(',', $roles), (int)$viewId]);
    }

    public function deleteSectionView($viewId) {
        $stmt = $this->pdo->prepare('DELETE FROM dashboard_section_views WHERE id = ?');
        return $stmt->execute([(int)$viewId]);
    }

    public function getProductsByView($viewId) {
        $stmt = $this->pdo->prepare('SELECT * FROM dashboard_products WHERE view_id = ? ORDER BY created_at DESC, id DESC');
        $stmt->execute([(int)$viewId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveProduct($viewId, $name, $value, $weight, $photoPath, $photoUrl, $createdBy, $productId = null) {
        $name = trim((string)$name);
        if ($name === '') {
            return false;
        }

        if ($productId) {
            $stmt = $this->pdo->prepare('UPDATE dashboard_products SET name = ?, value = ?, weight = ?, photo_path = ?, photo_url = ? WHERE id = ? AND view_id = ?');
            return $stmt->execute([$name, (float)$value, (float)$weight, $photoPath, $photoUrl, (int)$productId, (int)$viewId]);
        }

        $stmt = $this->pdo->prepare('INSERT INTO dashboard_products (view_id, name, value, weight, photo_path, photo_url, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([(int)$viewId, $name, (float)$value, (float)$weight, $photoPath, $photoUrl, (int)$createdBy]);
    }

    public function deleteProduct($productId) {
        $stmt = $this->pdo->prepare('DELETE FROM dashboard_products WHERE id = ?');
        return $stmt->execute([(int)$productId]);
    }

    public function getPendingChanges($viewerRole = null, $viewerId = null) {
        if (($viewerRole ?? '') === 'moderator') {
            $stmt = $this->pdo->prepare(
                'SELECT r.*, s.title AS current_title, s.slug AS current_slug FROM section_change_requests r LEFT JOIN dashboard_sections s ON s.id = r.section_id WHERE r.status IN ("pending","in_progress") AND r.requested_by = ? ORDER BY r.created_at DESC'
            );
            $stmt->execute([$viewerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->pdo->query(
            'SELECT r.*, s.title AS current_title, s.slug AS current_slug FROM section_change_requests r LEFT JOIN dashboard_sections s ON s.id = r.section_id WHERE r.status IN ("pending","in_progress") ORDER BY CASE r.status WHEN "pending" THEN 0 WHEN "in_progress" THEN 1 ELSE 2 END, r.created_at DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResolvedChanges($viewerRole = null, $viewerId = null) {
        if (($viewerRole ?? '') === 'moderator') {
            $stmt = $this->pdo->prepare(
                'SELECT r.*, s.title AS current_title, s.slug AS current_slug FROM section_change_requests r LEFT JOIN dashboard_sections s ON s.id = r.section_id WHERE r.status IN ("approved","rejected","deleted") AND r.requested_by = ? ORDER BY r.reviewed_at DESC, r.created_at DESC'
            );
            $stmt->execute([$viewerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->pdo->query(
            'SELECT r.*, s.title AS current_title, s.slug AS current_slug FROM section_change_requests r LEFT JOIN dashboard_sections s ON s.id = r.section_id WHERE r.status IN ("approved","rejected","deleted") ORDER BY r.reviewed_at DESC, r.created_at DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePendingChange($requestId, $title, $content, $currentUserId) {
        $stmt = $this->pdo->prepare(
            'UPDATE section_change_requests SET requested_title = ?, requested_content = ? WHERE id = ? AND requested_by = ? AND status = "pending"'
        );

        return $stmt->execute([$title, $content, $requestId, $currentUserId]);
    }

    public function openChange($requestId) {
        $stmt = $this->pdo->prepare(
            'UPDATE section_change_requests SET status = "in_progress", reviewed_by = NULL, reviewed_at = NULL WHERE id = ? AND status = "pending"'
        );

        return $stmt->execute([$requestId]);
    }

    public function deleteChange($requestId) {
        $stmt = $this->pdo->prepare(
            'UPDATE section_change_requests SET status = "deleted", reviewed_by = NULL, reviewed_at = NOW() WHERE id = ? AND status IN ("pending","in_progress")'
        );

        return $stmt->execute([$requestId]);
    }

    public function approveChange($requestId, $approvedBy) {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM section_change_requests WHERE id = ? AND status IN ("pending","in_progress")'
        );
        $stmt->execute([$requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            return false;
        }

        $this->pdo->beginTransaction();

        try {
            $this->pdo->prepare(
                'UPDATE dashboard_sections SET title = ?, slug = ?, content = ? WHERE id = ?'
            )->execute([
                $request['requested_title'],
                $this->slugify($request['requested_title']),
                $request['requested_content'],
                $request['section_id']
            ]);

            $this->pdo->prepare(
                'UPDATE section_change_requests SET status = "approved", reviewed_by = ?, reviewed_at = NOW() WHERE id = ?'
            )->execute([$approvedBy, $requestId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function rejectChange($requestId, $reviewedBy) {
        $stmt = $this->pdo->prepare(
            'UPDATE section_change_requests SET status = "rejected", reviewed_by = ?, reviewed_at = NOW() WHERE id = ? AND status IN ("pending","in_progress")'
        );

        return $stmt->execute([$reviewedBy, $requestId]);
    }

    private function slugify($value) {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
