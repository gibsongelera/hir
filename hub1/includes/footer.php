</div>
<?php if (!isset($hide_footer)): ?>
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container text-center">
        <p class="mb-1"><i class="fas fa-seedling text-success"></i> <?= APP_NAME ?> &mdash; <?= APP_TAGLINE ?></p>
        <p class="text-muted small mb-0">ZPPSU &copy; <?= date('Y') ?>. All rights reserved.</p>
    </div>
</footer>
<?php endif; ?>
<?php if (isset($use_leaflet) && $use_leaflet): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    function initToasts(){
        document.querySelectorAll('.crh-toast[data-auto-dismiss]').forEach(function(toast){
            var duration = parseInt(toast.getAttribute('data-auto-dismiss')) || 5000;
            var bar = toast.querySelector('.crh-toast__progress');
            if(bar) bar.style.animationDuration = duration + 'ms';

            var timer = setTimeout(function(){ dismissToast(toast); }, duration);

            toast.addEventListener('mouseenter', function(){
                clearTimeout(timer);
                if(bar) bar.style.animationPlayState = 'paused';
            });
            toast.addEventListener('mouseleave', function(){
                var remaining = bar ? (bar.getBoundingClientRect().width / toast.getBoundingClientRect().width) * duration : 2000;
                if(bar) bar.style.animationPlayState = 'running';
                timer = setTimeout(function(){ dismissToast(toast); }, Math.max(remaining, 800));
            });

            var closeBtn = toast.querySelector('.crh-toast__close');
            if(closeBtn) closeBtn.addEventListener('click', function(){ clearTimeout(timer); dismissToast(toast); });
        });
    }

    function dismissToast(el){
        el.classList.add('crh-toast--exit');
        el.addEventListener('animationend', function(){ el.remove(); }, {once:true});
    }

    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initToasts);
    else initToasts();

    window.crhToast = function(type, message, title, duration){
        var icons = {success:'fa-check-circle', danger:'fa-exclamation-circle', warning:'fa-exclamation-triangle', info:'fa-info-circle'};
        var titles = {success:'Success', danger:'Error', warning:'Warning', info:'Information'};
        var container = document.getElementById('crhToastContainer');
        if(!container){
            container = document.createElement('div');
            container.className = 'crh-toast-container';
            container.id = 'crhToastContainer';
            document.body.appendChild(container);
        }
        var d = duration || 5000;
        var t = document.createElement('div');
        t.className = 'crh-toast crh-toast--' + type;
        t.setAttribute('role', 'alert');
        t.setAttribute('data-auto-dismiss', d);
        t.innerHTML =
            '<div class="crh-toast__icon"><i class="fas ' + (icons[type]||'fa-bell') + '"></i></div>' +
            '<div class="crh-toast__body"><strong class="crh-toast__title">' + (title || titles[type] || 'Notice') + '</strong>' +
            '<p class="crh-toast__msg">' + message + '</p></div>' +
            '<button type="button" class="crh-toast__close" aria-label="Close"><i class="fas fa-times"></i></button>' +
            '<div class="crh-toast__progress"></div>';
        container.appendChild(t);
        initSingleToast(t, d);
    };

    function initSingleToast(toast, duration){
        var bar = toast.querySelector('.crh-toast__progress');
        if(bar) bar.style.animationDuration = duration + 'ms';
        var timer = setTimeout(function(){ dismissToast(toast); }, duration);
        toast.addEventListener('mouseenter', function(){ clearTimeout(timer); if(bar) bar.style.animationPlayState='paused'; });
        toast.addEventListener('mouseleave', function(){
            if(bar) bar.style.animationPlayState='running';
            timer = setTimeout(function(){ dismissToast(toast); }, 2000);
        });
        var closeBtn = toast.querySelector('.crh-toast__close');
        if(closeBtn) closeBtn.addEventListener('click', function(){ clearTimeout(timer); dismissToast(toast); });
    }
})();
</script>
<?php if (isset($extra_js)): ?>
<script src="<?= APP_URL ?>/assets/js/<?= $extra_js ?>"></script>
<?php endif; ?>
<?php if (isset($inline_js)): ?>
<script><?= $inline_js ?></script>
<?php endif; ?>
</body>
</html>
