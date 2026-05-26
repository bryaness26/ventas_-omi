# Ventas Ñomi

Sistema de gestión de ventas y gastos diseñado con un enfoque moderno y modular. Incluye manejo seguro de sesiones y cálculo de montos dinámicos en base a la tasa de cambio oficial del Banco Central de Venezuela (BCV).

## Características Principales

- **Gestión de Usuarios:** Registro e inicio de sesión seguro utilizando hashes para contraseñas.
- **Módulo de Ventas:** Registro, edición, visualización y eliminación de ventas realizadas.
- **Módulo de Gastos:** Registro, edición, visualización y eliminación de gastos del negocio.
- **Integración BCV:** Consulta automática de la tasa de cambio oficial del dólar con un sistema de caché de 1 hora para optimizar el rendimiento.
- **Cálculos en Tiempo Real:** Conversiones automáticas entre Bolívares y Dólares al registrar operaciones.
- **Diseño Moderno (UI/UX):** Interfaz estilizada usando técnicas de Glassmorphism, completamente responsiva, y con modales de confirmación personalizados.

## Tecnologías Utilizadas

- **Frontend:** HTML5, CSS3 (Vanilla), JavaScript, Diseño Glassmorphism.
- **Backend:** PHP 8+ (PDO), MariaDB / MySQL.
- **Librerías Adicionales:** Fetch API / cURL (para consultas y web scraping de la tasa del dólar).

## Instalación

1. Clona este repositorio en la carpeta correspondiente de tu servidor local (ej. `htdocs` en XAMPP).
   ```bash
   git clone https://github.com/bryaness26/ventas_-omi.git
   ```
2. Crea una base de datos en MariaDB/MySQL (por ejemplo, `ventas_nomi`).
3. Importa la estructura de la base de datos que se encuentra en `database/schema.sql`.
4. Configura tus credenciales de base de datos en `config/database.php`.
5. Accede al sistema desde tu navegador web y comienza a registrar tus datos.

## Autor

- [@bryaness26](https://github.com/bryaness26)
