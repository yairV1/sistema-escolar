<?php
/**
 * =====================================================
 * SidebarBuilder
 * =====================================================
 * Construye el HTML completo del sidebar a partir de
 * app/config/menu.php. La vista NUNCA debe contener
 * lógica de estructura: solo llama a render().
 *
 * Responsabilidades:
 *  - Filtrar items/secciones por rol de usuario
 *  - Determinar qué item/hijo está activo según la página actual
 *  - Generar HTML semántico con soporte de accordion
 *  - Escapar todo el output para evitar XSS
 *
 * Escalabilidad: agregar 100 módulos más NO requiere
 * tocar esta clase, solo el array de configuración.
 * =====================================================
 */

class SidebarBuilder
{
    private array $menu;
    private string $currentPage;
    private array $userRoles;

    public function __construct(array $menu, string $currentPage, array $userRoles = [])
    {
        $this->menu = $menu;
        $this->currentPage = $currentPage;
        $this->userRoles = $userRoles;
    }

    /**
     * Punto de entrada único. La vista llama esto.
     */
    public function render(): string
    {
        $html  = $this->renderTopLevel($this->menu['top'] ?? []);
        foreach ($this->menu['sections'] ?? [] as $section) {
            if (!$this->userCanSeeSection($section)) {
                continue;
            }
            $html .= $this->renderSection($section);
        }
        return $html;
    }

    // -----------------------------------------------------
    // Nivel superior (ej: "Inicio", sin sección/accordion)
    // -----------------------------------------------------
    private function renderTopLevel(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $out = '<ul class="nav-top-level">';
        foreach ($items as $item) {
            if (!$this->userCanSeeItem($item)) {
                continue;
            }
            $isActive = $this->isPageActive($item['page'] ?? null);
            $out .= sprintf(
                '<li><a href="%s" class="slink%s" data-page="%s">
                    <i class="%s"></i>
                    <span>%s</span>
                </a></li>',
                $this->esc($item['url']),
                $isActive ? ' active' : '',
                $this->esc($item['page'] ?? ''),
                $this->esc($item['icon']),
                $this->esc($item['title'])
            );
        }
        $out .= '</ul>';
        return $out;
    }

    // -----------------------------------------------------
    // Una sección completa (ej: "COMUNIDAD")
    // -----------------------------------------------------
    private function renderSection(array $section): string
    {
        $itemsHtml = '';
        foreach ($section['items'] as $item) {
            if (!$this->userCanSeeItem($item)) {
                continue;
            }
            $itemsHtml .= $this->renderItem($item);
        }

        // Si tras filtrar por rol no queda nada, no mostramos la sección vacía
        if (trim($itemsHtml) === '') {
            return '';
        }

        return sprintf(
            '<div class="nav-section">
                <span class="nav-group-label">%s</span>
                <ul>%s</ul>
            </div>',
            $this->esc($section['section']),
            $itemsHtml
        );
    }

    // -----------------------------------------------------
    // Un item: puede ser link directo o padre con accordion
    // -----------------------------------------------------
    private function renderItem(array $item): string
    {
        $hasChildren = !empty($item['children']);

        if (!$hasChildren) {
            return $this->renderDirectLink($item);
        }

        return $this->renderAccordionItem($item);
    }

    private function renderDirectLink(array $item): string
    {
        $isActive = $this->isPageActive($item['page'] ?? null);

        return sprintf(
            '<li>
                <a href="%s" class="slink%s" data-page="%s">
                    <i class="%s"></i>
                    <span>%s</span>
                    %s
                </a>
            </li>',
            $this->esc($item['url']),
            $isActive ? ' active' : '',
            $this->esc($item['page'] ?? ''),
            $this->esc($item['icon']),
            $this->esc($item['title']),
            $this->renderBadge($item['badge'] ?? null)
        );
    }

    private function renderAccordionItem(array $item): string
    {
        $childrenActive = $this->anyChildActive($item['children']);
        $isOpen = $childrenActive; // el padre queda abierto si un hijo está activo

        $childrenHtml = '';
        foreach ($item['children'] as $child) {
            if (!$this->userCanSeeItem($child)) {
                continue;
            }
            $childActive = $this->isPageActive($child['page'] ?? null);
            $childrenHtml .= sprintf(
                '<li>
                    <a href="%s" class="slink-child%s" data-page="%s">
                        <span class="child-dot"></span>
                        <span>%s</span>
                        %s
                    </a>
                </li>',
                $this->esc($child['url']),
                $childActive ? ' active' : '',
                $this->esc($child['page'] ?? ''),
                $this->esc($child['title']),
                $this->renderBadge($child['badge'] ?? null)
            );
        }

        return sprintf(
            '<li class="nav-parent%s">
                <button type="button" class="slink slink-toggle%s" aria-expanded="%s">
                    <i class="%s"></i>
                    <span>%s</span>
                    %s
                    <i class="fas fa-chevron-right accordion-arrow"></i>
                </button>
                <ul class="nav-children" %s>
                    %s
                </ul>
            </li>',
            $isOpen ? ' is-open' : '',
            $childrenActive ? ' active' : '',
            $isOpen ? 'true' : 'false',
            $this->esc($item['icon']),
            $this->esc($item['title']),
            $this->renderBadge($item['badge'] ?? null),
            $isOpen ? '' : 'style="display:none;"',
            $childrenHtml
        );
    }

    // -----------------------------------------------------
    // Badges con jerarquía semántica de color
    // -----------------------------------------------------
    private function renderBadge(?array $badge): string
    {
        if (!$badge) {
            return '';
        }
        $type = $badge['type'] ?? 'info'; // info | pendiente | urgente | novedad
        return sprintf(
            '<span class="slink-badge badge-%s">%s</span>',
            $this->esc($type),
            $this->esc($badge['text'])
        );
    }

    // -----------------------------------------------------
    // Helpers de estado / permisos
    // -----------------------------------------------------
    private function isPageActive(?string $page): bool
    {
        return $page !== null && $page === $this->currentPage;
    }

    private function anyChildActive(array $children): bool
    {
        foreach ($children as $child) {
            if ($this->isPageActive($child['page'] ?? null)) {
                return true;
            }
        }
        return false;
    }

    private function userCanSeeSection(array $section): bool
    {
        return $this->rolesAllowed($section['roles'] ?? null);
    }

    private function userCanSeeItem(array $item): bool
    {
        return $this->rolesAllowed($item['roles'] ?? null);
    }

    private function rolesAllowed(?array $requiredRoles): bool
    {
        if ($requiredRoles === null) {
            return true; // sin restricción = visible para todos
        }
        if (empty($this->userRoles)) {
            return false;
        }
        return count(array_intersect($requiredRoles, $this->userRoles)) > 0;
    }

    private function esc(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}