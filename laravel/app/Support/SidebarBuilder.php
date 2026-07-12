<?php

namespace App\Support;

/**
 * Construye el HTML del sidebar a partir de config('panel_menu').
 * Port del SidebarBuilder legacy (app/helpers/Panel/SidebarBuilder.php):
 * misma lógica de accordion/badges/roles, solo cambia la resolución de
 * URLs (route() de Laravel o fallback al sistema legacy — ver resolveUrl()).
 */
class SidebarBuilder
{
    public function __construct(
        private array $menu,
        private string $currentPage,
        private array $userRoles = [],
    ) {}

    public function render(): string
    {
        $html = $this->renderTopLevel($this->menu['top'] ?? []);

        foreach ($this->menu['sections'] ?? [] as $section) {
            if (! $this->userCanSeeSection($section)) {
                continue;
            }
            $html .= $this->renderSection($section);
        }

        return $html;
    }

    private function renderTopLevel(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $out = '<ul class="nav-top-level">';
        foreach ($items as $item) {
            if (! $this->userCanSeeItem($item)) {
                continue;
            }
            $isActive = $this->isPageActive($item['page'] ?? null);
            $out .= sprintf(
                '<li><a href="%s" class="slink%s" data-page="%s"><i class="%s"></i><span>%s</span></a></li>',
                $this->resolveUrl($item),
                $isActive ? ' active' : '',
                $this->esc($item['page'] ?? ''),
                $this->esc($item['icon']),
                $this->esc($item['title']),
            );
        }
        $out .= '</ul>';

        return $out;
    }

    private function renderSection(array $section): string
    {
        $itemsHtml = '';
        foreach ($section['items'] as $item) {
            if (! $this->userCanSeeItem($item)) {
                continue;
            }
            $itemsHtml .= $this->renderItem($item);
        }

        if (trim($itemsHtml) === '') {
            return '';
        }

        return sprintf(
            '<div class="nav-section"><span class="nav-group-label">%s</span><ul>%s</ul></div>',
            $this->esc($section['section']),
            $itemsHtml,
        );
    }

    private function renderItem(array $item): string
    {
        return empty($item['children'])
            ? $this->renderDirectLink($item)
            : $this->renderAccordionItem($item);
    }

    private function renderDirectLink(array $item): string
    {
        $isActive = $this->isPageActive($item['page'] ?? null);

        return sprintf(
            '<li><a href="%s" class="slink%s" data-page="%s"><i class="%s"></i><span>%s</span>%s</a></li>',
            $this->resolveUrl($item),
            $isActive ? ' active' : '',
            $this->esc($item['page'] ?? ''),
            $this->esc($item['icon']),
            $this->esc($item['title']),
            $this->renderBadge($item['badge'] ?? null),
        );
    }

    private function renderAccordionItem(array $item): string
    {
        $childrenActive = $this->anyChildActive($item['children']);
        $isOpen = $childrenActive;

        $childrenHtml = '';
        foreach ($item['children'] as $child) {
            if (! $this->userCanSeeItem($child)) {
                continue;
            }
            $childActive = $this->isPageActive($child['page'] ?? null);
            $childrenHtml .= sprintf(
                '<li><a href="%s" class="slink-child%s" data-page="%s"><span class="child-dot"></span><span>%s</span>%s</a></li>',
                $this->resolveUrl($child),
                $childActive ? ' active' : '',
                $this->esc($child['page'] ?? ''),
                $this->esc($child['title']),
                $this->renderBadge($child['badge'] ?? null),
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
                <ul class="nav-children" %s>%s</ul>
            </li>',
            $isOpen ? ' is-open' : '',
            $childrenActive ? ' active' : '',
            $isOpen ? 'true' : 'false',
            $this->esc($item['icon']),
            $this->esc($item['title']),
            $this->renderBadge($item['badge'] ?? null),
            $isOpen ? '' : 'style="display:none;"',
            $childrenHtml,
        );
    }

    private function renderBadge(?array $badge): string
    {
        if (! $badge) {
            return '';
        }

        return sprintf(
            '<span class="slink-badge badge-%s">%s</span>',
            $this->esc($badge['type'] ?? 'info'),
            $this->esc($badge['text']),
        );
    }

    /** Route de Laravel si el módulo ya está migrado; si no, el sistema legacy. */
    private function resolveUrl(array $item): string
    {
        if (! empty($item['route'])) {
            return route($item['route']);
        }

        if (! empty($item['legacy'])) {
            return rtrim(config('legacy.url'), '/').'/'.ltrim($item['legacy'], '/');
        }

        return '#';
    }

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
            return true;
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
