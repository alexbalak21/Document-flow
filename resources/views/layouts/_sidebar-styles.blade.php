{{-- Sidebar & layout inline styles --}}
<style>
    :root { --sidebar-width: 240px; }

    body { background: #f4f6f9; }

    /* Sidebar */
    #sidebar {
        position: fixed;
        top: 0; left: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background: #1e2533;
        display: flex;
        flex-direction: column;
        transition: width .2s ease;
        z-index: 1000;
        overflow: hidden;
    }

    #sidebar.collapsed { width: 56px; }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 16px;
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        border-bottom: 1px solid rgba(255,255,255,.08);
        white-space: nowrap;
        text-decoration: none;
    }

    .sidebar-brand .brand-icon { font-size: 20px; flex-shrink: 0; color: #60a5fa; }

    .sidebar-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255,255,255,.35);
        padding: 14px 16px 4px;
        white-space: nowrap;
    }

    .sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding-bottom: 12px; }

    .nav-item-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 16px;
        color: rgba(255,255,255,.7);
        text-decoration: none;
        font-size: 13.5px;
        border-left: 3px solid transparent;
        white-space: nowrap;
        transition: background .15s, color .15s;
    }

    .nav-item-link:hover,
    .nav-item-link.active {
        background: rgba(255,255,255,.07);
        color: #fff;
        border-left-color: #60a5fa;
    }

    .nav-item-link i { font-size: 15px; flex-shrink: 0; width: 20px; text-align: center; }

    .nav-group-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 16px;
        color: rgba(255,255,255,.7);
        font-size: 13.5px;
        cursor: pointer;
        border-left: 3px solid transparent;
        white-space: nowrap;
        user-select: none;
        transition: background .15s;
    }

    .nav-group-toggle:hover { background: rgba(255,255,255,.07); color: #fff; }
    .nav-group-toggle i.icon { font-size: 15px; flex-shrink: 0; width: 20px; text-align: center; }
    .nav-group-toggle .chevron { margin-left: auto; font-size: 11px; transition: transform .2s; }
    .nav-group-toggle.open .chevron { transform: rotate(90deg); }

    .nav-group-children { padding-left: 16px; }

    .nav-group-children .nav-item-link {
        font-size: 13px;
        padding: 7px 16px;
        color: rgba(255,255,255,.55);
    }

    .sidebar-footer {
        border-top: 1px solid rgba(255,255,255,.08);
        padding: 10px 8px;
    }

    .sidebar-footer .nav-item-link { font-size: 13px; border-radius: 6px; border-left: none; }

    #sidebar-toggle {
        position: fixed;
        top: 14px;
        left: calc(var(--sidebar-width) - 14px);
        z-index: 1100;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #dee2e6;
        box-shadow: 0 1px 4px rgba(0,0,0,.12);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: left .2s ease;
        color: #6c757d;
        font-size: 11px;
    }

    #sidebar.collapsed ~ #sidebar-toggle { left: 42px; }

    #main {
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        transition: margin-left .2s ease;
    }

    #sidebar.collapsed ~ #main { margin-left: 56px; }

    #sidebar.collapsed .sidebar-label,
    #sidebar.collapsed .nav-text,
    #sidebar.collapsed .chevron,
    #sidebar.collapsed .brand-text { display: none; }

    .topbar {
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 10px 24px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }
</style>
