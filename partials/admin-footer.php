<?php
// partials/admin-footer.php
?>
</main><!-- /.admin-content -->
</div><!-- /.admin-main -->
</div><!-- /.admin-layout -->
<script src="/assets/js/admin.js?v=<?= filemtime(__DIR__ . '/../assets/js/admin.js') ?>"></script>
<script>if(typeof lucide!=='undefined')lucide.createIcons();</script>
<?php if (isset($extra_js)): ?>
<script><?= $extra_js ?></script>
<?php endif; ?>
</body>
</html>
