<html lang="en" class="no-js">
    <!-- BEGIN HEAD -->
    <head>
        <meta charset="utf-8"/>
        <title>Orancan</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport"/>
        <meta content="" name="description"/>
        <meta content="" name="author"/>

        <!-- GLOBAL MANDATORY STYLES -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">
        <link href="<?= vendor() ?>/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>

        <!-- PAGE LEVEL PLUGIN STYLES -->
        <link href="<?= csspath() ?>style.css" rel="stylesheet">
        <link href="<?= csspath() ?>bootstrap.min.css" rel="stylesheet">
        <link href="<?= vendor() ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        
        <!-- Favicon -->
        <link rel="shortcut icon" href="favicon.ico"/>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" type="text/javascript"></script>

        <link href='https://fonts.googleapis.com/css?family=PT+Sans:400,700' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="https://s3-us-west-2.amazonaws.com/s.cdpn.io/148866/reset.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
        
        <!-- <link href="<?= csspath() ?>style_modal.css" rel="stylesheet"> -->
    </head>
    <!-- END HEAD -->

    <!-- BODY -->
    <body>

        <!--========== HEADER ==========-->
        <div class="container">
            <div class="header-top">
                <div class="row">
                    <div class="col-sm-6">
                        <a herf="#"><img src="assets/images/header-logo.png"/></a>
                    </div>
                    <div class="col-sm-6 pull-right header-top-right">
                        <ul>
                            <li>
                                <a href="#">Sell Your Merch</a>
                            </li>
                            <li>
                                <a class="main-nav" href="#0">Your Account</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <header class="header">
            <div class="container">
                <nav class="navbar navbar-inverse navbar-static-top marginBottom-0" role="navigation">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>
                    <div class="collapse navbar-collapse" id="navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="#">T-shirts</a></li>
                            <li><a href="#">Hoodies</a></li>
                            <li><a href="#">Phone Cases</a></li>
                            <li><a href="#">Wall Art</a></li>
                            <li><a href="#">Accessories</a></li>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>

        <div class="home-banner">
            <div class="container">
                <div class="display-table-cell">
                    <div class="display-table">
                        <h2>Creative Merchandise For Your Favorite Interests.</h2>
                        <div class="banner-search-box">
                            <input type="text" name="fname" placeholder="Find an Artist, Brand, or Something Interesting">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php #$this->load->view('authe/form_login'); ?>
        
       