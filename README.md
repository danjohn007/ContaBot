# ContaBot - Sistema Básico Contable

Sistema online para el control de **gastos personales, de negocio, fiscales y no fiscales**, diseñado para usuarios individuales, pequeños negocios y contadores que requieren un control simple pero funcional de sus finanzas.

## Características Principales

- ✅ **Registro de Gastos e Ingresos**: Captura completa con categorización
- ✅ **Clasificación Fiscal**: Diferenciación entre gastos fiscales y no fiscales  
- ✅ **Gestión de Evidencias**: Adjuntar comprobantes PDF e imágenes
- ✅ **Control de Facturación**: Seguimiento del estatus de facturado
- ✅ **Reportes Financieros**: Informes mensuales, trimestrales y anuales
- ✅ **Categorías Personalizables**: Crear y gestionar categorías propias
- ✅ **Multi-usuario**: Soporte para personas físicas y negocios

## Tecnologías Utilizadas

- **Backend**: PHP 7+ (puro, sin framework)
- **Base de Datos**: MySQL 5.7
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Gráficas**: Chart.js
- **Autenticación**: Sesiones PHP con password_hash()
- **Arquitectura**: MVC (Modelo-Vista-Controlador)

## Requisitos del Sistema

- PHP 7.0 o superior
- MySQL 5.7 o superior
- Servidor web Apache con mod_rewrite
- Extensiones PHP: PDO, pdo_mysql, mbstring, fileinfo

## Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/danjohn007/ContaBot.git
cd ContaBot
```

### 2. Configurar el servidor web
Apuntar el DocumentRoot a la carpeta `public/` o crear un virtual host:

```apache
<VirtualHost *:80>
    DocumentRoot /ruta/a/ContaBot/public
    ServerName contabot.local
    
    <Directory /ruta/a/ContaBot/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Configurar la base de datos
Editar las credenciales en `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'contabot_db';
private $username = 'tu_usuario';
private $password = 'tu_password';
```

### 4. Crear la base de datos
```sql
CREATE DATABASE contabot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Verificar la instalación
Navegar a: `http://tu-dominio/test`

Esta página verificará:
- Conexión a la base de datos
- Configuración de PHP
- Permisos de directorios
- Extensiones requeridas

### 6. Inicializar datos
Desde la página de test, hacer clic en "Inicializar Base de Datos" para crear las tablas y datos de ejemplo.

## Estructura del Proyecto

```
ContaBot/
├── config/              # Configuración del sistema
├── controllers/         # Controladores MVC
├── models/             # Modelos de datos
├── views/              # Vistas HTML/PHP
├── public/             # Archivos públicos (CSS, JS, uploads)
├── sql/                # Scripts de base de datos
├── assets/             # Recursos estáticos
└── .htaccess           # Configuración Apache
```

## URLs del Sistema

- `/` - Dashboard principal
- `/login` - Inicio de sesión
- `/register` - Registro de usuarios
- `/movements` - Gestión de movimientos
- `/categories` - Gestión de categorías
- `/reports` - Informes y reportes
- `/profile` - Perfil de usuario
- `/test` - Verificación del sistema

## Datos de Prueba

El sistema incluye datos de ejemplo:

**Usuarios:**
- admin@contabot.com / password123 (Administrador)
- usuario@ejemplo.com / password123 (Personal)
- negocio@ejemplo.com / password123 (Negocio)

## Características Futuras (Roadmap)

- [ ] Generación de informes fiscales completos
- [ ] Implementación de roles: Administrador, Capturista, Consulta
- [ ] Exportación avanzada (PDF/Excel)
- [ ] Integración con cuentas bancarias
- [ ] API REST para integraciones
- [ ] Dashboard con gráficas avanzadas
- [ ] Notificaciones y recordatorios
- [ ] Backup automático

## Soporte

Para reportar problemas o solicitar nuevas características, crear un issue en GitHub.

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.
