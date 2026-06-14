# 🗺️ Hoja de ruta de ScoutFit

Visión del producto y funcionalidades futuras, ordenadas por fases.
La filosofía no cambia: **los datos objetivos se importan; la valoración la pone el club.**

## ✅ Hecho

- Fichas de jugadores y equipos (estilo Transfermarkt).
- Motor de compatibilidad jugador–club (Fit Score con 5 dimensiones + veto + veredicto).
- Ranking de mejores fichajes para un club.
- Buscador y columnas ordenables.
- CRUD completo de jugadores y equipos desde la web.
- Login con sesiones y contraseñas cifradas (lectura pública, escritura privada).
- Seguridad: prepared statements (SQLi) + htmlspecialchars (XSS).
- Importación de datos reales desde API-Football (plantillas y equipos).

## 🔜 Fase 1 — Perfil de scouting más rico (fácil)

Enriquecer la ficha del jugador con conceptos de ojeo real:

- **Posiciones secundarias**: las posiciones que también puede ocupar
  (un jugador rara vez juega en una sola).
- **Rol**: el rol concreto dentro de su posición
  (ej. un mediocentro puede ser *pivote*, *box-to-box*, *organizador*).

> Son valoraciones del cuerpo técnico → las rellena el club, como el resto del perfil.

## 🚀 Fase 2 — Motor v2: encaje táctico

Que el rol y la formación entren en el cálculo del Fit Score:

- **Formación donde rinde** el jugador (4-3-3, 4-4-2…).
- El motor cruza el rol/formación del jugador con lo que **necesita el club**
  en su sistema → un encaje deportivo mucho más fino y creíble.
  Ej.: *"el club juega 4-3-3 y busca un interior; este jugador rinde de interior
  en 4-3-3 → encaje deportivo altísimo"*.

## 🌐 Fase 3 — Producto

- **Despliegue online** (URL pública compartible).
- **Jugadores similares**: dado un jugador, mostrar perfiles parecidos
  (alternativas de fichaje) mediante una "distancia" entre atributos.
- Validación reforzada y diseño responsive (móvil).
- Importación automática de datos reales más amplia vía API.

---

*Documento vivo. Refleja la dirección del proyecto, no todo está implementado.*
