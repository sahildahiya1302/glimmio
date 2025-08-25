<?php
$id = $id ?? basename(__FILE__, '.php') . '-' . uniqid();

// Load section settings from config/settings_data.json or passed $settings
$settings = $settings ?? [];

// Helper function to get setting with default
if (!function_exists('get_setting')) {
    function get_setting($key, $default = null) {
        global $settings;
        return $settings[$key] ?? $default;
    }
}

// Extract settings with defaults
$logoImage = get_setting('logo_image', '');
$logoText = get_setting('logo_text', 'Your store');
$logoTextColor = get_setting('logo_text_color', '#000000');
$logoFontFamily = get_setting('logo_font_family', 'Arial, sans-serif');
$logoFontSize = get_setting('logo_font_size', 24);
$logoWidth = get_setting('logo_width', 120);
$logoAlignment = get_setting('logo_alignment', 'left');
$showSearch = get_setting('show_search', true);
$searchPlaceholder = get_setting('search_placeholder', 'Search Gifts for your dearest...');
$menuItems = get_setting('menu_items', []);
$blocks = $blocks ?? [];
if (!empty($blocks)) {
    $menuItems = [];
    foreach ($blocks as $block) {
        if ($block['type'] === 'menu_item') {
            $menuItems[] = $block['settings'];
        } elseif ($block['type'] === 'logo') {
            $logoImage = $block['settings']['logo_image'] ?? $logoImage;
        } elseif ($block['type'] === 'search_toggle') {
            $showSearch = true;
        }
    }
}
$headerIcons = get_setting('header_icons', []);
$announcementEnabled = get_setting('announcement_enabled', false);
$announcementText    = get_setting('announcement_text', 'Free shipping on orders over \u20b9999!');
$announcementBg      = get_setting('announcement_bg', '#f44336');
$announcementColor   = get_setting('announcement_color', '#ffffff');
$menuOverlayColor    = get_setting('menu_overlay_color', 'rgba(0,0,0,0.7)');
$headerSticky        = get_setting('header_sticky', false);
$backgroundColor     = get_setting('background_color', '#ffffff');
$textColor           = get_setting('text_color', '#000000');
$paddingTop = get_setting('padding_top', 10);
$paddingBottom = get_setting('padding_bottom', 10);
$marginTop = get_setting('margin_top', 0);
$marginBottom = get_setting('margin_bottom', 0);
$entryAnimation = get_setting('entry_animation', 'none');
$animationDelay = get_setting('animation_delay', 0);
$animationRepeat = get_setting('animation_repeat', false);
$showOnDesktop = get_setting('show_on_desktop', true);
$showOnMobile = get_setting('show_on_mobile', true);
$pagesVisibility = get_setting('pages_visibility', []);
$customCss = get_setting('custom_css', '');
$customId = get_setting('custom_id', $id);
$customClass = get_setting('custom_class', '');
$htmlAbove = get_setting('html_above', '');
$htmlBelow = get_setting('html_below', '');

// Device visibility classes
$deviceClasses = [];
if (!$showOnDesktop) $deviceClasses[] = 'hide-desktop';
if (!$showOnMobile) $deviceClasses[] = 'hide-mobile';

// Page visibility logic (simplified, assuming current page slug available as $currentPageSlug)
$currentPageSlug = basename($_SERVER['PHP_SELF'], '.php');
$pageVisible = empty($pagesVisibility) || in_array($currentPageSlug, $pagesVisibility);
if (!$pageVisible) {
    echo "<!-- Section hidden on this page -->";
    return;
}

// Animation attributes
$animationAttrs = "data-animation='{$entryAnimation}' data-delay='{$animationDelay}' data-repeat='" . ($animationRepeat ? 'true' : 'false') . "'";

// Inline styles
$inlineStyles = "background-color: {$backgroundColor}; color: {$textColor}; padding-top: {$paddingTop}px; padding-bottom: {$paddingBottom}px; margin-top: {$marginTop}px; margin-bottom: {$marginBottom}px;";

// Helper functions for icons
if (!function_exists('render_icon')) {
    function render_icon($iconType) {
        return "<svg width='24' height='24' aria-hidden='true'><use xlink:href='#icon-{$iconType}'></use></svg>";
    }
}

