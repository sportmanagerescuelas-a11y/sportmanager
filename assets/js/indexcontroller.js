(function(){
    // Detectar soporte touch
    var isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    // Dropdown toggle para dispositivos táctiles (solo si es touch)
    function initDropdownTouch(){
        var toggles = document.querySelectorAll('.nav-item.dropdown > .nav-link');
        toggles.forEach(function(toggle){
            toggle.addEventListener('click', function(e){
                e.preventDefault();
                var parent = toggle.parentElement;
                parent.classList.toggle('show');
                var menu = parent.querySelector('.dropdown-menu');
                if(parent.classList.contains('show')){
                    menu.classList.add('show');
                } else {
                    menu.classList.remove('show');
                }
            });
        });
    }

    // Lee la variable CSS --navbar-overlap y devuelve un número en px
    function getNavbarOverlap(){
        var val = getComputedStyle(document.documentElement).getPropertyValue('--navbar-overlap');
        if(!val) return 62; // fallback
        var n = parseInt(val, 10);
        return isNaN(n) ? 62 : n;
    }

    // Scroll suave a una sección por id, compensando la navbar solapada
    function scrollToSectionById(id){
        if(!id) return;
        var el = document.getElementById(id);
        if(!el) return;
        var offset = getNavbarOverlap() + 10; // pequeño margen extra
        var top = el.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    // Inicializa handlers de los enlaces de la navbar y marca active en click.
    function initNavLinks(){
        var links = document.querySelectorAll('.navbar-nav .nav-link');
        links.forEach(function(link){
            link.addEventListener('click', function(e){
                // Ignorar toggles de dropdown
                if (link.closest('.dropdown')) return;

                var href = link.getAttribute('href');
                // Siempre marcar el enlace como activo en el click (antes de scroll)
                document.querySelectorAll('.navbar-nav .nav-link').forEach(function(n){ n.classList.remove('active'); });
                link.classList.add('active');

                // Suprimir temporalmente el observer para evitar que sobrescriba la selección
                window.__navClickSuppress = true;
                setTimeout(function(){ window.__navClickSuppress = false; }, 900);

                if(href && href.indexOf('#') === 0){
                    var id = href.slice(1);
                    var target = document.getElementById(id);
                    if(target){
                        e.preventDefault();
                        scrollToSectionById(id);
                    }
                }
            });
        });
    }

    function onReady(){
        if(isTouch) initDropdownTouch();
        initNavLinks();
        // Inicializar marcado de enlace activo según la sección visible
        initActiveOnScroll();

        initBackToTop();

        // Si la página se carga con un hash, hacer scroll al objetivo
        if(window.location.hash){
            var id = window.location.hash.slice(1);
            // retraso pequeño para que el layout esté listo
            setTimeout(function(){ scrollToSectionById(id); }, 60);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        // ya cargado
        onReady();
    }

})();

// Función: observa secciones y marca el link de la navbar correspondiente
function initActiveOnScroll(){
    var sections = document.querySelectorAll('section[id]');
    if(!sections || sections.length === 0) return;

    var navLinksSelector = '.navbar-nav a[href^="#"]';

    // Calcula rootMargin superior para compensar la navbar solapada
    var topOffset = (function(){
        var val = getComputedStyle(document.documentElement).getPropertyValue('--navbar-overlap');
        var n = parseInt(val, 10);
        return (isNaN(n) ? 62 : n) + 10; // pequeño margen extra
    })();

    var observerOptions = {
        root: null,
        rootMargin: `-${topOffset}px 0px -40% 0px`,
        threshold: [0.25, 0.5, 0.75]
    };

    var observer = new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
            if(entry.isIntersecting){
                // Si hemos hecho click recientemente, suprimir actualización por observer
                if (window.__navClickSuppress) return;
                var id = entry.target.id;
                // remover active en todos
                document.querySelectorAll('.navbar-nav .nav-link').forEach(function(n){ n.classList.remove('active'); });
                var link = document.querySelector(navLinksSelector + '[href="#' + id + '"]');
                if(link) link.classList.add('active');
            }
        });
    }, observerOptions);

    sections.forEach(function(s){ observer.observe(s); });
}

// Back to top: muestra el botón cuando el usuario ha scrolleado hacia abajo
function initBackToTop(){
    const btn = document.getElementById('backToTop');
    if (!btn) {
        console.warn('initBackToTop: backToTop button not found');
        return;
    }
    const showAfter = 240; // px
    function check() {
        const sc = window.scrollY || document.documentElement.scrollTop;
        // Si estamos muy cerca del top, ocultar
        if (sc <= 0 || sc <= showAfter) {
            if (btn.classList.contains('visible')) {
                btn.classList.remove('visible');
                btn.setAttribute('aria-hidden', 'true');
            }
            return;
        }
        // en caso contrario mostrar
        if (!btn.classList.contains('visible')) {
            btn.classList.add('visible');
            btn.setAttribute('aria-hidden', 'false');
        }
    }
    // initial check in case page loaded scrolled
    check();
    window.addEventListener('scroll', throttle(check, 150));
    btn.addEventListener('click', function(e){
        e.preventDefault();
        // comportamiento principal: scroll suave
        try {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (err) {
            // fallback inmediato
            document.documentElement.scrollTop = 0;
            document.body.scrollTop = 0;
        }
    });

    // init complete
}

// Simple throttle
function throttle(fn, wait){
    var time = Date.now();
    return function(){
        if ((time + wait - Date.now()) < 0) {
            fn();
            time = Date.now();
        }
    }
}