<?php require_once('php/_stock.php'); ?>
<?php require_once('php/_editstockoptionform.php'); ?>
<?php AcctAuth(); ?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Update Stock Description</title>
<meta name="description" content="This English web page works together with php/_submitstockoptions.php and php/_editstockoptionform.php to update a stock description.">
<link href="../../common/style.css" rel="stylesheet" type="text/css" />
</head>

<body bgproperties=fixed leftmargin=0 topmargin=0 onLoad=OnLoad()>
<?php _LayoutTopLeft(false); ?>

<div>
<h1>Update Stock Description</h1>
<?php StockOptionEditForm(STOCK_OPTION_EDIT); ?>
</div>

<?php LayoutTail(false); ?>

</body>
</html>
