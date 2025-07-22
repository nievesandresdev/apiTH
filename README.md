# 🚀 API Central de Gestión Hotelera

**Motor principal del ecosistema SaaS + WebApp**, diseñada para gestionar todas las operaciones entre hoteles, huéspedes y plataformas externas.

### 🛠️ **Contribuciones Técnicas Clave**
- **Arquitectura Backend**: Implementé el núcleo del sistema usando:
  - `Laravel 10` (API RESTful con Service Layer)
  - `MySQL` (Diseño relacional optimizado)
  - `Redis` (Caché y sesiones)

- **Features Destacadas**:
  - ✅ **Sistema de reputación automático** con algoritmo propio
  - ✅ **Autenticación dual** (JWT para operadores + tokens temporales para huéspedes)
  - ✅ **Procesamiento asíncrono** con Laravel Queues (5K+ operaciones/día)
  - ✅ **WebSockets** integrados para notificaciones push

- **Principios Técnicos**:
  ```bash
  # Arquitectura escalable
  - Service Layer Pattern
  - Código 100% documentado
  - Pruebas unitarias (PHPUnit)