?>

    <style>
   
    .power-header.sticky {
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .announcement-bar {
        font-size: 0.85rem;
        padding: 4px 10px;
        text-align: center;
    }
    .power-header .logo {
        display: flex;
        align-items: center;
        justify-content: <?php echo $logoAlignment === 'left' ? 'flex-start' : ($logoAlignment === 'center' ? 'center' : 'flex-end'); ?>;
    }
    .power-header .logo img {
        max-width: <?php echo intval($logoWidth); ?>px;
        height: auto;
    }
    .power-header .main-navigation .menu-list,
    .power-header .mobile-navigation .mobile-menu-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        gap: 1rem;
    }
    .power-header .main-navigation .menu-item a,
    .power-header .mobile-navigation .mobile-menu-item a {
        text-decoration: none;
        color: inherit;
    }
    .power-header .header-icons {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    .power-header .header-icons a {
        color: inherit;
        text-decoration: none;
        position: relative;
    }
    .power-header .header-icons .badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: red;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 0.75rem;
    }
    /* Hide mobile menu toggle button on desktop */
    .mobile-menu-toggle {
        display: none;
    }
    /* Show mobile menu toggle button on mobile */
    @media (max-width: 768px) {
        .mobile-menu-toggle {
            display: inline-flex !important;
        }
        /* Hide expanded navigation menu on mobile */
        .main-navigation {
            display: none !important;
        }
        /* Show mobile menu drawer close button only on mobile */
        .mobile-menu-close {
            display: inline-block;
        }
    }
    /* Hide mobile menu close button on desktop */
    .mobile-menu-close {
        display: none;
    }
    .mobile-menu-drawer {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: <?php echo htmlspecialchars($menuOverlayColor); ?>;
        display: none;
        flex-direction: column;
        padding: 1rem;
        box-sizing: border-box;
        z-index: 9999;
    }
    .search-suggestions {
        position: absolute;
        left: 0;
        right: 0;
        top: 100%;
        background: #fff;
        border: 1px solid #ddd;
        z-index: 50;
    }
    .search-suggestions div {
        padding: 4px 8px;
        cursor: pointer;
    }
    .search-suggestions div:hover {
        background: #f5f5f5;
    }
    </style>

<?php if ($announcementEnabled): ?>
<div class="announcement-bar" style="background: <?= htmlspecialchars($announcementBg) ?>; color: <?= htmlspecialchars($announcementColor) ?>;">
    <?= htmlspecialchars($announcementText) ?>
</div>
<?php endif; ?>

<header
    id="<?= htmlspecialchars($customId) ?>"
    class="power-header <?= htmlspecialchars($customClass) . ' ' . implode(' ', $deviceClasses) ?><?= $headerSticky ? ' sticky' : '' ?>"
    role="banner"
    style="<?= $inlineStyles ?>"
    <?= $animationAttrs ?>
