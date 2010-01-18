<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php sympal_minify() ?>
  <?php include_stylesheets() ?>
  <?php include_javascripts() ?>
</head>
<body>

  <div id="sympal_ajax_loading">
    Loading...
  </div>

  <div id="container">

  <!-- content -->
  <div id="content">

  <?php if ($sf_sympal_context->getSite() && $sf_user->isAuthenticated()): ?>
    <div id="header">
      <h1><?php echo $sf_sympal_context->getSite()->getTitle() ?> <?php echo sfSympalConfig::getCurrentVersion() ?> Admin</h1>
    </div>
  <?php endif; ?>

  <?php echo get_sympal_flash() ?>
  <?php echo $sf_content ?>

  </div>
  <!-- end content -->
  <br style="clear: both;" />
  </div>
</body>
</html>