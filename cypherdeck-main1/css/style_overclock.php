<style>
    :root {
    --theme-color: #00ff66; /* bright green to match other pages */
    }

    body {
        background-color: #111;
        color: var(--theme-color);
        transition: background-color 0.3s, color 0.3s;
        font-family: "Courier New", Courier, monospace;
    }

    .navbar.border-bottom {
        border-color: var(--theme-color) !important;
    }

    .navbar .nav-link.active {
        color: var(--theme-color) !important;
    }

    .main-box {
        background-color: #000;
        border: 2px solid var(--theme-color);
        border-radius: 0.5rem;
        padding: 2rem;
        box-shadow: 0 0 20px var(--theme-color);
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .btn-theme {
        border-color: var(--theme-color);
        color: var(--theme-color);
        transition: all 0.2s;
    }

    .btn-theme.active {
        background-color: var(--theme-color);
        color: #000;
        box-shadow: 0 0 15px var(--theme-color);
    }

    .go-btn {
        display: none;
        background-color: var(--theme-color);
        color: #000;
        border: none;
        margin-top: 1rem;
        padding: 0.75rem 2rem;
        font-size: 1.25rem;
        border-radius: 0.5rem;
        transition: background-color 0.3s, box-shadow 0.3s;
        box-shadow: 0 0 10px var(--theme-color);
    }

    .go-btn:hover {
        box-shadow: 0 0 20px var(--theme-color);
    }

    .main-box h1.display-3 {
    color: var(--theme-color);
    text-shadow: 0 0 5px var(--theme-color), 0 0 10px var(--theme-color), 0 0 20px var(--theme-color);
    transition: color 0.3s, text-shadow 0.3s;
    }


.navbar-brand {
    color: var(--theme-color);
    text-shadow: 0 0 5px var(--theme-color), 0 0 10px var(--theme-color), 0 0 20px var(--theme-color);
    font-family: "Courier New", monospace;
    transition: color 0.3s, text-shadow 0.3s;
}

    .navbar {
        border-bottom: 2px solid var(--theme-color);
        box-shadow: 0 2px 10px var(--theme-color);
    }

    .difficulty-heading {
        color: var(--theme-color);
        text-shadow: 0 0 8px var(--theme-color);
        transition: color 0.3s, text-shadow 0.3s;
    }
</style>