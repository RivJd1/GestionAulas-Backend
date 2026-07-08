# Sistema de Asignación de Aulas y Secciones

Sistema web para digitalizar y automatizar la asignación de aulas, docentes y horarios en la Universidad Católica de Honduras (UNICAH), Campus San Isidro. Reemplaza el proceso manual actual basado en hojas de Excel y correos internos entre departamentos, centralizando la información y detectando conflictos de horario antes de que una asignación se guarde.

## Institución y curso

- **Universidad:** Universidad Católica de Honduras (UNICAH) — Campus San Isidro, La Ceiba, Atlántida
- **Carrera:** Ingeniería en Ciencias de la Computación
- **Curso:** Portales Web
- **Docente:** Ing. Jonatan Moncada

## Integrantes

| Número de Identidad | Nombre completo | 
|---|---|
| 0101200804041 | Francisco Joel Cruz Fernández | 
| 0101200600754 | Alex Gabriel Funez Romero | 
| 0101200603305 | Denis David Zuniga Moreno | 
| 0101200504387 | Leonardo Enrique Hernandez Melendez |
| 0101200503909 | Diego Jafet Espinoza Rivas | 
| 0101200503070 | Jose Daniel Martinez Meza | 
| 02092007002211 | Jassiel Antonio Hernandez Zelaya |
| 0901200501448 | Darwyn Antonio Godoy Simpson | 
| 0101200600397 | Josué David Rivera Ortega | 
| 0101200404134 | Laura Fabiola Berganza Molina | 
| 0101200500703 | Nain Jaziz Perdomo Caceres | 
| 0203200500156 | Nayeri Gissel Melendres Centeno | 
| 0101200401263 | Oscar Javier Agurcia Sosa | 
| 0101200603776 | Mikel Antonio Romero Salinas | 
| 0801200510035 | Mauricio Daniel Rivera Rodriguez | 
| 0101200503660 | Alberto Daniel Galindo Sorto | 
| 0104200700013 | Milexcy Nicol Perez Gonzalez | 
| 0205200400241 | Odry Alejandra Hernandez Romero | 
| 0101200503429 | Wilfredo Daniel Dominguez Martinez | 
| 0101200500046 | Dunia Yineyri Martinez Diaz | 
| 1807200500493 | Jose Remberto Rosales Castro | 

## Tecnologías

| Tecnología | Uso |
|---|---|
| PHP 8.2+ | Lenguaje base del backend |
| Laravel 12 | Framework backend — rutas, controladores, Eloquent ORM, migraciones |
| MySQL | Base de datos relacional |
| Vite 7 | Compilación de assets del frontend |
| Tailwind CSS 4 | Estilos del frontend |
| Faker (fakerphp/faker) | Generación de datos de prueba en factories/seeders |
| PHPUnit | Pruebas automatizadas |
| Git | Control de versiones |

## Roles del sistema

El sistema maneja dos roles a un mismo nivel jerárquico:

- **Coordinador Académico:** opera el día a día — gestiona docentes, aulas y secciones, hace las asignaciones de sección a aula, arma los horarios semanales y resuelve los conflictos que el sistema le señale.
- **Administrador:** alcance acotado a mantenimiento del sistema — gestiona las cuentas de usuario y crea/abre/cierra los Periodos Académicos.

## Modelo de datos

El esquema distingue tres tipos de datos:

1. **Catálogos permanentes** (`docentes`, `aulas`, `secciones`): se registran una sola vez y se reutilizan en todos los períodos futuros.
2. **Periodos académicos** (`periodos_academicos`): una etiqueta de tiempo que agrupa todas las asignaciones de un mismo semestre. Solo puede existir un periodo activo a la vez (regla aplicada por el backend, no por la base de datos).
3. **Asignaciones e instancias por periodo** (`asignaciones`, `sesiones_horario`): lo que cambia cada periodo — aula, docente real, matrícula y horario específico — vinculado siempre al periodo correspondiente.

Las validaciones de negocio (sobrecupo, conflicto de aula, conflicto de docente, bloqueo por mantenimiento o licencia, un solo periodo activo) viven en el backend, no en la base de datos. La base de datos solo garantiza integridad estructural: llaves foráneas, campos obligatorios, correos y nombre+edificio únicos, y que no se duplique sección+periodo.

## Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configura tu conexión MySQL en `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), y luego:

```bash
php artisan migrate:fresh --seed
npm run dev
```

## Datos de prueba (seeders)

`php artisan db:seed` (incluido en el `migrate:fresh --seed` de arriba) genera:

- 1 Administrador y varias cuentas de Coordinador.
- Docentes en sus tres estados (activo, licencia, inactivo), con departamento opcional.
- Aulas disponibles y en mantenimiento, incluyendo un caso que demuestra que el mismo número de salón puede repetirse en edificios distintos.
- Secciones de un catálogo real de materias, algunas sin docente titular todavía.
- 3 periodos académicos (2 cerrados, 1 activo).
- Asignaciones en distintos estados del flujo (pendientes de aula/docente, con aula/docente pero sin horario, completamente asignadas), incluyendo un caso de sobrecupo confirmado y una comparación de matrícula de la misma sección entre dos periodos distintos.
- Horarios semanales sin conflictos de aula ni de docente, con bloques marcados como generados automáticamente cuando corresponde a secciones de varias sesiones por semana.

Credenciales de prueba (contraseña por defecto: `password`):

| Correo | Rol |
|---|---|
| admin@unicah.edu.hn | Administrador |
| coordinador@unicah.edu.hn | Coordinador |
