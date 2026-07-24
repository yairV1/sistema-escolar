/**
 * Wrapper axios genérico del módulo Calendario. Hoy solo expone el feed
 * (Fase 1, solo lectura); las mutaciones de fases futuras (CRUD, mover,
 * adjuntos, comentarios) se agregan acá sin tocar quién las consume.
 */
export function fetchFeed(feedUrl, params) {
    return window.axios.get(feedUrl, { params }).then((response) => response.data);
}
