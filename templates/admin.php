<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php $ui = get_sympal_ui() ?>
  <?php $editor = get_sympal_editor() ?>
  <?php $flash = get_sympal_flash() ?>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php include_stylesheets() ?>
  <?php include_javascripts() ?>
</head>
<body class="yui-skin-sam">

  <?php echo $ui ?>

  <div id="container">
  <!-- header -->
  <div id="header">
    <div id="logo"><?php echo link_to(image_tag('/sfSympalPlugin/images/spacer.gif'), '@homepage', 'id=logo_spacer') ?></div>

    <h1>Sympal Admin</h1>
  </div>
  <!-- end header -->

  <!-- content -->
  <div id="content">

  <?php echo $flash ?>

  <!-- left column -->
  <div id="column_left">
    <?php echo $sf_content ?>
  </div>
  <!-- end left column -->

  <?php $subMenu = get_sympal_menu(sfSympalToolkit::getCurrentMenuItem(), true) ?>
  <?php if (has_slot('sympal_right_sidebar') || $subMenu): ?>
    <?php use_stylesheet('/sfSympalPlugin/css/right.css', 'last') ?>
    <!-- right column -->
    <div id="column_right">
     <br />
     <div class="roundedbox">
      <div class="roundedbox_head"><div></div></div>
      <div class="roundedbox_body">
        <?php echo get_slot('sympal_right_sidebar') ?>

        <?php if ($subMenu): ?>
          <div id="sympal_sub_menu">
            <?php echo $subMenu ?>
          </div>
        <?php endif; ?>
      </div>
     </div>
    </div>
    <!-- end right column -->
  <?php endif; ?>

  <br style="clear: both;" />

  </div>
  <!-- end content -->

  <!-- box_footer -->
  <div id="box_footer">
  </div>
  <!-- end box_footer -->
  </div>

  <!-- footer -->
  <div id="footer">
  <p>
    Brought to you by <?php echo link_to(image_tag('/sfSympalPlugin/images/sensio_labs_button.gif'), 'http://www.sensiolabs.com', 'target=_BLANK') ?>.<br/>
    Powered by <?php echo link_to(image_tag('/sfSympalPlugin/images/symfony_button.gif'), 'http://www.symfony-project.org', 'target=_BLANK') ?> 
    and <?php echo link_to(image_tag('/sfSympalPlugin/images/doctrine_button.gif'), 'http://www.doctrine-project.org', 'target=_BLANK') ?>
  </p>
  <?php echo get_sympal_menu('footer') ?>
  </div>
  <!-- end footer -->

  <script type="text/javascript">
   var uservoiceJsHost = ("https:" == document.location.protocol) ? "https://uservoice.com" : "http://cdn.uservoice.com";
   document.write(unescape("%3Cscript src='" + uservoiceJsHost + "/javascripts/widgets/tab.js' type='text/javascript'%3E%3C/script%3E"))
  </script>
  <script type="text/javascript">
  UserVoice.Tab.show({ 
   key: 'sympal',
   host: 'sympal.uservoice.com', 
   forum: 'general', 
   alignment: 'left',
   background_color:'#f00', 
   text_color: 'white',
   hover_color: '#06C',
   lang: '<?php echo $sf_user->getCulture() ?>'
  })
  </script>

  <?php echo $editor ?>
</body>
</html>