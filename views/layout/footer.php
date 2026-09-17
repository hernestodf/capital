  </div>
</div>

<!-- TOAST CONTAINER -->
<?php 
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/toast/toast.php';
echo renderToastContainer(); 
?>

<!-- SCRIPTS -->
<script src="<?= $baseUrl ?>/js/scripts.js"></script>
<script>
// Off-canvas
function openLeft() { document.getElementById('left-canvas').classList.add('open'); document.getElementById('overlay-left').classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeLeft() { document.getElementById('left-canvas').classList.remove('open'); document.getElementById('overlay-left').classList.remove('active'); document.body.style.overflow = ''; }
function openRight() { document.getElementById('right-canvas').classList.add('open'); document.getElementById('overlay-right').classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeRight() { document.getElementById('right-canvas').classList.remove('open'); document.getElementById('overlay-right').classList.remove('active'); document.body.style.overflow = ''; }
function selectChip(el) { el.parentElement.querySelectorAll('.lc-chip').forEach(function(c) { c.classList.remove('active'); }); el.classList.add('active'); }

// Global variables for JavaScript modules
window.BASE_URL = '<?= $baseUrl ?>';
window.CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
</script>
</body>
</html>
