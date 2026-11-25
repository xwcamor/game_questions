# PlayLab — Kahoot multijugador

**Este repositorio contiene el código fuente de PlayLab directamente en la carpeta principal.** Los archivos antiguos de evaluaciones se conservan en `old/`.

PlayLab permite crear cuestionarios, diseñar preguntas, abrir salas públicas o privadas con código de acceso, recibir participantes y competir con cronómetro, puntuación y ranking.

## Funcionalidades

- Registro, inicio, cierre y recuperación de cuentas.
- Roles de administrador y anfitrión.
- Cuestionarios reutilizables y preguntas con 2 a 6 alternativas.
- Preguntas de selección única, selección múltiple y verdadero/falso.
- Marcado de respuestas correctas y explicación opcional.
- Tiempo global por cuestionario y tiempo individual opcional.
- Salas públicas y privadas con código de seis caracteres, contraseña opcional y límite de participantes.
- Sala de espera, control del anfitrión, revelación de respuestas y podio.
- Una respuesta por participante y pregunta; el servidor calcula tiempo y puntos.
- Mezcla de preguntas y alternativas.
- Sonidos, temporizador, animaciones y confeti con control para silenciar.
- Cuestionario de demostración con 40 preguntas de Sistemas Operativos, cada una con seis alternativas.

## Estructura principal

```text
game_questions/
├── app/                  # Controladores y modelos de PlayLab
├── database/
│   ├── migrations/       # Usuarios, preguntas, salas y respuestas
│   └── seeders/          # Admin y 40 preguntas de demostración
├── resources/
│   ├── css/              # Diseño visual y responsive
│   ├── js/               # Cliente de la partida en vivo
│   └── views/            # Inicio, panel, cuentas, quizzes y salas
├── routes/web.php
├── tests/
├── INSTALAR.bat
├── README.md
└── old/                  # HTML y ZIP anteriores
```

## Requisitos

- Windows 10/11 y Laragon.
- PHP 8.3 o superior compatible con Laravel 13.
- Composer 2.
- Node.js LTS y npm.
- MySQL 8 o MariaDB compatible.

## Preparar el proyecto en la carpeta principal

Clona el repositorio dentro de Laragon:

```bat
cd C:\laragon\www
git clone https://github.com/xwcamor/game_questions.git
cd game_questions
```

Abre la terminal de Laragon **como administrador solo si tu instalación lo requiere** y ejecuta:

```bat
INSTALAR.bat
```

El instalador crea una base limpia de Laravel 13 en una carpeta temporal y combina sus archivos de framework con este código, sin sobrescribir las vistas, rutas, estilos, seeders ni controladores de PlayLab. El resultado queda en **esta misma carpeta principal**.

Si prefieres hacerlo manualmente, crea una instalación limpia de Laravel 13 en una carpeta temporal y combina sus archivos de framework con este repositorio. No reemplaces los archivos personalizados indicados arriba.

## Base de datos MySQL automática

PlayLab utiliza **MySQL como base principal**. El archivo `.env.example` ya contiene la configuración de MySQL para Laragon:

- Host: `127.0.0.1`
- Puerto: `3306`
- Base de datos: `playlab`
- Usuario predeterminado: `root`
- Contraseña predeterminada: vacía (configuración habitual de Laragon)

No tienes que crear la base manualmente. `INSTALAR.bat` ejecuta `scripts/setup-database.php`, que conecta con MySQL y crea `playlab` mediante `CREATE DATABASE IF NOT EXISTS`. Después ejecuta automáticamente `php artisan migrate --seed --force`, que crea las tablas y carga el administrador y el cuestionario de demostración.

Si tu instalación de MySQL tiene una contraseña distinta, coloca ese valor en `DB_PASSWORD` dentro de `.env` y vuelve a ejecutar `INSTALAR.bat`. No publiques el archivo `.env`.

El instalador también reconoce el `.env` antiguo que Laravel creó con SQLite y lo convierte al perfil MySQL predeterminado de Laragon.

Abre `http://127.0.0.1:8000`.

## Acceso inicial

| Perfil | Correo | Contraseña |
|---|---|---|
| Administrador de demostración | `admin@kahoot.local` | `DemoAdmin2026!` |

Cambia esta contraseña antes de publicar la aplicación. Los usuarios que se registren reciben el perfil `host`.

## Probar las 40 preguntas

1. Ingresa con la cuenta administradora o registra un anfitrión.
2. Abre **Cuestionarios** y elige **Evaluación de Sistemas Operativos**.
3. Crea una sala pública o privada y comparte su código de seis caracteres.
4. Abre otro navegador o dispositivo, ingresa a la sala con un nombre y avatar.
5. Desde el anfitrión inicia la partida y responde desde el segundo dispositivo.

La respuesta correcta no se envía al cliente mientras la pregunta está activa. La puntuación se calcula en el servidor: una respuesta correcta puede valer hasta 1,000 puntos según el tiempo restante; una incorrecta o no respondida vale cero.

## Sincronización de salas

Esta versión utiliza consultas HTTP periódicas (aproximadamente cada 1,1 segundos) para actualizar la sala. Es suficiente para probar en una red local con grupos pequeños; no es una conexión WebSocket. Para concurrencia alta se recomienda una siguiente etapa con Laravel Reverb y Redis.

## Probar desde celulares en la misma red

En la computadora con Laragon ejecuta:

```bat
php artisan serve --host=0.0.0.0 --port=8000
```

Consulta la IPv4 con `ipconfig` y abre en el celular conectado al mismo Wi-Fi `http://IP-DE-TU-PC:8000`. En producción se necesita HTTPS y un servidor de aplicaciones configurado.

## Archivos anteriores

Los archivos `index.html`, `index_v2.html` y `aula_quiz_live_profesional_participantes (1).zip` se movieron a `old/` para conservarlos sin mezclar versiones antiguas con el proyecto actual.

## Importante

El repositorio contiene el código de PlayLab y un instalador que prepara Laravel en esta carpeta. No se suben `vendor/`, credenciales locales ni el archivo `.env`. La primera ejecución requiere Composer, MySQL y Node.js instalados.