>
        <?= $htmlAbove ?>
    <div class="container" style="display: flex; flex-direction: column; padding: 0 1rem;">
        <div class="top-row" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: nowrap; padding: 0.5rem 0;">
            <!-- Mobile menu toggle button -->
            <button class="mobile-menu-toggle" aria-label="Open mobile menu" style="background: none; border: none; cursor: pointer; padding: 0; margin-right: 1rem; display: none;">
                <svg width="24" height="24" aria-hidden="true" fill="currentColor"><use xlink:href="#icon-menu"></use></svg>
            </button>

            <!-- Logo (Left) -->
            <div class="header-left logo" style="flex: 0 1 auto; display: flex; align-items: center;">
                <a href="/" aria-label="Home" style="display: flex; align-items: center; text-decoration: none;">
                <?php if ($logoImage): ?>
                    <img src="<?= htmlspecialchars($logoImage) ?>" alt="<?= htmlspecialchars($settings['store_name'] ?? 'your store') ?>" style="max-width: <?= intval($logoWidth) ?>px; height: auto;" />
                <?php else: ?>
                    <span class="store-name" style="color: <?= htmlspecialchars($logoTextColor) ?>; font-family: <?= htmlspecialchars($logoFontFamily) ?>; font-size: <?= intval($logoFontSize) ?>px; font-weight: 700; letter-spacing: 0.1em;">
                        <?= htmlspecialchars($settings['store_name'] ?? 'Your Store') ?>
                    </span>
                <?php endif; ?>
                </a>
            </div>

            <!-- Search (Center) -->
            <?php if ($showSearch): ?>
            <div class="header-center" style="flex: 1 1 60%; display: flex; justify-content: center;">
                <form class="search-container" role="search" method="get" action="/search" style="width: 100%; max-width: 300px; position: relative;">
                    <input type="search" id="site-search" name="q" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" aria-label="Search products" autocomplete="off" style="width: 100%; padding: 0.5rem 2.5rem 0.5rem 1rem; border-radius: 20px; border: 1px solid #ccc; background-color: #f5f0e9; font-size: 1rem;" />
                    <button class="search-button" aria-label="Search" type="submit" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0;">
                        <svg width="20" height="20" aria-hidden="true" fill="currentColor" style="color: #333;"><use xlink:href="#icon-search"></use></svg>
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Icons (Right) -->
            <div class="header-right header-icons" style="flex: 0 1 auto; display: flex; justify-content: flex-end; align-items: center; gap: 1rem;">
                <?php
                $explicitIcons = [
                    ['icon_type' => 'heart', 'url' => '/wishlist', 'label' => 'Wishlist', 'show_badge' => true],
                    ['icon_type' => 'shopping-bag', 'url' => '/cart', 'label' => 'Cart', 'show_badge' => true],
                    ['icon_type' => 'user', 'url' => '/profile', 'label' => 'Profile', 'show_badge' => false],
                ];
                foreach ($explicitIcons as $icon):
                    $iconType = $icon['icon_type'];
                    $url = $icon['url'];
                    $label = $icon['label'];
                    $showBadge = $icon['show_badge'];
                ?>
                    <a href="<?= htmlspecialchars($url) ?>" aria-label="<?= htmlspecialchars($label) ?>" class="header-icon-link" style="position: relative; display: inline-flex; align-items: center; color: inherit; text-decoration: none;">
                        <?= render_icon($iconType) ?>
                        <?php if ($showBadge): ?>
                            <span class="badge" style="position: absolute; top: -6px; right: -6px; background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.75rem;">0</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Expanded navigation menu (desktop) -->
        <nav class="main-navigation" role="navigation" aria-label="Main menu" style="display: flex; justify-content: center; border-top: 1px solid #ddd; padding: 0.5rem 0;">
            <ul class="menu-list" style="list-style: none; margin: 0; padding: 0; display: flex; gap: 1.5rem;">
                <?php foreach ($menuItems as $item): ?>
                    <?php
                    $showItemDesktop = $item['show_on_desktop'] ?? true;
                    $showItemMobile = $item['show_on_mobile'] ?? true;
                    $itemClasses = [];
                    if (!$showItemDesktop) $itemClasses[] = 'hide-desktop';
                    if (!$showItemMobile) $itemClasses[] = 'hide-mobile';
                    ?>
                    <li class="menu-item <?php echo implode(' ', $itemClasses); ?>">
                        <a href="<?php echo htmlspecialchars($item['url'] ?? '#'); ?>" style="text-decoration: none; color: inherit; font-weight: 600;">
                            <?php echo htmlspecialchars($item['label'] ?? ''); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>


    <!-- Mobile drawer menu -->
    <div class="mobile-menu-drawer" aria-hidden="true">
        <button class="mobile-menu-close" aria-label="Close mobile menu">&times;</button>
        <nav class="mobile-navigation" role="navigation" aria-label="Mobile menu">
            <ul class="mobile-menu-list">
                <?php foreach ($menuItems as $item): ?>
                    <?php
                    $showItemDesktop = $item['show_on_desktop'] ?? true;
                    $showItemMobile = $item['show_on_mobile'] ?? true;
                    $itemClasses = [];
                    if (!$showItemDesktop) $itemClasses[] = 'hide-desktop';
                    if (!$showItemMobile) $itemClasses[] = 'hide-mobile';
                    ?>
                    <li class="mobile-menu-item <?php echo implode(' ', $itemClasses); ?>">
                        <a href="<?php echo htmlspecialchars($item['url'] ?? '#'); ?>">
                            <?php echo htmlspecialchars($item['label'] ?? ''); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
    <?php echo $htmlBelow; ?>
</header>

<script>
    // Mobile menu toggle script
document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const menuDrawer = document.querySelector('.mobile-menu-drawer');
    const menuClose = document.querySelector('.mobile-menu-close');

    const searchInput = document.getElementById('site-search');
    if (searchInput) {
        const box = document.createElement('div');
        box.className = 'search-suggestions';
        searchInput.parentNode.appendChild(box);
        const suggestions = ['ring','necklace','bracelet','earrings'];
        searchInput.addEventListener('input', function(){
            const q = this.value.toLowerCase();
            box.innerHTML = '';
            if(!q){ box.style.display='none'; return; }
            suggestions.filter(s=>s.includes(q)).forEach(s=>{
                const item=document.createElement('div');
                item.textContent=s;
                item.addEventListener('mousedown', ()=>{ searchInput.value=s; box.style.display='none'; });
                box.appendChild(item);
            });
            box.style.display = box.childElementCount ? 'block':'none';
        });
    }

        menuToggle.addEventListener('click', function () {
            menuDrawer.setAttribute('aria-hidden', 'false');
            menuDrawer.style.display = 'block';
        });

        menuClose.addEventListener('click', function () {
            menuDrawer.setAttribute('aria-hidden', 'true');
            menuDrawer.style.display = 'none';
        });
    });
</script>
