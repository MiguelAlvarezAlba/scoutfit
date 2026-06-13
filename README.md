# ⚽ ScoutFit — Sistema de scouting y compatibilidad de fichajes

Aplicación web que, **antes de fichar a un jugador, calcula en un % si encaja con un club**
según su perfil deportivo, económico, de edad y de valores — y explica por qué.
En esencia, una herramienta de *data scouting* pensada para clubes modestos
(Segunda División y categorías inferiores) que no disponen de grandes departamentos de datos.

Proyecto desarrollado para las prácticas (FCT) del Grado Superior de Desarrollo de Aplicaciones Web (DAW).

## ¿Qué hace?

- **Fichas de jugadores** con datos, radar de personalidad y trayectoria por temporadas.
- **Fichas de equipos** con plantilla y análisis (valor total, edad media, distribución por posiciones).
- **Motor de compatibilidad jugador–club**: un Fit Score (0–100 %) calculado a partir de 5 dimensiones
  ponderadas (encaje deportivo, económico, proyecto/edad, valores y nivel), con regla de veto económico
  y un veredicto explicado.
- **Mejores fichajes para un club**: ranking de los jugadores que mejor encajan en un club concreto.
- **Buscador** de jugadores y **gestión** (alta y edición) desde la propia web.

## Tecnologías

- **Backend:** PHP
- **Base de datos:** MySQL / MariaDB
- **Frontend:** HTML, CSS y JavaScript
- **Gráficos:** Chart.js
- **Entorno:** XAMPP (Apache + MySQL)

## Cómo ejecutarlo en local

1. Instalar [XAMPP](https://www.apachefriends.org/) y arrancar **Apache** y **MySQL**.
2. Copiar este proyecto en `C:\xampp\htdocs\scouting`.
3. Crear la base de datos importando el script `scouting_db.sql` (pendiente de exportar) en phpMyAdmin o MySQL Workbench.
4. Abrir en el navegador: `http://localhost/scouting/`

## El motor de compatibilidad

Cada dimensión devuelve una nota de 0 a 100 según reglas diseñadas con criterio futbolístico,
y el resultado final es la media ponderada. La lógica está centralizada en `includes/motor.php`.

## Estado y limitaciones

- Los datos objetivos (edad, posición, valor de mercado) se introducen manualmente a partir de
  fuentes públicas; la valoración cualitativa (liderazgo, disciplina, compromiso, estilo) la define el club.
- Los pesos y umbrales del motor son ajustables y están calibrados a mano, no derivados de datos históricos.
- Proyecto en desarrollo. Próximos pasos: autenticación de usuarios, validación reforzada,
  búsqueda de jugadores similares e importación de datos reales vía API.

---

Desarrollado por Miguel Álvarez.
