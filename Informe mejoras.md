# 🍺 Informe de Análisis y Propuesta de Valor: Proyecto Birra Finder

**Fecha:** 13 de Enero de 2026  
**Objetivo:** Identificar áreas de mejora técnica y proponer funcionalidades únicas (USP) para diferenciar el proyecto en el mercado.

---

## 1. Áreas de Mejora (Técnicas y UX)
*Lo que "debe" tener para funcionar bien y aprobar con nota en DAM.*

### A. Optimización de la API de Mapas
Las apps de este tipo suelen fallar por cargar demasiados marcadores a la vez.

- **Mejora:** Implementar *Clustering*. Cuando el usuario aleja el mapa, no muestres 50 pines superpuestos; muestra un círculo con el número "50". Al hacer zoom, se desglosan.
- **Técnica:** En JavaScript, utiliza la librería `MarkerClusterer` junto con la API de Google Maps.
- **Backend:** Asegúrate de que tu consulta SQL filtre por coordenadas (*bounding box*) para no traer bares de Madrid si el usuario está en Valencia.

### B. Estandarización del Código (Tu Marca Personal)
Para que el proyecto sea escalable y profesional, mantén tu disciplina de código:

- **Estructura:** Separa la lógica (PHP) de la vista (HTML). Aunque uses PHP puro, intenta simular un patrón MVC simple.
- **Comentarios:** Mantén tus `## BLOQUES LÓGICOS ##` y *Docstrings* al inicio. Esto es vital si en el futuro quieres mostrar este código en una entrevista de trabajo.
- **Nomenclatura:** Revisa que todas las columnas de tu MySQL estén en `snake_case` (ej. `precio_medio`, `tiene_terraza`) para que coincidan con tu lógica de variables en Python/PHP.

### C. Enfoque "Mobile First" Agresivo
Nadie busca un bar desde el ordenador de sobremesa.

- **Mejora:** La interfaz debe ser botones grandes, pocas opciones de texto y carga inmediata.
- **Acción:** Usa *media queries* en CSS para ocultar elementos decorativos en pantallas móviles y priorizar el mapa y el botón "Cómo llegar".

---

## 2. Diferenciación: Lo que la competencia NO tiene
*Aquí es donde ganas a Untappd y Google Maps, atacando necesidades locales (especialmente en España/Valencia).*

### 🌟 La "Economía de la Tapa" (El Factor Clave)
Las apps americanas no entienden el concepto de tapa gratis.

- **Feature:** Un sistema de valoración doble: Calidad de la Cerveza vs. Calidad de la Tapa Gratuita.
- **Filtro Único:** "¿Buscas solo beber o cenar gratis con 3 cañas?".
- **Dato:** Campo booleano en SQL `tapa_gratis` (T/F) y `ranking_tapa` (1-5).

### ☀️ El Buscador de "Sol y Sombra"
En ciudades como Valencia, la orientación de la terraza es vital. En invierno buscas sol, en verano buscas sombra.

- **Feature:** Integrar un filtro de "Terraza al Sol ahora mismo".
- **Cómo funciona:** Puede ser manual (usuario reporta) o calculado cruzando la orientación del mapa con la hora del día (más complejo, pero impresionante para un TFG).

### 💸 El "Índice de la Caña" (Precios Reales)
Google Maps te dice "€" o "€€€", lo cual es vago.

- **Feature:** Los usuarios reportan el precio exacto de la caña o el tercio.
- **Utilidad:** Mapa de calor de precios. *"Muestrame dónde beber por menos de 2€"*.
- **Gamificación:** El usuario que actualiza el precio gana puntos en la plataforma.

### 🔊 Medidor de "Vibe" (Ambiente)
A veces quieres una cerveza tranquila para charlar, otras veces quieres lío.

- **Feature:** Categorización por ambiente acústico.
    - **Nivel 1:** Cita romántica / Estudio.
    - **Nivel 2:** Charla con amigos.
    - **Nivel 3:** Previa / Música alta / Fútbol.
- **Implementación:** Un simple `SELECT` en un *dropdown* al filtrar.

---

## 3. Resumen de Estructura de Datos Sugerida
Para soportar estas nuevas ideas, tu tabla principal de bares en MySQL necesitaría campos extra que la competencia suele ignorar:

| Campo (snake_case) | Tipo de Dato | Descripción |
| :--- | :--- | :--- |
| `precio_cana` | `DECIMAL(4,2)` | Precio exacto reportado |
| `tiene_tapa_gratis` | `BOOLEAN` | 1 si ponen tapa, 0 si no |
| `tipo_ambiente` | `ENUM` | 'Tranquilo', 'Ruidoso', 'Deportivo' |
| `orientacion_terraza` | `VARCHAR` | 'Sol', 'Sombra', 'Interior' |