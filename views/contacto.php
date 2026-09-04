<section class="contacto-page">
    <div class="contacto-grid">
        <div class="contacto-card">
            <h2>Sobre nosotros</h2>
            <p><?= htmlspecialchars($data['titulo']) ?> ayuda a empresas y personas a crear soluciones digitales con una experiencia clara, moderna y centrada en el usuario.</p>
        </div>

        <div class="contacto-card">
            <h2>Servicios</h2>
            <p>Diseño web, desarrollo de aplicaciones, mantenimiento y soporte técnico para proyectos personales y profesionales.</p>
        </div>

        <div class="contacto-card">
            <h2>Contacto</h2>
            <p>Estamos listos para ayudarte a materializar tu idea. Escribe y te responderemos con la mejor solución para tu proyecto.</p>
        </div>
    </div>

    <div class="contact-form-wrap">
        <h2>Envíanos un mensaje</h2>
        <?php if (!empty($message)): ?>
            <p class="contact-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" class="contact-form">
            <label>
                Nombre
                <input type="text" name="name" placeholder="Tu nombre" required>
            </label>
            <label>
                Email
                <input type="email" name="email" placeholder="tu@email.com" required>
            </label>
            <label>
                Asunto
                <input type="text" name="subject" placeholder="Asunto">
            </label>
            <label>
                Mensaje
                <textarea name="message" rows="5" placeholder="Escribe tu mensaje" required></textarea>
            </label>
            <button type="submit">Enviar</button>
        </form>
    </div>
</section>
