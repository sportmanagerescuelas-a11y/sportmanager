(function(){
    var isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

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

    function getNavbarOverlap(){
        var val = getComputedStyle(document.documentElement).getPropertyValue('--navbar-overlap');
        if(!val) return 62;
        var n = parseInt(val, 10);
        return isNaN(n) ? 62 : n;
    }

    function scrollToSectionById(id){
        if(!id) return;
        var el = document.getElementById(id);
        if(!el) return;
        var offset = getNavbarOverlap() + 10;
        var top = el.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    function initNavLinks(){
        var links = document.querySelectorAll('.navbar-nav .nav-link');
        links.forEach(function(link){
            link.addEventListener('click', function(e){
                if (link.closest('.dropdown')) return;

                var href = link.getAttribute('href');
                document.querySelectorAll('.navbar-nav .nav-link').forEach(function(n){ n.classList.remove('active'); });
                link.classList.add('active');

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

    function initRevealAnimations(){
        var items = document.querySelectorAll('.home-page .home-reveal');
        if (!items.length) return;

        if (!('IntersectionObserver' in window)) {
            items.forEach(function(item){
                item.classList.add('is-visible');
            });
            return;
        }

        var observer = new IntersectionObserver(function(entries, obs){
            entries.forEach(function(entry){
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, {
            root: null,
            rootMargin: '0px 0px -10% 0px',
            threshold: 0.15
        });

        items.forEach(function(item){
            observer.observe(item);
        });
    }

    function onReady(){
        if(isTouch) initDropdownTouch();
        initNavLinks();
        initActiveOnScroll();
        initRevealAnimations();
        initBackToTop();

        if(window.location.hash){
            var id = window.location.hash.slice(1);
            setTimeout(function(){ scrollToSectionById(id); }, 60);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
})();

function initActiveOnScroll(){
    var sections = document.querySelectorAll('section[id]');
    if(!sections || sections.length === 0) return;

    var navLinksSelector = '.navbar-nav a[href^="#"]';
    var topOffset = (function(){
        var val = getComputedStyle(document.documentElement).getPropertyValue('--navbar-overlap');
        var n = parseInt(val, 10);
        return (isNaN(n) ? 62 : n) + 10;
    })();

    var observerOptions = {
        root: null,
        rootMargin: `-${topOffset}px 0px -40% 0px`,
        threshold: [0.25, 0.5, 0.75]
    };

    var observer = new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
            if(entry.isIntersecting){
                if (window.__navClickSuppress) return;
                var id = entry.target.id;
                document.querySelectorAll('.navbar-nav .nav-link').forEach(function(n){ n.classList.remove('active'); });
                var link = document.querySelector(navLinksSelector + '[href="#' + id + '"]');
                if(link) link.classList.add('active');
            }
        });
    }, observerOptions);

    sections.forEach(function(s){ observer.observe(s); });
}

function initBackToTop(){
    const btn = document.getElementById('backToTop');
    if (!btn) {
        console.warn('initBackToTop: backToTop button not found');
        return;
    }
    const showAfter = 240;
    function check() {
        const sc = window.scrollY || document.documentElement.scrollTop;
        if (sc <= 0 || sc <= showAfter) {
            if (btn.classList.contains('visible')) {
                btn.classList.remove('visible');
                btn.setAttribute('aria-hidden', 'true');
            }
            return;
        }
        if (!btn.classList.contains('visible')) {
            btn.classList.add('visible');
            btn.setAttribute('aria-hidden', 'false');
        }
    }
    check();
    window.addEventListener('scroll', throttle(check, 150));
    btn.addEventListener('click', function(e){
        e.preventDefault();
        try {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (err) {
            document.documentElement.scrollTop = 0;
            document.body.scrollTop = 0;
        }
    });
}

function throttle(fn, wait){
    var time = Date.now();
    return function(){
        if ((time + wait - Date.now()) < 0) {
            fn();
            time = Date.now();
        }
    }
}
