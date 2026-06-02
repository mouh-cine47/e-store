

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop — E-Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           VARIABLES & RESET
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
        
        :root {
            --bg-primary: #F8F7F4;
            --bg-white: #FFFFFF;
            --text-primary: #111111;
            --text-secondary: #6B6B6B;
            --text-tertiary: #9B9B9B;
            --accent: #8B9D84;
            --accent-dark: #6B7D64;
            --accent-light: #E8EFE6;
            --border: #D9D7D1;
            --border-light: #E8E6E0;
            --shadow-xs: 0 1px 3px rgba(17, 17, 17, 0.05);
            --shadow-sm: 0 2px 8px rgba(17, 17, 17, 0.08);
            --shadow-md: 0 4px 16px rgba(17, 17, 17, 0.12);
            --shadow-lg: 0 8px 24px rgba(17, 17, 17, 0.15);
            --shadow-xl: 0 12px 40px rgba(17, 17, 17, 0.2);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.2s ease-out;
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
            --spacing-2xl: 48px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           NAVBAR
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .navbar {
            background: var(--bg-white);
            border-bottom: 1px solid var(--border-light);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 999;
            transition: all 0.3s ease;
            box-shadow: 0 1px 0 rgba(0,0,0,0.04);
        }

        .navbar.shrink {
            padding: 0.75rem 0;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-xl);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
            letter-spacing: -0.5px;
            flex-shrink: 0;
        }

        .navbar-nav {
            display: flex;
            gap: var(--spacing-xl);
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .nav-link {
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            position: relative;
            transition: color 0.2s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--accent);
            transition: width 0.3s ease;
        }

        .nav-link:hover {
            color: var(--text-primary);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            margin-left: auto;
        }

        .nav-icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 1.1rem;
            transition: color 0.2s ease;
            position: relative;
            padding: 0;
        }

        .nav-icon-btn:hover {
            color: var(--text-primary);
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: var(--spacing);
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .logout-btn {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .logout-btn:hover {
            color: var(--accent);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           HERO SECTION
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .hero {
            background: linear-gradient(135deg, var(--accent-light) 0%, var(--bg-primary) 100%);
            padding: 80px var(--spacing-lg);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            animation: fadeInUp 0.8s ease-out;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 6vw, 3.5rem);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--spacing-lg);
            line-height: 1.1;
            letter-spacing: -0.5px;
        }

        .hero p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-xl);
            line-height: 1.6;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           CONTAINER & MAIN LAYOUT
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }

        .shop-wrapper {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: var(--spacing-2xl);
            padding: var(--spacing-2xl) 0;
            min-height: 100vh;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           FILTERS SIDEBAR
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .filters-panel {
            position: sticky;
            top: 80px;
            height: fit-content;
        }

        .filter-title-main {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--text-primary);
        }

        .filter-group {
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-xl);
            border-bottom: 1px solid var(--border-light);
        }

        .filter-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .filter-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }

        .filter-label:hover {
            color: var(--accent);
        }

        .filter-toggle {
            font-size: 0.75rem;
            transition: transform 0.3s ease;
        }

        .filter-group.collapsed .filter-toggle {
            transform: rotate(-90deg);
        }

        .filter-content {
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .filter-group.collapsed .filter-content {
            max-height: 0;
        }

        .filter-option {
            display: flex;
            align-items: center;
            margin-bottom: var(--spacing-sm);
        }

        .filter-option input {
            margin-right: var(--spacing-sm);
            accent-color: var(--accent);
            cursor: pointer;
        }

        .filter-option label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.2s ease;
            flex: 1;
        }

        .filter-option input:checked + label {
            color: var(--text-primary);
            font-weight: 600;
        }

        .filter-option label:hover {
            color: var(--accent);
        }

        /* Color swatches */
        .color-swatches {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
        }

        .color-swatch {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .color-swatch:hover {
            transform: scale(1.1);
        }

        .color-swatch.active {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px var(--bg-primary), 0 0 0 4px var(--accent);
        }

        /* Size pills */
        .size-pills {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
        }

        .size-pill {
            padding: var(--spacing-sm) var(--spacing);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            background: var(--bg-white);
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .size-pill:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .size-pill.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           MAIN CONTENT
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .shop-content {
            display: flex;
            flex-direction: column;
        }

        .shop-header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid var(--border-light);
        }

        .shop-title {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .shop-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .results-count {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .sort-control {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .sort-control label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .sort-select {
            padding: var(--spacing-sm) var(--spacing);
            border: 1px solid var(--border-light);
            border-radius: 4px;
            background: var(--bg-white);
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sort-select:hover,
        .sort-select:focus {
            border-color: var(--accent);
            outline: none;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           ACTIVE FILTERS
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            background: var(--accent-light);
            color: var(--accent-dark);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .filter-tag button {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
            transition: opacity 0.2s ease;
        }

        .filter-tag button:hover {
            opacity: 0.7;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           PRODUCT GRID
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-2xl);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           PRODUCT CARD
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .product-card {
            background: var(--bg-white);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--shadow-xs);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            animation: cardFadeIn 0.6s ease-out backwards;
        }

        .product-card:nth-child(1) { animation-delay: 0.05s; }
        .product-card:nth-child(2) { animation-delay: 0.1s; }
        .product-card:nth-child(3) { animation-delay: 0.15s; }
        .product-card:nth-child(n+4) { animation-delay: calc((var(--card-index, 1) - 1) * 50ms); }

        .product-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .product-image-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 3 / 4;
            background: var(--bg-primary);
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            color: var(--text-tertiary);
            font-size: 3rem;
        }

        .product-badges {
            position: absolute;
            top: var(--spacing-lg);
            left: var(--spacing-lg);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            z-index: 2;
        }

        .badge {
            display: inline-block;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--accent);
            color: white;
        }

        .wishlist-btn {
            position: absolute;
            top: var(--spacing-lg);
            right: var(--spacing-lg);
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--text-secondary);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
            z-index: 3;
        }

        .wishlist-btn:hover {
            background: var(--accent);
            color: white;
            transform: scale(1.1);
        }

        .wishlist-btn.active {
            color: #e74c3c;
        }

        .quick-add-btn {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: var(--spacing) var(--spacing-lg);
            background: rgba(139, 157, 132, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(100%);
            z-index: 4;
        }

        .product-card:hover .quick-add-btn {
            opacity: 1;
            transform: translateY(0);
        }

        .quick-add-btn:hover {
            background: rgba(107, 125, 100, 0.95);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           PRODUCT INFO
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .product-info {
            padding: var(--spacing-lg);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            flex: 1;
        }

        .product-brand {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-tertiary);
        }

        .product-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-meta {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: auto;
        }

        .color-dots {
            display: flex;
            gap: var(--spacing-xs);
            margin-top: auto;
        }

        .color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 1px solid var(--border);
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: var(--spacing-sm);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           EMPTY STATE
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .empty-state {
            text-align: center;
            padding: var(--spacing-2xl) var(--spacing-lg);
            grid-column: 1 / -1;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--border);
            margin-bottom: var(--spacing-lg);
            opacity: 0.5;
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--text-primary);
            margin-bottom: var(--spacing);
        }

        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: var(--spacing-lg);
        }

        .empty-cta {
            display: inline-block;
            padding: var(--spacing) var(--spacing-xl);
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .empty-cta:hover {
            background: var(--accent-dark);
            transform: translateY(-1px);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           PAGINATION
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-xl) 0;
            border-top: 1px solid var(--border-light);
        }

        .pagination-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 var(--spacing-sm);
            border: 1px solid var(--border-light);
            border-radius: 4px;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .pagination-link:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .pagination-link.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        .pagination-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           MOBILE FILTER DRAWER
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .filter-drawer {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-white);
            border-top: 1px solid var(--border-light);
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.1);
            z-index: 1000;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease-out;
        }

        .filter-drawer.active {
            display: block;
        }

        .filter-drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--border-light);
            position: sticky;
            top: 0;
            background: var(--bg-white);
        }

        .filter-drawer-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .filter-drawer-content {
            padding: var(--spacing-lg);
        }

        .filter-drawer-actions {
            display: flex;
            gap: var(--spacing);
            padding: var(--spacing-lg);
            border-top: 1px solid var(--border-light);
            position: sticky;
            bottom: 0;
            background: var(--bg-white);
        }

        .filter-drawer-actions button {
            flex: 1;
            padding: var(--spacing) var(--spacing-lg);
            border-radius: 4px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-apply {
            background: var(--accent);
            color: white;
        }

        .filter-apply:hover {
            background: var(--accent-dark);
        }

        .filter-reset {
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-light);
        }

        .filter-reset:hover {
            background: var(--border-light);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           ANIMATIONS
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           RESPONSIVE - TABLET
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        @media (max-width: 1024px) {
            .shop-wrapper {
                grid-template-columns: 220px 1fr;
                gap: var(--spacing-lg);
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing-lg);
            }

            .navbar-nav {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           RESPONSIVE - MOBILE
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        @media (max-width: 768px) {
            .shop-wrapper {
                grid-template-columns: 1fr;
                gap: 0;
                padding: 0;
            }

            .filters-panel {
                display: none;
            }

            .navbar-actions {
                gap: var(--spacing-sm);
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .hero {
                padding: 60px var(--spacing-lg);
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .shop-header-bar {
                flex-wrap: wrap;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing);
            }

            .product-card {
                border-radius: 10px;
            }

            .product-info {
                padding: var(--spacing);
            }

            .product-name {
                font-size: 0.85rem;
            }

            .product-price {
                font-size: 1rem;
            }

            .shop-content {
                padding: var(--spacing-lg);
            }

            .filter-btn {
                display: inline-flex;
                align-items: center;
                gap: var(--spacing-xs);
                background: var(--bg-white);
                border: 1px solid var(--border-light);
                padding: var(--spacing-sm) var(--spacing);
                border-radius: 4px;
                color: var(--text-primary);
                cursor: pointer;
                font-weight: 500;
                font-size: 0.85rem;
            }

            .filter-btn:hover {
                border-color: var(--accent);
                color: var(--accent);
            }

            .shop-header-bar {
                margin-bottom: var(--spacing-lg);
            }
        }

        @media (max-width: 480px) {
            .navbar-container {
                padding: 0 var(--spacing);
                gap: var(--spacing);
            }

            .navbar-brand {
                font-size: 1rem;
            }

            .user-menu {
                display: none;
            }

            .navbar-actions {
                gap: var(--spacing-sm);
            }

            .hero {
                padding: 40px var(--spacing);
            }

            .hero h1 {
                font-size: 1.5rem;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing-sm);
            }

            .product-info {
                padding: var(--spacing-sm);
            }

            .shop-content {
                padding: var(--spacing);
            }

            .shop-title h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="home.php" class="navbar-brand">
                <i class="fas fa-shopping-bag"></i>
                E-Store
            </a>
            
            <div class="navbar-nav">
                <a href="home.php" class="nav-link">Home</a>
                <a href="shop.php" class="nav-link">Shop</a>
                <a href="#" class="nav-link">Men</a>
                <a href="#" class="nav-link">Women</a>
            </div>

            <div class="navbar-actions">
                <button class="nav-icon-btn" title="Search">
                    <i class="fas fa-search"></i>
                </button>
                <button class="nav-icon-btn" title="Wishlist">
                    <i class="fas fa-heart"></i>
                </button>
                <button class="nav-icon-btn" title="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge">0</span>
                </button>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Sign out</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover Our Collection</h1>
            <p>Curated styles for the modern wardrobe</p>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <div class="container">
        <div class="shop-wrapper">
            <!-- FILTERS SIDEBAR -->
            <aside class="filters-panel">
                <h3 class="filter-title-main">
                    <i class="fas fa-sliders-h"></i>Filters
                </h3>

                <!-- Search -->
                <div class="filter-group">
                    <label class="filter-label" onclick="this.parentElement.classList.toggle('collapsed')">
                        Search
                        <i class="fas fa-chevron-right filter-toggle"></i>
                    </label>
                    <div class="filter-content">
                        <form method="GET" style="margin-top: var(--spacing-sm);">
                            <input type="text" name="search" placeholder="Search products..." style="width: 100%; padding: var(--spacing-sm); border: 1px solid var(--border-light); border-radius: 4px; font-family: 'DM Sans', sans-serif;" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                        </form>
                    </div>
                </div>

                <!-- Category -->
                <?php if (!empty($categories)): ?>
                <div class="filter-group">
                    <label class="filter-label" onclick="this.parentElement.classList.toggle('collapsed')">
                        Category
                        <i class="fas fa-chevron-right filter-toggle"></i>
                    </label>
                    <div class="filter-content">
                        <?php foreach ($categories as $cat): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="cat-<?php echo (int)$cat['id']; ?>" name="category" value="<?php echo (int)$cat['id']; ?>" <?php echo ($categoryId == $cat['id']) ? 'checked' : ''; ?>>
                            <label for="cat-<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Brand -->
                <?php if (!empty($brands)): ?>
                <div class="filter-group">
                    <label class="filter-label" onclick="this.parentElement.classList.toggle('collapsed')">
                        Brand
                        <i class="fas fa-chevron-right filter-toggle"></i>
                    </label>
                    <div class="filter-content">
                        <?php foreach ($brands as $b): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="brand-<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>" name="brand" value="<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($brand === $b['brand']) ? 'checked' : ''; ?>>
                            <label for="brand-<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Size -->
                <?php if (!empty($sizes)): ?>
                <div class="filter-group">
                    <label class="filter-label" onclick="this.parentElement.classList.toggle('collapsed')">
                        Size
                        <i class="fas fa-chevron-right filter-toggle"></i>
                    </label>
                    <div class="filter-content">
                        <div class="size-pills">
                            <?php foreach ($sizes as $s): ?>
                            <button type="button" class="size-pill" data-size="<?php echo htmlspecialchars($s['size'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($size === $s['size']) ? 'class="size-pill active"' : ''; ?>>
                                <?php echo htmlspecialchars($s['size'], ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Price Range -->
                <div class="filter-group">
                    <label class="filter-label" onclick="this.parentElement.classList.toggle('collapsed')">
                        Price
                        <i class="fas fa-chevron-right filter-toggle"></i>
                    </label>
                    <div class="filter-content">
                        <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-sm);">
                            <input type="number" name="min_price" placeholder="Min" style="flex: 1; padding: var(--spacing-sm); border: 1px solid var(--border-light); border-radius: 4px; font-family: 'DM Sans', sans-serif;" value="<?php echo htmlspecialchars($minPrice, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="number" name="max_price" placeholder="Max" style="flex: 1; padding: var(--spacing-sm); border: 1px solid var(--border-light); border-radius: 4px; font-family: 'DM Sans', sans-serif;" value="<?php echo htmlspecialchars($maxPrice, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                </div>
            </aside>

            <!-- MAIN SHOP CONTENT -->
            <main class="shop-content">
                <!-- Header Bar -->
                <div class="shop-header-bar">
                    <div class="shop-title">
                        <h2><?php echo $categoryId ? 'Collection' : 'All Products'; ?></h2>
                        <span class="results-count"><?php echo $totalProducts; ?> items</span>
                    </div>

                    <div class="sort-control">
                        <label for="sort-select">Sort by:</label>
                        <select id="sort-select" class="sort-select" onchange="location.href='?sort=' + this.value + (window.location.search ? '&' + window.location.search.substring(1).replace(/sort=[^&]*/gi, '') : '');">
                            <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest</option>
                            <option value="price-asc" <?php echo ($sort === 'price-asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-desc" <?php echo ($sort === 'price-desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo ($sort === 'name') ? 'selected' : ''; ?>>Name: A-Z</option>
                        </select>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <?php
                $activeFilters = [];
                if ($search) $activeFilters[] = ['name' => 'Search', 'value' => $search];
                if ($categoryId) {
                    $catName = array_column($categories, 'name', 'id')[$categoryId] ?? 'Category';
                    $activeFilters[] = ['name' => 'Category', 'value' => $catName];
                }
                if ($brand) $activeFilters[] = ['name' => 'Brand', 'value' => $brand];
                if ($color) $activeFilters[] = ['name' => 'Color', 'value' => $color];
                if ($size) $activeFilters[] = ['name' => 'Size', 'value' => $size];
                if ($minPrice) $activeFilters[] = ['name' => 'Min Price', 'value' => '$' . $minPrice];
                if ($maxPrice) $activeFilters[] = ['name' => 'Max Price', 'value' => '$' . $maxPrice];
                ?>
                
                <?php if (!empty($activeFilters)): ?>
                <div class="active-filters">
                    <?php foreach ($activeFilters as $filter): ?>
                    <span class="filter-tag">
                        <?php echo htmlspecialchars($filter['value'], ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" onclick="removeFilter('<?php echo htmlspecialchars($filter['name'], ENT_QUOTES, 'UTF-8'); ?>')">×</button>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Products Grid -->
                <?php if (count($products) === 0): ?>
                    <div class="products-grid">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3>No products found</h3>
                            <p>Try adjusting your filters or explore our other collections</p>
                            <a href="shop.php" class="empty-cta">View All Products</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $index => $product): ?>
                        <div class="product-card" style="--card-index: <?php echo $index + 1; ?>;">
                            <!-- Product Image -->
                            <div class="product-image-wrapper">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" class="product-image">
                                <?php else: ?>
                                    <div class="product-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Badges -->
                                <div class="product-badges">
                                    <?php if ((int)$product['stock'] <= 0): ?>
                                        <span class="badge">Out of Stock</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Wishlist Button -->
                                <button class="wishlist-btn" title="Add to wishlist">
                                    <i class="far fa-heart"></i>
                                </button>

                                <!-- Quick Add Button -->
                                <?php if ((int)$product['stock'] > 0): ?>
                                <form method="POST" action="cart_action.php" style="display: contents;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                    <button type="submit" class="quick-add-btn">Quick Add</button>
                                </form>
                                <?php endif; ?>
                            </div>

                            <!-- Product Info -->
                            <div class="product-info">
                                <div class="product-brand">
                                    <?php echo htmlspecialchars($product['brand'] ?? $product['category_name'] ?? 'Brand', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <h3 class="product-name">
                                    <a href="product.php?id=<?php echo (int)$product['id']; ?>" style="color: inherit; text-decoration: none;">
                                        <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </h3>
                                
                                <!-- Color Dots -->
                                <?php if (!empty($product['color'])): ?>
                                <div class="color-dots">
                                    <div class="color-dot" style="background-color: <?php echo htmlspecialchars($product['color'], ENT_QUOTES, 'UTF-8'); ?>;"></div>
                                </div>
                                <?php endif; ?>

                                <!-- Price -->
                                <div class="product-price">
                                    $<?php echo number_format((float)$product['price'], 2); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination-container">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&sort=<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>" class="pagination-link">← Previous</a>
                        <?php endif; ?>

                        <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                            <?php if ($p === $page): ?>
                            <span class="pagination-link active"><?php echo $p; ?></span>
                            <?php else: ?>
                            <a href="?page=<?php echo $p; ?>&sort=<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>" class="pagination-link"><?php echo $p; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&sort=<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>" class="pagination-link">Next →</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Filter Drawer (Mobile) -->
    <div class="filter-drawer" id="filterDrawer">
        <div class="filter-drawer-header">
            <h3>Filters</h3>
            <button class="filter-drawer-close" onclick="closeFilterDrawer()">✕</button>
        </div>
        <div class="filter-drawer-content">
            <!-- Filters will be duplicated here for mobile -->
        </div>
        <div class="filter-drawer-actions">
            <button class="filter-apply" onclick="applyMobileFilters()">Apply Filters</button>
            <button class="filter-reset" onclick="resetMobileFilters()">Reset</button>
        </div>
    </div>

    <script>
        // Navbar shrink on scroll
        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('shrink');
            } else {
                navbar.classList.remove('shrink');
            }
        });

        // Wishlist functionality
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
            });
        });

        // Mobile filter drawer
        function openFilterDrawer() {
            document.getElementById('filterDrawer').classList.add('active');
        }

        function closeFilterDrawer() {
            document.getElementById('filterDrawer').classList.remove('active');
        }

        function applyMobileFilters() {
            // Collect filters and redirect
            const formData = new FormData();
            formData.append('search', document.querySelector('input[name="search"]')?.value || '');
            closeFilterDrawer();
        }

        function resetMobileFilters() {
            location.href = 'shop.php';
        }

        function removeFilter(filterName) {
            // Build URL without the specific filter
            let url = new URL(window.location);
            switch(filterName) {
                case 'Search': url.searchParams.delete('search'); break;
                case 'Category': url.searchParams.delete('category'); break;
                case 'Brand': url.searchParams.delete('brand'); break;
                case 'Color': url.searchParams.delete('color'); break;
                case 'Size': url.searchParams.delete('size'); break;
                case 'Min Price': url.searchParams.delete('min_price'); break;
                case 'Max Price': url.searchParams.delete('max_price'); break;
            }
            window.location = url.toString();
        }

        // Size pill selection
        document.querySelectorAll('.size-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                document.querySelectorAll('.size-pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Accordion collapsing
        document.querySelectorAll('.filter-label').forEach(label => {
            label.addEventListener('click', function(e) {
                e.preventDefault();
                this.parentElement.classList.toggle('collapsed');
            });
        });
    </script>
</body>
</html>
